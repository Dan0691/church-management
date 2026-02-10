<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteer extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'member_id',
        'event_id',
        'department_id',
        'role',
        'date',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
