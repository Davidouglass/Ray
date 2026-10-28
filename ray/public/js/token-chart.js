(function () {
    'use strict';

    var el = document.getElementById('tvchart');
    if (!el) return;

    var status = document.getElementById('tvchart-status');
    var tfBar = document.getElementById('tvTf');
    var mint = el.getAttribute('data-mint') || '';
    var pool = el.getAttribute('data-pool') || '';
    var symbol = el.getAttribute('data-symbol') || '';

    function showStatus(html) {
        if (!status) return;
        status.innerHTML = html;
        status.classList.remove('hidden');
    }
    function hideStatus() {
        if (status) status.classList.add('hidden');
    }

    /* ---------------- TradingView embed (preferred when available) ------------- */

    var tvState = 'idle'; // idle | loading | ready | failed

    function loadTv(cb) {
        if (window.TradingView && window.TradingView.widget) { cb(true); return; }
        if (tvState === 'loading') { cb(false); return; }
        tvState = 'loading';

        var s = document.createElement('script');
        s.src = 'https://s3.tradingview.com/tv.js';
        s.async = true;

        var settled = false;
        var timer = setTimeout(function () {
            if (settled) return;
            settled = true;
            tvState = 'failed';
            cb(false);
        }, 6000);

        s.onload = function () {
            if (settled) return;
            settled = true;
            clearTimeout(timer);
            var ok = !!(window.TradingView && window.TradingView.widget);
            tvState = ok ? 'ready' : 'failed';
            cb(ok);
        };
        s.onerror = function () {
            if (settled) return;
            settled = true;
            clearTimeout(timer);
            tvState = 'failed';
            cb(false);
        };
        document.head.appendChild(s);
    }

    function initTradingView(symbol) {
        showStatus('<i class="ri-loader-4-line"></i> Loading TradingView…');
        loadTv(function (ok) {
            if (!ok) { initLightweight(); return; }
            try {
                el.innerHTML = '';
                /* global TradingView */
                new window.TradingView.widget({
                    container_id: 'tvchart',
                    symbol: symbol,
                    interval: '60',
                    theme: 'dark',
                    style: '1',
                    locale: 'en',
                    autosize: true,
                    hide_side_toolbar: false,
                    allow_symbol_change: false,
                    save_image: true,
                    withdateranges: true,
                    details: false,
                    backgroundColor: 'rgba(0,0,0,0)',
                    gridColor: 'rgba(34,36,45,.55)',
                    support_host: 'https://www.tradingview.com'
                });
                if (tfBar) tfBar.style.display = 'none';
                hideStatus();
            } catch (e) {
                initLightweight();
            }
        });
    }

    /* ---------------- Built-in chart (Lightweight Charts + GeckoTerminal) ------- */

    var lwStarted = false;

    function initLightweight() {
        if (lwStarted) return;
        lwStarted = true;

        if (!window.LightweightCharts) {
            showStatus('Chart engine unavailable.');
            return;
        }

        var LWC = window.LightweightCharts;
        var UP = '#2FE3AC';
        var DOWN = '#EC397A';

        function fmtPrice(p) {
            p = Number(p);
            if (!p) return '0';
            if (p >= 1e9) return (p / 1e9).toFixed(2) + 'B';
            if (p >= 1e6) return (p / 1e6).toFixed(2) + 'M';
            if (p >= 1000) return p.toFixed(2);
            if (p >= 1) return p.toFixed(3);
            if (p >= 0.01) return p.toFixed(5);
            return p.toPrecision(4);
        }

        var chart = LWC.createChart(el, {
            autoSize: true,
            layout: {
                background: { type: 'solid', color: 'transparent' },
                textColor: '#777A8C',
                fontFamily: "'Geist Mono', ui-monospace, Menlo, monospace",
                fontSize: 11
            },
            grid: {
                vertLines: { color: 'rgba(34,36,45,.55)' },
                horzLines: { color: 'rgba(34,36,45,.55)' }
            },
            rightPriceScale: { borderColor: 'rgba(50,53,66,.6)' },
            timeScale: { borderColor: 'rgba(50,53,66,.6)', timeVisible: true, secondsVisible: false },
            crosshair: {
                mode: LWC.CrosshairMode ? LWC.CrosshairMode.Normal : 0,
                vertLine: { color: 'rgba(82,111,255,.55)', width: 1, style: 3, labelBackgroundColor: '#526FFF' },
                horzLine: { color: 'rgba(82,111,255,.55)', labelBackgroundColor: '#526FFF' }
            },
            localization: { priceFormatter: fmtPrice }
        });

        var candles = chart.addCandlestickSeries({
            upColor: UP, downColor: DOWN,
            borderUpColor: UP, borderDownColor: DOWN,
            wickUpColor: UP, wickDownColor: DOWN,
            priceLineVisible: false, lastValueVisible: true
        });

        var volume = chart.addHistogramSeries({
            priceFormat: { type: 'volume' },
            priceScaleId: '',
            lastValueVisible: false, priceLineVisible: false
        });
        volume.priceScale().applyOptions({ scaleMargins: { top: 0.82, bottom: 0 } });

        var seq = 0;

        function load(tf, agg, limit) {
            var mine = ++seq;

            if (!mint) {
                showStatus('No token address.');
                return;
            }
            showStatus('<i class="ri-loader-4-line"></i> Loading chart…');

            var url = '/api/token/' + encodeURIComponent(mint) + '/candles?tf=' + encodeURIComponent(tf) +
                '&agg=' + encodeURIComponent(agg) + '&limit=' + encodeURIComponent(limit) +
                (pool ? '&pool=' + encodeURIComponent(pool) : '');

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (mine !== seq) return;

                    var list = (d && d.candles) ? d.candles : [];
                    var cs = [], vs = [], last = 0;

                    list.forEach(function (c) {
                        var t = Number(c.time) || 0;
                        if (t <= last) return;
                        last = t;
                        cs.push({ time: t, open: c.open, high: c.high, low: c.low, close: c.close });
                        vs.push({
                            time: t,
                            value: c.volume,
                            color: c.close >= c.open ? 'rgba(47,227,172,.35)' : 'rgba(236,57,122,.35)'
                        });
                    });

                    if (!cs.length) {
                        candles.setData([]);
                        volume.setData([]);
                        showStatus('No chart data available for this pair.');
                        return;
                    }

                    candles.setData(cs);
                    volume.setData(vs);

                    var ts = chart.timeScale();
                    ts.fitContent();

                    // Fresh pools can have only a handful of candles; fitContent
                    // would stretch one bar across the whole chart, so instead show
                    // at least `slots` candle-widths to keep the sticks thin.
                    var width = el.clientWidth || 0;
                    var slots = Math.max(30, Math.floor(width / 7));
                    if (cs.length < slots) {
                        var to = cs.length + 1;
                        ts.setVisibleLogicalRange({ from: to - slots, to: to });
                    }

                    hideStatus();
                })
                .catch(function () {
                    if (mine !== seq) return;
                    showStatus('Chart failed to load.');
                });
        }

        if (tfBar) {
            tfBar.addEventListener('click', function (e) {
                var b = e.target.closest ? e.target.closest('button') : null;
                if (!b) return;
                var btns = tfBar.querySelectorAll('button');
                for (var i = 0; i < btns.length; i++) btns[i].classList.remove('active');
                b.classList.add('active');
                load(b.getAttribute('data-tf'), b.getAttribute('data-agg'), b.getAttribute('data-limit'));
            });
        }

        var active = tfBar ? tfBar.querySelector('button.active') : null;
        load(
            active ? active.getAttribute('data-tf') : 'hour',
            active ? active.getAttribute('data-agg') : 1,
            active ? active.getAttribute('data-limit') : 168
        );
    }

    /* ---------------- Boot: TradingView if the pair resolves, else built-in ----- */

    if (!mint) {
        initLightweight();
        return;
    }

    fetch('/api/token/' + encodeURIComponent(mint) + '/tradingview?symbol=' + encodeURIComponent(symbol), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (d) {
            if (d && d.symbol) {
                initTradingView(d.symbol);
            } else {
                hideStatus();
                initLightweight();
            }
        })
        .catch(function () {
            initLightweight();
        });
})();
