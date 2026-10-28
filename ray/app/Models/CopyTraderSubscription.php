<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CopyTraderSubscription extends Model
{
    protected $fillable = [
        'follower_id',
        'copy_trader_id',
        'fee_amount',
        'paid_amount',
        'status',
        'expires_at',
        'paid_at',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:9',
        'paid_amount' => 'decimal:9',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function copyTrader()
    {
        return $this->belongsTo(CopyTrader::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}