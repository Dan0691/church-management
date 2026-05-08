<?php

namespace App\Http\Controllers\Api;

use App\Exports\MembersExport;
use App\Http\Controllers\Controller;
use App\Imports\MembersImport;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MemberController extends Controller
{
    /**
     * Get the current church ID from authenticated user
     */
    private function getCurrentChurchId()
    {
        // Get church_id from authenticated user
        $user = auth()->user();

        if (!$user) {
            \Log::error('getCurrentChurchId: No authenticated user');
            return null;
        }

        // If user has church_id directly
        if ($user->church_id) {
            \Log::info('getCurrentChurchId: Found church_id=' . $user->church_id);
            return $user->church_id;
        }

        // If user has church relationship
        if ($user->church) {
            \Log::info('getCurrentChurchId: Found church through relationship, id=' . $user->church->id);
            return $user->church->id;
        }

        \Log::warning('getCurrentChurchId: User ' . $user->id . ' has no church_id or church relationship');
        return null;
    }

        public function indexreal(Request $request)
    {
        $churchId = $this->getCurrentChurchId();

        $query = Member::where('church_id', $churchId);

        // Search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;

                // Handle special search keywords
                switch ($search) {
                    case 'no-email':
                        $query->whereNull('email')->orWhere('email', '');
                        break;
                    case 'no-phone':
                        $query->whereNull('phone')->orWhere('phone', '');
                        break;
                    case 'birthday-month':
                        $query->whereMonth('birth_date', date('m'));
                        break;
                    default:
                        $query->where(function($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('other_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('occupation', 'like', "%{$search}%");
                        });
                }
            }

            // Status filter
            if ($request->has('status') && !empty($request->status)) {
                $query->where('membership_status', $request->status);
            }

            // New this month filter (for quick filter)
            if ($request->has('new_this_month') && $request->new_this_month) {
                $query->whereMonth('join_date', date('m'))
                    ->whereYear('join_date', date('Y'));
            }

            // Advanced filters
            if ($request->has('gender') && !empty($request->gender)) {
                $query->whereIn('gender', (array)$request->gender);
            }

            if ($request->has('marital_status') && !empty($request->marital_status)) {
                $query->whereIn('marital_status', (array)$request->marital_status);
            }

            if ($request->has('city') && !empty($request->city)) {
                $query->where('city', 'like', "%{$request->city}%");
            }

            if ($request->has('occupation') && !empty($request->occupation)) {
                $query->where('occupation', 'like', "%{$request->occupation}%");
            }

            // Date range filters
            // if ($request->has('joinDateRange.start') && !empty($request->{'joinDateRange.start'})) {
            //     $query->whereDate('join_date', '>=', $request->{'joinDateRange.start'});
            // }

            // if ($request->has('joinDateRange.end') && !empty($request->{'joinDateRange.end'})) {
            //     $query->whereDate('join_date', '<=', $request->{'joinDateRange.end'});
            // }


                $start = $request->input('joinDateRange.start');
                if ($start) {
                    $query->whereDate('join_date', '>=', $start);
                }

                $end = $request->input('joinDateRange.end');
                if ($end) {
                    $query->whereDate('join_date', '<=', $end);
                }

                if ($request->has('birthYear') && !empty($request->birthYear)) {
                    $query->whereYear('birth_date', $request->birthYear);
                }


            // Sorting
            $sortBy = $request->get('sort_by', 'created_at_desc');
            switch ($sortBy) {
                case 'name_asc':
                    $query->orderBy('first_name')->orderBy('other_name')->orderBy('last_name');
                    break;
                case 'name_desc':
                    $query->orderByDesc('first_name')->orderByDesc('other_name')->orderByDesc('last_name');
                    break;
                case 'join_date':
                    $query->orderBy('join_date');
                    break;
                case 'birth_date':
                    $query->orderBy('birth_date');
                    break;
                case 'created_at_asc':
                    $query->orderBy('created_at');
                    break;
                case 'created_at_desc':
                default:
                    $query->orderByDesc('created_at');
                    break;
            }

        // Paginate the filtered results
        $perPage = $request->get('per_page', 15);
        $members = $query->paginate($perPage);

        // IMPORTANT: Calculate stats WITHOUT filters (from base query)
        $baseQuery = Member::where('church_id', $churchId);

        $stats = [
            'total' => $baseQuery->count(),
            'active' => $baseQuery->where('membership_status', 'active')->count(),
            'visitors' => $baseQuery->where('membership_status', 'visitor')->count(),
            'new_this_month' => $baseQuery
                ->whereMonth('join_date', date('m'))
                ->whereYear('join_date', date('Y'))
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $members->items(),
            'meta' => [
                'current_page' => $members->currentPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
                'last_page' => $members->lastPage(),
            ],
            'stats' => $stats  // This will always be total stats
        ]);
    }


    public function index(Request $request)
{
    $churchId = $this->getCurrentChurchId();

    $query = Member::where('church_id', $churchId);

    // Search
    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;

        // Handle special search keywords
        switch ($search) {
            case 'no-email':
                $query->whereNull('email')->orWhere('email', '');
                break;
            case 'no-phone':
                $query->whereNull('phone')->orWhere('phone', '');
                break;
            case 'birthday-month':
                $query->whereMonth('birth_date', date('m'));
                break;
            default:
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('other_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('occupation', 'like', "%{$search}%");
                });
        }
    }

    // Status filter
    if ($request->has('status') && !empty($request->status)) {
        $query->where('membership_status', $request->status);
    }

    // New this month filter (for quick filter)
    if ($request->has('new_this_month') && $request->new_this_month) {
        $query->whereMonth('join_date', date('m'))
            ->whereYear('join_date', date('Y'));
    }

    // Advanced filters
    if ($request->has('gender') && !empty($request->gender)) {
        $query->whereIn('gender', (array)$request->gender);
    }

    if ($request->has('marital_status') && !empty($request->marital_status)) {
        $query->whereIn('marital_status', (array)$request->marital_status);
    }

    if ($request->has('city') && !empty($request->city)) {
        $query->where('city', 'like', "%{$request->city}%");
    }

    if ($request->has('occupation') && !empty($request->occupation)) {
        $query->where('occupation', 'like', "%{$request->occupation}%");
    }

    // Date range filters
    $start = $request->input('joinDateRange.start');
    if ($start) {
        $query->whereDate('join_date', '>=', $start);
    }

    $end = $request->input('joinDateRange.end');
    if ($end) {
        $query->whereDate('join_date', '<=', $end);
    }

    if ($request->has('birthYear') && !empty($request->birthYear)) {
        $query->whereYear('birth_date', $request->birthYear);
    }

    // Sorting
    $sortBy = $request->get('sort_by', 'created_at_desc');
    switch ($sortBy) {
        case 'name_asc':
            $query->orderBy('first_name')->orderBy('other_name')->orderBy('last_name');
            break;
        case 'name_desc':
            $query->orderByDesc('first_name')->orderByDesc('other_name')->orderByDesc('last_name');
            break;
        case 'join_date':
            $query->orderBy('join_date');
            break;
        case 'birth_date':
            $query->orderBy('birth_date');
            break;
        case 'created_at_asc':
            $query->orderBy('created_at');
            break;
        case 'created_at_desc':
        default:
            $query->orderByDesc('created_at');
            break;
    }

    // Paginate the filtered results
    $perPage = $request->get('per_page', 15);
    $members = $query->paginate($perPage);

    // ✅ FIXED: Calculate stats with fresh queries (not reusing the same query builder)
    $stats = [
        'total' => Member::where('church_id', $churchId)->count(),
        'active' => Member::where('church_id', $churchId)
                        ->where('membership_status', 'active')
                        ->count(),
        'visitors' => Member::where('church_id', $churchId)
                        ->where('membership_status', 'visitor')
                        ->count(),
        'new_this_month' => Member::where('church_id', $churchId)
                            ->whereMonth('join_date', date('m'))
                            ->whereYear('join_date', date('Y'))
                            ->count(),
    ];

    return response()->json([
        'success' => true,
        'data' => $members->items(),
        'meta' => [
            'current_page' => $members->currentPage(),
            'per_page' => $members->perPage(),
            'total' => $members->total(),
            'last_page' => $members->lastPage(),
        ],
        'stats' => $stats  // Now returns correct values, unaffected by filters
    ]);
}


        // In your index method or create a new method
    public function index000(Request $request)
    {
        $churchId = $this->getCurrentChurchId();

        // $churchId = auth()->user()->church_id;

        $query = Member::where('church_id', $churchId);

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;

            // Handle special search keywords
            switch ($search) {
                case 'no-email':
                    $query->whereNull('email')->orWhere('email', '');
                    break;
                case 'no-phone':
                    $query->whereNull('phone')->orWhere('phone', '');
                    break;
                case 'birthday-month':
                    $query->whereMonth('birth_date', date('m'));
                    break;
                default:
                    $query->where(function($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('other_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('occupation', 'like', "%{$search}%");
                    });
            }
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('membership_status', $request->status);
        }

        // New this month filter (for quick filter)
        if ($request->has('new_this_month') && $request->new_this_month) {
            $query->whereMonth('join_date', date('m'))
                ->whereYear('join_date', date('Y'));
        }

        // Advanced filters
        if ($request->has('gender') && !empty($request->gender)) {
            $query->whereIn('gender', (array)$request->gender);
        }

        if ($request->has('marital_status') && !empty($request->marital_status)) {
            $query->whereIn('marital_status', (array)$request->marital_status);
        }

        if ($request->has('city') && !empty($request->city)) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        if ($request->has('occupation') && !empty($request->occupation)) {
            $query->where('occupation', 'like', "%{$request->occupation}%");
        }

        // Date range filters
        if ($request->has('joinDateRange.start') && !empty($request->{'joinDateRange.start'})) {
            $query->whereDate('join_date', '>=', $request->{'joinDateRange.start'});
        }

        if ($request->has('joinDateRange.end') && !empty($request->{'joinDateRange.end'})) {
            $query->whereDate('join_date', '<=', $request->{'joinDateRange.end'});
        }

        if ($request->has('birthYear') && !empty($request->birthYear)) {
            $query->whereYear('birth_date', $request->birthYear);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at_desc');
        switch ($sortBy) {
            case 'name_asc':
                $query->orderBy('first_name')->orderBy('other_name')->orderBy('last_name');
                break;
            case 'name_desc':
                $query->orderByDesc('first_name')->orderByDesc('other_name')->orderByDesc('last_name');
                break;
            case 'join_date':
                $query->orderBy('join_date');
                break;
            case 'birth_date':
                $query->orderBy('birth_date');
                break;
            case 'created_at_asc':
                $query->orderBy('created_at');
                break;
            case 'created_at_desc':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $members = $query->paginate($perPage);

        // Get stats
        $total = Member::where('church_id', $churchId)->count();
        $active = Member::where('church_id', $churchId)->where('membership_status', 'active')->count();
        $visitors = Member::where('church_id', $churchId)->where('membership_status', 'visitor')->count();
        $newThisMonth = Member::where('church_id', $churchId)
            ->whereMonth('join_date', date('m'))
            ->whereYear('join_date', date('Y'))
            ->count();

        return response()->json([
            'success' => true,
            'data' => $members->items(),
            'meta' => [
                'current_page' => $members->currentPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
                'last_page' => $members->lastPage(),
            ],
            'stats' => [
                'total' => $total,
                'active' => $active,
                'visitors' => $visitors,
                'new_this_month' => $newThisMonth
            ]
        ]);
    }

    public function indexoribinal(Request $request)
    {
        // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        // Start query with church filter
        $query = Member::with('church')->where('church_id', $churchId);

        // Apply filters
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('other_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('occupation', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('membership_status', $request->status);
        }

        if ($request->has('gender') && $request->gender) {
            if (is_array($request->gender)) {
                $query->whereIn('gender', $request->gender);
            } else {
                $query->where('gender', $request->gender);
            }
        }

        if ($request->has('marital_status') && $request->marital_status) {
            if (is_array($request->marital_status)) {
                $query->whereIn('marital_status', $request->marital_status);
            } else {
                $query->where('marital_status', $request->marital_status);
            }
        }

        if ($request->has('city') && $request->city) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        if ($request->has('occupation') && $request->occupation) {
            $query->where('occupation', 'like', "%{$request->occupation}%");
        }

        if ($request->has('join_date_start') && $request->join_date_start) {
            $query->whereDate('join_date', '>=', $request->join_date_start);
        }

        if ($request->has('join_date_end') && $request->join_date_end) {
            $query->whereDate('join_date', '<=', $request->join_date_end);
        }

        if ($request->has('birth_year') && $request->birth_year) {
            $query->whereYear('birth_date', $request->birth_year);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at_desc');
        switch ($sortBy) {
            case 'name_asc':
                $query->orderBy('first_name')->orderBy('other_name')->orderBy('last_name');
                break;
            case 'name_desc':
                $query->orderByDesc('first_name')->orderByDesc('other_name')->orderByDesc('last_name');
                break;
            case 'join_date':
                $query->orderBy('join_date');
                break;
            case 'created_at_asc':
                $query->orderBy('created_at');
                break;
            default:
                $query->orderByDesc('created_at');
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $members = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $members->items(),
            'current_page' => $members->currentPage(),
            'last_page' => $members->lastPage(),
            'per_page' => $members->perPage(),
            'total' => $members->total(),
        ]);
    }

    public function store(Request $request)
{
    // Get current user's church ID
    $churchId = $this->getCurrentChurchId();

    if (!$churchId) {
        return response()->json([
            'success' => false,
            'message' => 'No church associated with user'
        ], 403);
    }

    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:255',
        'other_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:members,email',
        'phone' => 'nullable|string|max:20',
        'birth_date' => 'nullable|date',
        'join_date' => 'required|date',
        'gender' => 'nullable|string|in:male,female,Other',
        'marital_status' => 'nullable|string|in:single,married,divorced,widowed,separated',
        'occupation' => 'nullable|string|max:255',
        'address' => 'nullable|string',
        'city' => 'nullable|string|max:100',
        'state' => 'nullable|string|max:100',
        'zip_code' => 'nullable|string|max:20',
        'membership_status' => 'required|in:active,inactive,visitor,pending,transferred',
        'notes' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    $memberData = $request->all();
    $memberData['created_by'] = auth()->id();
    $memberData['church_id'] = $churchId;

    // Convert empty strings to null for nullable fields
    $nullableFields = ['email', 'phone', 'birth_date', 'gender', 'marital_status',
                      'occupation', 'address', 'city', 'state', 'zip_code', 'notes'];

    foreach ($nullableFields as $field) {
        if (isset($memberData[$field]) && $memberData[$field] === '') {
            $memberData[$field] = null;
        }
    }

    $member = Member::create($memberData);

    return response()->json([
        'success' => true,
        'message' => 'Member created successfully',
        'data' => $member
    ], 201);
}

    public function show($id)
    {
        // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        // Only show members from current church
        $member = Member::with(['church', 'attendances.event'])
            ->where('church_id', $churchId)
            ->find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found or you do not have permission to view this member'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $member
        ]);
    }

    public function update(Request $request, $id)
    {
        // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        // Only allow updating members from current church
        $member = Member::where('church_id', $churchId)->find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found or you do not have permission to update this member'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'other_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:members,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'join_date' => 'required|date',
            'gender' => 'nullable|string|in:male,female,Other',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed,separated',
            'occupation' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            // 'membership_status' => 'required|in:Active,Inactive,Visitor,Pending,Transferred',
            'membership_status' => 'required|in:active,inactive,visitor,pending,transferred',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update member data
        $updateData = $request->all();

        // Ensure church_id stays the same (prevent moving members between churches)
        unset($updateData['church_id']);

        $member->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Member updated successfully',
            'data' => $member
        ]);
    }

    public function destroy($id)
    {
        // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        // Only allow deleting members from current church
        $member = Member::where('church_id', $churchId)->find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found or you do not have permission to delete this member'
            ], 404);
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member deleted successfully'
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:members,id',
            'membership_status' => 'sometimes|in:active,inactive,visitor,pending,transferred',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['membership_status']);

        // Only update members from current church
        $updatedCount = Member::whereIn('id', $request->ids)
            ->where('church_id', $churchId)
            ->update($updateData);

        return response()->json([
            'success' => true,
            'message' => $updatedCount . ' members updated successfully'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:members,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Only delete members from current church
        $deletedCount = Member::whereIn('id', $request->ids)
            ->where('church_id', $churchId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => $deletedCount . ' members deleted successfully'
        ]);
    }

    public function import11(Request $request)
    {
        // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            // Pass church_id to import class
            $import = new MembersImport($churchId);
            Excel::import($import, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Members imported successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import members: ' . $e->getMessage()
            ], 500);
        }
    }

