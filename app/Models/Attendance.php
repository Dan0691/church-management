<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'children', 'men', 'women', 'visitors', 'total', 'recorded_by','church_id','notes'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

        public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
