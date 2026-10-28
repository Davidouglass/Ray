<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointEvent extends Model
{
    protected $fillable = ['user_id', 'kind', 'points', 'meta'];
    protected $casts = ['meta' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
