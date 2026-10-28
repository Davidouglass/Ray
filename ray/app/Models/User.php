<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'referral_code',
        'referred_by',
        'is_admin',
        'status',
        'admin_role',
        'suspended_reason',
        'last_login_at',
    ];

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_admin && $this->admin_role === 'super';
    }

    public function canManageSettings(): bool
    {
        return $this->is_admin && in_array($this->admin_role, ['super', 'admin'], true);
    }

    public function canManageAdmins(): bool
    {
        return $this->is_admin && $this->admin_role === 'super';
    }

    public function trackedWallets()
    {
        return $this->hasMany(TrackedWallet::class);
    }

    public function connectedWallets()
    {
        return $this->hasMany(ConnectedWallet::class);
    }

    public function depositAddresses()
    {
        return $this->hasMany(DepositAddress::class);
    }

    public function watchlist()
    {
        return $this->hasMany(WatchlistItem::class);
    }

    public function streams()
    {
        return $this->hasMany(Stream::class);
    }

    public function pointEvents()
    {
        return $this->hasMany(PointEvent::class);
    }

    public function points(): int
    {
        return (int) $this->pointEvents()->sum('points');
    }

    public function copyTrader()
    {
        return $this->hasOne(CopyTrader::class);
    }

    public function copySubscriptions()
    {
        return $this->hasMany(CopyTraderSubscription::class, 'follower_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }
}
