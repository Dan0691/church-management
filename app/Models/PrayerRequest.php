<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerRequest extends Model
{
    //
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrayerRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'church_id',
        'member_id',
        'title',
        'request',
        'category',
        'privacy',
        'priority',
        'status',
        'prayer_count',
        'is_answered',
        'answered_at',
        'allow_prayers',
        'allow_comments',
        'created_by',
    ];

    protected $casts = [
        'is_answered' => 'boolean',
        'allow_prayers' => 'boolean',
        'allow_comments' => 'boolean',
        'answered_at' => 'datetime',
    ];

    // Relationships
    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function prayedBy()
    {
        return $this->belongsToMany(User::class, 'prayer_request_prayers')
                    ->withPivot('prayed_at')
                    ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(PrayerRequestComment::class);
    }

    public function answers()
    {
        return $this->hasMany(PrayerRequestAnswer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('privacy', 'public');
    }

    public function scopeChurchOnly($query)
    {
        return $query->where('privacy', 'church_only');
    }

    public function scopePrivate($query)
    {
        return $query->where('privacy', 'private');
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeAnswered($query)
    {
        return $query->where('is_answered', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Methods
    public function isPublic()
    {
        return $this->privacy === 'public';
    }

    public function isAnswered()
    {
        return $this->is_answered;
    }

    public function getUserPrayedAttribute()
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->prayedBy()
            ->where('user_id', auth()->id())
            ->exists();
    }

    public function getShortRequestAttribute()
    {
        return strlen($this->request) > 100
            ? substr($this->request, 0, 100) . '...'
            : $this->request;
    }
}

class PrayerRequestComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'prayer_request_id',
        'user_id',
        'comment',
        'is_private',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function prayerRequest()
    {
        return $this->belongsTo(PrayerRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class PrayerRequestAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'prayer_request_id',
        'user_id',
        'answer',
        'answered_date',
    ];

    protected $casts = [
        'answered_date' => 'date',
    ];

    public function prayerRequest()
    {
        return $this->belongsTo(PrayerRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
