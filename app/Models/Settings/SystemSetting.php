<?php

namespace App\Models\Settings;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    use HasUuid;

    protected $fillable = ['group', 'key', 'value', 'is_encrypted'];

    protected $casts = ['is_encrypted' => 'boolean'];

    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("settings.{$group}.{$key}", function () use ($group, $key, $default) {
            $setting = static::query()->where('group', $group)->where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return $setting->is_encrypted ? Crypt::decryptString($setting->value) : $setting->value;
        });
    }

    public static function set(string $group, string $key, mixed $value, bool $encrypted = false): self
    {
        $stored = $encrypted ? Crypt::encryptString((string) $value) : $value;

        $setting = static::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $stored, 'is_encrypted' => $encrypted]
        );

        Cache::forget("settings.{$group}.{$key}");

        return $setting;
    }

    public static function group(string $group): array
    {
        return static::query()->where('group', $group)->get()
            ->mapWithKeys(fn ($s) => [$s->key => $s->is_encrypted ? Crypt::decryptString($s->value) : $s->value])
            ->toArray();
    }
}
