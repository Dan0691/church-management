<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'church_id',
        'profile_photo',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'bio',
        'date_of_birth',
        'gender',
        'marital_status',
        'occupation',
        'emergency_contact_name',
        'emergency_contact_phone',
        'is_active',
        'last_login_at',
        'email_verified_at',
        'settings'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'settings' => 'array'
    ];

      // Add this accessor for the profile photo URL
    protected $appends = ['profile_photo_url'];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }


    // Check if user is admin of their church
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Check if user is pastor
    public function isPastor(): bool
    {
        return $this->role === 'pastor';
    }

    // public function getProfilePhotoUrlAttribute()
    // {
    //     if ($this->profile_photo) {
    //         return asset('storage/' . $this->profile_photo);
    //     }

    //     return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    // }

       public function getProfilePhotoUrlAttribute()
    {
        if (!$this->profile_photo) {
            return null;
        }

        return Storage::url($this->profile_photo);
    }
}
