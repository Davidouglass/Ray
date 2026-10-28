@extends('layout')

@section('content')

<!-- HERO -->
<header class="hero">
    <!-- Expanding gradient frame stack (top animation) -->
    <div class="hero-expansions" aria-hidden="true">
        <div class="expand-frame ef-6"><div class="ef-inner"></div></div>
        <div class="expand-frame ef-5"><div class="ef-inner"></div></div>
        <div class="expand-frame ef-4"><div class="ef-inner"></div></div>
        <div class="expand-frame ef-3"><div class="ef-inner"></div></div>
        <div class="expand-frame ef-2"><div class="ef-inner"></div></div>
        <div class="expand-frame ef-1"><div class="ef-inner"></div></div>
    </div>

    <div class="hero-inner">
        <div class="hero-copy">
            <img src="{{ asset('images/veridian-lockup.png') }}" alt="Veridian" class="hero-logo-img">
            <h1>The Gateway to DeFi</h1>
            <p class="sub">Veridian is the only trading platform you'll ever need.</p>
            <div class="hero-cta">
                <a href="/login" class="btn-start btn-lg">Start Trading</a>
            </div>
        </div>

        <div class="backed">
            <span>Backed by</span>
            <span class="backed-logo">
                <svg class="yc-badge" width="22" height="22" viewBox="0 0 24 24" fill="none"><rect x="1" y="1" width="22" height="22" rx="4" fill="#FF6600"/><path d="M6 6.5h2.9L12 13l3.1-6.5H18L13.5 17.5H10.5L6 6.5Z" fill="#fff"/></svg>
                <span>Y Combinator</span>
            </span>
        </div>

        <!-- Picture-in-picture hero demo -->
        <div class="hero-pip">
            <div class="pip-blur">
                <video class="pip-video pip-video-back" src="{{ asset('videos/hero-demo.mp4') }}" autoplay muted loop playsinline preload="auto" aria-hidden="true"></video>
            </div>
            <div class="pip-main">
                <div class="pip-screen">
                    <video class="pip-video pip-video-front" src="{{ asset('videos/hero-demo.mp4') }}" autoplay muted loop playsinline preload="auto"></video>
                </div>
                <div class="pip-tag">
                    <span class="pip-dot"></span><span>Live demo &middot; SOL/USDC</span>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- FEATURES -->
