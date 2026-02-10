<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sermon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'church_id',
        'title',
        'description',
        'speaker',
        'sermon_date',
        'scripture_reference',
        'series',
        'duration',
        'audio_url',
        'video_url',
        'slides_url',
        'notes_url',
        'audio_file_name',
        'video_file_name',
        'slides_file_name',
        'notes_file_name',
        'audio_file_size',
        'video_file_size',
        'slides_file_size',
        'notes_file_size',
        'views',
        'downloads',
        'created_by',
    ];

    protected $casts = [
        'sermon_date' => 'date',
        'views' => 'integer',
        'downloads' => 'integer',
        'duration' => 'integer',
        'audio_file_size' => 'integer',
        'video_file_size' => 'integer',
        'slides_file_size' => 'integer',
        'notes_file_size' => 'integer',
    ];

    // Relationships
    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('sermon_date', 'desc')->limit($limit);
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('views', 'desc')->limit($limit);
    }

    public function scopeBySeries($query, $series)
    {
        return $query->where('series', $series);
    }

    public function scopeBySpeaker($query, $speaker)
    {
        return $query->where('speaker', $speaker);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('sermon_date', $year);
    }

    // Methods
    public function hasAudio()
    {
        return !empty($this->audio_url);
    }

    public function hasVideo()
    {
        return !empty($this->video_url);
    }

    public function hasSlides()
    {
        return !empty($this->slides_url);
    }

    public function hasNotes()
    {
        return !empty($this->notes_url);
    }

    public function getFormattedDuration()
    {
        if (!$this->duration) return null;

        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        }

        return sprintf('%dm', $minutes);
    }

    public function getFileSize($type)
    {
        $sizeField = $type . '_file_size';
        $size = $this->$sizeField;

        if (!$size) return null;

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($size, 1024));

        return round($size / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    public function incrementViewCount()
    {
        $this->increment('views');
        $this->save();
    }

    public function incrementDownloadCount()
    {
        $this->increment('downloads');
        $this->save();
    }
}
