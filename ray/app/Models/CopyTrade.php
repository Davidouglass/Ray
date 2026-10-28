<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CopyTrade extends Model
{
    protected $fillable = [
        'copy_trader_id',
        'subscription_id',
        'action',
        'mint',
        'symbol',
        'amount',
        'price',
        'total_usd',
        'tx_signature',
        'executed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:9',
        'price' => 'decimal:9',
        'total_usd' => 'decimal:2',
        'executed_at' => 'datetime',
    ];

    public function copyTrader()
    {
        return $this->belongsTo(CopyTrader::class);
    }

    public function subscription()
    {
        return $this->belongsTo(CopyTraderSubscription::class);
    }
}