<section class="section section-dark" id="features">
    <div class="container">
        <div class="section-head reveal">
            <h2>Advanced Features to videoline Your Trading.</h2>
            <p>From wallet tracking to real-time analytics, we've got you covered.</p>
        </div>

        <!-- Tab bar (scrollable strip) -->
        <div class="feat-tabs reveal" role="tablist">
            <button class="feat-tab active" data-tab="0">
                <span class="tab-bar"></span>
                <div class="feat-tab-copy">
                    <span class="tab-title">Order Execution Engine</span>
                    <span class="tab-sub">Trade with confidence.</span>
                </div>
            </button>
            <button class="feat-tab" data-tab="1">
                <span class="tab-bar"></span>
                <div class="feat-tab-copy">
                    <span class="tab-title">Wallet and Twitter Tracker</span>
                    <span class="tab-sub">Trade and track all in one place.</span>
                </div>
            </button>
            <button class="feat-tab" data-tab="2">
                <span class="tab-bar"></span>
                <div class="feat-tab-copy">
                    <span class="tab-title">Hyperliquid Perpetuals</span>
                    <span class="tab-sub">Trade leveraged Perps.</span>
                </div>
            </button>
            <button class="feat-tab" data-tab="3">
                <span class="tab-bar"></span>
                <div class="feat-tab-copy">
                    <span class="tab-title">Yield</span>
                    <span class="tab-sub">Earn while you sleep.</span>
                </div>
            </button>
        </div>

        <!-- Carousel -->
        <div class="feat-carousel reveal">
            <div class="carousel-track" id="carouselTrack">

                <!-- Slide 0: Order Execution Engine - accordion + right pane -->
                <div class="carousel-slide cs-active" data-slide="0">
                    <div class="slide-btn active-btn" role="button">
                        <div class="acc-col">
                            <div class="acc-item active" data-idx="0">
                                <button class="acc-head" type="button">
                                    <svg class="acc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    <span>Land in &le; 1 Block</span>
                                    <svg class="acc-chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="acc-body" style="--open:1">
                                    <span class="acc-lead">Our limit order execution engine is the fastest in the market.</span>
                                    <div class="acc-divider"></div>
                                    <span class="acc-detail">Our colocated nodes and proprietary execution path push limit orders through in &le; 1 block.</span>
                                    <div class="acc-mobile-media">
                                        <div class="mock">
                                            <div class="mock-bar"><span class="mock-dot"></span><span class="mock-title">EXECUTION &middot; &le; 1 BLOCK</span></div>
                                            <div class="mock-body">
                                                <div class="mock-chart">
                                                    <i style="height:32%"></i><i class="red" style="height:44%"></i><i style="height:40%"></i><i style="height:58%"></i><i style="height:46%"></i><i style="height:70%"></i><i class="red" style="height:54%"></i><i style="height:76%"></i><i style="height:64%"></i><i style="height:88%"></i><i style="height:72%"></i><i style="height:96%"></i>
                                                </div>
                                                <div class="mock-order"><div class="buy">Buy</div><div class="sell">Sell</div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="acc-item" data-idx="1">
                                <button class="acc-head" type="button">
                                    <svg class="acc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <span>Migration Sniper</span>
                                    <svg class="acc-chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="acc-body">
                                    <span class="acc-lead">Buy and sell any migrating token, landing in &le; 1 block.</span>
                                    <div class="acc-divider"></div>
                                    <span class="acc-detail">Be first into new opportunities or take profits quickly - the sniper handles timing for you.</span>
                                    <div class="acc-mobile-media">
                                        <div class="mock">
                                            <div class="mock-bar"><span class="mock-dot"></span><span class="mock-title">MIGRATION SNIPER</span></div>
                                            <div class="mock-body">
                                                <div class="mock-row head"><span>Token</span><span>Status</span><span>Entry</span></div>
                                                <div class="mock-row data"><span class="mock-pair"><span class="pair-badge">A</span> ALPHA</span><span class="up">Filled</span><span class="muted">block 298,114,203</span></div>
                                                <div class="mock-row data"><span class="mock-pair"><span class="pair-badge">B</span> BETA</span><span class="up">Filled</span><span class="muted">block 298,114,241</span></div>
                                                <div class="mock-row data"><span class="mock-pair"><span class="pair-badge">C</span> GAMMA</span><span class="muted">Queued</span><span class="muted">block 298,114,260</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="acc-item" data-idx="2">
                                <button class="acc-head" type="button">
                                    <svg class="acc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    <span>No MEV Triggers</span>
                                    <svg class="acc-chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="acc-body">
                                    <span class="acc-lead">Never get your limit orders triggered by MEV again.</span>
                                    <div class="acc-divider"></div>
                                    <span class="acc-detail">Orders route along MEV-resistant paths with frontrunning and sandwiching protection, keeping limit orders safe.</span>
                                    <div class="acc-mobile-media">
                                        <div class="mock">
                                            <div class="mock-bar"><span class="mock-dot"></span><span class="mock-title">MEV PROTECTION</span></div>
                                            <div class="mock-body">
                                                <div class="mock-stat-line"><span>Protection mode</span><b>Reduced</b></div>
                                                <div class="mock-stat-line"><span>Frontrunning</span><b class="up">Blocked</b></div>
                                                <div class="mock-stat-line"><span>Sandwiching</span><b class="up">Blocked</b></div>
                                                <div class="mock-stat-line"><span>Estimated slippage</span><b>0.4%</b></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="acc-item" data-idx="3">
                                <button class="acc-head" type="button">
                                    <svg class="acc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                                    <span>Auto-Strategies</span>
                                    <svg class="acc-chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="acc-body">
                                    <span class="acc-lead">Set your entire trading strategy in motion with a single click.</span>
                                    <div class="acc-divider"></div>
                                    <span class="acc-detail">Combine buys and limit orders in a single click, then let the platform manage execution.</span>
                                    <div class="acc-mobile-media">
                                        <div class="mock">
                                            <div class="mock-bar"><span class="mock-dot"></span><span class="mock-title">AUTO-STRATEGY</span></div>
                                            <div class="mock-body">
                                                <div class="mock-row data"><span class="mock-pair"><span class="pair-badge">1</span> Buy at 0.0042</span><span class="up">Armed</span></div>
                                                <div class="mock-row data"><span class="mock-pair"><span class="pair-badge">2</span> Limit sell at 0.0068</span><span class="up">Armed</span></div>
                                                <div class="mock-row data"><span class="mock-pair"><span class="pair-badge">3</span> Stop loss at 0.0035</span><span class="up">Armed</span></div>
                                                <div class="mock-order"><div class="buy">Launch strategy</div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right pane: active accordion mock -->
                        <div class="acc-pane">
                            <div class="media-slide active" data-slide="0">
                                <div class="mock">
                                    <div class="mock-bar"><span class="mock-dot"></span><span class="mock-title">EXECUTION &middot; &le; 1 BLOCK</span></div>
                                    <div class="mock-body">
                                        <div class="mock-chart">
                                            <i style="height:32%"></i><i class="red" style="height:44%"></i><i style="height:40%"></i><i style="height:58%"></i><i style="height:46%"></i><i style="height:70%"></i><i class="red" style="height:54%"></i><i style="height:76%"></i><i style="height:64%"></i><i style="height:88%"></i><i style="height:72%"></i><i style="height:96%"></i>
                                        </div>
                                        <div class="mock-order"><div class="buy">Buy</div><div class="sell">Sell</div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="media-slide" data-slide="1">
                                <div class="mock">
                                    <div class="mock-bar"><span class="mock-dot"></span><span class="mock-title">MIGRATION SNIPER</span></div>
                                    <div class="mock-body">
                                        <div class="mock-row head"><span>Token</span><span>Status</span><span>Entry</span></div>
                                        <div class="mock-row data"><span class="mock-pair"><span class="pair-badge">A</span> ALPHA</span><span class="up">Filled</span><span class="muted">block 298,114,203</span></div>
                                        <div class="mock-row data"><span class="mock-pair"><span class="pair-badge">B</span> BETA</span><span class="up">Filled</span><span class="muted">block 298,114,241</span></div>
                                        <div class="mock-row data"><span class="mock-pair"><span class="pair-badge">C</span> GAMMA</span><span class="muted">Queued</span><span class="muted">block 298,114,260</span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="media-slide" data-slide="2">
                                <div class="mock">
                                    <div class="mock-bar"><span class="mock-dot"></span><span class="mock-title">MEV PROTECTION</span></div>
                                    <div class="mock-body">
                                        <div class="mock-stat-line"><span>Protection mode</span><b>Reduced</b></div>
                                        <div class="mock-stat-line"><span>Frontrunning</span><b class="up">Blocked</b></div>
                                        <div class="mock-stat-line"><span>Sandwiching</span><b class="up">Blocked</b></div>
                                        <div class="mock-stat-line"><span>Estimated slippage</span><b>0.4%</b></div>
                                    </div>
                                </div>
                            </div>
                            <div class="media-slide" data-slide="3">
                                <div class="mock">
                                    <div class="mock-bar"><span class="mock-dot"></span><span class="mock-title">AUTO-STRATEGY</span></div>
                                    <div class="mock-body">
                                        <div class="mock-row data"><span class="mock-pair"><span class="pair-badge">1</span> Buy at 0.0042</span><span class="up">Armed</span></div>
                                        <div class="mock-row data"><span class="mock-pair"><span class="pair-badge">2</span> Limit sell at 0.0068</span><span class="up">Armed</span></div>
                                        <div class="mock-row data"><span class="mock-pair"><span class="pair-badge">3</span> Stop loss at 0.0035</span><span class="up">Armed</span></div>
                                        <div class="mock-order"><div class="buy">Launch strategy</div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 1: Wallet and Twitter Tracker -->
                <div class="carousel-slide" data-slide="1">
                    <div class="slide-btn" role="button">
                        <div class="iface-mock">
                            <div class="mock-bar"><span class="mock-dot"></span><span class="mock-title">WALLET &middot; TWITTER TRACKER</span></div>
                            <div class="mock-body">
                                <div class="mock-row head"><span>Wallet</span><span>Label</span><span>Action</span><span>Age</span></div>
                                <div class="mock-row data row-pulse"><span class="muted">7Fq&hellip;k9P</span><span class="muted">Smart money</span><span class="up">BUY SOLARIS 42 SOL</span><span class="muted">3s</span></div>
                                <div class="mock-row data"><span class="muted">9Az&hellip;m2C</span><span class="muted">Flipper</span><span class="down">SELL NOVA 18 SOL</span><span class="muted">21s</span></div>
                                <div class="mock-row data"><span class="muted">Bn1&hellip;q4L</span><span class="muted">Long-term</span><span class="up">BUY VERTEX 120 SOL</span><span class="muted">1m</span></div>
                                <div class="mock-tweet slide-in"><b>@solana_desk</b><span class="muted">1m</span><p>Volume rotating into older caps this hour.</p></div>
                                <div class="mock-tweet slide-in" style="animation-delay:.4s"><b>@dev_notes</b><span class="muted">4m</span><p>Contract renounced, LP burned. Watching.</p></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2: Hyperliquid Perpetuals interface -->
                <div class="carousel-slide" data-slide="2">
                    <div class="slide-btn" role="button">
                        <div class="iface-screen">
                            <div class="iface-chart">
                                <div class="mock-bar"><span class="mock-dot"></span><span class="mock-title">PERPS</span></div>
                                <div class="mock-chart">
                                    <i style="height:40%"></i><i style="height:56%"></i><i class="red" style="height:44%"></i><i style="height:64%"></i><i style="height:72%"></i><i class="red" style="height:58%"></i><i style="height:84%"></i><i style="height:76%"></i><i style="height:92%"></i>
                                </div>
                            </div>
                            <div class="iface-bar">
                                <div class="iface-row"><span class="iface-ico">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                                </span>Deep Liquidity</div>
                                <div class="iface-row"><span class="iface-ico">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                </span>Instant Fills</div>
                                <div class="iface-powered"><span>Powered by</span><b>Hyperliquid</b></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3: Yield interface -->
                <div class="carousel-slide" data-slide="3">
                    <div class="slide-btn" role="button">
                        <div class="iface-screen">
                            <div class="iface-chart">
                                <div class="mock-bar"><span class="mock-dot"></span><span class="mock-title">YIELD PLATFORM</span></div>
                                <div class="yield-visual" style="padding:20px 16px">
                                    <div class="yield-ring"><span>15%</span><small>APY</small></div>
                                    <div class="yield-bars">
                                        <i style="height:35%"></i><i style="height:50%"></i><i style="height:45%"></i><i style="height:65%"></i><i style="height:80%"></i><i style="height:95%"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="iface-bar">
                                <div class="iface-row"><span class="iface-ico">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v12M15 9h-2.5a1.5 1.5 0 0 0 0 3H11a1.5 1.5 0 0 1 0 3h2"/></svg>
                                </span>Up to 15% APY</div>
                                <div class="iface-row"><span class="iface-ico">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                </span>Instant Withdrawals</div>
                                <div class="iface-powered"><span>Powered by</span><b>Marginfi</b></div>
