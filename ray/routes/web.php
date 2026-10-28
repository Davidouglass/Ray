<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalloutsController;
use App\Http\Controllers\CopyTradingController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\LaunchpadsController;
use App\Http\Controllers\MarketsController;
use App\Http\Controllers\PerpetualsController;
use App\Http\Controllers\PnlController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PredictionsController;
use App\Http\Controllers\RewardsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\StreamsController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\TrackersController;
use App\Http\Controllers\VisionController;
use App\Models\ContentPage;
use App\Services\DiscoverFeed;
use App\Services\MarketFeed;
use App\Services\PulseFeed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/install', [\App\Http\Controllers\InstallController::class, 'run']);

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect('/discover');
    }
    return view('home', ['authMode' => 'login']);
})->name('login');

Route::get('/signup', function () {
    if (Auth::check()) {
        return redirect('/discover');
    }
    return view('home', ['authMode' => 'signup']);
});

Route::get('/terms', function () {
    $page = ContentPage::render('terms');

    return view('legal', [
        'title'    => 'Terms of Service',
        'heading'  => $page['title'],
        'icon'     => 'file-list-3-line',
        'updated'  => $page['updated'],
        'sections' => $page['sections'],
        'html'     => $page['html'],
    ]);
});

Route::get('/privacy', function () {
    $page = ContentPage::render('privacy');

    return view('legal', [
        'title'    => 'Privacy Policy',
        'heading'  => $page['title'],
        'icon'     => 'shield-keyhole-line',
        'updated'  => $page['updated'],
        'sections' => $page['sections'],
        'html'     => $page['html'],
    ]);
});

Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::get('/discover', function (DiscoverFeed $feed, MarketFeed $market) {
    $tokens = $feed->trending();
    return view('discover', [
        'liveTokens'  => $tokens,
        'spot'        => $market->spot(),
        'liveStreams' => \App\Models\Stream::where('status', 'live')->with('user')->orderByDesc('viewers')->limit(8)->get(),
        'community'   => array_slice($tokens, 0, 8),
    ]);
})->middleware('auth');

Route::get('/pulse', function (PulseFeed $feed, MarketFeed $market) {
    return view('pulse', [
        'pulse' => $feed->all(),
        'spot'  => $market->spot(),
    ]);
})->middleware('auth');

