<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'church_id',
        'description',
        'options',
        'is_public',
        'category'
    ];

    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean'
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public static function getValue($key, $churchId = null, $default = null)
    {
        $query = self::where('key', $key);

        if ($churchId) {
            $query->where('church_id', $churchId);
        }

        $setting = $query->first();

        return $setting ? $setting->value : $default;
    }

    public static function setValue($key, $value, $churchId = null, $type = 'text', $description = null)
    {
        $data = [
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'description' => $description
        ];

        if ($churchId) {
            $data['church_id'] = $churchId;
        }

        return self::updateOrCreate(
            ['key' => $key, 'church_id' => $churchId],
            $data
        );
    }

    public static function getSettingsByCategory($churchId = null)
    {
        $query = self::query();

        if ($churchId) {
            $query->where('church_id', $churchId);
        } else {
            $query->whereNull('church_id');
        }

        return $query->get()->groupBy('category');
    }
}