</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Try Advanced Ordering (right aligned) -->
        <div class="try-row reveal">
            <button class="try-btn" type="button">
                <span>Try Advanced Ordering</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M7 7h10v10"/></svg>
            </button>
        </div>
    </div>
</section>
<!-- REWARDS -->
<section class="rewards-section" id="rewards">
    <div class="rewards-wrap">
        <div class="rewards-head">
            <span class="rewards-title">Rewards</span>
            <span class="rewards-tag">Get paid to trade.</span>
        </div>
    </div>
    <div class="rewards-glow" style="background:radial-gradient(98.01% 119.36% at 65.17% 0%, #97A8FF 0%, #1D254A 49.58%, #000 100%)">
        <div class="rewards-glow" style="background:radial-gradient(113.38% 130.34% at 82.85% -0.62%, #97A8FF 0%, #1D254A 49.58%, #000 100%)">
            <div class="rewards-glow" style="background:radial-gradient(125.49% 125.66% at 99.41% 14.86%, #97A8FF 0%, #1D254A 49.58%, #000 100%)">
                <div class="rewards-overlay">
                    <div class="rewards-grid">
                        <div class="rewards-row">
                            <div class="reward-cell cell-big b-r-b">
                                <div class="reward-cell-inner">
                                    <span class="rc-title">Rewards</span>
                                    <span class="rc-sub">Earn SOL from trading.</span>
                                    <div class="rc-media">
                                        <div class="notif-illust">
                                            <div class="notif slide-in"><span class="notif-dot"></span><div><b>+2.4 SOL</b><small>Reward earned</small></div></div>
                                            <div class="notif slide-in" style="animation-delay:.5s"><span class="notif-dot"></span><div><b>+0.8 SOL</b><small>Trading reward</small></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="reward-cell cell-sm b-b">
                                <div class="reward-cell-inner">
                                    <span class="rc-title">Progress through the Ranks</span>
                                    <span class="rc-sub">Earn higher reward rates.</span>
                                    <div class="rc-media">
                                        <div class="rank-illust">
                                            <div class="rank-row"><span>Gold</span><div class="rank-track"><i style="--w:78%"></i></div></div>
                                            <div class="rank-row"><span>Plat</span><div class="rank-track"><i style="--w:54%"></i></div></div>
                                            <div class="rank-row"><span>Diamond</span><div class="rank-track"><i style="--w:31%"></i></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="rewards-row">
                            <div class="reward-cell cell-sm b-b b-r xb-b-0">
                                <div class="reward-cell-inner">
                                    <span class="rc-title">Referrals</span>
                                    <span class="rc-sub">Earn points and SOL from your friends.</span>
                                    <div class="rc-media">
                                        <div class="ref-illust">
                                            <div class="ref-chip slide-in"><span class="pair-badge">A</span> friend joined</div>
                                            <div class="ref-chip slide-in" style="animation-delay:.35s"><span class="pair-badge">B</span> +500 points</div>
                                            <div class="ref-chip slide-in" style="animation-delay:.7s"><span class="pair-badge">C</span> +1.2 SOL</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="reward-cell cell-big">
                                <div class="reward-cell-inner">
                                    <span class="rc-title">Veridian Points</span>
                                    <span class="rc-sub">Earn points through trading, referrals, and quests.</span>
                                    <div class="rc-media">
                                        <div class="points-illust">
                                            <div class="points-counter"><span class="points-num" data-count="12450">0</span><small>PTS</small></div>
                                            <div class="points-spark">
                                                <i style="height:30%"></i><i style="height:55%"></i><i style="height:40%"></i><i style="height:70%"></i><i style="height:60%"></i><i style="height:90%"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ARCHITECTURE -->
