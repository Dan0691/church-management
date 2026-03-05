<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'church_id',
        'title',
        'description',
        'type',
        'category',
        'start_date',
        'end_date',
        'location',
        'created_by',
        'recurring',
        'recurrence_pattern',
        'recurrence_end_date',
        'send_notifications',
        'track_attendance',
        'image',
        'capacity',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'recurrence_end_date' => 'date',
        'recurring' => 'boolean',
        'send_notifications' => 'boolean',
        'track_attendance' => 'boolean',
    ];

    protected $appends = ['is_upcoming', 'is_past', 'duration_hours'];

    /**
     * Relationships
     */
    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function volunteers()
    {
        return $this->hasMany(Volunteer::class);
    }

    /**
     * Scopes
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<=', now());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('start_date', now()->month)
                     ->whereYear('start_date', now()->year);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('start_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_date', today());
    }

    /**
     * Accessors
     */
    public function getIsUpcomingAttribute()
    {
        return $this->start_date > now();
    }

    public function getIsPastAttribute()
    {
        return $this->end_date <= now();
    }

    public function getDurationHoursAttribute()
    {
        if ($this->start_date && $this->end_date) {
            return $this->end_date->diffInHours($this->start_date);
        }
        return 0;
    }

    /**
     * Get total attendance
     */
    public function getTotalAttendance()
    {
        return $this->attendances()->sum('total');
    }

    /**
     * Get attendance by category
     */
    public function getAttendanceBreakdown()
    {
        return [
            'men' => $this->attendances()->sum('men'),
            'women' => $this->attendances()->sum('women'),
            'children' => $this->attendances()->sum('children'),
            'visitors' => $this->attendances()->sum('visitors'),
        ];
    }
}
