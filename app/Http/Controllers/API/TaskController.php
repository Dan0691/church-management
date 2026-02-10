<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Member;
use App\Models\Department;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 20;
        $churchId = auth()->user()->church_id;

        $query = Task::where('church_id', $churchId)
            ->with(['department', 'assignedTo', 'createdBy', 'comments.user'])
            ->orderBy('due_date', 'asc');

        // Apply filters
        if ($request->filled('status')) {
            if (is_array($request->status)) {
                $query->whereIn('status', $request->status);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('priority')) {
            if (is_array($request->priority)) {
                $query->whereIn('priority', $request->priority);
            } else {
                $query->where('priority', $request->priority);
            }
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('overdue')) {
            $query->where('due_date', '<', now())
                  ->whereIn('status', ['pending', 'in_progress']);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('due_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('due_date', '<=', $request->end_date);
        }

        // For regular users, show only assigned tasks or public tasks
        if (!auth()->user()->is_admin) {
            $query->where(function($q) {
                $q->where('assigned_to', auth()->user()->member_id)
                  ->orWhere('is_public', true)
                  ->orWhere('created_by', auth()->id());
            });
        }

        // Handle search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $search);
            });
        }

        $tasks = $query->paginate($perPage);

        // Format data for frontend
        $formattedTasks = $tasks->getCollection()->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'department_id' => $task->department_id,
                'department_name' => $task->department?->name,
                'assigned_to' => $task->assigned_to,
                'assigned_to_name' => $task->assignedTo?->full_name,
                'assigned_to_avatar' => $task->assignedTo?->avatar,
                'created_by' => $task->created_by,
                'created_by_name' => $task->createdBy?->name,
                'due_date' => $task->due_date,
                'priority' => $task->priority,
                'status' => $task->status,
                'estimated_hours' => $task->estimated_hours,
                'actual_hours' => $task->actual_hours,
                'is_public' => $task->is_public,
                'tags' => $task->tags ?? [],
                'reminders' => $task->reminders,
                'completed_at' => $task->completed_at,
                'completion_notes' => $task->completion_notes,
                'comments_count' => $task->comments->count(),
                'comments' => $task->comments->map(function($comment) {
                    return [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'is_private' => $comment->is_private,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                            'avatar' => $comment->user->avatar,
                        ],
                        'created_at' => $comment->created_at,
                        'updated_at' => $comment->updated_at,
                    ];
                }),
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
            ];
        });

        // Statistics
        $stats = [
            'total_tasks' => Task::where('church_id', $churchId)->count(),
            'pending' => Task::where('church_id', $churchId)->where('status', 'pending')->count(),
            'in_progress' => Task::where('church_id', $churchId)->where('status', 'in_progress')->count(),
            'completed' => Task::where('church_id', $churchId)->where('status', 'completed')->count(),
            'cancelled' => Task::where('church_id', $churchId)->where('status', 'cancelled')->count(),
            'overdue' => Task::where('church_id', $churchId)
                ->where('due_date', '<', now())
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'my_tasks' => Task::where('church_id', $churchId)
                ->where('assigned_to', auth()->user()->member_id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $tasks->setCollection($formattedTasks),
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'assigned_to' => 'nullable|exists:members,id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'is_public' => 'boolean',
            'tags' => 'nullable|array',
            'completion_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = Task::create([
            'church_id' => auth()->user()->church_id,
            'title' => $request->title,
            'description' => $request->description,
            'department_id' => $request->department_id,
            'assigned_to' => $request->assigned_to,
            'created_by' => auth()->id(),
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'status' => 'pending',
            'estimated_hours' => $request->estimated_hours,
            'actual_hours' => $request->actual_hours,
            'is_public' => $request->boolean('is_public', false),
            'tags' => $request->tags ?? [],
            'reminders' => $request->reminders ?? [],
            'completion_notes' => $request->completion_notes,
        ]);

        // Send notification to assigned member
        if ($request->assigned_to) {
            $this->sendTaskAssignmentNotification($task);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $task->load(['department', 'assignedTo', 'createdBy']),
        ], 201);
    }

    public function show($id)
    {
        $task = Task::where('church_id', auth()->user()->church_id)
            ->with(['department', 'assignedTo', 'createdBy', 'comments.user'])
            ->findOrFail($id);

        // Check permissions
        if (!$this->canAccessTask($task)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this task',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $task,
        ]);
    }

    public function update(Request $request, $id)
    {
        $task = Task::where('church_id', auth()->user()->church_id)->findOrFail($id);

        // Check permissions
        if (!$this->canModifyTask($task)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to modify this task',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'assigned_to' => 'nullable|exists:members,id',
            'due_date' => 'nullable|date',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'is_public' => 'boolean',
            'tags' => 'nullable|array',
            'status' => 'sometimes|required|in:pending,in_progress,completed,cancelled',
            'completion_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // If task is being assigned to someone new
        if ($request->has('assigned_to') && $request->assigned_to != $task->assigned_to) {
            $this->sendTaskAssignmentNotification($task, $request->assigned_to);
        }

        // If status is changing to completed
        if ($request->has('status') && $request->status === 'completed' && $task->status !== 'completed') {
            $request->merge(['completed_at' => now()]);
        }

        // If status is changing from completed
        if ($request->has('status') && $request->status !== 'completed' && $task->status === 'completed') {
            $request->merge(['completed_at' => null]);
        }

        $task->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $task->load(['department', 'assignedTo', 'createdBy', 'comments.user']),
        ]);
    }

    public function destroy($id)
    {
        $task = Task::where('church_id', auth()->user()->church_id)->findOrFail($id);

        // Only admins or creators can delete tasks
        if (!auth()->user()->is_admin && $task->created_by != auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this task',
            ], 403);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    public function assign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:members,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = Task::where('church_id', auth()->user()->church_id)->findOrFail($id);

        // Check permissions
        if (!$this->canModifyTask($task)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to assign this task',
            ], 403);
        }

        $task->update(['assigned_to' => $request->assigned_to]);

        // Send notification
        $this->sendTaskAssignmentNotification($task, $request->assigned_to);

        return response()->json([
            'success' => true,
            'message' => 'Task assigned successfully',
            'data' => $task,
        ]);
    }

    public function complete(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'actual_hours' => 'nullable|numeric|min:0',
            'completion_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = Task::where('church_id', auth()->user()->church_id)->findOrFail($id);

        // Check permissions - only assigned user or admin can update status
        if ($task->assigned_to !== auth()->user()->member_id && !auth()->user()->is_admin && $task->created_by != auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this task',
            ], 403);
        }

        $updateData = [
            'status' => $request->status,
            'actual_hours' => $request->actual_hours ?? $task->actual_hours,
            'completion_notes' => $request->completion_notes,
        ];

        if ($request->status === 'completed') {
            $updateData['completed_at'] = now();
        } else {
            $updateData['completed_at'] = null;
        }

        $task->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully',
            'data' => $task->load(['department', 'assignedTo', 'createdBy']),
        ]);
    }

    public function myTasks(Request $request)
    {
        $perPage = $request->per_page ?? 20;
        $memberId = auth()->user()->member_id;

        if (!$memberId) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'No member profile found',
            ]);
        }

        $query = Task::where('assigned_to', $memberId)
            ->where('church_id', auth()->user()->church_id)
            ->with(['department', 'createdBy', 'comments.user'])
            ->orderBy('due_date', 'asc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('overdue')) {
            $query->where('due_date', '<', now())
                  ->whereIn('status', ['pending', 'in_progress']);
        }

        $tasks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tasks,
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

        $task = Task::where('church_id', auth()->user()->church_id)->findOrFail($id);

        // Check if user can comment on this task
        if (!$this->canAccessTask($task)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to comment on this task',
            ], 403);
        }

        $comment = $task->comments()->create([
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

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = Task::where('church_id', auth()->user()->church_id)
            ->with(['department', 'assignedTo', 'createdBy'])
            ->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->query}%")
                  ->orWhere('description', 'like', "%{$request->query}%")
                  ->orWhereJsonContains('tags', $request->query);
            });

        // For regular users, show only assigned tasks or public tasks
        if (!auth()->user()->is_admin) {
            $query->where(function($q) {
                $q->where('assigned_to', auth()->user()->member_id)
                  ->orWhere('is_public', true)
                  ->orWhere('created_by', auth()->id());
            });
        }

        $tasks = $query->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $tasks,
        ]);
    }

    private function canAccessTask($task)
    {
        $user = auth()->user();

        // Admin can access all tasks
        if ($user->is_admin) {
            return true;
        }

        // Task creator can access their own tasks
        if ($task->created_by == $user->id) {
            return true;
        }

        // Assigned member can access tasks assigned to them
        if ($task->assigned_to == $user->member_id) {
            return true;
        }

        // Public tasks can be accessed by anyone in the church
        if ($task->is_public) {
            return true;
        }

        return false;
    }

    private function canModifyTask($task)
    {
        $user = auth()->user();

        // Admin can modify all tasks
        if ($user->is_admin) {
            return true;
        }

        // Task creator can modify their own tasks
        if ($task->created_by == $user->id) {
            return true;
        }

        return false;
    }

    private function sendTaskAssignmentNotification($task, $newAssigneeId = null)
    {
        // Implement notification logic here
        // This could be email, push notification, or in-app notification
        // For now, we'll just log it
        \Log::info('Task assigned', [
            'task_id' => $task->id,
            'task_title' => $task->title,
            'assigned_to' => $newAssigneeId ?? $task->assigned_to,
            'assigned_by' => auth()->user()->id,
        ]);

        // You can integrate with your notification system here
        // Example:
        // $member = Member::find($newAssigneeId ?? $task->assigned_to);
        // if ($member && $member->user) {
        //     $member->user->notify(new TaskAssignedNotification($task));
        // }
    }
}