<section class="arch-section" id="architecture">
    <div class="arch-wrap">
        <div class="arch-head">
            <span class="arch-title">Architecture</span>
        </div>
        <div class="arch-row">
            <div class="arch-card">
                <div class="arch-card-head"><span class="arch-card-title">Integrations</span></div>
                <p class="arch-card-text">Veridian integrates all the different protocols and applications you use, giving you a seamless trading experience.</p>
                <div class="arch-card-media">
                    <div class="orbit">
                        <span class="orbit-node n1">Solana</span>
                        <span class="orbit-node n2">Hyperliquid</span>
                        <span class="orbit-node n3">Coinbase</span>
                        <span class="orbit-node n4">Marginfi</span>
                        <span class="orbit-node n5">Turnkey</span>
                        <span class="orbit-node n6">Social</span>
                        <span class="orbit-center">
                            <svg width="26" height="26" viewBox="0 0 36 36" fill="none"><path d="M24.14 17.39H11.86L18 7l6.14 10.39Z" fill="currentColor"/><path d="M31 29H5l4.97-8.41h16.06L31 29Z" fill="currentColor"/></svg>
                        </span>
                    </div>
                </div>
            </div>
            <div class="arch-card">
                <div class="arch-card-head"><span class="arch-card-title">Non-Custodial</span></div>
                <p class="arch-card-text">The Veridian wallet is fully non-custodial, secured by Turnkey's scalable infrastructure for managing private keys across blockchains. With air-gapped architecture, it ensures robust security, seamless recovery, and protection from vulnerabilities.</p>
                <div class="arch-card-media">
                    <div class="shield-wrap">
                        <div class="shield-pulse"></div>
                        <svg class="shield-svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                        <div class="shield-chips">
                            <span class="chip">Onchain</span>
                            <span class="chip">Air-gapped</span>
                            <span class="chip">Recovery</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section" id="faq">
    <div class="container">
        <div class="section-head reveal">
            <h2>FAQ</h2>
        </div>

        <div class="faq-list reveal">
            <div class="faq-item open">
                <button class="faq-q">What is Veridian?
                    <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="faq-a"><div class="faq-a-inner">Veridian is The one application you need for onchain trading - integrated access to the hottest assets without switching tabs.</div></div>
            </div>
            <div class="faq-item">
                <button class="faq-q">How secure is Veridian?
                    <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="faq-a"><div class="faq-a-inner">Veridian combines non-custodial infrastructure, secure key management and encryption to keep you protected. Learn more about the wallet stack at our partner <a href="https://www.turnkey.com" target="_blank" rel="noopener">Turnkey</a>.</div></div>
            </div>
            <div class="faq-item">
                <button class="faq-q">Can I buy crypto on Veridian?
                    <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="faq-a"><div class="faq-a-inner">Yes. Via our partnership with <a href="https://www.coinbase.com" target="_blank" rel="noopener">Coinbase</a>, users can buy up to $500 of crypto per week with no KYC required.</div></div>
            </div>
            <div class="faq-item">
                <button class="faq-q">How does Veridian offer yield?
                    <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="faq-a"><div class="faq-a-inner">Veridian makes passive income simple: up to 15% APY on your assets with instant withdrawals. Details are available at our partner <a href="https://www.marginfi.com" target="_blank" rel="noopener">Marginfi</a>.</div></div>
            </div>
            <div class="faq-item">
                <button class="faq-q">Is Veridian decentralized?
                    <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="faq-a"><div class="faq-a-inner">Yes. Veridian connects directly to decentralized protocols and applications &mdash; every transaction settles onchain and your funds always remain under your control.</div></div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-band">
    <div class="cta-bg" aria-hidden="true"></div>
    <div class="cta-content reveal">
        <h2>Optimize Your Trades with the Right Tools</h2>
        <a href="/login" class="btn btn-primary btn-lg">Launch Veridian</a>
    </div>
</section>

@if (isset($authMode))
    @include('partials.auth-modal', ['authMode' => $authMode])
@endif

@endsection