<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'order',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = Cache::rememberForever("setting_{$key}", function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => (bool) $setting->value,
            'json'    => json_decode($setting->value, true),
            default   => $setting->value,
        };
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'text', string $group = 'general'): void
    {
        if ($type === 'json' && is_array($value)) {
            $value = json_encode($value);
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );

        Cache::forget("setting_{$key}");
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)
            ->orderBy('order')
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget("setting_{$setting->key}");
        }
    }

    /**
     * Check if setting is a file
     */
    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    /**
     * Get URL for file settings
     */
    public function getUrl(): ?string
    {
        if (! $this->value) {
            return null;
        }

        if (filter_var($this->value, FILTER_VALIDATE_URL)) {
            return $this->value;
        }

        return asset('uploads/' . $this->value);
    }
}
