<?php

namespace App\Services;

use App\Models\PointEvent;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Axiom-style points & referral ledger. Points are earned from trading,
 * referrals and quests; the tier table mirrors Axiom's reward ranks.
 */
class RewardsService
{
    public const TIERS = [
        ['name' => 'Bronze',   'min' => 0,     'rate' => '1.00x'],
        ['name' => 'Silver',   'min' => 1000,  'rate' => '1.05x'],
        ['name' => 'Gold',     'min' => 5000,  'rate' => '1.15x'],
        ['name' => 'Platinum', 'min' => 20000, 'rate' => '1.30x'],
        ['name' => 'Diamond',  'min' => 100000, 'rate' => '1.50x'],
    ];

    public function total(User $user): int
    {
        return (int) $user->pointEvents()->sum('points');
    }

    public function award(User $user, string $kind, int $points, array $meta = []): PointEvent
    {
        return $user->pointEvents()->create([
            'kind'   => $kind,
            'points' => $points,
            'meta'   => $meta ?: null,
        ]);
    }

    public function claimDaily(User $user): array
    {
        $today = now()->startOfDay();
        $already = $user->pointEvents()
            ->where('kind', 'daily')
            ->where('created_at', '>=', $today)
            ->exists();

        if ($already) {
            return ['claimed' => false, 'points' => 0];
        }

        $points = (int) \App\Models\SiteSetting::get('daily_points', '100');
        $this->award($user, 'daily', $points, ['reason' => 'Daily check-in']);

        return ['claimed' => true, 'points' => $points];
    }

    public function tier(int $points): array
    {
        $current = self::TIERS[0];
        $next    = null;
        foreach (self::TIERS as $i => $t) {
            if ($points >= $t['min']) {
                $current = $t;
                $next    = self::TIERS[$i + 1] ?? null;
            }
        }
        $span = $next ? ($next['min'] - $current['min']) : 1;
        $into = $next ? ($points - $current['min']) : 1;
        $pct  = $next ? min(100, (int) round($into / max($span, 1) * 100)) : 100;

        return ['current' => $current, 'next' => $next, 'progress' => $pct];
    }

    public function claimedToday(User $user): bool
    {
        return $user->pointEvents()->where('kind', 'daily')->whereDate('created_at', today())->exists();
    }

    public function ensureCode(User $user): string
    {
        if (! $user->referral_code) {
            do {
                $code = strtoupper(Str::random(8));
            } while (User::where('referral_code', $code)->exists());
            $user->forceFill(['referral_code' => $code])->save();
        }

        return $user->referral_code;
    }

    /** @return \Illuminate\Support\Collection<int,User> */
    public function referrals(User $user)
    {
        return User::where('referred_by', $user->id)->latest()->get();
    }

    public function history(User $user, int $limit = 25)
    {
        return $user->pointEvents()->latest()->limit($limit)->get();
    }
}
