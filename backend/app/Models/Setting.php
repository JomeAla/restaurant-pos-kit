<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if (!$setting) return $default;
            $value = $setting->value;

            if (is_null($value)) return $default;
            if ($value === 'true' || $value === 'false') return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            if (is_numeric($value)) return str_contains($value, '.') ? (float) $value : (int) $value;

            return $value;
        });
    }

    public static function setValue(string $key, mixed $value, string $group = 'general'): void
    {
        $val = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
        static::updateOrCreate(['key' => $key], ['value' => $val, 'group' => $group]);
        Cache::forget("setting.{$key}");
    }

    public static function getGroup(string $group): array
    {
        return static::where('group', $group)->get()->pluck('value', 'key')->toArray();
    }
}
