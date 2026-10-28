@extends('layouts.app')

@section('content')
<div class="perp-terminal" data-coin="HYPE">
    <main class="perp-grid">
    <div class="perp-market-head">
        <div class="perp-market-name">
            <span class="perp-hype-icon">∩∩</span>
            <span class="perp-pair">HYPE</span>
            <span class="perp-pair-caret">⌄</span>
            <div><span class="perp-last-price" id="perpMark">--</span> <span class="perp-loss" id="perpChange">--</span></div>
        </div>
        <div class="perp-stat"><span>Oracle Price</span><b id="perpOracle">--</b></div>
        <div class="perp-stat"><span>24h Volume</span><b id="perpVolume">--</b></div>
        <div class="perp-stat"><span>Open Interest</span><b id="perpOi">--</b></div>
        <div class="perp-stat"><span>Funding / Countdown</span><b><span class="perp-funding" id="perpFunding">--</span>&nbsp;&nbsp;<span class="perp-countdown" id="perpCountdown">00:00:00</span></b></div>
    </div>

        <section class="perp-chart-area" aria-label="HYPE-USDC perpetual chart">
            <div class="tradingview-widget-container">
                <div id="tvPerpChart"></div>
            </div>
        </section>

        <section class="perp-orderbook">
            <div class="perp-book-tabs"><button class="active" data-view="book">Order Book</button><button data-view="trades">Trades</button></div>
            <div class="perp-book-pane" data-pane="book">
                <div class="perp-book-head"><span>Price</span><span>Amount (USD)</span><span>Total (USD)</span></div>
                <div class="perp-book-rows">
                    <div id="perpAskRows"></div>
                    <div class="perp-spread"><span>Spread:</span><b id="perpSpreadAbs">--</b><b id="perpSpreadPct">--</b></div>
                    <div id="perdBidRows"></div>
                </div>
            </div>
            <div class="perp-book-pane" data-pane="trades" hidden>
                <div class="perp-book-head"><span>Price</span><span>Amount (HYPE)</span><span>Time</span></div>
                <div class="perp-trades-rows" id="perpTradesRows"></div>
            </div>
        </section>

        <section class="perp-positions">
            <div class="perp-position-tabs"><button class="active">Positions</button><button>Open Orders</button><button>Trades</button></div>
            <div class="perp-position-head"><span>Asset</span><span>Position</span><span>Position Value</span><span>Entry Price</span><span>Mark Price</span><span>Liquidation Price <b>Margin Used (PNL) ↓</b></span><span>TP / SL</span><span></span><span>Close</span></div>
            <div class="perp-empty-state">No open positions</div>
        </section>

        <section class="perp-order-panel">
            <div class="perp-side-toggle"><button class="long active" data-side="Long">Long</button><button class="short" data-side="Short">Short</button></div>
            <div class="perp-order-tabs"><button class="active" data-order="Market">Market</button><button data-order="Limit">Limit</button><span class="perp-leverage">Leverage: 10x</span></div>
            <div class="perp-amount-box">
                <div class="perp-amount-label"><span>Buy Amount</span><span>HYPE</span></div>
                <div class="perp-amount-entry"><span class="perp-amount-value">0.0 USDC</span><span class="perp-balance">∩∩ 0</span></div>
            </div>
            <div>
                <div class="perp-slider"><input type="range" min="0" max="100" value="0" id="perpAmountSlider" aria-label="Order amount percentage"></div>
                <div class="perp-percentages"><span>0%</span><span>25%</span><span>50%</span><span>75%</span><span>100%</span></div>
            </div>
            <label class="perp-tp-row"><input class="perp-check" type="checkbox"><span>TP/SL</span><span class="perp-estimate">Est. Liq. Price: --</span></label>
            <button class="perp-place-order">Place Long Order</button>
            <button class="perp-funds">Add More Funds</button>
            <div class="perp-account-info">
                <div class="perp-info-row"><span>Available Margin</span><b class="perp-balance-chip">0.00 USDC</b></div>
                <div class="perp-info-row"><span>Account Value</span><b>0.00 USDC</b></div>
                <div class="perp-info-row"><span>Current Position</span><b>--</b></div>
            </div>
            <div class="perp-powered">powered by <b>∩∩</b> Hyperliquid</div>
        </section>
    </main>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
