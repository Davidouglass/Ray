<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    public const SUPPORT_URL_DEFAULT = 'https://t.me/Veridain_supportTeam';

    /** Feature keys that can be toggled on/off from the admin panel. */
    public const FLAGS = [
        'copy-trading' => 'Copy Trading',
        'perpetuals'   => 'Perpetuals',
        'predictions'  => 'Predictions',
        'vision'       => 'Vision',
        'streams'      => 'Streams',
        'callouts'     => 'Callouts',
        'social'       => 'Social',
        'launchpads'   => 'Launchpads',
        'trackers'     => 'Trackers',
        'friends'      => 'Friends',
        'markets'      => 'Markets',
        'pnl'          => 'PnL',
    ];

    protected $fillable = ['key', 'value'];

    /** @var array<string, string|null> */
    private static array $memo = [];

    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, self::$memo)) {
            return self::$memo[$key];
        }

        $value = Cache::rememberForever('site_setting:' . $key, function () use ($key, $default) {
            $row = static::query()->where('key', $key)->first();

            return $row ? $row->value : $default;
        });

        return self::$memo[$key] = $value;
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget('site_setting:' . $key);
        self::$memo[$key] = $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return trim((string) self::get($key, $default ? '1' : '0')) !== '0';
    }

    public static function flag(string $key, bool $default = true): bool
    {
        return self::bool('flag_' . $key, $default);
    }

    public static function supportEnabled(): bool
    {
        return self::bool('support_enabled', true);
    }

    public static function supportUrl(): string
    {
        if (! self::supportEnabled()) {
            return '';
        }

        $url = trim((string) self::get('support_url', self::SUPPORT_URL_DEFAULT));

        return $url === '' ? self::SUPPORT_URL_DEFAULT : $url;
    }
}
