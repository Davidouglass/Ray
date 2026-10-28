/* Axiom in-app trading — connects the user's browser wallet (Phantom /
   Solflare / Backpack) and executes Jupiter swaps without leaving the site.
   No keys ever touch the server: swaps are signed in the wallet popup.

   Standard order panel: pay/receive boxes with a token selector, % presets,
   slippage settings, live rate / price-impact / route / fee readout, USD
   value, quote refresh and a stale-quote guard before executing.

   Also exposes window.AxiomTrade.open(mint, symbol[, price]) so listing pages
   (Discover / chain pages) can pop the same buy panel as a modal. Keep the
   panel markup in sync with resources/views/partials/trade-panel.blade.php. */
(function () {
    'use strict';

    var SOL = 'So11111111111111111111111111111111111111112';
    var USDC = 'EPjFWdd5AufqSSqeM2qN1xzybapC8G4wEGGkZwyTDt1v';
    var QUOTE = {
        SOL: { mint: SOL, sym: 'SOL', dec: 9 },
        USDC: { mint: USDC, sym: 'USDC', dec: 6 }
    };
    var QUOTE_ORDER = ['SOL', 'USDC'];
    var STALE_MS = 25000;
    var MAX_IMPACT = 15;
    var web3Promise = null;

    var PANEL_HTML =
        '<div class="at-head">' +
            '<div class="fx-tabs" style="flex:1">' +
                '<button type="button" class="fx-tab active" data-side="buy">Buy</button>' +
                '<button type="button" class="fx-tab" data-side="sell">Sell</button>' +
            '</div>' +
            '<button type="button" class="at-gear" data-at-gear title="Slippage settings" aria-label="Slippage settings"><i class="ri-equalizer-2-line"></i></button>' +
        '</div>' +
        '<div class="at-slip-panel" data-at-slip-panel hidden>' +
            '<div class="at-slip-head">Slippage tolerance</div>' +
            '<div class="at-slip-row">' +
                '<button type="button" class="at-slip-opt" data-bps="50">0.5%</button>' +
                '<button type="button" class="at-slip-opt is-active" data-bps="100">1%</button>' +
                '<button type="button" class="at-slip-opt" data-bps="300">3%</button>' +
                '<button type="button" class="at-slip-opt" data-bps="500">5%</button>' +
            '</div>' +
            '<label class="at-slip-custom"><span>Custom</span>' +
                '<input type="text" inputmode="decimal" data-at-slip-custom placeholder="0.5"><span>%</span></label>' +
        '</div>' +
        '<div class="at-box">' +
            '<div class="at-box-top"><span>You pay</span><span class="at-bal" data-at-paybal></span></div>' +
            '<div class="at-box-mid">' +
                '<input type="text" class="at-input" data-at-amount inputmode="decimal" placeholder="0.0" autocomplete="off" aria-label="Amount to pay">' +
                '<select class="at-select" data-at-paysel aria-label="Pay token"></select>' +
            '</div>' +
            '<div class="at-box-bot">' +
                '<span class="at-usd" data-at-payusd></span>' +
                '<div class="at-presets">' +
                    '<button type="button" data-pct="25">25%</button>' +
                    '<button type="button" data-pct="50">50%</button>' +
                    '<button type="button" data-pct="75">75%</button>' +
                    '<button type="button" data-pct="100">MAX</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
        '<div class="at-box">' +
            '<div class="at-box-top"><span>You receive</span><span class="at-bal" data-at-getbal></span></div>' +
            '<div class="at-box-mid">' +
                '<div class="at-out" data-at-out>0</div>' +
                '<select class="at-select" data-at-getsel aria-label="Receive token"></select>' +
            '</div>' +
            '<div class="at-box-bot"><span class="at-usd" data-at-getusd></span></div>' +
        '</div>' +
        '<div class="at-info" data-at-info></div>' +
        '<button type="button" class="fx-btn" data-at-btn>Connect wallet</button>' +
        '<div class="at-status" data-at-status></div>' +
        '<div class="at-note"><i class="ri-lock-line"></i> Trades are signed in your wallet — no redirects, keys never leave your device.</div>';

    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function provider() {
        if (window.phantom && window.phantom.solana && window.phantom.solana.isPhantom) return window.phantom.solana;
        if (window.solana && window.solana.isPhantom) return window.solana;
        if (window.solflare && window.solflare.isSolflare) return window.solflare;
        if (window.backpack && window.backpack.solana) return window.backpack.solana;
        if (window.solana && window.solana.connect) return window.solana;
        return null;
    }

    function loadWeb3() {
        if (window.solanaWeb3) return Promise.resolve(window.solanaWeb3);
        if (!web3Promise) {
            web3Promise = new Promise(function (resolve, reject) {
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/@solana/web3.js@1.95.8/lib/index.iife.min.js';
                s.onload = function () { resolve(window.solanaWeb3); };
                s.onerror = reject;
                document.head.appendChild(s);
            });
        }
        return web3Promise;
    }

    function api(path, opts) {
        opts = opts || {};
        opts.headers = Object.assign({ 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }, opts.headers || {});
        if (opts.body && typeof opts.body !== 'string') {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(opts.body);
        }
        return fetch(path, opts).then(function (r) {
            return r.json().then(function (d) {
                if (!r.ok) { throw new Error(d.error || 'Request failed'); }
                return d;
            });
        });
    }

    function fmt(x, dp) {
        if (x == null || isNaN(x)) return '0';
        return Number(x).toLocaleString(undefined, { maximumFractionDigits: dp == null ? 6 : dp });
    }

    function fmtUsd(x) {
        if (!x || isNaN(x)) return '';
        return x >= 1 ? fmt(x, 2) : Number(x).toPrecision(2);
    }

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
        });
    }

    function init(root) {
        var hostMint = root.getAttribute('data-mint') || '';
        var hostSym = root.getAttribute('data-symbol') || 'token';
        var price = parseFloat(root.getAttribute('data-price')) || 0;
        var hostDec = 6;
        var quoteKey = 'SOL';
        var side = 'buy';
        var wallet = null;
        var quote = null;
        var quoteAt = 0;
        var quoteSeq = 0;
        var slippageBps = 100;
        var balances = {};

        function q(sel) { return root.querySelector(sel); }
        var els = {
            tabs: root.querySelectorAll('[data-side]'),
            gear: q('[data-at-gear]'),
            slipPanel: q('[data-at-slip-panel]'),
            slipOpts: root.querySelectorAll('[data-bps]'),
            slipCustom: q('[data-at-slip-custom]'),
            amount: q('[data-at-amount]'),
            paySel: q('[data-at-paysel]'),
            getSel: q('[data-at-getsel]'),
            payBal: q('[data-at-paybal]'),
            getBal: q('[data-at-getbal]'),
            payUsd: q('[data-at-payusd]'),
            getUsd: q('[data-at-getusd]'),
            out: q('[data-at-out]'),
            info: q('[data-at-info]'),
            btn: q('[data-at-btn]'),
            status: q('[data-at-status]'),
            presets: root.querySelectorAll('[data-pct]')
        };

        /* ---- token model ---- */
        function hostToken() { return { mint: hostMint, sym: hostSym, dec: hostDec }; }
        function quoteToken() { return QUOTE[quoteKey] || QUOTE.SOL; }
        function pay() { return side === 'buy' ? quoteToken() : hostToken(); }
        function get() { return side === 'buy' ? hostToken() : quoteToken(); }
        function addr() { return wallet && wallet.publicKey ? wallet.publicKey.toString() : null; }
        function bal(m) { return balances.hasOwnProperty(m) ? balances[m] : 0; }

        function setStatus(msg, kind) {
            if (!els.status) return;
            els.status.className = 'at-status' + (kind ? ' ' + kind : '');
            els.status.textContent = msg || '';
        }
        function setInfo(html) { if (els.info) els.info.innerHTML = html || ''; }

        function fillSelect(sel, tokens, selected) {
            if (!sel) return;
            var html = '';
            tokens.forEach(function (t) {
                html += '<option value="' + esc(t.mint) + '"' + (t.mint === selected ? ' selected' : '') + '>' + esc(t.sym) + '</option>';
            });
            sel.innerHTML = html;
            sel.classList.toggle('is-static', tokens.length <= 1);
        }

        function renderSelects() {
            var payTokens = side === 'buy' ? QUOTE_ORDER.map(function (k) { return QUOTE[k]; }) : [hostToken()];
            var getTokens = side === 'buy' ? [hostToken()] : QUOTE_ORDER.map(function (k) { return QUOTE[k]; });
            fillSelect(els.paySel, payTokens, pay().mint);
            fillSelect(els.getSel, getTokens, get().mint);
        }

        function setQuoteFromMint(m) {
            var k = QUOTE_ORDER.filter(function (x) { return QUOTE[x].mint === m; })[0];
            if (!k) { renderSelects(); return; }
            quoteKey = k;
            if (els.amount) els.amount.value = '';
            quote = null;
            renderSelects();
            renderQuoteEmpty();
            setInfo('');
            refreshBalances();
        }

        function loadHostDec() {
            if (!hostMint) return;
            fetchMeta(hostMint).then(function (d) { hostDec = d.decimals || 6; renderSelects(); }).catch(function () {});
        }

        function fetchMeta(m, owner) {
            var url = '/api/swap/meta?mint=' + encodeURIComponent(m) + (owner ? '&owner=' + encodeURIComponent(owner) : '');
            return api(url);
        }

        function renderBalances() {
            var a = addr();
            if (els.payBal) els.payBal.textContent = a ? ('Balance: ' + fmt(bal(pay().mint), 6) + ' ' + pay().sym) : '';
            if (els.getBal) els.getBal.textContent = a ? ('Balance: ' + fmt(bal(get().mint), 6) + ' ' + get().sym) : '';
        }

        function refreshBalances() {
            renderBalances();
            var a = addr();
            if (!a) return;
            var mints = {};
            mints[pay().mint] = 1;
            mints[get().mint] = 1;
            Object.keys(mints).forEach(function (m) {
                if (!m) return;
                fetchMeta(m, a).then(function (d) { balances[m] = Number(d.balance) || 0; renderBalances(); }).catch(function () {});
            });
        }

        function updateButton() {
            if (!els.btn) return;
            if (!provider() && !wallet) {
                els.btn.textContent = 'Install a wallet';
                els.btn.classList.add('is-ghost');
                return;
            }
            if (!addr()) {
                els.btn.textContent = 'Connect wallet';
                els.btn.classList.add('is-ghost');
                return;
            }
            els.btn.classList.remove('is-ghost');
            var val = parseFloat((els.amount && els.amount.value || '').replace(/,/g, ''));
            if (!val || val <= 0) { els.btn.textContent = 'Enter an amount'; return; }
            var impact = quote && quote.priceImpactPct ? Number(quote.priceImpactPct) * 100 : 0;
            if (quote && impact >= MAX_IMPACT) { els.btn.textContent = 'Price impact too high'; return; }
            els.btn.textContent = side === 'buy' ? ('Buy ' + hostSym) : ('Sell ' + hostSym);
        }

        function renderQuoteEmpty() {
            if (els.out) els.out.textContent = '0';
            if (els.payUsd) els.payUsd.textContent = '';
            if (els.getUsd) els.getUsd.textContent = '';
            setInfo('');
        }

        function routeLabels(res) {
            var seen = {}, out = [];
            (res.routePlan || []).forEach(function (r) {
                var l = r && r.swapInfo && r.swapInfo.label;
                if (l && !seen[l]) { seen[l] = 1; out.push(l); }
            });
            return out.join(' → ');
        }

        function row(label, val, cls) {
            return '<div class="at-info-row"><span>' + label + '</span><b' + (cls ? ' class="' + cls + '"' : '') + '>' + val + '</b></div>';
        }

        function fmtRate(r) {
            if (!r || isNaN(r)) return '0';
            if (r >= 1) return fmt(r, 4);
            if (r >= 0.001) return fmt(r, 6);
            return Number(r.toPrecision(4)).toString();
        }

        function renderQuote(res) {
            var p = pay(), g = get();
            var out = Number(res.outAmount) / Math.pow(10, g.dec);
            var min = Number(res.otherAmountThreshold) / Math.pow(10, g.dec);
            var inAmt = Number(res.inAmount) / Math.pow(10, p.dec);
            var impact = res.priceImpactPct ? Number(res.priceImpactPct) * 100 : 0;
            var impactCls = impact < 1 ? 'imp-ok' : (impact < 3 ? 'imp-mid' : 'imp-bad');

            if (els.out) els.out.textContent = fmt(out, Math.min(g.dec, 9));

            var usd = side === 'buy' ? out * price : inAmt * price;
            if (els.payUsd) els.payUsd.textContent = side === 'sell' && usd > 0 ? ('$' + fmtUsd(usd)) : '';
            if (els.getUsd) els.getUsd.textContent = side === 'buy' && usd > 0 ? ('$' + fmtUsd(usd)) : '';

            var rate = inAmt > 0 ? out / inAmt : 0;
            var html =
                row('Rate', '1 ' + esc(p.sym) + ' ≈ ' + fmtRate(rate) + ' ' + esc(g.sym)) +
                row('Min received', fmt(min, Math.min(g.dec, 9)) + ' ' + esc(g.sym)) +
                row('Price impact', impact.toFixed(2) + '%', impactCls) +
                row('Slippage', (slippageBps / 100) + '%') +
                (routeLabels(res) ? row('Route', esc(routeLabels(res))) : '') +
                row('Network fee', '~0.000005 SOL') +
                row('Priority fee', '≤0.001 SOL') +
                '<button type="button" class="at-refresh" data-at-refresh><i class="ri-refresh-line"></i> Refresh quote</button>';
            if (impact >= MAX_IMPACT) {
                html += '<div class="at-warn imp-bad"><i class="ri-alert-line"></i> Very high price impact — you may lose a large share of your funds.</div>';
            } else if (impact >= 3) {
                html += '<div class="at-warn imp-mid"><i class="ri-alert-line"></i> High price impact.</div>';
            }
            setInfo(html);
        }

        function requestQuote() {
            if (!hostMint) return;
            var val = parseFloat((els.amount && els.amount.value || '').replace(/,/g, ''));
            if (!val || val <= 0) { quote = null; renderQuoteEmpty(); updateButton(); return; }
            var p = pay();
            var base = Math.floor(val * Math.pow(10, p.dec));
            if (base <= 0) { quote = null; renderQuoteEmpty(); updateButton(); return; }
            var seq = ++quoteSeq;
            setInfo('<div class="at-loading"><i class="ri-loader-4-line"></i> Fetching best route…</div>');
            api('/api/swap/quote?inputMint=' + p.mint + '&outputMint=' + get().mint + '&amount=' + base + '&slippageBps=' + slippageBps)
                .then(function (res) {
                    if (seq !== quoteSeq) return;
                    quote = res; quoteAt = Date.now();
                    renderQuote(res);
                    updateButton();
                })
                .catch(function (e) {
                    if (seq !== quoteSeq) return;
                    quote = null;
                    renderQuoteEmpty();
                    setInfo('<div class="at-down">' + esc(e.message) + '</div>');
                    updateButton();
                });
        }

        function freshQuote() {
            var p = pay();
            var val = parseFloat((els.amount && els.amount.value || '').replace(/,/g, ''));
            if (!val || val <= 0) return Promise.resolve(null);
            var base = Math.floor(val * Math.pow(10, p.dec));
            return api('/api/swap/quote?inputMint=' + p.mint + '&outputMint=' + get().mint + '&amount=' + base + '&slippageBps=' + slippageBps)
                .then(function (res) { quote = res; quoteAt = Date.now(); renderQuote(res); return res; })
                .catch(function () { return null; });
        }

        function applyPct(pct) {
            var a = addr();
            if (!a) { if (els.amount) els.amount.value = ''; return; }
            var m = pay().mint;
            var ready = balances.hasOwnProperty(m)
                ? Promise.resolve(bal(m))
                : fetchMeta(m, a).then(function (d) { balances[m] = Number(d.balance) || 0; return balances[m]; });
            ready.then(function (b) {
                var raw = Number(b) || 0;
                var amt = raw * pct / 100;
                if (pct === 100 && pay().mint === SOL) amt = Math.max(0, raw - 0.01);
                if (els.amount) els.amount.value = amt > 0 ? amountStr(amt) : '';
                renderBalances();
                requestQuote();
            }).catch(function () {});
        }

        function amountStr(x) {
            var d = Math.min(pay().dec, 9);
            var s = Number(x).toFixed(d);
            if (s.indexOf('.') >= 0) s = s.replace(/0+$/, '').replace(/\.$/, '');
            return s;
        }

        function execute() {
            var a = addr();
            if (!a) return;
            if (!quote) { setStatus('Enter an amount to quote first.', 'err'); return; }
            var impact = quote.priceImpactPct ? Number(quote.priceImpactPct) * 100 : 0;
            if (impact >= MAX_IMPACT) { setStatus('Price impact too high to execute.', 'err'); return; }
            var run = function (res) {
                els.btn.disabled = true;
                setStatus('Preparing transaction…');
                api('/api/swap/build', { method: 'POST', body: { quoteResponse: res, userPublicKey: a } })
                    .then(function (built) {
                        return loadWeb3().then(function (web3) {
                            var bytes = Uint8Array.from(atob(built.swapTransaction), function (c) { return c.charCodeAt(0); });
                            var tx = web3.VersionedTransaction.deserialize(bytes);
                            setStatus('Confirm in your wallet…');
                            return wallet.signAndSendTransaction(tx).then(function (res2) {
                                var sig = (res2 && (res2.signature || res2)) || res;
                                setStatus('');
                                els.status.className = 'at-status ok';
                                els.status.innerHTML = 'Trade sent! <a href="https://solscan.io/tx/' + sig + '" target="_blank" rel="noopener">View on Solscan ↗</a>';
                                if (els.amount) els.amount.value = '';
                                quote = null;
                                renderQuoteEmpty();
                                setTimeout(refreshBalances, 3000);
                            });
                        });
                    })
                    .catch(function (e) { setStatus('Trade failed: ' + (e && e.message ? e.message : e), 'err'); })
                    .finally(function () { els.btn.disabled = false; updateButton(); });
            };
            if (quoteAt && Date.now() - quoteAt > STALE_MS) {
                setStatus('Refreshing quote…');
                freshQuote().then(function (res) {
                    if (!res) { setStatus('Could not refresh the quote — try again.', 'err'); return; }
                    run(res);
                });
            } else {
                run(quote);
            }
        }

        function connect() {
            var p = provider();
            if (!p) { setStatus('No Solana wallet detected. Install Phantom or Solflare.', 'err'); return; }
            els.btn.disabled = true;
            setStatus('Connecting…');
            wallet = provider();
            wallet.connect().then(function (res) {
                var a = (res && res.publicKey ? res.publicKey : wallet.publicKey).toString();
                wallet.publicKey = wallet.publicKey || { toString: function () { return addr; } };
                try { localStorage.setItem('axiom_wallet', a); } catch (e) {}
                setStatus('');
                updateButton();
                refreshBalances();
                if (els.amount && els.amount.value) requestQuote();
            }).catch(function () {
                setStatus('Wallet connection rejected.', 'err');
            }).finally(function () { els.btn.disabled = false; });
        }

        function onButton() {
            if (!provider() && !wallet) { window.open('https://phantom.app', '_blank', 'noopener'); return; }
            if (!addr()) return connect();
            execute();
        }

        function setSide(next) {
            side = next;
            Array.prototype.forEach.call(els.tabs, function (t) {
                t.classList.toggle('active', t.getAttribute('data-side') === next);
            });
            quote = null;
            if (els.amount) els.amount.value = '';
            renderSelects();
            renderQuoteEmpty();
            setStatus('');
            updateButton();
            refreshBalances();
        }

        function setToken(nextMint, nextSymbol, nextPrice) {
            hostMint = nextMint || '';
            hostSym = nextSymbol || 'token';
            price = Number(nextPrice) || 0;
            hostDec = 6;
            quoteKey = 'SOL';
            side = 'buy';
            quote = null;
            quoteAt = 0;
            balances = {};
            root.setAttribute('data-mint', hostMint);
            root.setAttribute('data-symbol', hostSym);
            root.setAttribute('data-price', String(price));
            Array.prototype.forEach.call(els.tabs, function (t) {
                t.classList.toggle('active', t.getAttribute('data-side') === 'buy');
            });
            if (els.amount) els.amount.value = '';
            renderSelects();
            renderQuoteEmpty();
            setStatus('');
            updateButton();
            loadHostDec();
            refreshBalances();
        }

        function setAmount(v) {
            if (els.amount) els.amount.value = v == null ? '' : String(v);
            updateButton();
            requestQuote();
        }

        /* ---- events ---- */
        Array.prototype.forEach.call(els.tabs, function (t) {
            t.addEventListener('click', function () { setSide(t.getAttribute('data-side')); });
        });
        if (els.gear && els.slipPanel) {
            els.gear.addEventListener('click', function () { els.slipPanel.hidden = !els.slipPanel.hidden; });
        }
        Array.prototype.forEach.call(els.slipOpts, function (o) {
            o.addEventListener('click', function () {
                if (els.slipCustom) els.slipCustom.value = '';
                applySlippage(Number(o.getAttribute('data-bps')));
            });
        });
        if (els.slipCustom) {
            els.slipCustom.addEventListener('input', function () {
                var v = parseFloat(els.slipCustom.value);
                if (!isNaN(v) && v > 0) applySlippage(Math.round(v * 100));
            });
        }
        if (els.amount) {
            els.amount.addEventListener('input', function () {
                updateButton();
                clearTimeout(els.amount._t);
                els.amount._t = setTimeout(requestQuote, 350);
            });
        }
        if (els.paySel) {
            els.paySel.addEventListener('change', function () {
                if (side === 'buy') setQuoteFromMint(els.paySel.value); else renderSelects();
            });
        }
        if (els.getSel) {
            els.getSel.addEventListener('change', function () {
                if (side === 'sell') setQuoteFromMint(els.getSel.value); else renderSelects();
            });
        }
        Array.prototype.forEach.call(els.presets, function (b) {
            b.addEventListener('click', function () { applyPct(Number(b.getAttribute('data-pct'))); });
        });
        if (els.info) {
            els.info.addEventListener('click', function (e) {
                if (e.target.closest('[data-at-refresh]')) requestQuote();
            });
        }
        if (els.btn) els.btn.addEventListener('click', onButton);

        function applySlippage(bps) {
            slippageBps = Math.max(1, Math.min(5000, Math.round(bps)));
            Array.prototype.forEach.call(els.slipOpts, function (o) {
                o.classList.toggle('is-active', Number(o.getAttribute('data-bps')) === slippageBps);
            });
            if (quote) requestQuote();
        }

        /* ---- restore an eagerly-connected wallet ---- */
        var p0 = provider();
        if (p0) {
            wallet = p0;
            if (p0.publicKey) { refreshBalances(); }
            else if (p0.isConnected && p0.isConnected()) {
                p0.connect({ onlyIfTrusted: true }).then(function () { updateButton(); refreshBalances(); }).catch(function () {});
            }
        }

        loadHostDec();
        renderSelects();
        updateButton();
        refreshBalances();

        return { setToken: setToken, refresh: refreshBalances, setAmount: setAmount };
    }

    /* ---------- modal built for listing pages ---------- */
    var modal = null, modalApi = null;

    function buildModal() {
        var el = document.createElement('div');
        el.className = 'at-modal';
        el.innerHTML =
            '<div class="at-modal-backdrop" data-at-close></div>' +
            '<div class="at-modal-card" role="dialog" aria-modal="true" aria-label="Trade token">' +
                '<div class="at-modal-head">' +
                    '<span class="at-modal-title">Trade <b class="at-modal-sym">token</b></span>' +
                    '<button type="button" class="at-modal-x" data-at-close aria-label="Close"><i class="ri-close-line"></i></button>' +
                '</div>' +
                '<div class="fx-panel axiom-trade" data-mint="" data-symbol="token" data-price="0">' + PANEL_HTML + '</div>' +
            '</div>';
        document.body.appendChild(el);

        el.addEventListener('click', function (e) {
            if (e.target.closest('[data-at-close]')) { closeModal(); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && el.classList.contains('is-open')) { closeModal(); }
        });

        var panel = el.querySelector('.axiom-trade');
        modalApi = init(panel);
        modal = el;
        return el;
    }

    function openModal(mint, symbol, price, amount) {
        if (!modal) buildModal();
        var sym = symbol || 'token';
        var title = modal.querySelector('.at-modal-sym');
        if (title) title.textContent = symbol || 'token';
        modalApi.setToken(mint, symbol || 'token', price || 0);
        if (amount != null && amount !== '' && modalApi.setAmount) modalApi.setAmount(amount);
        modal.classList.add('is-open');
        document.body.classList.add('at-modal-open');
        var input = modal.querySelector('[data-at-amount]');
        if (input) setTimeout(function () { try { input.focus(); } catch (e) {} }, 60);
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        document.body.classList.remove('at-modal-open');
    }

    function initBuyButtons() {
        document.querySelectorAll('.buy-btn[data-mint]').forEach(function (b) {
            if (b.dataset.atBound) return;
            b.dataset.atBound = '1';
            b.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var mint = b.getAttribute('data-mint');
                var chain = (b.getAttribute('data-chain') || 'sol').toLowerCase();
                if (!mint) {
                    if (window.Axiom && window.Axiom.toast) window.Axiom.toast('Trading is not available for this token yet.');
                    return;
                }
                if (chain !== 'sol') {
                    if (window.Axiom && window.Axiom.toast) window.Axiom.toast('Trading for ' + chain.toUpperCase() + ' is coming soon.');
                    return;
                }
                var field = document.querySelector('.qb-field input');
                var amount = field ? field.value : null;
                openModal(mint, b.getAttribute('data-symbol'), b.getAttribute('data-price'), amount);
            });
        });
    }

    function boot() {
        document.querySelectorAll('.axiom-trade').forEach(function (root) {
            if (!root.closest('.at-modal') && !root.dataset.atInit) {
                root.dataset.atInit = '1';
                init(root);
            }
        });
        initBuyButtons();
    }

    window.AxiomTrade = { open: openModal, close: closeModal };
    window.AxiomTradeBoot = boot;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
