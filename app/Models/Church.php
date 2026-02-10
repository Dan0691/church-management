<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Church extends Model
{
    use HasFactory;

    protected $fillable = [
    'name',
    'slug',
    'subdomain',
    'email',
    'phone',
    'address',
    'city',
    'state',
    'country',
    'postal_code',
    'pastor_name',
    'pastor_phone',
    'pastor_email',
    'website',
    'logo',
    'banner_image',
    'denomination',
    'established_date',
    'membership_count',
    'service_times',
    'about',
    'mission_statement',
    'vision_statement',
    'core_values',
    'social_media',
    'bank_name',
    'account_number',
    'account_name',
    'swift_code',
    'is_active',
    'settings',
    'metadata'
];
    // Automatically create slug before saving
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($church) {
            if (empty($church->slug)) {
                $church->slug = Str::slug($church->name);
            }
        });
    }

    // Relationship: A church has many users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relationship: A church has many members
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    // Relationship: A church has many events
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
