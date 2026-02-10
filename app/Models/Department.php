<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'church_id',
        'leader_id',
        'email',
        'phone',
        'meeting_schedule',
        'category',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    // Relationships
    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function leader()
    {
        return $this->belongsTo(Member::class, 'leader_id');
    }

    public function members()
    {
        return $this->belongsToMany(Member::class, 'department_member')
                    ->withPivot(['role', 'joined_date', 'notes'])
                    ->withTimestamps();
    }

    public function activities()
    {
        return $this->hasMany(DepartmentActivity::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'department_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Methods
    public function getMemberCountAttribute()
    {
        return $this->members()->count();
    }

    public function getUpcomingActivitiesAttribute()
    {
        return $this->activities()
            ->where('activity_date', '>=', now())
            ->orderBy('activity_date')
            ->get();
    }

    public function getMemberRoles()
    {
        return $this->members()->pluck('role')->unique();
    }
}

class DepartmentMember extends Model
{
    use HasFactory;

    protected $table = 'department_member';

    protected $fillable = [
        'department_id',
        'member_id',
        'role',
        'joined_date',
        'notes',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}

class DepartmentActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'user_id',
        'type',
        'title',
        'description',
        'activity_date',
        'attendance',
    ];

    protected $casts = [
        'activity_date' => 'datetime',
        'attendance' => 'array',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
