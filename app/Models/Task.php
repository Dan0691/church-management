<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'church_id',
        'title',
        'description',
        'department_id',
        'assigned_to',
        'created_by',
        'due_date',
        'priority',
        'status',
        'estimated_hours',
        'tags',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'tags' => 'array',
        'estimated_hours' => 'integer',
    ];

    // Relationships
    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(Member::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TaskTimeEntry::class);
    }

    // Scopes
    public function scopeAssignedTo($query, $memberId)
    {
        return $query->where('assigned_to', $memberId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('status', ['pending', 'in_progress']);
    }

    // Methods
    public function getTotalTimeSpent()
    {
        return $this->timeEntries()->sum('hours_spent');
    }

    public function isOverdue()
    {
        return $this->due_date < now() && in_array($this->status, ['pending', 'in_progress']);
    }

    public function markAsComplete($userId)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $userId,
        ]);
    }

    public function addComment($userId, $comment)
    {
        return $this->comments()->create([
            'user_id' => $userId,
            'comment' => $comment,
        ]);
    }

    public function logTime($userId, $hours, $description = null)
    {
        return $this->timeEntries()->create([
            'user_id' => $userId,
            'hours_spent' => $hours,
            'description' => $description,
            'date' => now(),
        ]);
    }
}

class TaskComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'comment',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class TaskTimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'hours_spent',
        'description',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'hours_spent' => 'decimal:2',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
