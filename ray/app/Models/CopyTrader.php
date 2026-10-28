<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CopyTrader extends Model
{
    protected $fillable = [
        'user_id',
        'display_name',
        'slug',
        'tagline',
        'bio',
        'avatar_url',
        'banner_url',
        'wallet_address',
        'tags',
        'risk_level',
        'is_verified',
        'is_featured',
        'is_active',
        'followers_count',
        'trades_count',
        'total_pnl_usd',
        'roi_7d',
        'roi_30d',
        'win_rate',
        'max_drawdown',
        'volume_24h_usd',
        'avg_trade_usd',
        'stats_history',
        'socials',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'tags' => 'array',
        'stats_history' => 'array',
        'socials' => 'array',
        'total_pnl_usd' => 'decimal:2',
        'roi_7d' => 'decimal:2',
        'roi_30d' => 'decimal:2',
        'win_rate' => 'decimal:2',
        'max_drawdown' => 'decimal:2',
        'volume_24h_usd' => 'decimal:2',
        'avg_trade_usd' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $trader) {
            if (empty($trader->slug) && !empty($trader->display_name)) {
                $trader->slug = Str::slug($trader->display_name) . '-' . Str::lower(Str::random(4));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(CopyTraderSubscription::class);
    }

    public function trades()
    {
        return $this->hasMany(CopyTrade::class);
    }

    public function isSubscribedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->subscriptions()
            ->where('follower_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function getShortWalletAttribute(): ?string
    {
        if (!$this->wallet_address) {
            return null;
        }
        $w = $this->wallet_address;
        return strlen($w) > 12 ? substr($w, 0, 4) . '...' . substr($w, -4) : $w;
    }

    public function getPnlColorAttribute(): string
    {
        return $this->total_pnl_usd >= 0 ? 'fx-up' : 'fx-down';
    }

    public function getRiskScoreAttribute(): int
    {
        return match ($this->risk_level) {
            'low' => 25,
            'medium' => 55,
            'high' => 85,
            default => 55,
        };
    }
}
