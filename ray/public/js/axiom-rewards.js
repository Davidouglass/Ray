/* Rewards page interactions: tab switching, toasts, referral copy. */
(function () {
    var root = document.getElementById('rewardsRoot');
    if (!root) return;

    var toastEl = document.querySelector('.toast');
    var toastTimer;
    function toast(msg) {
        if (window.Axiom && window.Axiom.toast) { window.Axiom.toast(msg); return; }
        if (!toastEl) return;
        toastEl.textContent = msg;
        toastEl.classList.add('show');
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(function () { toastEl.classList.remove('show'); }, 2100);
    }

    root.querySelectorAll('.page-tabs button, .subtabs button:not([disabled])').forEach(function (button) {
        button.addEventListener('click', function () {
            var group = button.closest('.page-tabs') || button.closest('.subtabs');
            if (!group) return;
            group.querySelectorAll('button').forEach(function (item) { item.classList.remove('active'); });
            button.classList.add('active');
        });
    });

    root.querySelectorAll('[data-toast]').forEach(function (button) {
        button.addEventListener('click', function () { toast(button.getAttribute('data-toast')); });
    });

    root.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            var value = button.getAttribute('data-copy') || '';
            if (navigator.clipboard && value) {
                navigator.clipboard.writeText(value).catch(function () {});
            }
            toast('Referral link copied');
        });
    });

    var flash = root.getAttribute('data-flash');
    if (flash) toast(flash);
})();