public function importoriginan(Request $request)
{
    // Get current user's church ID
    $churchId = $this->getCurrentChurchId();

    if (!$churchId) {
        return response()->json([
            'success' => false,
            'message' => 'No church associated with user'
        ], 403);
    }

    // Validate the request
    $validator = Validator::make($request->all(), [
        'file' => 'required|file|mimes:csv,xlsx,xls|max:10240'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Get the file
        $file = $request->file('file');

        // Pass church_id to import class
        $import = new MembersImport($churchId);

        // Configure Excel import to handle dates properly
        Excel::import($import, $file, null, \Maatwebsite\Excel\Excel::XLSX, [
            'date_format' => 'Y-m-d',
            'ignoreEmpty' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Members imported successfully'
        ]);

    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
        $failures = $e->failures();

        $errors = [];
        foreach ($failures as $failure) {
            $errors[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values()
            ];
        }

        return response()->json([
            'success' => false,
            'message' => 'Validation failed during import',
            'errors' => $errors
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to import members: ' . $e->getMessage()
        ], 500);
    }
}


 public function import(Request $request)
    {
    $churchId = $this->getCurrentChurchId();
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv|max:5120'
    ]);

    try {
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $data = [];
        if ($extension == 'csv') {
            $data = array_map('str_getcsv', file($file));
        } else {
            $spreadsheet = IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
        }

        // Remove header row
        $headers = array_shift($data);

        $imported = 0;
        $skipped = 0;
        $duplicates = [];

        foreach ($data as $row) {
            // Map row to associative array
            $rowData = array_combine($headers, $row);

            // Check for required fields
            if (empty($rowData['first_name']) || empty($rowData['other_name']) || empty($rowData['last_name'])) {
                $skipped++;
                continue;
            }

            // Check for duplicate email
            if (!empty($rowData['email'])) {
                $existing = Member::where('email', $rowData['email'])
                    ->where('church_id', auth()->user()->church_id)
                    ->first();

                if ($existing) {
                    $duplicates[] = $rowData['email'];
                    $skipped++;
                    continue;
                }
            }

            // Create member
            Member::create([
                'first_name' => $rowData['first_name'],
                'other_name' => $rowData['other_name'],
                'last_name' => $rowData['last_name'],
                'email' => $rowData['email'] ?? null,
                'phone' => $rowData['phone'] ?? null,
                'birth_date' => $rowData['birth_date'] ?? null,
                'join_date' => $rowData['join_date'] ?? now(),
                'gender' => $rowData['gender'] ?? null,
                'marital_status' => $rowData['marital_status'] ?? null,
                'occupation' => $rowData['occupation'] ?? null,
                'membership_status' => $rowData['membership_status'] ?? 'active',
                'address' => $rowData['address'] ?? null,
                'city' => $rowData['city'] ?? null,
                'state' => $rowData['state'] ?? null,
                'zip_code' => $rowData['zip_code'] ?? null,
                'notes' => $rowData['notes'] ?? null,
                'church_id' => auth()->user()->church_id,
                'created_by' => auth()->user()->id
            ]);

            $imported++;
        }

        return response()->json([
            'success' => true,
            'message' => "Imported $imported members successfully. Skipped $skipped records." .
                        ($duplicates ? ' Duplicate emails: ' . implode(', ', array_slice($duplicates, 0, 5)) . (count($duplicates) > 5 ? '...' : '') : ''),
            'imported' => $imported,
            'skipped' => $skipped
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Import failed: ' . $e->getMessage()
        ], 500);
    }
}
    public function export($format = 'excel')
    {
        // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        $filename = 'members_' . date('Y-m-d') . '.' . $format;

        if ($format === 'excel') {
            return Excel::download(new MembersExport($churchId), $filename);
        } elseif ($format === 'csv') {
            return Excel::download(new MembersExport($churchId), $filename, \Maatwebsite\Excel\Excel::CSV);
        } elseif ($format === 'pdf') {
            return Excel::download(new MembersExport($churchId), $filename, \Maatwebsite\Excel\Excel::MPDF);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid export format'
        ], 400);
    }


    public function stats()
    {
        $churchId = $this->getCurrentChurchId(); // adjust to your auth logic

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        $stats = [
            'total' => Member::where('church_id', $churchId)->count(),
            'active' => Member::where('church_id', $churchId)
                        ->where('membership_status', 'active')->count(),
            'visitors' => Member::where('church_id', $churchId)
                        ->where('membership_status', 'visitor')->count(),
            'new_this_month' => Member::where('church_id', $churchId)
                            ->whereMonth('join_date', Carbon::now()->month)
                            ->whereYear('join_date', Carbon::now()->year)
                            ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function stats00()
    {
        try {
            // Get church ID from authenticated user
            $user = auth()->user();

            // Debug: Log user info
            \Log::info('Stats request from user:', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'church_id' => $user->church_id
            ]);

            // Use the user's church_id directly
            $churchId = $user->church_id;

            if (!$churchId) {
                \Log::error('No church_id found for user', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'User is not associated with any church'
                ], 400);
            }

            $stats = [
                'total' => Member::where('church_id', $churchId)->count(),
                'active' => Member::where('church_id', $churchId)
                    ->where('membership_status', 'active')
                    ->count(),
                'visitors' => Member::where('church_id', $churchId)
                    ->where('membership_status', 'visitor')
                    ->count(),
                'new_this_month' => Member::where('church_id', $churchId)
                    ->whereMonth('join_date', date('m'))
                    ->whereYear('join_date', date('Y'))
                    ->count(),
            ];

            \Log::info('Stats computed:', $stats);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Stats error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ageGroups()
    {
        // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        $ageGroups = [
            '0-18' => Member::where('church_id', $churchId)
                ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 0 AND 18')
                ->count(),
            '19-35' => Member::where('church_id', $churchId)
                ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 19 AND 35')
                ->count(),
            '36-50' => Member::where('church_id', $churchId)
                ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 36 AND 50')
                ->count(),
            '51-65' => Member::where('church_id', $churchId)
                ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 51 AND 65')
                ->count(),
            '65+' => Member::where('church_id', $churchId)
                ->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) > 65')
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $ageGroups
        ]);
    }

    public function recent()
    {
        // Get current user's church ID
        $churchId = $this->getCurrentChurchId();

        if (!$churchId) {
            return response()->json([
                'success' => false,
                'message' => 'No church associated with user'
            ], 403);
        }

        $members = Member::with('church')
            ->where('church_id', $churchId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
    }

    public function downloadTemplate()
    {
        $filename = 'member_import_template.xlsx';

        return Excel::download(new MembersExport(null, true), $filename);
    }
}
