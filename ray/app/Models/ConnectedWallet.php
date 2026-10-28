<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectedWallet extends Model
{
    protected $fillable = ['user_id', 'chain', 'address', 'label', 'is_primary'];
    protected $casts = ['is_primary' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
