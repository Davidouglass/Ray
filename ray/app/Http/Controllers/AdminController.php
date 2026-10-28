<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AdminAuditLog;
use App\Models\ConnectedWallet;
use App\Models\ContentPage;
use App\Models\CopyTrade;
use App\Models\CopyTrader;
use App\Models\CopyTraderSubscription;
use App\Models\DepositAddress;
use App\Models\PointEvent;
use App\Models\SiteSetting;
use App\Models\Stream;
use App\Models\TrackedWallet;
use App\Models\User;
use App\Services\AdminAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    private function admin(): ?User
    {
        return Auth::guard('admin')->user();
    }

    private function requireSettings(): void
    {
        abort_unless($this->admin() && $this->admin()->canManageSettings(), 403, 'You do not have permission to perform this action.');
    }

    private function requireSuper(): void
    {
        abort_unless($this->admin() && $this->admin()->canManageAdmins(), 403, 'Only super admins can perform this action.');
    }

    // ---------------------------------------------------------------- dashboard

    public function dashboard()
    {
        $stats = [
            'users'           => User::count(),
            'admins'          => User::where('is_admin', true)->count(),
            'banned'          => User::where('status', '!=', 'active')->count(),
            'traders'         => CopyTrader::count(),
            'activeTraders'   => CopyTrader::where('is_active', true)->count(),
            'verifiedTraders' => CopyTrader::where('is_verified', true)->count(),
            'featuredTraders' => CopyTrader::where('is_featured', true)->count(),
            'subscriptions'   => CopyTraderSubscription::where('status', 'active')->count(),
            'trades'          => CopyTrade::count(),
            'liveStreams'     => Stream::where('status', 'live')->count(),
            'wallets'         => ConnectedWallet::count(),
            'points'          => (int) PointEvent::sum('points'),
        ];

        $recentUsers   = User::orderByDesc('created_at')->take(6)->get();
        $recentTraders = CopyTrader::orderByDesc('created_at')->take(6)->get();
        $recentAudit   = AdminAuditLog::with('admin')->orderByDesc('id')->take(8)->get();

        return view('admin.dashboard', [
            'title'         => 'Admin · Dashboard',
            'active'        => 'dashboard',
            'stats'         => $stats,
            'chart'         => $this->signupSeries(14),
            'recentUsers'   => $recentUsers,
            'recentTraders' => $recentTraders,
            'recentAudit'   => $recentAudit,
        ]);
    }

    public function analytics()
    {
        $signups30 = User::where('created_at', '>=', now()->subDays(29)->startOfDay())->count();
        $activeToday = DB::table('sessions')->where('last_activity', '>=', now()->startOfDay()->timestamp)->distinct()->count('user_id');

        $stats = [
            'signups30'   => $signups30,
            'activeToday' => $activeToday,
            'wallets'     => ConnectedWallet::count(),
            'deposits'    => DepositAddress::count(),
            'tracked'     => TrackedWallet::count(),
            'subsActive'  => CopyTraderSubscription::where('status', 'active')->count(),
            'revenue'     => (float) CopyTraderSubscription::sum('paid_amount'),
            'points'      => (int) PointEvent::sum('points'),
        ];

        $topTraders = CopyTrader::orderByDesc('followers_count')->take(8)->get();
        $chains = ConnectedWallet::selectRaw('chain, COUNT(*) as c')->groupBy('chain')->orderByDesc('c')->get();
        $topReferrers = User::withCount(['copySubscriptions as subs_count'])
            ->has('copySubscriptions')
            ->orderByDesc('subs_count')
            ->take(8)
            ->get();

        return view('admin.analytics', [
            'title'      => 'Admin · Analytics',
            'active'     => 'analytics',
            'stats'      => $stats,
            'chart'      => $this->signupSeries(30),
            'topTraders' => $topTraders,
            'chains'     => $chains,
            'recentAudit' => AdminAuditLog::with('admin')->orderByDesc('id')->take(10)->get(),
        ]);
    }

    private function signupSeries(int $days): array
    {
        $rows = User::selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('d')
            ->pluck('c', 'd');

        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $out[] = ['date' => $date, 'label' => now()->subDays($i)->format('M j'), 'count' => (int) ($rows[$date] ?? 0)];
        }

        return $out;
    }

    // ------------------------------------------------------------------ traders

    public function traders(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $query = CopyTrader::query();

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('display_name', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('wallet_address', 'like', "%{$q}%");
            });
        }

        match ($status) {
            'active'   => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            'verified' => $query->where('is_verified', true),
            'featured' => $query->where('is_featured', true),
            default    => null,
        };

        $traders = $query->orderByDesc('followers_count')->paginate(25)->withQueryString();

        return view('admin.traders', [
            'title'   => 'Admin · Copy Traders',
            'active'  => 'traders',
            'traders' => $traders,
            'q'       => $q,
            'status'  => $status,
        ]);
    }

    public function createTrader()
    {
        return view('admin.trader-create', [
            'title'  => 'Admin · New Trader',
            'active' => 'traders',
        ]);
    }

    public function storeTrader(Request $request)
    {
        $this->requireSettings();

        $data = $request->validate($this->traderRules());

        $owner = User::firstOrCreate(
            ['email' => Str::slug($data['display_name'], '.') . '@traders.veridian'],
            ['name' => $data['display_name'], 'password' => Hash::make(Str::random(24)), 'referral_code' => strtoupper(Str::random(8))]
        );

        $trader = new CopyTrader();
        $trader->user_id = $owner->id;
        $trader->fill($this->traderAttributes($data, $request));
        $trader->slug = $this->uniqueSlug($data['display_name']);
        $trader->avatar_url = $this->handleAvatar($request) ?? ($data['avatar_url'] ?? null);
        $trader->save();

        AdminAudit::log('trader.create', $trader, ['name' => $trader->display_name]);

        return redirect('/admin/traders')->with('ok', "Created {$trader->display_name}.");
    }

    public function editTrader(CopyTrader $copyTrader)
    {
        return view('admin.trader-edit', [
            'title'  => 'Admin · Edit Trader',
            'active' => 'traders',
            'trader' => $copyTrader,
        ]);
    }

    public function updateTrader(Request $request, CopyTrader $copyTrader)
    {
        $this->requireSettings();

        $data = $request->validate($this->traderRules());

        $copyTrader->fill($this->traderAttributes($data, $request));

        if ($avatar = $this->handleAvatar($request)) {
            $copyTrader->avatar_url = $avatar;
        } elseif (array_key_exists('avatar_url', $data)) {
            $copyTrader->avatar_url = $data['avatar_url'];
        }

        $copyTrader->save();

        AdminAudit::log('trader.update', $copyTrader, ['name' => $copyTrader->display_name]);

        return redirect('/admin/traders')->with('ok', "Saved {$copyTrader->display_name}.");
    }

    public function toggleTrader(Request $request, CopyTrader $copyTrader)
    {
        $this->requireSettings();

        $field = (string) $request->input('field');

        if (! in_array($field, ['is_active', 'is_verified', 'is_featured'], true)) {
            return back()->withErrors(['error' => 'Unknown toggle field.']);
        }

        $copyTrader->{$field} = ! $copyTrader->{$field};
        $copyTrader->save();

        AdminAudit::log('trader.toggle', $copyTrader, ['field' => $field, 'value' => $copyTrader->{$field}]);

        return back()->with('ok', "Updated {$copyTrader->display_name}.");
    }

    public function destroyTrader(Request $request, CopyTrader $copyTrader)
    {
        $this->requireSettings();

        $name = $copyTrader->display_name;
        AdminAudit::log('trader.delete', $copyTrader, ['name' => $name]);
        $copyTrader->delete();

        return back()->with('ok', "Deleted {$name}.");
    }

    private function traderRules(): array
    {
        return [
            'display_name'    => ['required', 'string', 'max:120'],
            'tagline'         => ['nullable', 'string', 'max:160'],
            'bio'             => ['nullable', 'string', 'max:4000'],
            'wallet_address'  => ['nullable', 'string', 'max:120'],
            'avatar_url'      => ['nullable', 'string', 'max:300'],
            'avatar'          => ['nullable', 'image', 'max:2048'],
            'risk_level'      => ['required', 'in:low,medium,high'],
            'total_pnl_usd'   => ['nullable', 'numeric'],
            'roi_7d'          => ['nullable', 'numeric'],
            'roi_30d'         => ['nullable', 'numeric'],
            'win_rate'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_drawdown'    => ['nullable', 'numeric'],
            'followers_count' => ['nullable', 'integer', 'min:0'],
            'trades_count'    => ['nullable', 'integer', 'min:0'],
            'volume_24h_usd'  => ['nullable', 'numeric'],
            'avg_trade_usd'   => ['nullable', 'numeric'],
            'tags'            => ['nullable', 'string', 'max:300'],
        ];
    }

    private function traderAttributes(array $data, Request $request): array
    {
        $tags = array_values(array_filter(array_map('trim', explode(',', (string) ($data['tags'] ?? '')))));

        return [
            'display_name'    => $data['display_name'],
            'tagline'         => $data['tagline'] ?? null,
            'bio'             => $data['bio'] ?? null,
            'wallet_address'  => $data['wallet_address'] ?? null,
            'risk_level'      => $data['risk_level'],
            'total_pnl_usd'   => $data['total_pnl_usd'] ?? 0,
            'roi_7d'          => $data['roi_7d'] ?? 0,
            'roi_30d'         => $data['roi_30d'] ?? 0,
            'win_rate'        => $data['win_rate'] ?? 0,
            'max_drawdown'    => $data['max_drawdown'] ?? 0,
            'followers_count' => $data['followers_count'] ?? 0,
            'trades_count'    => $data['trades_count'] ?? 0,
            'volume_24h_usd'  => $data['volume_24h_usd'] ?? 0,
            'avg_trade_usd'   => $data['avg_trade_usd'] ?? 0,
            'tags'            => $tags,
            'is_active'       => $request->boolean('is_active'),
            'is_verified'     => $request->boolean('is_verified'),
            'is_featured'     => $request->boolean('is_featured'),
        ];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'trader';
        $slug = $base;
        while (CopyTrader::where('slug', $slug)->exists()) {
            $slug = $base . '-' . Str::lower(Str::random(4));
        }

        return $slug;
    }

    private function handleAvatar(Request $request): ?string
    {
        if (! $request->hasFile('avatar')) {
            return null;
        }

        $dir = public_path('images/traders');
        File::ensureDirectoryExists($dir);

        $file = $request->file('avatar');
        $name = 'trader-' . Str::lower(Str::random(12)) . '.' . ($file->getClientOriginalExtension() ?: 'png');
        $file->move($dir, $name);

        return '/images/traders/' . $name;
    }

    // ------------------------------------------------------------ subscriptions

    public function subscriptions(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $query = CopyTraderSubscription::with(['follower', 'copyTrader']);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereHas('follower', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                    ->orWhereHas('copyTrader', fn ($t) => $t->where('display_name', 'like', "%{$q}%"));
            });
        }

        if (in_array($status, ['active', 'cancelled', 'expired', 'refunded'], true)) {
            $query->where('status', $status);
        }

        $subs = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('admin.subscriptions', [
            'title'  => 'Admin · Subscriptions',
            'active' => 'subscriptions',
            'subs'   => $subs,
            'q'      => $q,
            'status' => $status,
        ]);
    }

    public function updateSubscription(Request $request, CopyTraderSubscription $subscription)
    {
        $this->requireSettings();

        $data = $request->validate(['status' => ['required', 'in:active,cancelled,expired,refunded']]);
        $subscription->status = $data['status'];
        $subscription->save();

        AdminAudit::log('subscription.status', $subscription, ['status' => $data['status']]);

        return back()->with('ok', 'Subscription updated.');
    }

    public function copyTrades(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = CopyTrade::with(['copyTrader', 'subscription.follower']);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('symbol', 'like', "%{$q}%")
                    ->orWhere('mint', 'like', "%{$q}%")
                    ->orWhere('tx_signature', 'like', "%{$q}%");
            });
        }

        $trades = $query->orderByDesc('executed_at')->orderByDesc('id')->paginate(30)->withQueryString();

        return view('admin.copy-trades', [
            'title'  => 'Admin · Copy Trades',
            'active' => 'copy-trades',
            'trades' => $trades,
            'q'      => $q,
        ]);
    }

    // ------------------------------------------------------------------ streams

    public function streams(Request $request)
    {
        $moderation = (string) $request->query('moderation', 'all');

        $query = Stream::with('user')->orderByDesc('id');

        if (in_array($moderation, ['visible', 'hidden', 'removed'], true)) {
            $query->where('moderation_status', $moderation);
        }

        $streams = $query->paginate(25)->withQueryString();

        return view('admin.streams', [
            'title'      => 'Admin · Streams',
            'active'     => 'streams',
            'streams'    => $streams,
            'moderation' => $moderation,
        ]);
    }

    public function moderateStream(Request $request, Stream $stream)
    {
        $this->requireSettings();

        $action = (string) $request->input('action');

        if ($action === 'end') {
            $stream->status = 'ended';
            $stream->ended_at = now();
        } elseif (in_array($action, ['hide', 'show', 'remove'], true)) {
            $stream->moderation_status = ['hide' => 'hidden', 'show' => 'visible', 'remove' => 'removed'][$action];
        } else {
            abort(422, 'Unknown moderation action.');
        }

        $stream->moderated_by = $this->admin()?->id;
        $stream->moderated_at = now();
        $stream->save();

        AdminAudit::log('stream.moderate', $stream, ['action' => $action]);

        return back()->with('ok', 'Stream updated.');
    }

    public function destroyStream(Request $request, Stream $stream)
    {
        $this->requireSettings();

        AdminAudit::log('stream.delete', $stream, ['title' => $stream->title]);
        $stream->delete();

        return back()->with('ok', 'Stream deleted.');
    }

    // ------------------------------------------------------------------ wallets

    public function wallets(Request $request)
    {
        $q    = trim((string) $request->query('q', ''));
        $kind = (string) $request->query('kind', 'connected');

        if ($kind === 'deposits') {
            $query = DepositAddress::with('user')->orderByDesc('id');
            if ($q !== '') {
                $query->where(fn ($w) => $w->where('address', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%")));
            }
            $rows = $query->paginate(30)->withQueryString();
        } else {
            $query = ConnectedWallet::with('user')->orderByDesc('id');
            if ($q !== '') {
                $query->where(fn ($w) => $w->where('address', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%")));
            }
            $rows = $query->paginate(30)->withQueryString();
        }

        return view('admin.wallets', [
            'title'  => 'Admin · Wallets',
            'active' => 'wallets',
            'rows'   => $rows,
            'kind'   => $kind,
            'q'      => $q,
        ]);
    }

    public function destroyConnectedWallet(Request $request, ConnectedWallet $connectedWallet)
    {
        $this->requireSettings();

        AdminAudit::log('wallet.delete', $connectedWallet, ['address' => $connectedWallet->address]);
        $connectedWallet->delete();

        return back()->with('ok', 'Wallet removed.');
    }

    public function destroyDepositAddress(Request $request, DepositAddress $depositAddress)
    {
        $this->requireSettings();

        AdminAudit::log('deposit.delete', $depositAddress, ['address' => $depositAddress->address]);
        $depositAddress->delete();

        return back()->with('ok', 'Deposit address removed.');
    }

    // ------------------------------------------------------------------ rewards

    public function rewards(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = PointEvent::with('user')->orderByDesc('id');
        if ($q !== '') {
            $query->where(fn ($w) => $w->where('kind', 'like', "%{$q}%")
                ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%")));
        }

        $events = $query->paginate(30)->withQueryString();

        return view('admin.rewards', [
            'title'     => 'Admin · Rewards',
            'active'    => 'rewards',
            'events'    => $events,
            'q'         => $q,
            'total'     => (int) PointEvent::sum('points'),
            'issued30'  => (int) PointEvent::where('created_at', '>=', now()->subDays(30))->sum('points'),
            'referrals' => (int) PointEvent::where('kind', 'referral')->count(),
        ]);
    }

    public function storePointEvent(Request $request)
    {
        $this->requireSettings();

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'points'  => ['required', 'integer', 'not_in:0', 'between:-1000000,1000000'],
            'reason'  => ['nullable', 'string', 'max:160'],
        ]);

        $event = PointEvent::create([
            'user_id' => $data['user_id'],
            'kind'    => 'admin_adjustment',
            'points'  => (int) $data['points'],
            'meta'    => ['reason' => $data['reason'] ?? 'Manual adjustment by admin', 'admin' => $this->admin()?->id],
        ]);

        AdminAudit::log('points.adjust', $event, ['user_id' => $data['user_id'], 'points' => (int) $data['points']]);

        return back()->with('ok', 'Points adjustment recorded.');
    }

    // ------------------------------------------------------------------- users

    public function users(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $query = User::withCount(['connectedWallets', 'depositAddresses', 'trackedWallets']);

        if ($q !== '') {
            $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }

        if ($status === 'admins') {
            $query->where('is_admin', true);
        } elseif ($status === 'banned') {
            $query->where('status', '!=', 'active');
        } elseif ($status === 'active') {
            $query->where('status', 'active');
        }

        $users = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('admin.users', [
            'title'  => 'Admin · Users',
            'active' => 'users',
            'users'  => $users,
            'q'      => $q,
            'status' => $status,
        ]);
    }

    public function showUser(User $user)
    {
        $user->loadCount(['connectedWallets', 'depositAddresses', 'trackedWallets', 'streams', 'pointEvents', 'copySubscriptions']);

        return view('admin.user-show', [
            'title'         => 'Admin · ' . $user->name,
            'active'        => 'users',
            'user'          => $user,
            'wallets'       => $user->connectedWallets()->latest()->get(),
            'deposits'      => $user->depositAddresses()->latest()->get(),
            'tracked'       => $user->trackedWallets()->latest()->take(20)->get(),
            'points'        => $user->pointEvents()->latest()->take(20)->get(),
            'subscriptions' => $user->copySubscriptions()->with('copyTrader')->latest()->get(),
            'streams'       => $user->streams()->latest()->take(10)->get(),
            'points_total'  => $user->points(),
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $this->requireSettings();

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:120'],
            'email'            => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'status'           => ['required', 'in:active,suspended,banned'],
            'suspended_reason' => ['nullable', 'string', 'max:255'],
            'password'         => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->status = $data['status'];
        $user->suspended_reason = $data['status'] === 'active' ? null : ($data['suspended_reason'] ?? null);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        if ($data['status'] !== 'active') {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        AdminAudit::log('user.update', $user, ['email' => $user->email, 'status' => $user->status]);

        return back()->with('ok', 'User updated.');
    }

    public function updateUserStatus(Request $request, User $user)
    {
        $this->requireSettings();

        if ($user->id === $this->admin()?->id) {
            return back()->withErrors(['error' => "You can't change your own status."]);
        }

        $data = $request->validate([
            'status' => ['required', 'in:active,suspended,banned'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $user->status = $data['status'];
        $user->suspended_reason = $data['status'] === 'active' ? null : ($data['reason'] ?? null);
        $user->save();

        if ($data['status'] !== 'active') {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        AdminAudit::log('user.status', $user, ['status' => $data['status']]);

        return back()->with('ok', "{$user->email} is now {$data['status']}.");
    }

    public function impersonate(Request $request, User $user)
    {
        $this->requireSettings();

        if ($user->id === $this->admin()?->id) {
            return back()->withErrors(['error' => "You are already yourself."]);
        }

        AdminAudit::log('user.impersonate', $user, ['email' => $user->email]);

        Auth::guard('web')->login($user);
        $request->session()->put('impersonator_admin_id', $this->admin()?->id);
        $request->session()->put('impersonating_user', $user->name);

        return redirect('/discover')->with('ok', "Viewing the app as {$user->name}.");
    }

    public function stopImpersonate(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->forget(['impersonator_admin_id', 'impersonating_user']);
        $request->session()->regenerate();

        return redirect('/admin/users')->with('ok', 'Stopped impersonating.');
    }

    public function toggleAdmin(Request $request, User $user)
    {
        $this->requireSuper();

        if ($user->id === $this->admin()?->id) {
            return back()->withErrors(['error' => "You can't change your own admin access."]);
        }

        if ($user->is_admin) {
            $user->is_admin = false;
            $user->admin_role = null;
        } else {
            $user->is_admin = true;
            $user->admin_role = 'admin';
        }
        $user->save();

        AdminAudit::log('admin.toggle', $user, ['is_admin' => $user->is_admin]);

        return back()->with('ok', "Updated {$user->email}.");
    }

    public function admins(Request $request)
    {
        $admins = User::where('is_admin', true)->orderByDesc('id')->get();

        return view('admin.admins', [
            'title'  => 'Admin · Administrators',
            'active' => 'admins',
            'admins' => $admins,
        ]);
    }

    public function storeAdmin(Request $request)
    {
        $this->requireSuper();

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:8'],
            'admin_role'  => ['required', 'in:super,admin,support'],
        ]);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => $data['password'],
            'is_admin'   => true,
            'admin_role' => $data['admin_role'],
            'status'     => 'active',
        ]);

        AdminAudit::log('admin.create', $user, ['email' => $user->email, 'role' => $user->admin_role]);

        return back()->with('ok', "Created admin {$user->email}.");
    }

    public function updateAdminRole(Request $request, User $user)
    {
        $this->requireSuper();

        if ($user->id === $this->admin()?->id) {
            return back()->withErrors(['error' => "You can't change your own role."]);
        }

        $data = $request->validate(['admin_role' => ['required', 'in:super,admin,support']]);

        if ($user->admin_role === 'super' && $data['admin_role'] !== 'super') {
            $otherSupers = User::where('is_admin', true)->where('admin_role', 'super')->where('id', '!=', $user->id)->count();
            if ($otherSupers === 0) {
                return back()->withErrors(['error' => 'At least one super admin is required.']);
            }
        }

        $user->admin_role = $data['admin_role'];
        $user->is_admin = true;
        $user->save();

        AdminAudit::log('admin.role', $user, ['role' => $data['admin_role']]);

        return back()->with('ok', "{$user->email} is now a {$data['admin_role']} admin.");
    }

    public function destroyUser(Request $request, User $user)
    {
        $this->requireSuper();

        if ($user->id === $this->admin()?->id) {
            return back()->withErrors(['error' => "You can't delete your own account."]);
        }

        $email = $user->email;
        AdminAudit::log('user.delete', $user, ['email' => $email]);
        $user->delete();

        return back()->with('ok', "Deleted {$email}.");
    }

    // ------------------------------------------------------------ announcements

    public function announcements()
    {
        return view('admin.announcements', [
            'title'         => 'Admin · Announcements',
            'active'        => 'announcements',
            'announcements' => Announcement::with('author')->orderByDesc('id')->paginate(20),
        ]);
    }

    public function storeAnnouncement(Request $request)
    {
        $this->requireSettings();

        $data = $this->announcementData($request);

        $a = Announcement::create($data + ['created_by' => $this->admin()?->id]);
        AdminAudit::log('announcement.create', $a, ['title' => $a->title]);

        return back()->with('ok', 'Announcement published.');
    }

    public function updateAnnouncement(Request $request, Announcement $announcement)
    {
        $this->requireSettings();

        $announcement->update($this->announcementData($request));
        AdminAudit::log('announcement.update', $announcement, ['title' => $announcement->title]);

        return back()->with('ok', 'Announcement updated.');
    }

    public function toggleAnnouncement(Announcement $announcement)
    {
        $this->requireSettings();

        $announcement->is_active = ! $announcement->is_active;
        $announcement->save();
        AdminAudit::log('announcement.toggle', $announcement, ['is_active' => $announcement->is_active]);

        return back()->with('ok', 'Announcement updated.');
    }

    public function destroyAnnouncement(Announcement $announcement)
    {
        $this->requireSettings();

        AdminAudit::log('announcement.delete', $announcement, ['title' => $announcement->title]);
        $announcement->delete();

        return back()->with('ok', 'Announcement deleted.');
    }

    private function announcementData(Request $request): array
    {
        $data = $request->validate([
            'title'     => ['nullable', 'string', 'max:140'],
            'message'   => ['required', 'string', 'max:600'],
            'type'      => ['required', 'in:info,success,warning,danger'],
            'starts_at' => ['nullable', 'date'],
            'ends_at'   => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable'],
        ]);

        return [
            'title'     => $data['title'] ?? null,
            'message'   => $data['message'],
            'type'      => $data['type'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at'   => $data['ends_at'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    // ------------------------------------------------------------------ content

    public function content()
    {
        $pages = [];
        foreach (array_keys(ContentPage::defaults()) as $key) {
            $pages[$key] = ContentPage::firstOrNew(['key' => $key], [
                'title'         => ContentPage::defaultFor($key)['title'],
                'updated_label' => ContentPage::defaultFor($key)['updated'],
            ]);
        }

        return view('admin.content', [
            'title'  => 'Admin · Content',
            'active' => 'content',
            'pages'  => $pages,
        ]);
    }

    public function updateContent(Request $request, string $key)
    {
        $this->requireSettings();

        abort_unless(array_key_exists($key, ContentPage::defaults()), 404);

        $data = $request->validate([
            'title'         => ['required', 'string', 'max:140'],
            'updated_label' => ['nullable', 'string', 'max:80'],
            'body'          => ['nullable', 'string', 'max:200000'],
        ]);

        $page = ContentPage::firstOrNew(['key' => $key]);
        $page->title = $data['title'];
        $page->updated_label = $data['updated_label'] ?? null;
        $page->body = $request->filled('body') ? $data['body'] : null;
        $page->updated_by = $this->admin()?->id;
        $page->save();

        AdminAudit::log('content.update', $page, ['key' => $key]);

        return back()->with('ok', 'Page saved.');
    }

    // ----------------------------------------------------------------- settings

    public function settings()
    {
        return view('admin.settings', [
            'title'          => 'Admin · Site settings',
            'active'         => 'settings',
            'supportUrl'     => SiteSetting::get('support_url', SiteSetting::SUPPORT_URL_DEFAULT),
            'supportEnabled' => SiteSetting::supportEnabled(),
            'socials'        => [
                'x'       => (string) SiteSetting::get('social_x', 'https://x.com/axiomexchange'),
                'discord' => (string) SiteSetting::get('social_discord', 'https://discord.gg/axiomtrade'),
                'docs'    => (string) SiteSetting::get('social_docs', 'https://docs.axiom.trade'),
            ],
            'maintenanceMode'    => SiteSetting::bool('maintenance_mode'),
            'maintenanceMessage' => (string) SiteSetting::get('maintenance_message', ''),
            'maintenanceStarts'  => (string) SiteSetting::get('maintenance_starts_at', ''),
            'maintenanceEnds'    => (string) SiteSetting::get('maintenance_ends_at', ''),
            'signupsOpen'        => SiteSetting::bool('signups_open', true),
            'referralPoints'     => (int) SiteSetting::get('referral_points', '250'),
            'dailyPoints'        => (int) SiteSetting::get('daily_points', '100'),
            'flags'              => collect(SiteSetting::FLAGS)->mapWithKeys(fn ($label, $k) => [$k => SiteSetting::flag($k)])->all(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $this->requireSettings();

        $data = $request->validate([
            'support_url'         => ['required', 'url', 'max:300'],
            'social_x'            => ['nullable', 'url', 'max:300'],
            'social_discord'      => ['nullable', 'url', 'max:300'],
            'social_docs'         => ['nullable', 'url', 'max:300'],
            'maintenance_message' => ['nullable', 'string', 'max:400'],
            'maintenance_starts_at' => ['nullable', 'date'],
            'maintenance_ends_at'   => ['nullable', 'date'],
            'referral_points'     => ['nullable', 'integer', 'min:0', 'max:100000'],
            'daily_points'        => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $starts = $request->filled('maintenance_starts_at')
            ? Carbon::parse($data['maintenance_starts_at'])->format('Y-m-d\TH:i')
            : null;
        $ends = $request->filled('maintenance_ends_at')
            ? Carbon::parse($data['maintenance_ends_at'])->format('Y-m-d\TH:i')
            : null;

        if ($starts && $ends && Carbon::parse($ends)->lt(Carbon::parse($starts))) {
            return back()->withInput()->withErrors(['maintenance_ends_at' => 'The end time must be after the start time.']);
        }

        SiteSetting::set('support_url', $data['support_url']);
        SiteSetting::set('support_enabled', $request->boolean('support_enabled') ? '1' : '0');
        SiteSetting::set('social_x', $data['social_x'] ?? null);
        SiteSetting::set('social_discord', $data['social_discord'] ?? null);
        SiteSetting::set('social_docs', $data['social_docs'] ?? null);
        SiteSetting::set('maintenance_mode', $request->boolean('maintenance_mode') ? '1' : '0');
        SiteSetting::set('maintenance_message', $data['maintenance_message'] ?? null);
        SiteSetting::set('maintenance_starts_at', $starts);
        SiteSetting::set('maintenance_ends_at', $ends);
        SiteSetting::set('signups_open', $request->boolean('signups_open') ? '1' : '0');
        SiteSetting::set('referral_points', (string) ($data['referral_points'] ?? 250));
        SiteSetting::set('daily_points', (string) ($data['daily_points'] ?? 100));

        foreach (array_keys(SiteSetting::FLAGS) as $flag) {
            SiteSetting::set('flag_' . $flag, $request->boolean('flag_' . $flag) ? '1' : '0');
        }

        AdminAudit::log('settings.update');

        return redirect('/admin/settings')->with('ok', 'Settings saved.');
    }

    // -------------------------------------------------------------------- audit

    public function audit(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = AdminAuditLog::with('admin')->orderByDesc('id');
        if ($q !== '') {
            $query->where(fn ($w) => $w->where('action', 'like', "%{$q}%")
                ->orWhereHas('admin', fn ($u) => $u->where('email', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%")));
        }

        return view('admin.audit', [
            'title'  => 'Admin · Audit log',
            'active' => 'audit',
            'logs'   => $query->paginate(40)->withQueryString(),
            'q'      => $q,
        ]);
    }

    public function exportAudit()
    {
        $this->requireSettings();

        $logs = AdminAuditLog::with('admin')->orderByDesc('id')->cursor();

        $filename = 'veridian-audit-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'when', 'action', 'admin', 'target_type', 'target_id', 'meta', 'ip']);
            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->id,
                    optional($log->created_at)->toDateTimeString(),
                    $log->action,
                    $log->admin->email ?? '',
                    $log->target_type ? class_basename($log->target_type) : '',
                    $log->target_id,
                    $log->meta ? json_encode($log->meta) : '',
                    $log->ip ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