Route::get('/chain/{key}', function (string $key, DiscoverFeed $feed, \App\Services\ChainFeed $chainFeed) {
    $asset = 'https://axiom-assets-v2.axiom-cdn.io/images';
    $meta = [
        'sol'       => ['name' => 'Solana',      'native' => 'SOL',  'color' => '#526FFF', 'art' => "$asset/sol-fill.svg"],
        'robinhood' => ['name' => 'Robinhood',   'native' => 'ETH',  'color' => '#BF0DF5', 'art' => "$asset/robinhood-logo.svg"],
        'arc'       => ['name' => 'Arc',         'native' => 'USDC', 'color' => '#2775CA', 'art' => "$asset/arc.svg"],
        'bnb'       => ['name' => 'BNB Chain',   'native' => 'BNB',  'color' => '#F0B90B', 'art' => "$asset/bnb-fill.svg"],
        'ink'       => ['name' => 'Ink',         'native' => 'ETH',  'color' => '#7132F5', 'art' => "$asset/ink.png"],
        'hype'      => ['name' => 'Hyperliquid', 'native' => 'HYPE', 'color' => '#97FCE4', 'art' => "$asset/hype-fill.svg"],
        'base'      => ['name' => 'Base',        'native' => 'ETH',  'color' => '#0052FF', 'art' => "$asset/base.svg"],
        'eth'       => ['name' => 'Ethereum',    'native' => 'ETH',  'color' => '#627EEA', 'art' => "$asset/eth-fill.svg"],
    ];

    abort_unless(isset($meta[$key]), 404);

    $tokens = $chainFeed->trending($key);

    if (empty($tokens)) {
        $tokens = array_values(array_filter(
            $feed->trending(60),
            fn ($t) => ($t['chain'] ?? '') === $key
        ));
    }

    return view('chain', [
        'key'    => $key,
        'meta'   => $meta,
        'tokens' => $tokens,
    ]);
})->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/trackers', [TrackersController::class, 'index'])->middleware('feature:trackers');
    Route::post('/trackers', [TrackersController::class, 'store'])->middleware('feature:trackers');
    Route::delete('/trackers/{wallet}', [TrackersController::class, 'destroy'])->middleware('feature:trackers');

    Route::get('/perpetuals', [PerpetualsController::class, 'index'])->middleware('feature:perpetuals');

    Route::get('/predictions', [PredictionsController::class, 'index'])->middleware('feature:predictions');

    Route::get('/vision', [VisionController::class, 'index'])->middleware('feature:vision');
    Route::get('/api/vision/scan', [VisionController::class, 'scan'])->middleware('feature:vision');

    Route::get('/portfolio', [PortfolioController::class, 'index']);
    Route::post('/portfolio/connect', [PortfolioController::class, 'connect']);
    Route::post('/portfolio/{wallet}/primary', [PortfolioController::class, 'primary']);
    Route::delete('/portfolio/{wallet}', [PortfolioController::class, 'disconnect']);
    Route::post('/watchlist', [PortfolioController::class, 'watchStore']);
    Route::delete('/watchlist/{item}', [PortfolioController::class, 'watchDestroy']);

    Route::get('/rewards', [RewardsController::class, 'index']);
    Route::post('/rewards/claim', [RewardsController::class, 'claim']);

    Route::get('/markets', [MarketsController::class, 'index'])->middleware('feature:markets');
    Route::get('/pnl', [PnlController::class, 'index'])->middleware('feature:pnl');

    Route::get('/friends', [FriendsController::class, 'index'])->middleware('feature:friends');
    Route::post('/friends', [FriendsController::class, 'store'])->middleware('feature:friends');
    Route::delete('/friends/{wallet}', [FriendsController::class, 'destroy'])->middleware('feature:friends');

    Route::get('/streams', [StreamsController::class, 'index'])->middleware('feature:streams');
    Route::get('/streams/create', [StreamsController::class, 'create'])->middleware('feature:streams');
    Route::post('/streams', [StreamsController::class, 'store'])->middleware('feature:streams');
    Route::get('/streams/{stream}', [StreamsController::class, 'show'])->middleware('feature:streams');
    Route::post('/streams/{stream}/end', [StreamsController::class, 'end'])->middleware('feature:streams');

    Route::get('/callouts', [CalloutsController::class, 'index'])->middleware('feature:callouts');
    Route::post('/callouts', [CalloutsController::class, 'store'])->middleware('feature:callouts');

    Route::get('/social', [SocialController::class, 'index'])->middleware('feature:social');
    Route::get('/launchpads', [LaunchpadsController::class, 'index'])->middleware('feature:launchpads');
    Route::get('/settings', [SettingsController::class, 'index']);

    Route::get('/token/{address}', [TokenController::class, 'show']);
    Route::get('/api/token/{address}/candles', [TokenController::class, 'candles']);
    Route::get('/api/token/{address}/tradingview', [TokenController::class, 'tradingview']);

    Route::get('/api/swap/quote', [\App\Http\Controllers\SwapController::class, 'quote']);
    Route::post('/api/swap/build', [\App\Http\Controllers\SwapController::class, 'build']);
    Route::get('/api/swap/meta', [\App\Http\Controllers\SwapController::class, 'meta']);

    Route::get('/api/deposit/addresses', [DepositController::class, 'index']);
    Route::post('/api/deposit/addresses', [DepositController::class, 'store']);
    Route::delete('/api/deposit/addresses/{depositAddress}', [DepositController::class, 'destroy']);
});

Route::get('/api/discover/trending', function (DiscoverFeed $feed) {
    return response()->json(['trending' => $feed->trending()]);
});

Route::get('/api/search', function (\Illuminate\Http\Request $request) {
    $q = trim((string) $request->query('q', ''));
    if ($q === '') {
        return response()->json(['results' => []]);
    }
    try {
        $res = \Illuminate\Support\Facades\Http::connectTimeout(4)->timeout(8)->acceptJson()
            ->get('https://api.dexscreener.com/latest/dex/search', ['q' => $q]);
        $pairs = $res->ok() ? ($res->json('pairs') ?? []) : [];
    } catch (\Throwable $e) {
        $pairs = [];
    }
    usort($pairs, fn ($a, $b) => ($b['liquidity']['usd'] ?? 0) <=> ($a['liquidity']['usd'] ?? 0));
    $out = [];
    $seen = [];
    foreach ($pairs as $p) {
        $addr = $p['baseToken']['address'] ?? '';
        if ($addr === '' || isset($seen[$addr])) {
            continue;
        }
        $seen[$addr] = true;
        $out[] = [
            'address' => $addr,
            'symbol'  => $p['baseToken']['symbol'] ?? '?',
            'name'    => $p['baseToken']['name'] ?? '',
            'chain'   => $p['chainId'] ?? '',
            'dex'     => $p['dexId'] ?? '',
            'price'   => (float) ($p['priceUsd'] ?? 0),
            'chg'     => (float) ($p['priceChange']['h24'] ?? 0),
            'img'     => $p['info']['imageUrl'] ?? '',
        ];
        if (count($out) >= 12) {
            break;
        }
    }
    return response()->json(['results' => $out]);
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    \App\Http\Middleware\NoCacheHeaders::class,
]);

