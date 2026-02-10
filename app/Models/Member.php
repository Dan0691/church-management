<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone',
        'birth_date', 'join_date', 'address', 'city',
        'state', 'zip_code', 'membership_status', 'church_id',
        'gender', 'marital_status', 'occupation', 'notes', 'created_by'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'join_date' => 'date',
    ];

    // Make sure these new fields are nullable
    protected $attributes = [
        'gender' => null,
        'marital_status' => null,
        'occupation' => null,
        'notes' => null,
        'created_by' => null,
    ];

    /**
     * Get the church that owns the member.
     */
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    /**
     * Get the user who created this member.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the member's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope a query to only include active members.
     */
    public function scopeActive($query)
    {
        return $query->where('membership_status', 'active');
    }

    /**
     * Scope a query to only include inactive members.
     */
    public function scopeInactive($query)
    {
        return $query->where('membership_status', 'inactive');
    }

    /**
     * Scope a query to only include visitors.
     */
    public function scopeVisitors($query)
    {
        return $query->where('membership_status', 'visitor');
    }

    /**
     * Get the age of the member.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    /**
     * Get the membership duration in years.
     */
    public function getMembershipDurationAttribute(): ?string
    {
        return $this->join_date?->diffForHumans();
    }
}
