/* In-site Hyperliquid perpetuals order ticket.
   Signs EIP-712 orders in the user's EVM wallet (MetaMask / Rabby / etc.)
   via the official community SDK — no redirects, no server-held keys. */
(function () {
    'use strict';

    var HL = null, viem = null;
    var state = { index: 0, name: '', mark: 0, szDec: 0, maxLev: 20, side: 'long', otype: 'market', wallet: null, account: null };

    function $(s, r) { return (r || document).querySelector(s); }
    function setStatus(msg, kind) {
        var el = $('#perpStatus'); if (!el) return;
        el.className = 'at-status' + (kind ? ' ' + kind : '');
        el.innerHTML = msg || '';
    }
    function fmt(n, d) { return Number(n || 0).toLocaleString(undefined, { maximumFractionDigits: d == null ? 4 : d }); }

    /* ---- load SDKs on demand (ESM from CDN) ---- */
    function loadSdk() {
        if (!HL) HL = import('https://esm.sh/@nktkas/hyperliquid@0.20.6');
        if (!viem) viem = import('https://esm.sh/viem@2.21.45');
        return Promise.all([HL, viem]).then(function (r) { HL = r[0]; viem = r[1]; });
    }

    /* ---- rounding per Hyperliquid rules ---- */
    function roundPx(p, szDec) {
        var s = Number(p);
        if (!s || !isFinite(s)) return '0';
        var sig = 5 - Math.max(0, Math.floor(Math.log10(Math.abs(s))));
        var dec = Math.min(Math.max(sig, 0), Math.max(0, 6 - szDec));
        return s.toFixed(dec);
    }
    function roundSz(s, szDec) {
        var p = Math.pow(10, szDec);
        return (Math.floor(Number(s) * p) / p).toFixed(szDec);
    }

    /* ---- recompute estimates ---- */
    function recompute() {
        var lev = parseInt($('#perpLev').value, 10) || 1;
        var margin = parseFloat($('#perpMargin').value) || 0;
        $('#perpLev').setAttribute('max', state.maxLev);
        $('[data-perp-size]').textContent = fmt(margin * lev / (state.mark || 1), 6) + ' ' + state.name;
        $('[data-perp-notional]').textContent = '$' + fmt(margin * lev, 2);
        $('[data-perp-liq]').textContent = (margin > 0 && state.mark > 0)
            ? '$' + fmt(state.mark * (1 + (state.side === 'long' ? -1 : 1) / lev), 4) : '—';
        $('[data-perp-fee]').textContent = margin > 0 ? '$' + fmt(margin * lev * 0.00035, 4) + ' (taker)' : '—';
        var btn = $('#perpPlace');
        if (btn) btn.textContent = state.wallet
            ? (state.side === 'long' ? 'Long ' : 'Short ') + state.name + ' ' + lev + 'x'
            : 'Connect wallet';
    }

    /* ---- wallet ---- */
    function connect() {
        var eth = window.ethereum;
        if (!eth) { setStatus('No EVM wallet found. Install MetaMask or Rabby.', 'err'); return Promise.reject('no wallet'); }
        return eth.request({ method: 'eth_requestAccounts' }).then(function (accts) {
            state.account = accts[0];
            var label = state.account.slice(0, 6) + '…' + state.account.slice(-4);
            document.querySelectorAll('[data-perp-wallet]').forEach(function (e) { e.textContent = label; });
            var avail = document.querySelector('[data-perp-avail]'); if (avail) avail.textContent = label;
            setStatus('', '');
            recompute();
            return loadSdk().then(function () {
                state.wallet = viem.createWalletClient({ account: state.account, transport: viem.custom(eth) });
            });
        }).catch(function (e) {
            if (e !== 'no wallet') setStatus('Wallet connection rejected.', 'err');
            throw e;
        });
    }

    /* ---- place order ---- */
    function place() {
        if (!state.wallet) { connect().then(place).catch(function () {}); return; }
        var margin = parseFloat($('#perpMargin').value) || 0;
        if (margin <= 0) { setStatus('Enter a margin amount.', 'err'); return; }
        var lev = parseInt($('#perpLev').value, 10) || 1;
        var isBuy = state.side === 'long';
        var px = state.otype === 'limit' ? parseFloat($('#perpPrice').value) : state.mark * (isBuy ? 1.01 : 0.99);
        if (!px || !isFinite(px)) { setStatus('Enter a valid limit price.', 'err'); return; }
        var size = (margin * lev) / (state.otype === 'limit' ? px : state.mark);
        var reduce = $('#perpReduce').checked;

        $('#perpPlace').disabled = true;
        setStatus('Setting leverage…');
        loadSdk().then(function () {
            var transport = new HL.HttpTransport({ isTestnet: false });
            var client = new HL.ExchangeClient({ wallet: state.wallet, transport: transport });
            return client.updateLeverage({ asset: state.index, isCross: true, leverage: lev })
                .catch(function () { return null; })
                .then(function () {
                    setStatus('Confirm the order in your wallet…');
                    return client.order({
                        orders: [{
                            a: state.index,
                            b: isBuy,
                            p: roundPx(px, state.szDec),
                            s: roundSz(size, state.szDec),
                            r: reduce,
                            t: { limit: { tif: state.otype === 'limit' ? 'Gtc' : 'Ioc' } }
                        }],
                        grouping: 'na'
                    });
                });
        }).then(function (res) {
            var st = res && res.response && res.response.data && res.response.data.statuses && res.response.data.statuses[0];
            var filled = st && (st.filled || st.resting);
            if (filled) setStatus('Order placed · ' + (filled.oid ? ('#' + filled.oid) : 'accepted'), 'ok');
            else setStatus('Order submitted.', 'ok');
            window.Axiom && window.Axiom.toast((state.side === 'long' ? 'Long' : 'Short') + ' ' + state.name + ' submitted');
        }).catch(function (e) {
            setStatus('Order failed: ' + ((e && e.message) ? e.message : String(e)), 'err');
        }).finally(function () { $('#perpPlace').disabled = false; });
    }

    /* ---- modal wiring ---- */
    function openFromButton(btn) {
        state.index = +btn.dataset.index;
        state.name = btn.dataset.name;
        state.mark = +btn.dataset.mark;
        state.szDec = +btn.dataset.szdec;
        state.maxLev = +btn.dataset.lev || 20;
        state.side = 'long'; state.otype = 'market';

        var lev = Math.min(10, state.maxLev);
        $('#perpLev').max = state.maxLev;
        $('#perpLev').value = lev;
        $('#perpLevVal').textContent = lev + 'x';
        $('#perpMargin').value = '';
        $('#perpPrice').value = '';
        $('#perpPrice').style.display = 'none';
        $('#perpReduce').checked = false;
        document.querySelectorAll('#perpModal [data-side]').forEach(function (b) { b.classList.toggle('active', b.dataset.side === 'long'); });
        document.querySelectorAll('#perpModal [data-otype]').forEach(function (b) { b.classList.toggle('active', b.dataset.otype === 'market'); });
        document.querySelectorAll('#perpModal [data-lev]').forEach(function (b) { b.classList.toggle('active', +b.dataset.lev === Math.min(10, state.maxLev)); });
        var bigs = document.querySelectorAll('#perpModal .mg-big');
        if (bigs[0]) bigs[0].textContent = state.name;
        if (bigs[1]) bigs[1].textContent = '$' + fmt(state.mark, 6);
        recompute();
        $('#perpModal').classList.add('open');
    }

    function boot() {
        document.querySelectorAll('[data-perp-trade]').forEach(function (b) {
            b.addEventListener('click', function () { openFromButton(b); });
        });
        document.querySelectorAll('[data-perp-connect]').forEach(function (b) {
            b.addEventListener('click', function () { connect().catch(function () {}); });
        });
        var modal = $('#perpModal');
        if (!modal) return;
        modal.querySelectorAll('[data-close]').forEach(function (c) { c.addEventListener('click', function () { modal.classList.remove('open'); }); });
        modal.querySelectorAll('[data-side]').forEach(function (b) {
            b.addEventListener('click', function () {
                state.side = b.dataset.side;
                modal.querySelectorAll('[data-side]').forEach(function (x) { x.classList.toggle('active', x === b); });
                recompute();
            });
        });
        modal.querySelectorAll('[data-otype]').forEach(function (b) {
            b.addEventListener('click', function () {
                state.otype = b.dataset.otype;
                modal.querySelectorAll('[data-otype]').forEach(function (x) { x.classList.toggle('active', x === b); });
                $('#perpPrice').style.display = state.otype === 'limit' ? '' : 'none';
                recompute();
            });
        });
        modal.querySelectorAll('[data-lev]').forEach(function (b) {
            b.addEventListener('click', function () {
                var v = Math.min(parseInt(b.dataset.lev, 10), state.maxLev);
                $('#perpLev').value = v; $('#perpLevVal').textContent = v + 'x';
                modal.querySelectorAll('[data-lev]').forEach(function (x) { x.classList.toggle('active', x === b); });
                recompute();
            });
        });
        $('#perpLev').addEventListener('input', function () { $('#perpLevVal').textContent = this.value + 'x'; recompute(); });
        $('#perpMargin').addEventListener('input', recompute);
        $('#perpPrice').addEventListener('input', recompute);
        $('#perpReduce').addEventListener('change', recompute);
        $('#perpPlace').addEventListener('click', place);

        if (window.ethereum) {
            window.ethereum.request({ method: 'eth_accounts' }).then(function (a) {
                if (a && a[0]) { state.account = a[0]; connect().catch(function () {}); }
            }).catch(function () {});
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
