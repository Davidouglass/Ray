(function () {
    'use strict';
    var ROOT = document.getElementById('predictRoot');
    if (!ROOT) return;

    var GAMMA = ROOT.getAttribute('data-gamma') || 'https://gamma-api.polymarket.com';
    var DATA  = ROOT.getAttribute('data-data')  || 'https://data-api.polymarket.com';
    var CLOB  = ROOT.getAttribute('data-clob')  || 'https://clob.polymarket.com';
    var CB_SPOT = 'https://api.coinbase.com/v2/prices/BTC-USD/spot';
    var CB_CANDLES = 'https://api.exchange.coinbase.com/products/BTC-USD/candles';
    var CB_STATS = 'https://api.exchange.coinbase.com/products/BTC-USD/stats';

    var TV_SYMBOL = 'COINBASE:BTCUSD';
    var TV_INTERVAL = { '60': '1', '300': '5', '3600': '60', '21600': '360', '86400': '1D', '604800': '1W', '2592000': '1M' };

    var $ = function (id) { return document.getElementById(id); };
    var set = function (id, t) { var e = $(id); if (e) e.textContent = t; };
    var state = { markets: [], featured: null, side: 'Buy', otype: 'Market', outcome: 0, amount: 0, gran: 300, tv: null, beat: 0, hold: [0, 0], cost: [0, 0], bought: 0, sold: 0, limitPrice: 50, bestBid: 0, bestAsk: 0 };

    function num(v) { var n = parseFloat(v); return isFinite(n) ? n : 0; }
    function usd(v) { v = num(v); if (Math.abs(v) >= 1e9) return '$' + (v / 1e9).toFixed(2) + 'B'; if (Math.abs(v) >= 1e6) return '$' + (v / 1e6).toFixed(2) + 'M'; if (Math.abs(v) >= 1e3) return '$' + (v / 1e3).toFixed(2) + 'K'; return '$' + v.toFixed(2); }
    function cents(p) { var c = num(p) * 100; return (c % 1 === 0 ? c.toFixed(0) : c.toFixed(1)) + '\u00a2'; }
    function price2(v) { return '$' + num(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function parseArr(v) { if (Array.isArray(v)) return v; if (typeof v === 'string') { try { return JSON.parse(v); } catch (e) { return []; } } return []; }
    function getJSON(url) { return fetch(url).then(function (r) { return r.ok ? r.json() : null; }).catch(function () { return null; }); }

    function norm(m) {
        return {
            id: String(m.id || m.conditionId || m.slug || ''),
            slug: m.slug || '',
            question: m.question || m.title || '',
            outcomes: parseArr(m.outcomes).map(String),
            prices: parseArr(m.outcomePrices || m.prices).map(Number),
            tokens: parseArr(m.clobTokenIds || m.tokens).map(String),
            cond: m.conditionId || '',
            vol: Number(m.volume != null ? m.volume : m.vol) || Number(m.volume24hr) || 0,
            liq: Number(m.liquidity != null ? m.liquidity : m.liq) || 0,
            end: m.endDate || m.end || '',
            desc: m.description || '',
            image: m.image || m.icon || ''
        };
    }

    function pickFeatured(list) {
        var btc = list.filter(function (m) { return /bitcoin\s+up\s+or\s+down/i.test(m.question) && m.prices.length; });
        var live = btc.filter(function (m) { return !isClosed(m); });
        live.sort(function (a, b) { return (Date.parse(a.end) || 0) - (Date.parse(b.end) || 0); });
        if (live.length) return live[0];
        var past = btc.slice().sort(function (a, b) { return (Date.parse(b.end) || 0) - (Date.parse(a.end) || 0); });
        if (past.length) return past[0];
        var btcAny = list.filter(function (m) { return /bitcoin/i.test(m.question) && m.prices.length; });
        return btcAny[0] || null;
    }

    function isClosed(m) { return m && m.end && (Date.parse(m.end) || 0) < Date.now(); }

    // ---------------- header / outcome row ----------
    function renderFeatured(m) {
        if (!m) return;
        set('pcTitle', m.question);
        set('pcEventName', m.question.length > 46 ? m.question.slice(0, 44) + '\u2026' : m.question);
        set('pcSubMkt', m.question);
        set('pcOutcomeTitle', m.question);
        set('pcVol', usd(m.vol));
        set('pcLiq', usd(m.liq));
        set('pcOutVol', '\u25A4 ' + usd(m.vol));
        set('pcOutLiq', '\u25C8 ' + usd(m.liq));

        var lead = m.prices.length ? Math.max(Number(m.prices[0]) || 0, Number(m.prices[1]) || 0) : 0;
        var leadIdx = (Number(m.prices[1]) || 0) > (Number(m.prices[0]) || 0) ? 1 : 0;
        var pct = Math.round(lead * 100);
        set('pcSubChance', pct + '%');
        set('pcChance', pct + '%');
        var deltaEl = $('pcChanceDelta');
        if (deltaEl) deltaEl.className = leadIdx ? 'down' : '';

        var closed = isClosed(m);
        set('pcStateText', closed ? 'Closed' : 'Live');
        var dot = $('pcStateDot'); if (dot) dot.className = 'closed-dot' + (closed ? ' is-closed' : ' is-live');
        set('pcEventState', closed ? 'CLOSED' : 'LIVE');

        var st = $('pcStatus');
        if (st) { st.textContent = (leadIdx ? '\u265C Down' : '\u265C Up'); st.className = 'up-status' + (leadIdx ? ' down' : ''); }

        set('pcUpPrice', cents(m.prices[0] != null ? m.prices[0] : 0));
        set('pcDownPrice', m.prices[1] != null ? cents(m.prices[1]) : '\u2014');

        var ocs = $('pcOutcomes');
        if (ocs) {
            var ob = ocs.querySelectorAll('button');
            if (ob[0] && ob[0].childNodes[0]) ob[0].childNodes[0].nodeValue = String(m.outcomes[0] || 'Up');
            if (ob[1] && ob[1].childNodes[0]) ob[1].childNodes[0].nodeValue = String(m.outcomes[1] || 'Down');
        }

        syncLimitRow();
        updateAmount();
        updatePills();
    }

    function loadMarkets() {
        Promise.all([
            getJSON(GAMMA + '/markets?closed=false&active=true&limit=100&order=startDate&ascending=false'),
            getJSON(GAMMA + '/markets?closed=false&active=true&limit=100&order=volume24hr&ascending=false'),
            getJSON(GAMMA + '/markets?closed=false&active=true&limit=100&order=endDate&ascending=true')
        ]).then(function (res) {
            var raw = [].concat(res[0] || [], res[1] || [], res[2] || []);
            var list = raw.map(norm).filter(function (m) { return m.question; });
            var seen = {}, out = [];
            list.forEach(function (m) { var k = m.id || m.slug; if (!seen[k]) { seen[k] = 1; out.push(m); } });
            state.markets = out;
            var cur = state.featured;
            if (!cur) {
                state.featured = pickFeatured(out);
                if (state.featured) { renderFeatured(state.featured); loadBeat(); loadBook(); }
            } else {
                var same = out.filter(function (x) { return x.slug === cur.slug; })[0];
                if (same) { state.featured = same; renderFeatured(same); }
            }
        });
    }

    
    // ---------------- price to beat (Coinbase, at market open) ----------------
    function to24(h, m, ap) { if (ap) { ap = ap.toUpperCase(); if (ap === 'PM' && h < 12) h += 12; if (ap === 'AM' && h === 12) h = 0; } return h * 60 + m; }
    function marketWindowMs(m) {
        var mm = (m.question || '').match(/(\d{1,2}):(\d{2})\s*(AM|PM)?[^0-9]+(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
        if (mm) {
            var t1 = to24(+mm[1], +mm[2], mm[3]);
            var t2 = to24(+mm[4], +mm[5], mm[6] || mm[3]);
            var d = t2 - t1; if (d < 0) d += 1440;
            if (d > 0) return d * 60000;
        }
        return 3600000;
    }
    function setBeatText(t) { set('pcBeat', t); var b = $('pcPsBeat'); if (b) b.textContent = t; }
    function loadBeat() {
        var m = state.featured; if (!m) return;
        var endMs = Date.parse(m.end) || 0;
        if (!endMs) { setBeatText('\u2014'); return; }
        var startMs = endMs - marketWindowMs(m);
        var nowMs = Date.now();
        if (startMs > nowMs) { setBeatText('\u2014'); return; }
        var s = new Date(startMs - 120000).toISOString();
        var e = new Date(Math.min(startMs + 120000, nowMs)).toISOString();
        getJSON(CB_CANDLES + '?granularity=60&start=' + encodeURIComponent(s) + '&end=' + encodeURIComponent(e)).then(function (rows) {
            if (!Array.isArray(rows) || !rows.length) {
                getJSON(CB_SPOT).then(function (j) { var p = j && j.data ? Number(j.data.amount) : 0; if (p) setBeatText(price2(p)); });
                return;
            }
            var best = rows.slice().sort(function (a, b) { return Math.abs(a[0] * 1000 - startMs) - Math.abs(b[0] * 1000 - startMs); })[0];
            var px = Number(best[4]) || Number(best[1]);
            if (px) setBeatText(price2(px));
        });
    }

    // ---------------- TradingView chart ----------------
    function createChart() {
        var host = $('pcTvChart');
        if (!host) return;
        if (!window.TradingView) { setTimeout(createChart, 400); return; }
        host.innerHTML = '';
        try {
            state.tv = new window.TradingView.widget({
                autosize: true,
                symbol: TV_SYMBOL,
                interval: TV_INTERVAL[state.gran] || '5',
                timezone: 'America/New_York',
                theme: 'dark',
                style: '1',
                locale: 'en',
                toolbar_bg: '#101115',
                enable_publishing: false,
                hide_side_toolbar: true,
                allow_symbol_change: false,
                save_image: false,
                withdateranges: true,
                studies: ['Volume@tv-basicstudies'],
                container_id: 'pcTvChart'
            });
        } catch (e) { /* leave status visible */ }
    }

    function updatePills() {
        var step = (state.gran || 300) * 1000;
        var now = Date.now();
        for (var i = 0; i < 4; i++) {
            var t = new Date(now - i * step);
            set('pcPill' + i, t.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }));
        }
    }

    // ---------------- Order book (Polymarket CLOB) ----------------
    function renderBook(book) {
        var el = $('pcBook'); if (!el) return;
        if (!book || (!book.bids && !book.asks)) { el.innerHTML = '<div class="bk-row"><span></span><span></span><span>No book</span></div>'; return; }
        var asks = (book.asks || []).map(function (a) { return { p: Number(a.price) || 0, s: Number(a.size) || 0 }; }).filter(function (r) { return r.p; }).sort(function (a, b) { return a.p - b.p; }).slice(0, 12).reverse();
        var bids = (book.bids || []).map(function (a) { return { p: Number(a.price) || 0, s: Number(a.size) || 0 }; }).filter(function (r) { return r.p; }).sort(function (a, b) { return b.p - a.p; }).slice(0, 12);
        var row = function (r) {
            return '<div class="bk-row"><span class="bk-price">' + (r.p * 100).toFixed(1) + '\u00A2</span><span>' + r.s.toLocaleString('en-US', { maximumFractionDigits: 0 }) + '</span><span>' + usd(r.p * r.s) + '</span></div>';
        };
        el.innerHTML = '<div class="bk-ask">' + asks.map(row).join('') + '</div>' +
                       '<div class="bk-bid">' + bids.map(row).join('') + '</div>';
        var bestAsk = Infinity, bestBid = 0;
        (book.asks || []).forEach(function (a) { var p = Number(a.price) || 0; if (p) bestAsk = Math.min(bestAsk, p); });
        (book.bids || []).forEach(function (a) { var p = Number(a.price) || 0; if (p) bestBid = Math.max(bestBid, p); });
        state.bestBid = isFinite(bestBid) ? bestBid : 0;
        state.bestAsk = isFinite(bestAsk) ? bestAsk : 0;
        var sp = (isFinite(bestAsk) && bestBid) ? (bestAsk - bestBid) : 0;
        set('pcSpread', 'Spread:' + '\u2002' + (sp ? (sp * 100).toFixed(1) + '\u00A2' : '\u2014'));
        updateLimitQuote();
    }
    function loadBook() {
        var m = state.featured; if (!m || !m.tokens.length) return;
        getJSON(CLOB + '/book?token_id=' + encodeURIComponent(m.tokens[state.outcome] || m.tokens[0])).then(renderBook);
    }

    // ---------------- Order panel ----------------
    function outcomePrice() {
        var m = state.featured; if (!m) return 0;
        return Number(m.prices[state.outcome]) || 0;
    }
    function isLimit() { return state.otype === 'Limit'; }
    function activePrice() {
        if (isLimit()) return (state.limitPrice || 0) / 100;
        return outcomePrice();
    }
    function toast(msg) {
        if (window.Axiom && window.Axiom.toast) window.Axiom.toast(msg);
    }
    function updateLimitQuote() {
        var q = $('pcLimitQuote'); if (!q) return;
        var bid = state.bestBid, ask = state.bestAsk;
        if (bid && ask) q.textContent = (bid * 100).toFixed(1) + '\u00A2 / ' + (ask * 100).toFixed(1) + '\u00A2';
        else q.textContent = '\u2014';
    }
    function syncLimitRow() {
        var row = $('pcLimitRow'); if (!row) return;
        row.hidden = !isLimit();
        var inp = $('pcLimitPrice');
        if (inp && document.activeElement !== inp) inp.value = (state.limitPrice || 0).toFixed(1);
        updateLimitQuote();
    }
    function updateSubmit() {
        var b = $('pcSubmit'); if (!b) return;
        var m = state.featured;
        var closed = m ? isClosed(m) : true;
        if (closed) { b.textContent = 'Market Closed'; b.disabled = true; b.style.background = ''; b.style.color = ''; return; }
        var oc = m && m.outcomes[state.outcome] ? m.outcomes[state.outcome] : '';
        var price = activePrice();
        var shares = price > 0 ? (state.amount || 0) / price : 0;
        b.textContent = state.side + ' ' + oc + (isLimit() ? ' (Limit)' : '') + (shares ? ' \u00b7 ' + shares.toFixed(1) + ' shares' : '');
        b.disabled = false;
        b.style.background = state.side === 'Sell' ? '#ff405b' : '#24d6a5';
        b.style.color = state.side === 'Sell' ? '#fff' : '#04130f';
    }
    function updateAmount() {
        var a = state.amount || 0;
        set('amountDisplay', a.toFixed(2));
        var price = activePrice();
        var shares = price > 0 ? a / price : 0;
        var av = document.querySelector('.amount-value');
        if (av) av.innerHTML = price > 0
            ? '<span>' + shares.toFixed(2) + ' shares</span><span>@ ' + cents(price) + (isLimit() ? ' limit' : '') + ' \u00b7 max ' + usd(shares) + '</span>'
            : '<span>0.00 shares</span><span>@ \u2014</span>';
        updateSubmit();
    }
    function updatePerf() {
        var m = state.featured;
        var holding = 0;
        if (m) for (var i = 0; i < 2; i++) holding += (state.hold[i] || 0) * (Number(m.prices[i]) || 0);
        set('pcBought', usd(state.bought));
        set('pcSold', usd(state.sold));
        set('pcHolding', usd(holding));
        var pnl = holding + state.sold - state.bought;
        var e = $('pcPnl');
        if (e) { e.textContent = (pnl >= 0 ? '+' : '-') + usd(Math.abs(pnl)); e.className = pnl >= 0 ? 'pos' : 'neg'; }
    }
    function placeOrder() {
        var m = state.featured;
        if (!m) return toast('No market selected');
        if (isClosed(m)) return toast('Market is closed');
        var price = activePrice();
        if (!price) return toast(isLimit() ? 'Set a limit price' : 'No price available');
        var a = state.amount || 0;
        if (a <= 0) return toast('Enter an amount');
        var idx = state.outcome;
        var name = m.outcomes[idx] || 'shares';
        var shares = a / price;
        if (state.side === 'Buy') {
            state.hold[idx] += shares; state.cost[idx] += a; state.bought += a;
            toast('Bought ' + shares.toFixed(2) + ' ' + (m.outcomes[idx] || 'shares') + ' @ ' + cents(price) + (isLimit() ? ' (limit)' : '') + ' · simulated');
        } else {
            var have = state.hold[idx] || 0;
            var sellSh = Math.min(shares, have);
            if (sellSh <= 0) return toast('No position to sell');
            var proceeds = sellSh * price;
            var basis = have > 0 ? state.cost[idx] * (sellSh / have) : 0;
            state.hold[idx] -= sellSh; state.cost[idx] -= basis; state.sold += proceeds;
            toast('Sold ' + sellSh.toFixed(2) + ' ' + (m.outcomes[state.outcome] || 'shares') + ' @ ' + cents(price) + (isLimit() ? ' (limit)' : '') + ' · simulated');
        }
        updatePerf();
        updateAmount();
    }

    // ---------------- wiring ----------------
    function wire() {
        var side = $('pcSide');
        if (side) side.addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) return;
            state.side = b.getAttribute('data-action');
            Array.prototype.forEach.call(side.querySelectorAll('button'), function (x) { x.classList.remove('active'); });
            b.classList.add('active'); updateSubmit();
        });
        var ot = $('pcOtype');
        if (ot) ot.addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) return;
            state.otype = b.getAttribute('data-otype');
            Array.prototype.forEach.call(ot.querySelectorAll('button'), function (x) { x.classList.remove('active'); });
            b.classList.add('active');
            syncLimitRow(); updateAmount();
        });
        var lmin = $('pcLimitMinus');
        if (lmin) lmin.addEventListener('click', function () { state.limitPrice = Math.max(0.1, Math.round((state.limitPrice - 0.1) * 10) / 10); syncLimitRow(); updateAmount(); });
        var lplus = $('pcLimitPlus');
        if (lplus) lplus.addEventListener('click', function () { state.limitPrice = Math.min(99.9, Math.round((state.limitPrice + 0.1) * 10) / 10); syncLimitRow(); updateAmount(); });
        var linput = $('pcLimitPrice');
        if (linput) linput.addEventListener('input', function () { state.limitPrice = Math.min(99.9, Math.max(0, Number(linput.value) || 0)); updateAmount(); });
        var lq = $('pcLimitQuick');
        if (lq) lq.addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) return;
            var lp = b.getAttribute('data-lp');
            if (lp === 'bid' && state.bestBid) state.limitPrice = state.bestBid * 100;
            else if (lp === 'ask' && state.bestAsk) state.limitPrice = state.bestAsk * 100;
            else if (lp === 'mid' && state.bestBid && state.bestAsk) state.limitPrice = (state.bestBid + state.bestAsk) * 50;
            state.limitPrice = Math.round(state.limitPrice * 10) / 10;
            syncLimitRow(); updateAmount();
        });
        var oc = $('pcOutcomes');
        if (oc) oc.addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) return;
            state.outcome = +b.getAttribute('data-outcome');
            Array.prototype.forEach.call(oc.querySelectorAll('button'), function (x) { x.classList.remove('active'); });
            b.classList.add('active'); updateSubmit(); loadBook(); updateAmount();
        });
        var pres = $('pcPresets');
        if (pres) pres.addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) return;
            if (b.getAttribute('data-amount')) { state.amount = Number(b.getAttribute('data-amount')) || 0; }
            else { var v = window.prompt('Enter amount (USD)', state.amount || ''); if (v !== null) state.amount = Number(v) || 0; }
            Array.prototype.forEach.call(pres.querySelectorAll('button'), function (x) { x.classList.toggle('active', x === b); });
            updateAmount();
        });
        var sub = $('pcSubmit');
        if (sub) sub.addEventListener('click', function () { placeOrder(); });
        var tabs = $('pcTabs');
        if (tabs) tabs.addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) return;
            Array.prototype.forEach.call(tabs.querySelectorAll('button'), function (x) { x.classList.remove('active'); });
            b.classList.add('active');
            showPane(b.getAttribute('data-tab') || 'overview');
        });
        var per = $('pcPeriods');
        if (per) per.addEventListener('click', function (e) {
            var b = e.target.closest('button'); if (!b) return;
            state.gran = Number(b.getAttribute('data-gran')) || 300;
            Array.prototype.forEach.call(per.querySelectorAll('button'), function (x) { x.classList.remove('active'); });
            b.classList.add('active'); updatePills(); createChart();
        });
        var hr = $('pcHideResolved');
        if (hr) hr.addEventListener('click', function () { hr.classList.toggle('on'); });
        var gl = $('pcGoLive');
        if (gl) gl.addEventListener('click', function () {
            var live = state.markets.filter(function (m) { return /bitcoin\s+up\s+or\s+down/i.test(m.question) && m.prices.length && !isClosed(m); });
            live.sort(function (a, b) { return (Date.parse(a.end) || 0) - (Date.parse(b.end) || 0); });
            if (live.length) { state.featured = live[0]; renderFeatured(live[0]); loadBeat(); loadBook(); }
        });
    }

    // ---------------- tab panes: BTC Price / Compare / Market ----------------
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function fmtTime(v) {
        var t = typeof v === 'number' ? (v > 1e12 ? v : (v > 1e9 ? v * 1000 : NaN)) : Date.parse(v);
        return t ? new Date(t).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }) : '\u2014';
    }

    function showPane(name) {
        var panes = document.querySelectorAll('.market-left .tab-pane');
        Array.prototype.forEach.call(panes, function (p) {
            var on = p.getAttribute('data-pane') === name;
            p.classList.toggle('active', on);
            p.hidden = !on;
        });
        if (name === 'price') { ensurePriceChart(); loadPriceStats(); }
        else if (name === 'compare') renderCompare();
        else if (name === 'market') { renderMarket(); ensureMarketChart(); }
    }

    var priceChartReady = false;
    function ensurePriceChart() {
        var host = $('pcPriceTvChart'); if (!host) return;
        if (priceChartReady && host.children.length) return;
        if (!window.TradingView) { setTimeout(ensurePriceChart, 400); return; }
        try {
            new window.TradingView.widget({
                autosize: true, symbol: TV_SYMBOL, interval: TV_INTERVAL[state.gran] || '5',
                timezone: 'America/New_York', theme: 'dark', style: '1', locale: 'en', toolbar_bg: '#101115',
                enable_publishing: false, hide_side_toolbar: true, allow_symbol_change: false, save_image: false,
                withdateranges: true, studies: ['Volume@tv-basicstudies'], container_id: 'pcPriceTvChart'
            });
            priceChartReady = true;
        } catch (e) {}
    }
    var marketChartReady = false;
    function ensureMarketChart() {
        var host = $('pcMarketTvChart'); if (!host) return;
        if (marketChartReady && host.children.length) return;
        if (!window.TradingView) { setTimeout(ensureMarketChart, 400); return; }
        try {
            new window.TradingView.widget({
                autosize: true, symbol: TV_SYMBOL, interval: TV_INTERVAL[state.gran] || '5',
                timezone: 'America/New_York', theme: 'dark', style: '1', locale: 'en', toolbar_bg: '#101115',
                enable_publishing: false, hide_side_toolbar: true, allow_symbol_change: false, save_image: false,
                withdateranges: true, studies: ['Volume@tv-basicstudies'], container_id: 'pcMarketTvChart'
            });
            marketChartReady = true;
        } catch (e) {}
    }
    function loadPriceStats() {
        getJSON(CB_STATS).then(function (j) {
            if (!j) return;
            var last = Number(j.last) || 0, open = Number(j.open) || 0;
            set('pcPsLast', last ? price2(last) : '\u2014');
            set('pcPsHigh', Number(j.high) ? price2(j.high) : '\u2014');
            set('pcPsLow', Number(j.low) ? price2(j.low) : '\u2014');
            if (open && last) {
                var chg = (last - open) / open * 100;
                var e = $('pcPsChg');
                if (e) { e.textContent = (chg >= 0 ? '+' : '') + chg.toFixed(2) + '%'; e.className = chg >= 0 ? 'pos' : 'neg'; }
            }
        });
        var b = $('pcPsBeat'); var bs = $('pcBeat'); if (b && bs) b.textContent = bs.textContent;
    }

    function renderCompare() {
        var el = $('pcCompareRows'); if (!el) return;
        var list = state.markets.filter(function (m) { return /bitcoin\s+up\s+or\s+down/i.test(m.question) && m.prices.length; });
        list.sort(function (a, b) {
            var ca = isClosed(a) ? 1 : 0, cb = isClosed(b) ? 1 : 0;
            if (ca !== cb) return ca - cb;
            var ea = Date.parse(a.end) || 0, eb = Date.parse(b.end) || 0;
            return ca ? eb - ea : ea - eb;
        });
        set('pcCompareSub', list.length + ' Bitcoin Up/Down markets · live & recent');
        if (!list.length) { el.innerHTML = '<div class="cmp-empty">No Bitcoin Up/Down markets</div>'; return; }
        var cur = state.featured ? state.featured.slug : '';
        el.innerHTML = list.slice(0, 40).map(function (m) {
            var up = Number(m.prices[0]) || 0, dn = Number(m.prices[1]) || 0, closed = isClosed(m);
            return '<div class="cmp-row' + (m.slug === cur ? ' active' : '') + '" data-slug="' + esc(m.slug) + '">' +
                '<span class="cmp-name">' + esc(m.question) + (closed ? ' <i class="cmp-closed">closed</i>' : '') + '</span>' +
                '<span class="cmp-up">' + (up ? Math.round(up * 100) + '%' : '\u2014') + '</span>' +
                '<span class="cmp-dn">' + (dn ? Math.round(dn * 100) + '%' : '\u2014') + '</span>' +
                '<span>' + usd(m.vol) + '</span><span>' + usd(m.liq) + '</span><span>' + fmtTime(m.end) + '</span>' +
                '<button class="cmp-open">Open</button></div>';
        }).join('');
        Array.prototype.forEach.call(el.querySelectorAll('.cmp-row'), function (row) {
            row.addEventListener('click', function () {
                var m = state.markets.filter(function (x) { return x.slug === row.getAttribute('data-slug'); })[0];
                if (m) { state.featured = m; renderFeatured(m); loadBeat(); loadBook(); }
                var ov = $('pcTabs').querySelector('button[data-tab="overview"]'); if (ov) ov.click();
            });
        });
    }

    function renderMarket() {
        var m = state.featured;
        var box = $('pcMarketBody'); if (!box) return;
        if (!m) { box.innerHTML = '<div class="cmp-empty">Loading\u2026</div>'; return; }
        set('pcMarketTitle', m.question);
        set('pcMarketSub', (isClosed(m) ? 'Closed' : 'Live') + ' \u00b7 ends ' + fmtTime(m.end));
        var up = Number(m.prices[0]) || 0, dn = Number(m.prices[1]) || 0;
        var rows = [
            ['Outcomes', esc((m.outcomes[0] || 'Up') + ' / ' + (m.outcomes[1] || 'Down'))],
            ['Up price', up ? cents(up) : '\u2014'],
            ['Down price', dn ? cents(dn) : '\u2014'],
            ['Volume', usd(m.vol)],
            ['Liquidity', usd(m.liq)],
            ['Ends', fmtTime(m.end)],
            ['Condition ID', esc(m.cond)]
        ];
        box.innerHTML = rows.map(function (r) { return '<div class="mk-cell"><span>' + r[0] + '</span><b>' + r[1] + '</b></div>'; }).join('') +
            (m.desc ? '<div class="mk-desc">' + esc(m.desc) + '</div>' : '');
        loadTrades(m);
    }
    function loadTrades(m) {
        var el = $('pcMarketTrades'); if (!el) return;
        if (!m.cond) { el.innerHTML = '<div class="cmp-empty">No activity</div>'; return; }
        el.innerHTML = '<div class="cmp-empty">Loading\u2026</div>';
        getJSON(DATA + '/trades?market=' + encodeURIComponent(m.cond) + '&limit=25').then(function (tr) {
            if (!Array.isArray(tr) || !tr.length) { el.innerHTML = '<div class="cmp-empty">No recent trades</div>'; return; }
            el.innerHTML = '<div class="tr-head"><span>Side</span><span>Outcome</span><span>Price</span><span>Size</span><span>Time</span></div>' + tr.map(function (t) {
                var side = String(t.side || '').toUpperCase();
                var px = Number(t.price) || 0, sz = Number(t.size) || 0;
                return '<div class="tr-row"><span class="tr-side ' + (side === 'BUY' ? 'buy' : 'sell') + '">' + esc(side || '\u2014') + '</span>' +
                    '<span>' + esc(t.outcome || '') + '</span><span>' + (px ? cents(px) : '\u2014') + '</span>' +
                    '<span>' + sz.toLocaleString('en-US', { maximumFractionDigits: 2 }) + '</span><span>' + fmtTime(t.timestamp) + '</span></div>';
            }).join('');
        });
    }

    function boot() {
        wire();
        syncLimitRow();
        updateAmount();
        updatePerf();
        createChart();
        loadMarkets();
        updatePills();
        setInterval(loadBook, 8000);
        setInterval(loadBeat, 60000);
        setInterval(loadMarkets, 45000);
        setInterval(updatePills, 60000);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
