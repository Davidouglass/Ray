/* Portfolio page: holdings search + sort.
   Wallet connect modal + copy are handled globally by axiom-wallet.js. */
(function () {
    'use strict';

    function setupHoldings() {
        var tbody = document.querySelector('[data-pf-holdings]');
        if (!tbody) return;

        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr.pf-row'));
        var table = tbody.closest('table');
        var search = document.querySelector('[data-pf-search]');
        var empty = document.querySelector('[data-pf-holdings-empty]');
        var count = document.querySelector('[data-pf-holdings-count]');

        function applyFilter() {
            var q = search ? search.value.trim().toLowerCase() : '';
            var shown = 0;
            rows.forEach(function (r) {
                var hit = !q || (r.dataset.search || '').indexOf(q) > -1;
                r.style.display = hit ? '' : 'none';
                if (hit) shown++;
            });
            if (empty) empty.style.display = shown === 0 ? '' : 'none';
            if (table) table.style.display = shown === 0 ? 'none' : '';
            if (count) count.textContent = shown + (shown === 1 ? ' asset' : ' assets');
        }

        if (search) search.addEventListener('input', applyFilter);

        rows.forEach(function (r) {
            r.addEventListener('click', function (e) {
                if (e.target.closest('a, button')) return;
                if (r.dataset.href) window.location.href = r.dataset.href;
            });
            r.addEventListener('keydown', function (e) {
                if ((e.key === 'Enter' || e.key === ' ') && r.dataset.href) {
                    e.preventDefault();
                    window.location.href = r.dataset.href;
                }
            });
        });

        document.querySelectorAll('[data-pf-sort]').forEach(function (th) {
            th.addEventListener('click', function () {
                var key = th.dataset.pfSort;
                var dir = th.dataset.dir === 'asc' ? 'desc' : 'asc';
                document.querySelectorAll('[data-pf-sort]').forEach(function (t) {
                    t.dataset.dir = '';
                    th.classList.remove('sorted');
                    t.classList.remove('sorted');
                });
                th.dataset.dir = dir;
                th.classList.add('sorted');
                rows.sort(function (a, b) {
                    var x = parseFloat(a.dataset[key]) || 0;
                    var y = parseFloat(b.dataset[key]) || 0;
                    return dir === 'asc' ? x - y : y - x;
                });
                rows.forEach(function (r) { tbody.appendChild(r); });
            });
        });
    }

    function boot() {
        setupHoldings();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
