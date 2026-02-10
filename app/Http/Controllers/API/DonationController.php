<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationType;
use App\Models\Member;
use App\Models\Church;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 20;
        $churchId = auth()->user()->church_id;

        $query = Donation::where('church_id', $churchId)
            ->with(['member:id,first_name,last_name,email,phone', 'donationType:id,name,description', 'recordedBy:id,name'])
            ->orderBy('donation_date', 'desc');

        // Apply filters
        $this->applyFilters($query, $request);

        // For regular users, show only their donations or public info
        if (!auth()->user()->is_admin && !auth()->user()->can('view_all_donations')) {
            $memberId = auth()->user()->member_id;
            $query->where(function($q) use ($memberId) {
                $q->where('member_id', $memberId)
                  ->orWhere('recorded_by', auth()->id());
            });
        }

        $donations = $query->paginate($perPage);

        // Statistics
        $stats = $this->getStatistics($churchId, $request);

        return response()->json([
            'success' => true,
            'data' => $donations,
            'stats' => $stats,
            'filters' => [
                'types' => DonationType::where('church_id', $churchId)->get(['id', 'name']),
                'members' => Member::where('church_id', $churchId)
                    ->active()
                    ->get(['id', 'first_name', 'last_name']),
                'payment_methods' => ['cash', 'check', 'card', 'transfer', 'online', 'other'],
                'frequencies' => ['one-time', 'weekly', 'monthly', 'yearly'],
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'nullable|exists:members,id',
            'donation_type_id' => 'required|exists:donation_types,id',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'currency' => 'required|string|size:3',
            'payment_method' => 'required|in:cash,check,card,transfer,online,other',
            'check_number' => 'nullable|string|required_if:payment_method,check',
            'donation_date' => 'required|date',
            'frequency' => 'required|in:one-time,weekly,monthly,yearly',
            'notes' => 'nullable|string|max:500',
            'is_recurring' => 'boolean',
            'next_payment_date' => 'nullable|date|required_if:is_recurring,true|after_or_equal:today',
            'metadata' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Start transaction
        DB::beginTransaction();
        
        try {
            $donation = Donation::create([
                'church_id' => auth()->user()->church_id,
                'member_id' => $request->member_id,
                'donation_type_id' => $request->donation_type_id,
                'transaction_id' => $this->generateTransactionId(),
                'amount' => $request->amount,
                'currency' => strtoupper($request->currency),
                'payment_method' => $request->payment_method,
                'check_number' => $request->check_number,
                'receipt_number' => $this->generateReceiptNumber(),
                'donation_date' => $request->donation_date,
                'frequency' => $request->frequency,
                'notes' => $request->notes,
                'metadata' => $request->metadata ?? [],
                'recorded_by' => auth()->id(),
                'is_verified' => $this->shouldAutoVerify($request->payment_method),
                'is_recurring' => $request->boolean('is_recurring', false),
                'next_payment_date' => $request->next_payment_date,
            ]);

            // Update member's total donations
            if ($request->member_id) {
                $member = Member::find($request->member_id);
                $member->increment('total_donations', $request->amount);
                $member->increment('donation_count');
                $member->last_donation_date = now();
                $member->save();
            }

            // Update church financials
            $church = Church::find(auth()->user()->church_id);
            $church->increment('total_donations', $request->amount);
            $church->increment('donation_count');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Donation recorded successfully',
                'data' => $donation->load(['member', 'donationType', 'recordedBy']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record donation: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $donation = Donation::with(['member', 'donationType', 'recordedBy', 'church'])
            ->where('church_id', auth()->user()->church_id)
            ->findOrFail($id);

        // Check permissions
        if (!$this->canAccessDonation($donation)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $donation,
        ]);
    }

    public function update(Request $request, $id)
    {
        $donation = Donation::where('church_id', auth()->user()->church_id)->findOrFail($id);

        // Only admin or creator can update
        if (!auth()->user()->is_admin && $donation->recorded_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this donation',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0.01|max:999999.99',
            'currency' => 'nullable|string|size:3',
            'payment_method' => 'nullable|in:cash,check,card,transfer,online,other',
            'donation_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'is_verified' => 'boolean',
            'is_recurring' => 'boolean',
            'next_payment_date' => 'nullable|date|required_if:is_recurring,true',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $oldAmount = $donation->amount;
            
            $donation->update($request->all());

            // Update member's total if amount changed
            if ($request->has('amount') && $donation->member_id) {
                $member = Member::find($donation->member_id);
                $difference = $request->amount - $oldAmount;
                $member->increment('total_donations', $difference);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Donation updated successfully',
                'data' => $donation->fresh(['member', 'donationType', 'recordedBy']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update donation: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $donation = Donation::where('church_id', auth()->user()->church_id)->findOrFail($id);

        // Only admin or creator can delete
        if (!auth()->user()->is_admin && $donation->recorded_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this donation',
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Update member's total
            if ($donation->member_id) {
                $member = Member::find($donation->member_id);
                $member->decrement('total_donations', $donation->amount);
                $member->decrement('donation_count');
            }

            // Update church financials
            $church = Church::find(auth()->user()->church_id);
            $church->decrement('total_donations', $donation->amount);
            $church->decrement('donation_count');

            $donation->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Donation deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete donation: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function verify($id)
    {
        $donation = Donation::where('church_id', auth()->user()->church_id)->findOrFail($id);

        // Only admin or finance team can verify
        if (!auth()->user()->is_admin && !auth()->user()->hasRole('finance')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to verify donations',
            ], 403);
        }

        $donation->update(['is_verified' => true]);

        // Send receipt if email exists
        if ($donation->member && $donation->member->email) {
            $this->sendReceiptEmail($donation);
        }

        return response()->json([
            'success' => true,
            'message' => 'Donation verified successfully',
            'data' => $donation,
        ]);
    }

    public function bulkVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'donation_ids' => 'required|array',
            'donation_ids.*' => 'exists:donations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Only admin or finance team
        if (!auth()->user()->is_admin && !auth()->user()->hasRole('finance')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $updated = Donation::where('church_id', auth()->user()->church_id)
            ->whereIn('id', $request->donation_ids)
            ->update(['is_verified' => true]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} donations verified successfully",
        ]);
    }

    public function getStats(Request $request)
    {
        $churchId = auth()->user()->church_id;
        $stats = $this->getStatistics($churchId, $request);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    private function applyFilters($query, $request)
    {
        $filters = [
            'start_date' => 'donation_date',
            'end_date' => 'donation_date',
            'member_id' => 'member_id',
            'donation_type_id' => 'donation_type_id',
            'payment_method' => 'payment_method',
            'is_verified' => 'is_verified',
            'is_recurring' => 'is_recurring',
            'frequency' => 'frequency',
            'recorded_by' => 'recorded_by',
        ];

        foreach ($filters as $param => $column) {
            if ($request->filled($param)) {
                if ($param === 'start_date') {
                    $query->whereDate($column, '>=', $request->$param);
                } elseif ($param === 'end_date') {
                    $query->whereDate($column, '<=', $request->$param);
                } else {
                    $query->where($column, $request->$param);
                }
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhere('check_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('member', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }
    }

    private function getStatistics($churchId, $request)
    {
        $baseQuery = Donation::where('church_id', $churchId);

        if ($request->filled('start_date')) {
            $baseQuery->whereDate('donation_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $baseQuery->whereDate('donation_date', '<=', $request->end_date);
        }

        return [
            'total_amount' => $baseQuery->sum('amount'),
            'total_donations' => $baseQuery->count(),
            'verified_amount' => $baseQuery->clone()->where('is_verified', true)->sum('amount'),
            'pending_amount' => $baseQuery->clone()->where('is_verified', false)->sum('amount'),
            'average_donation' => $baseQuery->clone()->avg('amount'),
            'monthly_trend' => $this->getMonthlyTrend($churchId),
            'by_payment_method' => $this->getByPaymentMethod($churchId),
            'by_donation_type' => $this->getByDonationType($churchId),
            'top_donors' => $this->getTopDonors($churchId, $request),
        ];
    }

    private function getMonthlyTrend($churchId)
    {
        return Donation::where('church_id', $churchId)
            ->whereYear('donation_date', date('Y'))
            ->selectRaw('MONTH(donation_date) as month, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function($item) {
                return [
                    Carbon::create()->month($item->month)->format('F') => [
                        'amount' => $item->total,
                        'count' => $item->count
                    ]
                ];
            });
    }

    private function getByPaymentMethod($churchId)
    {
        return Donation::where('church_id', $churchId)
            ->whereYear('donation_date', date('Y'))
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();
    }

    private function getByDonationType($churchId)
    {
        return Donation::where('church_id', $churchId)
            ->whereYear('donation_date', date('Y'))
            ->with('donationType:id,name')
            ->get()
            ->groupBy('donationType.name')
            ->map(function($donations) {
                return [
                    'amount' => $donations->sum('amount'),
                    'count' => $donations->count()
                ];
            });
    }

    private function getTopDonors($churchId, $request)
    {
        $query = Donation::where('church_id', $churchId)
            ->where('is_verified', true)
            ->whereNotNull('member_id')
            ->with('member:id,first_name,last_name')
            ->select('member_id', DB::raw('SUM(amount) as total_donated'), DB::raw('COUNT(*) as donation_count'))
            ->groupBy('member_id')
            ->orderBy('total_donated', 'desc')
            ->limit(10);

        if ($request->filled('start_date')) {
            $query->whereDate('donation_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('donation_date', '<=', $request->end_date);
        }

        return $query->get();
    }

    private function generateTransactionId(): string
    {
        return 'DON-' . time() . '-' . strtoupper(Str::random(6));
    }

    private function generateReceiptNumber(): string
    {
        $count = Donation::whereDate('created_at', today())->count() + 1;
        return 'RCPT-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function shouldAutoVerify($paymentMethod): bool
    {
        // Auto-verify for methods that are traceable
        return in_array($paymentMethod, ['check', 'card', 'transfer', 'online']);
    }

    private function canAccessDonation($donation): bool
    {
        $user = auth()->user();
        
        if ($user->is_admin) return true;
        if ($donation->recorded_by === $user->id) return true;
        if ($donation->member_id === $user->member_id) return true;
        if ($user->hasRole('finance')) return true;
        
        return false;
    }

    private function sendReceiptEmail($donation)
    {
        // Implement email sending logic here
        // This would typically use Laravel's Mail facade
    }
}