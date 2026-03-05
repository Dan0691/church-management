<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Has Church Trait
 *
 * Provides automatic church_id filtering for queries.
 * Ensures users can only access their own church's data.
 *
 * Usage:
 * class Member extends Model {
 *   use HasChurch;
 * }
 *
 * All queries will automatically be scoped to the authenticated user's church.
 */
trait HasChurch
{
    /**
     * Boot the trait
     */
    public static function bootHasChurch()
    {
        // Automatically scope queries to current church
        static::addGlobalScope('church', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('church_id', auth()->user()->church_id);
            }
        });
    }

    /**
     * Get the church that owns this model
     */
    public function church()
    {
        return $this->belongsTo(\App\Models\Church::class, 'church_id');
    }

    /**
     * Get records for all churches (admin only)
     *
     * @return Builder
     */
    public static function allChurches()
    {
        return static::withoutGlobalScopes();
    }

    /**
     * Get records for a specific church
     *
     * @param int $churchId
     * @return Builder
     */
    public static function forChurch($churchId)
    {
        return static::withoutGlobalScopes()->where('church_id', $churchId);
    }
}
