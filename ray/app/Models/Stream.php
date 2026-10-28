<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'token_address', 'token_symbol',
        'source', 'embed_url', 'status', 'moderation_status', 'moderated_at',
        'moderated_by', 'viewers', 'started_at', 'ended_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'ended_at'     => 'datetime',
        'moderated_at' => 'datetime',
        'viewers'      => 'integer',
    ];

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    /** Human "up" duration since start (e.g. 1h 04m). */
    public function uptime(): string
    {
        if (! $this->started_at) {
            return '—';
        }
        $end = $this->ended_at ?? now();
        $min = (int) $this->started_at->diffInMinutes($end);
        if ($min < 60) {
            return $min . 'm';
        }

        return intdiv($min, 60) . 'h ' . str_pad((string) ($min % 60), 2, '0', STR_PAD_LEFT) . 'm';
    }
}
