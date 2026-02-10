<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DepartmentMember;
use App\Models\DepartmentActivity;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 20;
        $churchId = auth()->user()->church_id;

        $query = Department::where('church_id', $churchId)
            ->with(['leader', 'members'])
            ->withCount('members');

        // Filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'name';
        $sortOrder = $request->sort_order ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $departments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'leader_id' => 'nullable|exists:members,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'meeting_schedule' => 'nullable|string',
            'category' => 'required|in:ministry,service,outreach,fellowship,administration,other',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $department = Department::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'church_id' => auth()->user()->church_id,
            'leader_id' => $request->leader_id,
            'email' => $request->email,
            'phone' => $request->phone,
            'meeting_schedule' => $request->meeting_schedule,
            'category' => $request->category,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Add leader as department member if specified
        if ($request->leader_id) {
            DepartmentMember::create([
                'department_id' => $department->id,
                'member_id' => $request->leader_id,
                'role' => 'leader',
                'joined_date' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully',
            'data' => $department->load(['leader', 'members']),
        ], 201);
    }

    public function show($id)
    {
        $department = Department::with(['leader', 'members', 'activities.recordedBy'])
            ->withCount('members')
            ->findOrFail($id);

        // Check if department belongs to user's church
        if ($department->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $department,
        ]);
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        if ($department->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'leader_id' => 'nullable|exists:members,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'meeting_schedule' => 'nullable|string',
            'category' => 'required|in:ministry,service,outreach,fellowship,administration,other',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $department->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'leader_id' => $request->leader_id,
            'email' => $request->email,
            'phone' => $request->phone,
            'meeting_schedule' => $request->meeting_schedule,
            'category' => $request->category,
            'is_active' => $request->boolean('is_active', $department->is_active),
        ]);

        // Update leader membership if changed
        if ($request->leader_id && $department->leader_id != $request->leader_id) {
            // Remove previous leader role
            DepartmentMember::where('department_id', $department->id)
                ->where('role', 'leader')
                ->update(['role' => 'member']);

            // Add new leader
            $existingMember = DepartmentMember::where('department_id', $department->id)
                ->where('member_id', $request->leader_id)
                ->first();

            if ($existingMember) {
                $existingMember->update(['role' => 'leader']);
            } else {
                DepartmentMember::create([
                    'department_id' => $department->id,
                    'member_id' => $request->leader_id,
                    'role' => 'leader',
                    'joined_date' => now(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully',
            'data' => $department->load(['leader', 'members']),
        ]);
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        if ($department->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully',
        ]);
    }

    // Department Members Management
    public function addMember(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'role' => 'required|in:leader,co-leader,member,volunteer',
            'joined_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $department = Department::findOrFail($id);

        if ($department->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        // Check if member already in department
        $existing = DepartmentMember::where('department_id', $id)
            ->where('member_id', $request->member_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Member already exists in this department',
            ], 409);
        }

        $departmentMember = DepartmentMember::create([
            'department_id' => $id,
            'member_id' => $request->member_id,
            'role' => $request->role,
            'joined_date' => $request->joined_date ?? now(),
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member added to department successfully',
            'data' => $departmentMember->load('member'),
        ]);
    }

    public function removeMember($id, $memberId)
    {
        $department = Department::findOrFail($id);

        if ($department->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        DepartmentMember::where('department_id', $id)
            ->where('member_id', $memberId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member removed from department successfully',
        ]);
    }

    public function updateMemberRole(Request $request, $id, $memberId)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:leader,co-leader,member,volunteer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $departmentMember = DepartmentMember::where('department_id', $id)
            ->where('member_id', $memberId)
            ->firstOrFail();

        $departmentMember->update([
            'role' => $request->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member role updated successfully',
            'data' => $departmentMember,
        ]);
    }

    // Department Activities
    public function recordActivity(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:meeting,outreach,training,prayer,planning,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'required|date',
            'attendance' => 'nullable|array',
            'attendance.*.member_id' => 'exists:members,id',
            'attendance.*.status' => 'in:present,absent,excused',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $department = Department::findOrFail($id);

        if ($department->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $activity = DepartmentActivity::create([
            'department_id' => $id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'activity_date' => $request->activity_date,
            'attendance' => $request->attendance,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activity recorded successfully',
            'data' => $activity->load('recordedBy'),
        ]);
    }

    public function getActivities($id)
    {
        $department = Department::findOrFail($id);

        if ($department->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $activities = DepartmentActivity::where('department_id', $id)
            ->with('recordedBy')
            ->orderBy('activity_date', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    // Department Reports
    public function generateReport($id)
    {
        $department = Department::with(['members.member', 'activities', 'leader'])
            ->withCount('members')
            ->findOrFail($id);

        if ($department->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        // Calculate activity statistics
        $activities = $department->activities;
        $activityStats = [
            'total' => $activities->count(),
            'by_type' => $activities->groupBy('type')->map->count(),
            'attendance_rate' => $this->calculateAttendanceRate($activities),
        ];

        // Member statistics
        $members = $department->members;
        $memberStats = [
            'total' => $members->count(),
            'by_role' => $members->groupBy('role')->map->count(),
            'new_this_month' => $members->where('joined_date', '>=', now()->subMonth())->count(),
        ];

        $report = [
            'department' => $department,
            'activity_stats' => $activityStats,
            'member_stats' => $memberStats,
            'generated_at' => now(),
            'generated_by' => auth()->user()->name,
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    private function calculateAttendanceRate($activities)
    {
        $totalActivities = $activities->count();
        if ($totalActivities === 0) return 0;

        $attendedActivities = $activities->filter(function ($activity) {
            return $activity->attendance && count($activity->attendance) > 0;
        })->count();

        return ($attendedActivities / $totalActivities) * 100;
    }

    // Bulk Operations
    public function bulkAddMembers(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:members,id',
            'role' => 'required|in:leader,co-leader,member,volunteer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $department = Department::findOrFail($id);

        if ($department->church_id !== auth()->user()->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $added = [];
        $skipped = [];

        foreach ($request->member_ids as $memberId) {
            // Check if already in department
            $existing = DepartmentMember::where('department_id', $id)
                ->where('member_id', $memberId)
                ->exists();

            if ($existing) {
                $skipped[] = $memberId;
                continue;
            }

            DepartmentMember::create([
                'department_id' => $id,
                'member_id' => $memberId,
                'role' => $request->role,
                'joined_date' => now(),
            ]);

            $added[] = $memberId;
        }

        return response()->json([
            'success' => true,
            'message' => 'Members added in bulk',
            'data' => [
                'added' => $added,
                'skipped' => $skipped,
                'total_added' => count($added),
                'total_skipped' => count($skipped),
            ],
        ]);
    }
}