<script>
(function () {
    var ROOT = document.querySelector('.perp-terminal');
    if (!ROOT) return;
    var COIN = ROOT.getAttribute('data-coin') || 'HYPE';
    var HL_INFO = 'https://api.hyperliquid.xyz/info';
    var HL_WS = 'wss://api.hyperliquid.xyz/ws';
    var ROW_H = 22;
    var MAX_TRADES = 60;
    var lastMark = 0;

    if (window.TradingView) {
        new TradingView.widget({
            autosize: true,
            symbol: 'HYPERLIQUID:HYPEUSDC.P',
            interval: '1',
            timezone: 'Etc/UTC',
            theme: 'dark',
            style: '1',
            locale: 'en',
            toolbar_bg: '#101116',
            enable_publishing: false,
            allow_symbol_change: true,
            hide_side_toolbar: false,
            save_image: false,
            container_id: 'tvPerpChart'
        });
    }

    function $(id) { return document.getElementById(id); }
    function setText(id, txt) { var el = $(id); if (el) el.textContent = txt; }
    function toPct(n) { return (Number(n) || 0) * 100; }

    function fmtUsd(v) {
        v = Number(v) || 0;
        if (v >= 1000) return Math.round(v).toLocaleString('en-US');
        if (v >= 1) return v.toFixed(2);
        return v.toFixed(4);
    }
    function fmtCompact(v) {
        v = Number(v) || 0;
        var a = Math.abs(v);
        if (a >= 1e9) return (v / 1e9).toFixed(2) + 'B';
        if (a >= 1e6) return (v / 1e6).toFixed(2) + 'M';
        if (a >= 1e3) return (v / 1e3).toFixed(2) + 'K';
        return v.toFixed(2);
    }
    function bookRowHtml(side, px, usd, total, width) {
        return '<div class="perp-book-row ' + side + '">' +
            '<i class="perp-depth-fill" style="width:' + width.toFixed(1) + '%"></i>' +
            '<span class="perp-price">' + px + '</span><span>' + usd + '</span><span>' + total + '</span></div>';
    }

    /* ---------------- Order book (l2Book) ---------------- */
    var book = null;

    function renderBook() {
        if (!book || !book.levels) return;
        var bids = book.levels[0] || [];
        var asks = book.levels[1] || [];
        var rowsEl = document.querySelector('.perp-book-rows');
        var avail = rowsEl && rowsEl.clientHeight ? rowsEl.clientHeight : 460;
        var n = Math.max(4, Math.floor((avail - 26) / ROW_H / 2));

        var askRows = [], total = 0, i;
        var asksRaw = asks.slice(0, n);
        for (i = 0; i < asksRaw.length; i++) {
            var a = asksRaw[i];
            var aUsd = (Number(a.px) || 0) * (Number(a.sz) || 0);
            total += aUsd;
            askRows.push({ px: a.px, usd: aUsd, total: total });
        }
        var askMax = total || 1;
        askRows.reverse();
        var askEl = document.getElementById('perpAskRows');
        if (askEl) askEl.innerHTML = askRows.map(function (r) {
            return bookRowHtml('ask', r.px, fmtUsd(r.usd), fmtUsd(r.total), r.total / askMax * 100);
        }).join('');

        var bidsRaw = bids.slice(0, n);
        var bidRows = [], bidTotal = 0;
        for (i = 0; i < bidsRaw.length; i++) {
            var bu = (Number(bidsRaw[i].px) || 0) * (Number(bidsRaw[i].sz) || 0);
            bidTotal += bu;
            bidRows.push({ px: bidsRaw[i].px, usd: bu, total: bidTotal });
        }
        var bidMax = bidTotal || 1;
        var bidEl = document.getElementById('perdBidRows');
        if (bidEl) bidEl.innerHTML = bidRows.map(function (r) {
            return bookRowHtml('bid', r.px, fmtUsd(r.usd), fmtUsd(r.total), r.total / bidMax * 100);
        }).join('');

        var bestBid = bidsRaw[0], bestAsk = asksRaw[0];
        if (bestBid && bestAsk) {
            var spread = (Number(bestAsk.px) || 0) - (Number(bestBid.px) || 0);
            var spreadPct = Number(bestBid.px) ? (spread / Number(bestBid.px) * 100) : 0;
            setText('perpSpreadAbs', spread.toFixed(3));
            setText('perpSpreadPct', spreadPct.toFixed(3) + '%');
        }
    }

    var trades = [];
    function renderTrades() {
        var el = document.getElementById('perpTradesRows');
        if (!el) return;
        el.innerHTML = trades.slice(0, MAX_TRADES).map(function (t) {
            var time = new Date(Number(t.time) || 0).toLocaleTimeString('en-GB', { hour12: false });
            var cls = t.side === 'B' ? 'perp-buy' : 'perp-sell';
            return '<div class="perp-trade-row"><span class="' + cls + '">' + t.px + '</span><span>' + t.sz + '</span><span>' + time + '</span></div>';
        }).join('');
    }
    function prependTrades(list) {
        if (!list || !list.length) return;
        for (var i = 0; i < list.length; i++) trades.unshift(list[i]);
        if (trades.length > MAX_TRADES) trades.length = MAX_TRADES;
        renderTrades();
    }

    function updateCtx(ctx) {
        if (!ctx) return;
        var mark = Number(ctx.markPx) || 0;
        var prev = Number(ctx.prevDayPx) || 0;
        lastMark = mark;
        setText('perpMark', mark.toFixed(3));
        setText('perpOracle', (Number(ctx.oraclePx) || 0).toFixed(3));
        setText('perpVolume', '$' + fmtCompact(ctx.dayNtlVlm));
        setText('perpOi', '$' + fmtCompact((Number(ctx.openInterest) || 0) * mark));
        setText('perpFunding', toPct(ctx.funding).toFixed(5) + '%');
        var chg = prev > 0 ? (mark - prev) / prev * 100 : 0;
        var chEl = document.getElementById('perpChange');
        if (chEl) {
            chEl.textContent = (chg >= 0 ? '+' : '') + chg.toFixed(2) + '%';
            chEl.classList.toggle('perp-loss', chg < 0);
            chEl.classList.toggle('perp-gain', chg >= 0);
        }
    }

    function pad(n) { return String(n).padStart(2, '0'); }
    function tickCountdown() {
        var now = new Date();
        var next = new Date(now.getTime());
        next.setMinutes(0, 0, 0);
        next.setHours(now.getHours() + 1);
        var s = Math.max(0, Math.floor((next - now) / 1000));
        setText('perpCountdown', pad(Math.floor(s / 3600)) + ':' + pad(Math.floor((s % 3600) / 60)) + ':' + pad(s % 60));
    }

    function info(body) {
        return fetch(HL_INFO, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }).then(function (r) { return r.ok ? r.json() : null; }).catch(function () { return null; });
    }
    function seed() {
        info({ type: 'l2Book', coin: COIN }).then(function (data) { if (data) { book = data; renderBook(); } });
        info({ type: 'recentTrades', coin: COIN }).then(function (data) { if (Array.isArray(data)) prependTrades(data); });
    }

    var wsOpen = false;
    function connect() {
        var ws;
        try { ws = new WebSocket(HL_WS); } catch (e) { return; }
        ws.onopen = function () {
            wsOpen = true;
            ['l2Book', 'trades', 'activeAssetCtx'].forEach(function (type) {
                ws.send(JSON.stringify({ method: 'subscribe', subscription: { type: type, coin: COIN } }));
            });
        };
        ws.onmessage = function (ev) {
            var msg; try { msg = JSON.parse(ev.data); } catch (e) { return; }
            if (msg.channel === 'l2Book' && msg.data) { book = msg.data; renderBook(); }
            else if (msg.channel === 'trades' && msg.data) { prependTrades(msg.data); }
            else if (msg.channel === 'activeAssetCtx' && msg.data && msg.data.ctx) { updateCtx(msg.data.ctx); }
        };
        ws.onclose = function () { wsOpen = false; setTimeout(connect, 3000); };
        ws.onerror = function () { try { ws.close(); } catch (e) {} };
    }

    setInterval(function () { if (!wsOpen) seed(); }, 3000);

    document.querySelectorAll('.perp-book-tabs button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.perp-book-tabs button').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var view = btn.getAttribute('data-view');
            document.querySelectorAll('.perp-book-pane').forEach(function (p) {
                p.hidden = p.getAttribute('data-pane') !== view;
            });
            if (view === 'book') renderBook();
        });
    });
    document.querySelectorAll('.perp-position-tabs button, .perp-order-tabs button').forEach(function (button) {
        button.addEventListener('click', function () {
            var group = button.parentElement.querySelectorAll('button');
            group.forEach(function (item) { item.classList.remove('active'); });
            button.classList.add('active');
        });
    });
    function activeSide() { var b = document.querySelector('.perp-side-toggle button.active'); return b ? b.getAttribute('data-side') : 'Long'; }
    function updateEstimate() {
        var el = document.querySelector('.perp-estimate');
        if (!el) return;
        if (!lastMark) { el.textContent = 'Est. Liq. Price: --'; return; }
        var liq = activeSide() === 'Short' ? lastMark * 1.1 : lastMark * 0.9;
        el.textContent = 'Est. Liq. Price: ' + liq.toFixed(3);
    }
    function setSide(side) {
        var isShort = side === 'Short';
        var label = document.querySelector('.perp-amount-label span:first-child');
        if (label) label.textContent = isShort ? 'Sell Amount' : 'Buy Amount';
        var btn = document.querySelector('.perp-place-order');
        if (btn) { btn.textContent = 'Place ' + (isShort ? 'Short' : 'Long') + ' Order'; btn.classList.toggle('short', isShort); }
        updateEstimate();
    }
    document.querySelectorAll('.perp-side-toggle button').forEach(function (button) {
        button.addEventListener('click', function () {
            document.querySelectorAll('.perp-side-toggle button').forEach(function (item) { item.classList.remove('active'); });
            button.classList.add('active');
            setSide(button.getAttribute('data-side'));
        });
    });
    var slider = document.getElementById('perpAmountSlider');
    if (slider) slider.addEventListener('input', function () {
        var v = document.querySelector('.perp-amount-value');
        if (v) v.textContent = slider.value + '% · ' + slider.value + ' USDC';
    });
    var placeBtn = document.querySelector('.perp-place-order');
    if (placeBtn) placeBtn.addEventListener('click', function () {
        var original = 'Place ' + (activeSide() === 'Short' ? 'Short' : 'Long') + ' Order';
        placeBtn.textContent = 'Connect Wallet to Trade';
        placeBtn.disabled = true;
        setTimeout(function () { placeBtn.textContent = original; placeBtn.disabled = false; }, 1500);
    });
    var fundsBtn = document.querySelector('.perp-funds');
    if (fundsBtn) fundsBtn.addEventListener('click', function () {
        var original = fundsBtn.textContent;
        fundsBtn.textContent = 'Connect Wallet to Deposit';
        fundsBtn.disabled = true;
        setTimeout(function () { fundsBtn.textContent = original; fundsBtn.disabled = false; }, 1500);
    });

    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(renderBook, 150);
    });

    seed();
    connect();
    tickCountdown();
    setInterval(tickCountdown, 1000);
})();
</script>
@endpush
