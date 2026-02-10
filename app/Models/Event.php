<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
     protected $fillable = [
        'title', 'description', 'start_date', 'end_date',
        'location', 'type', 'church_id'
    ];

    public function attendances()
  {
      return $this->hasMany(Attendance::class);
  }
    public function church()
  {
      return $this->belongsTo(Church::class);
  }

}
