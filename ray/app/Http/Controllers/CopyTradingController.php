<?php

namespace App\Http\Controllers;

use App\Models\CopyTrader;
use App\Models\CopyTraderSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CopyTradingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $this->hasCopyAccess($user)) {
            return view('copy-trading.gate', [
                'title' => 'Copy Trading',
                'active' => 'copy-trading',
                'hasWallet' => $user ? $user->connectedWallets()->exists() : false,
                'hasDeposit' => $user ? $user->depositAddresses()->exists() : false,
                'stats' => [
                    'traders' => CopyTrader::where('is_active', true)->count(),
                    'followers' => (int) CopyTrader::where('is_active', true)->sum('followers_count'),
                    'volume' => (float) CopyTrader::where('is_active', true)->sum('volume_24h_usd'),
                    'winRate' => (float) CopyTrader::where('is_active', true)->avg('win_rate'),
                ],
            ]);
        }

        $filter = $request->query('filter', 'all');
        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'popular');

        $query = CopyTrader::where('is_active', true);

        $query->when($search !== '', function ($q) use ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('display_name', 'like', "%{$search}%")
                    ->orWhere('tagline', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        });

        switch ($filter) {
            case 'featured':
                $query->where('is_featured', true);
                break;
            case 'verified':
                $query->where('is_verified', true);
                break;
            case 'top-pnl':
                $query->orderByDesc('total_pnl_usd');
                break;
            case 'top-roi':
                $query->orderByDesc('roi_30d');
                break;
            case 'winrate':
                $query->orderByDesc('win_rate');
                break;
            case 'low-risk':
                $query->where('risk_level', 'low');
                break;
            case 'high-risk':
                $query->where('risk_level', 'high');
                break;
            case 'new':
                $query->orderByDesc('created_at');
                break;
        }

        switch ($sort) {
            case 'pnl':
                $query->orderByDesc('total_pnl_usd');
                break;
            case 'roi':
                $query->orderByDesc('roi_30d');
                break;
            case 'winrate':
                $query->orderByDesc('win_rate');
                break;
            case 'newest':
                $query->orderByDesc('created_at');
                break;
            default:
                $query->orderByDesc('followers_count');
        }

        $traders = $query->paginate(60)->withQueryString();

        $featured = CopyTrader::where('is_active', true)
            ->where('is_featured', true)
            ->orderByDesc('followers_count')
            ->take(3)
            ->get();

        $mySubscription = null;
        if ($user) {
            $mySubscription = CopyTraderSubscription::where('follower_id', $user->id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();
        }

        $stats = [
            'traders' => CopyTrader::where('is_active', true)->count(),
            'followers' => (int) CopyTrader::where('is_active', true)->sum('followers_count'),
            'volume' => (float) CopyTrader::where('is_active', true)->sum('volume_24h_usd'),
            'winRate' => (float) CopyTrader::where('is_active', true)->avg('win_rate'),
        ];

        $liveTrades = \App\Models\CopyTrade::query()
            ->join('copy_traders', 'copy_traders.id', '=', 'copy_trades.copy_trader_id')
            ->where('copy_traders.is_active', true)
            ->orderByDesc('copy_trades.executed_at')
            ->take(14)
            ->get(['copy_trades.*', 'copy_traders.display_name as trader_name']);

        return view('copy-trading.index', [
            'title' => 'Copy Trading',
            'active' => 'copy-trading',
            'traders' => $traders,
            'featured' => $featured,
            'mySubscription' => $mySubscription,
            'filter' => $filter,
            'search' => $search,
            'sort' => $sort,
            'stats' => $stats,
            'liveTrades' => $liveTrades,
        ]);
    }

    public function show(string $slug)
    {
        $user = Auth::user();

        if (! $this->hasCopyAccess($user)) {
            return redirect('/copy-trading');
        }

        $trader = CopyTrader::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $recentTrades = $trader->trades()
            ->orderByDesc('executed_at')
            ->take(25)
            ->get();

        return view('copy-trading.show', [
            'title' => $trader->display_name . ' — Copy Trading',
            'active' => 'copy-trading',
            'trader' => $trader,
            'subscribed' => $trader->isSubscribedBy($user),
            'recentTrades' => $recentTrades,
        ]);
    }

    public function subscribe(Request $request, int $id)
    {
        $user = $request->user();
        if (!$user) {
            return redirect('/login')->withErrors(['error' => 'Please login first.']);
        }

        $trader = CopyTrader::where('id', $id)->where('is_active', true)->firstOrFail();

        if (! $this->hasCopyAccess($user)) {
            return back()->withErrors(['error' => 'Connect a wallet and add a deposit address before subscribing.']);
        }

        $existing = CopyTraderSubscription::where('follower_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
        if ($existing) {
            return back()->withErrors(['error' => 'You already have an active copy trading subscription.']);
        }

        $subscription = CopyTraderSubscription::create([
            'follower_id' => $user->id,
            'copy_trader_id' => $trader->id,
            'fee_amount' => 100.00,
            'paid_amount' => 100.00,
            'status' => 'active',
            'paid_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        $trader->increment('followers_count');

        return redirect("/copy-trading/{$trader->slug}")
            ->with('ok', "You are now copying {$trader->display_name}. $100.00 charged — renews {$subscription->expires_at->format('M j, Y')}.");
    }

    /**
     * Copy traders are only visible to users who have connected a wallet
     * AND added a deposit address (funding set up).
     */
    private function hasCopyAccess(?object $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->connectedWallets()->exists()
            && $user->depositAddresses()->exists();
    }
}
