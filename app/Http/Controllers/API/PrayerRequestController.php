<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrayerRequest;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PrayerRequestController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 20;
        $churchId = auth()->user()->church_id;

        $query = PrayerRequest::where('church_id', $churchId)
            ->with(['member', 'prayedBy'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('privacy')) {
            $query->where('privacy', $request->privacy);
        }

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->filled('answered')) {
            $query->where('is_answered', $request->boolean('answered'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // For non-admin users, show only public and church_only requests
        if (!auth()->user()->is_admin) {
            $query->whereIn('privacy', ['public', 'church_only']);
        }

        $prayerRequests = $query->paginate($perPage);

        // Get statistics
        $stats = [
            'total' => PrayerRequest::where('church_id', $churchId)->count(),
            'pending' => PrayerRequest::where('church_id', $churchId)->where('status', 'pending')->count(),
            'answered' => PrayerRequest::where('church_id', $churchId)->where('is_answered', true)->count(),
            'urgent' => PrayerRequest::where('church_id', $churchId)->where('priority', 'urgent')->count(),
            'by_category' => PrayerRequest::where('church_id', $churchId)
                ->groupBy('category')
                ->selectRaw('category, count(*) as count')
                ->get()
                ->pluck('count', 'category'),
        ];

        return response()->json([
            'success' => true,
            'data' => $prayerRequests,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'request' => 'required|string',
            'category' => 'required|in:healing,financial,guidance,protection,thanksgiving,other',
            'privacy' => 'required|in:public,church_only,private',
            'priority' => 'required|in:normal,urgent',
            'allow_prayers' => 'boolean',
            'allow_comments' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $prayerRequest = PrayerRequest::create([
            'church_id' => auth()->user()->church_id,
            'member_id' => $request->member_id ?? auth()->user()->member_id,
            'title' => $request->title,
            'request' => $request->request,
            'category' => $request->category,
            'privacy' => $request->privacy,
            'priority' => $request->priority,
            'status' => 'pending',
            'allow_prayers' => $request->boolean('allow_prayers', true),
            'allow_comments' => $request->boolean('allow_comments', true),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prayer request submitted successfully',
            'data' => $prayerRequest->load('member'),
        ], 201);
    }

    public function show($id)
    {
        $prayerRequest = PrayerRequest::with(['member', 'prayedBy', 'comments.user', 'answers'])
            ->findOrFail($id);

        // Check privacy settings
        if ($prayerRequest->privacy === 'private' && $prayerRequest->member_id !== auth()->user()->member_id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this prayer request',
            ], 403);
        }

        // Check if it belongs to the user's church
        if ($prayerRequest->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $prayerRequest,
        ]);
    }

    public function update(Request $request, $id)
    {
        $prayerRequest = PrayerRequest::findOrFail($id);

        // Check permissions
        if ($prayerRequest->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        // Only creator or admin can update
        if ($prayerRequest->created_by !== auth()->id() && !auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own prayer requests',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'request' => 'sometimes|required|string',
            'category' => 'sometimes|required|in:healing,financial,guidance,protection,thanksgiving,other',
            'privacy' => 'sometimes|required|in:public,church_only,private',
            'priority' => 'sometimes|required|in:normal,urgent',
            'status' => 'sometimes|required|in:pending,reviewing,praying,answered,closed',
            'allow_prayers' => 'boolean',
            'allow_comments' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $prayerRequest->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Prayer request updated successfully',
            'data' => $prayerRequest,
        ]);
    }

    public function destroy($id)
    {
        $prayerRequest = PrayerRequest::findOrFail($id);

        // Check permissions
        if ($prayerRequest->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        // Only creator or admin can delete
        if ($prayerRequest->created_by !== auth()->id() && !auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own prayer requests',
            ], 403);
        }

        $prayerRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prayer request deleted successfully',
        ]);
    }

    public function markAsPrayed($id)
    {
        $prayerRequest = PrayerRequest::findOrFail($id);

        // Check if user has already prayed for this request
        $alreadyPrayed = $prayerRequest->prayedBy()
            ->where('user_id', auth()->id())
            ->exists();

        if (!$alreadyPrayed) {
            $prayerRequest->prayedBy()->attach(auth()->id(), [
                'prayed_at' => now(),
            ]);

            $prayerRequest->increment('prayer_count');
        }

        return response()->json([
            'success' => true,
            'message' => 'Prayed for this request',
            'data' => [
                'prayer_count' => $prayerRequest->prayer_count,
                'user_prayed' => true,
            ],
        ]);
    }

    public function addComment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:1000',
            'is_private' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $prayerRequest = PrayerRequest::findOrFail($id);

        // Check if comments are allowed
        if (!$prayerRequest->allow_comments) {
            return response()->json([
                'success' => false,
                'message' => 'Comments are not allowed for this prayer request',
            ], 403);
        }

        $comment = $prayerRequest->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
            'is_private' => $request->boolean('is_private', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'data' => $comment->load('user'),
        ]);
    }

    public function addAnswer(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'answer' => 'required|string|max:2000',
            'answered_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $prayerRequest = PrayerRequest::findOrFail($id);

        $answer = $prayerRequest->answers()->create([
            'user_id' => auth()->id(),
            'answer' => $request->answer,
            'answered_date' => $request->answered_date,
        ]);

        // Mark the prayer request as answered
        $prayerRequest->update([
            'status' => 'answered',
            'is_answered' => true,
            'answered_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Answer added successfully',
            'data' => $answer,
        ]);
    }

    public function getStatistics()
    {
        $churchId = auth()->user()->church_id;

        $stats = [
            'total' => PrayerRequest::where('church_id', $churchId)->count(),
            'pending' => PrayerRequest::where('church_id', $churchId)->where('status', 'pending')->count(),
            'answered' => PrayerRequest::where('church_id', $churchId)->where('is_answered', true)->count(),
            'urgent' => PrayerRequest::where('church_id', $churchId)->where('priority', 'urgent')->count(),
            'prayer_count' => PrayerRequest::where('church_id', $churchId)->sum('prayer_count'),
        ];

        // Monthly statistics for the past 6 months
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $monthData = [
                'month' => $date->format('M Y'),
                'requests' => PrayerRequest::where('church_id', $churchId)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count(),
                'answered' => PrayerRequest::where('church_id', $churchId)
                    ->whereBetween('answered_at', [$monthStart, $monthEnd])
                    ->count(),
            ];

            $months->push($monthData);
        }

        // Category distribution
        $categories = PrayerRequest::where('church_id', $churchId)
            ->groupBy('category')
            ->selectRaw('category, count(*) as count')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'monthly' => $months,
                'categories' => $categories,
            ],
        ]);
    }

    public function getUrgentRequests()
    {
        $churchId = auth()->user()->church_id;

        $urgentRequests = PrayerRequest::where('church_id', $churchId)
            ->where('priority', 'urgent')
            ->where('status', '!=', 'answered')
            ->with('member')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $urgentRequests,
        ]);
    }

    public function getRecentRequests()
    {
        $churchId = auth()->user()->church_id;

        $recentRequests = PrayerRequest::where('church_id', $churchId)
            ->whereIn('privacy', ['public', 'church_only'])
            ->with('member')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recentRequests,
        ]);
    }

    public function exportPrayerRequests(Request $request)
    {
        $churchId = auth()->user()->church_id;

        $query = PrayerRequest::where('church_id', $churchId)
            ->with('member');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $prayerRequests = $query->get();

        $data = $prayerRequests->map(function ($request) {
            return [
                'ID' => $request->id,
                'Title' => $request->title,
                'Request' => $request->request,
                'Category' => $request->category,
                'Priority' => $request->priority,
                'Status' => $request->status,
                'Answered' => $request->is_answered ? 'Yes' : 'No',
                'Prayer Count' => $request->prayer_count,
                'Member' => $request->member ? $request->member->first_name . ' ' . $request->member->last_name : 'Anonymous',
                'Created At' => $request->created_at->format('Y-m-d H:i:s'),
                'Updated At' => $request->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        $filename = 'prayer_requests_' . date('Y_m_d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, array_keys($data->first()));

            // Add rows
            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
