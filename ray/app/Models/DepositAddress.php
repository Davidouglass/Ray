<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositAddress extends Model
{
    protected $fillable = ['user_id', 'chain', 'address', 'label'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
