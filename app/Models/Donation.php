<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'member_id',
        'donation_type_id',
        'transaction_id',
        'amount',
        'currency',
        'payment_method',
        'check_number',
        'receipt_number',
        'donation_date',
        'frequency',
        'notes',
        'metadata',
        'recorded_by',
        'is_verified',
        'is_recurring',
        'next_payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'donation_date' => 'date',
        'next_payment_date' => 'date',
        'is_verified' => 'boolean',
        'is_recurring' => 'boolean',
        'metadata' => 'array',
        'tags' => 'array',
    ];

    protected $appends = ['formatted_amount', 'status_badge'];

    // Relationships
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function donationType(): BelongsTo
    {
        return $this->belongsTo(DonationType::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function getStatusBadgeAttribute(): array
    {
        return $this->is_verified 
            ? ['color' => 'success', 'label' => 'Verified']
            : ['color' => 'warning', 'label' => 'Pending'];
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('donation_date', now()->month)
                    ->whereYear('donation_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('donation_date', now()->year);
    }

    public function scopeByMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    public function scopeByType($query, $typeId)
    {
        return $query->where('donation_type_id', $typeId);
    }
}