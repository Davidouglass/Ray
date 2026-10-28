/* Axiom global wallet layer: connect-wallet modal + deposit modal + copy.
   Loaded on every page via partials/top-nav. No keys touch the server. */
(function () {
    'use strict';

    function toast(msg) {
        if (window.Axiom && window.Axiom.toast) window.Axiom.toast(msg);
    }
    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
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
                if (!r.ok) throw new Error(d.error || 'Request failed');
                return d;
            });
        });
    }

    /* ---------------- copy ---------------- */
    function setupCopy() {
        document.addEventListener('click', function (e) {
            var el = e.target.closest('[data-copy]');
            if (!el) return;
            var text = el.getAttribute('data-copy');
            if (!text) return;
            var done = function () { toast('Address copied'); };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(done).catch(done);
            } else {
                var t = document.createElement('textarea');
                t.value = text; document.body.appendChild(t); t.select();
                try { document.execCommand('copy'); } catch (err) {}
                document.body.removeChild(t); done();
            }
        });
    }

    /* ---------------- connect wallet ---------------- */
    var PROVIDERS = {
        phantom: function () {
            if (window.phantom && window.phantom.solana && window.phantom.solana.isPhantom) return window.phantom.solana;
            if (window.solana && window.solana.isPhantom) return window.solana;
            return null;
        },
        solflare: function () { return window.solflare || null; },
        backpack: function () { return window.backpack || (window.xnft && window.xnft.solana) || null; },
        glow: function () { return window.glow || null; },
        coinbase: function () { return window.coinbaseSolana || (window.coinbaseWalletExtension && window.coinbaseWalletExtension.solana) || null; },
        okx: function () { return (window.okxwallet && window.okxwallet.solana) || null; },
        trust: function () { return (window.trustwallet && window.trustwallet.solana) || null; },
        generic: function () {
            var p = window.solana;
            if (!p || typeof p.connect !== 'function' || p.isPhantom) return null;
            return p;
        }
    };

    function publicKey(provider, result) {
        if (result && result.publicKey) result = result.publicKey;
        if (!result && provider && provider.publicKey) result = provider.publicKey;
        if (!result) return '';
        return typeof result === 'string' ? result : (result.toString ? result.toString() : String(result));
    }

    function submitAddress(address, label) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/portfolio/connect';
        form.style.display = 'none';
        [['_token', csrf()], ['address', address], ['label', label || '']].forEach(function (pair) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = pair[0];
            input.value = pair[1];
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    }

    function detect(modal) {
        modal.querySelectorAll('.pf-wallet-opt').forEach(function (opt) {
            var get = PROVIDERS[opt.dataset.wallet];
            var provider = get ? get() : null;
            var tag = opt.querySelector('.pf-wallet-tag');
            if (provider && typeof provider.connect === 'function') {
                opt.dataset.detected = '1';
                if (tag) { tag.textContent = 'Detected'; tag.className = 'pf-wallet-tag on'; }
            } else {
                opt.dataset.detected = '0';
                if (tag) { tag.textContent = opt.dataset.wallet === 'generic' ? 'Detect' : 'Install'; tag.className = 'pf-wallet-tag off'; }
            }
        });
    }

    function setupConnect() {
        var modal = document.getElementById('pfWallet');
        if (!modal) return;
        var open = function () {
            detect(modal);
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            var first = modal.querySelector('.pf-wallet-opt');
            if (first) first.focus({ preventScroll: true });
        };
        var close = function () {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
        };
        document.querySelectorAll('[data-wallet-connect]').forEach(function (b) {
            b.addEventListener('click', open);
        });
        modal.querySelectorAll('[data-pf-close]').forEach(function (b) {
            b.addEventListener('click', close);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('open')) close();
        });

        modal.querySelectorAll('.pf-wallet-opt').forEach(function (opt) {
            opt.addEventListener('click', function () {
                var key = opt.dataset.wallet;
                var provider = opt.dataset.detected === '1' && PROVIDERS[key] ? PROVIDERS[key]() : null;
                if (!provider) {
                    if (opt.dataset.install) window.open(opt.dataset.install, '_blank', 'noopener');
                    return;
                }
                opt.classList.add('busy');
                Promise.resolve(provider.connect()).then(function (result) {
                    var address = publicKey(provider, result);
                    if (!address) throw new Error('No public key returned');
                    toast('Wallet connected');
                    submitAddress(address, opt.querySelector('.pf-wallet-name').textContent);
                }).catch(function () {
                    opt.classList.remove('busy');
                    toast('Connection cancelled');
                });
            });
        });
    }

    /* ---------------- deposit ---------------- */
    var dp = { list: [], chain: 'sol', seq: 0 };

    function dpChainMap() {
        var map = {};
        document.querySelectorAll('#dpChains [data-dp-chain]').forEach(function (b) {
            var img = b.querySelector('img');
            map[b.dataset.dpChain] = {
                name: (b.querySelector('span') || {}).textContent || b.dataset.dpChain,
                icon: img ? img.getAttribute('src') : ''
            };
        });
        return map;
    }

    function dpRender() {
        var list = document.getElementById('dpList');
        if (!list) return;
        var empty = document.getElementById('dpEmpty');
        var emptyChain = document.getElementById('dpEmptyChain');
        var cmap = dpChainMap();
        var name = (cmap[dp.chain] || {}).name || dp.chain;
        if (emptyChain) emptyChain.textContent = name;

        var items = dp.list.filter(function (a) { return a.chain === dp.chain; });
        if (!items.length) {
            list.innerHTML = '';
            if (empty) empty.hidden = false;
            return;
        }
        if (empty) empty.hidden = true;

        list.innerHTML = items.map(function (a) {
            var icon = (cmap[a.chain] || {}).icon || '';
            var qr = 'https://api.qrserver.com/v1/create-qr-code/?size=132x132&margin=0&data=' + encodeURIComponent(a.address);
            return '<div class="dp-card">' +
                '<div class="dp-qr"><img src="' + qr + '" alt="Deposit address QR code" loading="lazy" onerror="this.closest(\'.dp-qr\').style.display=\'none\'"></div>' +
                '<div class="dp-info">' +
                    '<span class="dp-label">' + (icon ? '<img src="' + icon + '" alt="">' : '') + '<b>' + esc(a.label || name) + '</b></span>' +
                    '<code class="dp-addr">' + esc(a.address) + '</code>' +
                '</div>' +
                '<div class="dp-actions">' +
                    '<button type="button" class="dp-icon" data-copy="' + esc(a.address) + '" title="Copy address"><i class="ri-file-copy-line"></i></button>' +
                    '<button type="button" class="dp-icon danger" data-dp-del="' + a.id + '" title="Remove address"><i class="ri-delete-bin-line"></i></button>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function dpRefresh() {
        var seq = ++dp.seq;
        return api('/api/deposit/addresses').then(function (d) {
            if (seq !== dp.seq) return;
            dp.list = (d && d.addresses) || [];
            dpRender();
        }).catch(function () {});
    }

    function dpMsg(text, kind) {
        var el = document.getElementById('dpMsg');
        if (!el) return;
        el.className = 'dp-msg' + (kind ? ' ' + kind : '');
        el.textContent = text || '';
    }

    function dpHint(input) {
        if (input) input.placeholder = dp.chain === 'sol' ? 'Solana wallet address' : '0x wallet address';
    }

    function setupDeposit() {
        var modal = document.getElementById('dpModal');
        if (!modal) return;
        var input = document.getElementById('dpAddress');
        var open = function () {
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            dpMsg('');
            dpHint(input);
            dpRefresh();
        };
        var close = function () {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-wallet-deposit]').forEach(function (b) {
            b.addEventListener('click', function (e) { e.preventDefault(); open(); });
        });
        modal.querySelectorAll('[data-dp-close]').forEach(function (b) {
            b.addEventListener('click', close);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('open')) close();
        });

        document.querySelectorAll('#dpChains [data-dp-chain]').forEach(function (b) {
            b.addEventListener('click', function () {
                dp.chain = b.dataset.dpChain;
                document.querySelectorAll('#dpChains [data-dp-chain]').forEach(function (x) {
                    x.classList.toggle('is-active', x === b);
                });
                dpHint(input);
                dpRender();
            });
        });

        var list = document.getElementById('dpList');
        if (list) {
            list.addEventListener('click', function (e) {
                var del = e.target.closest('[data-dp-del]');
                if (!del) return;
                var id = del.getAttribute('data-dp-del');
                api('/api/deposit/addresses/' + id, { method: 'DELETE' }).then(function () {
                    dp.list = dp.list.filter(function (a) { return String(a.id) !== String(id); });
                    dpRender();
                    toast('Deposit address removed');
                }).catch(function (err) { dpMsg(err.message, 'err'); });
            });
        }

        var form = document.getElementById('dpForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var address = (document.getElementById('dpAddress') || {}).value || '';
                var label = (document.getElementById('dpLabel') || {}).value || '';
                address = address.trim();
                if (!address) return;
                dpMsg('');
                api('/api/deposit/addresses', { method: 'POST', body: { chain: dp.chain, address: address, label: label.trim() } })
                    .then(function (d) {
                        dp.list.push(d.address);
                        document.getElementById('dpAddress').value = '';
                        document.getElementById('dpLabel').value = '';
                        dpRender();
                        dpRefresh();
                        toast('Deposit address added');
                    })
                    .catch(function (err) { dpMsg(err.message, 'err'); });
            });
        }
    }

    function boot() {
        setupCopy();
        setupConnect();
        setupDeposit();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