Route::get('/api/pulse/token/{mint}', function (PulseFeed $feed, string $mint) {
    return response()->json($feed->tokenReport($mint));
});

Route::get('/copy-trading', [CopyTradingController::class, 'index'])
    ->middleware(['auth', 'feature:copy-trading'])->name('copy-trading.index');
Route::get('/copy-trading/{slug}', [CopyTradingController::class, 'show'])
    ->middleware('feature:copy-trading')->name('copy-trading.show');
Route::post('/copy-trading/{id}/subscribe', [CopyTradingController::class, 'subscribe'])
    ->middleware(['auth', 'feature:copy-trading'])->name('copy-trading.subscribe');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->middleware('guest:admin')->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('guest:admin', 'throttle:10,1');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth:admin', 'admin'])->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');

        // Copy traders
        Route::get('/traders', [AdminController::class, 'traders'])->name('traders');
        Route::get('/traders/create', [AdminController::class, 'createTrader'])->name('traders.create');
        Route::post('/traders', [AdminController::class, 'storeTrader'])->name('traders.store');
        Route::get('/traders/{copyTrader}/edit', [AdminController::class, 'editTrader'])->name('traders.edit');
        Route::put('/traders/{copyTrader}', [AdminController::class, 'updateTrader'])->name('traders.update');
        Route::post('/traders/{copyTrader}/toggle', [AdminController::class, 'toggleTrader'])->name('traders.toggle');
        Route::delete('/traders/{copyTrader}', [AdminController::class, 'destroyTrader'])->name('traders.destroy');

        // Subscriptions & trades
        Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('subscriptions');
        Route::post('/subscriptions/{subscription}/status', [AdminController::class, 'updateSubscription'])->name('subscriptions.status');
        Route::get('/copy-trades', [AdminController::class, 'copyTrades'])->name('copy-trades');

        // Streams
        Route::get('/streams', [AdminController::class, 'streams'])->name('streams');
        Route::post('/streams/{stream}/moderate', [AdminController::class, 'moderateStream'])->name('streams.moderate');
        Route::delete('/streams/{stream}', [AdminController::class, 'destroyStream'])->name('streams.destroy');

        // Wallets
        Route::get('/wallets', [AdminController::class, 'wallets'])->name('wallets');
        Route::delete('/wallets/connected/{connectedWallet}', [AdminController::class, 'destroyConnectedWallet'])->name('wallets.connected.destroy');
        Route::delete('/wallets/deposit/{depositAddress}', [AdminController::class, 'destroyDepositAddress'])->name('wallets.deposit.destroy');

        // Rewards
        Route::get('/rewards', [AdminController::class, 'rewards'])->name('rewards');
        Route::post('/rewards/adjust', [AdminController::class, 'storePointEvent'])->name('rewards.adjust');

        // Users
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('users.status');
        Route::post('/users/{user}/impersonate', [AdminController::class, 'impersonate'])->name('users.impersonate');
        Route::post('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('users.toggle-admin');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::post('/impersonate/stop', [AdminController::class, 'stopImpersonate'])->name('impersonate.stop');

        // Administrators & roles
        Route::get('/admins', [AdminController::class, 'admins'])->name('admins');
        Route::post('/admins', [AdminController::class, 'storeAdmin'])->name('admins.store');
        Route::post('/admins/{user}/role', [AdminController::class, 'updateAdminRole'])->name('admins.role');

        // Announcements
        Route::get('/announcements', [AdminController::class, 'announcements'])->name('announcements');
        Route::post('/announcements', [AdminController::class, 'storeAnnouncement'])->name('announcements.store');
        Route::put('/announcements/{announcement}', [AdminController::class, 'updateAnnouncement'])->name('announcements.update');
        Route::post('/announcements/{announcement}/toggle', [AdminController::class, 'toggleAnnouncement'])->name('announcements.toggle');
        Route::delete('/announcements/{announcement}', [AdminController::class, 'destroyAnnouncement'])->name('announcements.destroy');

        // Content
        Route::get('/content', [AdminController::class, 'content'])->name('content');
        Route::post('/content/{key}', [AdminController::class, 'updateContent'])->name('content.update');

        // Settings
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

        // Audit log
        Route::get('/audit', [AdminController::class, 'audit'])->name('audit');
        Route::get('/audit/export', [AdminController::class, 'exportAudit'])->name('audit.export');
    });
});
