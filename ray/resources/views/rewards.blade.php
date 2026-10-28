@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rewards.css') }}?v={{ @filemtime(public_path('css/rewards.css')) }}">
@endpush

@section('content')
@php
    $tier          = $tier ?? ['current' => ['name' => 'Bronze', 'rate' => '1.00x'], 'next' => null, 'progress' => 0];
    $total         = $total ?? 0;
    $code          = $code ?? '';
    $refLink       = $refLink ?? '';
    $referrals     = $referrals ?? collect();
    $history       = $history ?? collect();
    $claimedToday  = $claimedToday ?? false;
    $refCount      = $referrals->count();
@endphp

<div class="rw" id="rewardsRoot" data-flash="{{ session('ok') }}">

    <header class="topbar">
        <nav class="page-tabs" aria-label="Rewards sections">
            <button class="active">Rewards</button>
            <button data-toast="Leaderboard is coming soon">Leaderboard</button>
            <button data-toast="Benefits are coming soon">Benefits</button>
            <button data-toast="Yield is coming soon">Yield</button>
        </nav>
        <div class="topbar-right">
            <div class="network-icons" aria-label="Networks">
                <span>&#8787;</span><span>&#9827;</span><span>&#9673;</span><span>&#9817;</span><span>&#926;</span><span>&#9685;</span><span>&#9830;</span>
            </div>
            <button class="claim-all" data-toast="No rewards are ready to claim">&#9827; Claim All</button>
        </div>
    </header>

    <nav class="subtabs" aria-label="Reward type">
        <button class="active">Spot</button>
        <button>Predictions<sup class="new">NEW</sup></button>
        <button disabled>Perpetuals<span class="soon">SOON</span></button>
    </nav>

    <section class="overview" aria-label="Rewards overview">
        <div class="overview-main">
            <div class="wood-icon" aria-hidden="true"></div>
            <div class="overview-body">
                <div class="overview-head">
                    <h1>Referral Rewards</h1>
                    <span class="tier-pill">{{ $tier['current']['name'] }}</span>
                </div>
                <p class="overview-sub">Earn points by trading, referring friends and completing quests.</p>
                <div class="level-row">
                    @if ($tier['next'])
                        <span class="level-left"><span class="diamond">&#9670;</span>{{ $tier['current']['name'] }} &rarr; {{ $tier['next']['name'] }}</span>
                        <span class="level-right">{{ $tier['next']['name'] }} at {{ number_format($tier['next']['min']) }} pts</span>
                    @else
                        <span class="level-left"><span class="diamond">&#9670;</span>{{ $tier['current']['name'] }}</span>
                        <span class="level-right">Top tier reached</span>
                    @endif
                </div>
                <div class="progress"><span style="width:{{ (int) ($tier['progress'] ?? 0) }}%"></span></div>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat"><b>{{ number_format($total) }}</b><span>Points</span></div>
            <div class="stat"><b>{{ $refCount }}</b><span>Referrals</span></div>
            <div class="stat"><b>{{ $tier['current']['rate'] }}</b><span>Referral rate</span></div>
            <div class="stat-actions">
                <button data-copy="{{ $refLink }}">&#128279; Copy Link</button>
                <button class="share" data-copy="{{ $refLink }}">&#8599; Share</button>
            </div>
        </div>
    </section>

    <section class="claims" aria-label="Rewards to claim and quests">
        <h2 class="section-title">Claim</h2>
        <div class="claim-grid">
            <article class="card chart-card">
                <div class="card-head">
                    <span class="card-title"><span class="head-icon">&#9638;</span>Points Earned</span>
                    <button data-toast="Points breakdown">Points Breakdown</button>
                </div>
                <div class="chart-caption">{{ number_format($total) }} pts</div>
                <div class="chart-chart">
                    <svg viewBox="0 0 320 150" preserveAspectRatio="none" role="img" aria-label="Points earned chart">
                        <defs>
                            <linearGradient id="rwArea" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#566fff" stop-opacity=".35"/>
                                <stop offset="100%" stop-color="#566fff" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <path d="M0,120 L45,108 L90,112 L135,86 L180,92 L225,60 L270,54 L320,30 L320,150 L0,150 Z" fill="url(#rwArea)"/>
                        <path d="M0,120 L45,108 L90,112 L135,86 L180,92 L225,60 L270,54 L320,30" fill="none" stroke="#6684ff" stroke-width="2"/>
                    </svg>
                </div>
            </article>

            <article class="card claim-card">
                <div class="card-head">
                    <span class="card-title"><span class="head-icon">&#9671;</span>Claim</span>
                    <button data-toast="Claim settings">&#9881;</button>
                </div>
                <form class="claim-content" method="POST" action="/rewards/claim">
                    @csrf
                    <div class="claim-chips">
                        <div class="claim-chip sol"><span class="sol-symbol">&#8787;</span>+0</div>
                        <div class="claim-chip"><span class="point-symbol">&#9670;</span>+100</div>
                    </div>
                    <div class="pays-to">Daily check-in rewards &middot; resets every 24h</div>
                    <button class="claim-primary" @if ($claimedToday) disabled @endif>
                        {{ $claimedToday ? 'Claimed Today' : 'Claim +100 Points' }}
                    </button>
                    <button class="claim-secondary" type="button" data-toast="Your referral code is {{ $code }}">Code: {{ $code }}</button>
                </form>
            </article>

            <article class="card quests-card">
                <div class="card-head">
                    <span class="card-title"><span class="head-icon">&#10023;</span>Quests</span>
                    <button data-toast="Points breakdown">Points Breakdown</button>
                </div>
                <div class="quest-grid">
                    <button class="quest" data-toast="Reach 1,000 points to complete this quest"><div class="quest-ring"><span>+1,000</span></div><p>Trade volume<span>in any market</span></p></button>
                    <button class="quest" data-toast="Make 200 transactions this week"><div class="quest-ring"><span>+200</span></div><p>Make transactions<span>this week</span></p></button>
                </div>
            </article>
        </div>
    </section>

    <section class="activity" aria-label="Referral activity">
        <h2>Activity</h2>
        <div class="referral-table">
            <div class="referral-table-head"><span>Referrals</span><span class="referral-count">&#9827; {{ $refCount }}</span></div>
            <div class="referral-columns"><span>Referrals</span><span>Date</span><span>Type</span><span>Points</span><span>Rewards</span></div>
            @if ($refCount)
                <div class="referral-rows">
                    @foreach ($referrals as $referral)
                        <div class="referral-row">
                            <span class="who">{{ $referral->email ?? $referral->name ?? '—' }}</span>
                            <span>{{ optional($referral->created_at)->format('M j, Y') ?? '—' }}</span>
                            <span>Referral</span>
                            <span class="pos">+{{ number_format($referral->points()) }}</span>
                            <span class="pos">—</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="referral-empty">
                    <p>No referrals yet. Share your link to start earning.</p>
                    <button class="set-referral" data-copy="{{ $refLink }}">Copy your referral link</button>
                </div>
            @endif
        </div>
    </section>

    <div class="bottom-space">
        <button data-copy="{{ $refLink }}">Share your referral link</button>
    </div>
</div>

<div class="toast" role="status" aria-live="polite"></div>
@endsection

@push('scripts')
<script src="{{ asset('js/axiom-rewards.js') }}?v={{ @filemtime(public_path('js/axiom-rewards.js')) }}" defer></script>
@endpush
