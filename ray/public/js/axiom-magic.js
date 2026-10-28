/* Axiom "magic" layer — reveal-on-scroll, count-up stats, tilt, ripple,
   parallax backdrop, marquee, chart draw, toasts. Progressive enhancement. */
(function () {
    'use strict';
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- animated backdrop ---------- */
    function backdrop() {
        if (document.querySelector('.magic-bg')) return;
        var bg = document.createElement('div');
        bg.className = 'magic-bg';
        bg.innerHTML = '<span class="mg-orb a"></span><span class="mg-orb b"></span><span class="mg-orb c"></span>';
        document.body.insertBefore(bg, document.body.firstChild);

        if (reduce) return;
        var orbs = bg.querySelectorAll('.mg-orb');
        window.addEventListener('mousemove', function (e) {
            var x = (e.clientX / window.innerWidth - .5), y = (e.clientY / window.innerHeight - .5);
            orbs.forEach(function (o, i) {
                var d = (i + 1) * 14;
                o.style.transform = 'translate3d(' + (x * d) + 'px,' + (y * d) + 'px,0)';
            });
        }, { passive: true });
    }

    /* ---------- reveal on scroll ---------- */
    var obs = ('IntersectionObserver' in window)
        ? new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { e.target.classList.add('is-in'); obs.unobserve(e.target); }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -6% 0px' })
        : null;

    function reveal() {
        var set = new Set();
        document.querySelectorAll('.fx-page > *').forEach(function (el) { set.add(el); });
        document.querySelectorAll('.fx-grid > *').forEach(function (el) { set.add(el); });
        document.querySelectorAll('.fx-two > *').forEach(function (el) { set.add(el); });
        document.querySelectorAll('.fx-list > *').forEach(function (el) { set.add(el); });
        var i = 0;
        set.forEach(function (el) {
            if (el.classList.contains('mg-reveal')) return;
            el.classList.add('mg-reveal');
            el.style.transitionDelay = Math.min(i * 55, 520) + 'ms';
            i++;
        });
        set.forEach(function (el) {
            if (reduce || !obs) { el.classList.add('is-in'); return; }
            var r = el.getBoundingClientRect();
            if (r.top < window.innerHeight && r.bottom > 0) el.classList.add('is-in');
            else obs.observe(el);
        });
    }

    /* ---------- count-up ---------- */
    function parse(v) {
        var m = String(v).match(/^(\s*[^0-9]*?)([0-9][0-9,]*(?:\.[0-9]+)?)(.*)$/s);
        if (!m) return null;
        var num = parseFloat(m[2].replace(/,/g, ''));
        if (isNaN(num)) return null;
        var dec = (m[2].split('.')[1] || '').length;
        return { prefix: m[1], num: num, suffix: m[3], dec: dec, comma: m[2].indexOf(',') > -1 };
    }
    function fmt(n, dec, comma) {
        var s = n.toFixed(dec);
        if (comma) {
            var p = s.split('.');
            p[0] = p[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            s = p.join('.');
        }
        return s;
    }
    function countUp(el) {
        var p = parse(el.textContent);
        if (!p || p.num === 0) return;
        var target = p.num, start = null, dur = 1100;
        function step(ts) {
            if (!start) start = ts;
            var t = Math.min((ts - start) / dur, 1);
            var e = 1 - Math.pow(1 - t, 3);
            el.textContent = p.prefix + fmt(target * e, p.dec, p.comma) + p.suffix;
            if (t < 1) requestAnimationFrame(step);
            else el.textContent = p.prefix + fmt(target, p.dec, p.comma) + p.suffix;
        }
        requestAnimationFrame(step);
    }
    function counters() {
        if (reduce) return;
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { countUp(en.target); io.unobserve(en.target); }
            });
        }, { threshold: 0.4 });
        document.querySelectorAll('.fx-stat b, .mg-count, [data-count]').forEach(function (el) { io.observe(el); });
    }

    /* ---------- chart draw ---------- */
    function charts() {
        document.querySelectorAll('.fx-spark .line, .fx-spark .area').forEach(function (p) {
            if (!p.getAttribute('pathLength')) p.setAttribute('pathLength', '1');
        });
    }

    /* ---------- ripple ---------- */
    document.addEventListener('click', function (e) {
        var b = e.target.closest('.fx-btn, .mg-seg button, .mg-lev button, .fx-tab');
        if (!b || reduce) return;
        var r = b.getBoundingClientRect();
        var d = Math.max(r.width, r.height);
        var s = document.createElement('span');
        s.className = 'mg-ripple';
        s.style.width = s.style.height = d + 'px';
        s.style.left = (e.clientX - r.left - d / 2) + 'px';
        s.style.top = (e.clientY - r.top - d / 2) + 'px';
        if (getComputedStyle(b).position === 'static') b.style.position = 'relative';
        b.style.overflow = 'hidden';
        b.appendChild(s);
        setTimeout(function () { s.remove(); }, 620);
    }, true);

    /* ---------- 3D tilt ---------- */
    function tilt() {
        if (reduce || !window.matchMedia('(hover:hover)').matches) return;
        document.querySelectorAll('.fx-card, .mg-tilt').forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                var r = card.getBoundingClientRect();
                var x = (e.clientX - r.left) / r.width - 0.5;
                var y = (e.clientY - r.top) / r.height - 0.5;
                card.style.transform = 'perspective(800px) rotateX(' + (-y * 6).toFixed(2) + 'deg) rotateY(' + (x * 8).toFixed(2) + 'deg) translateY(-3px)';
            });
            card.addEventListener('mouseleave', function () { card.style.transform = ''; });
        });
    }

    /* ---------- marquee ---------- */
    function marquee() {
        document.querySelectorAll('.mg-marquee').forEach(function (t) {
            if (t.dataset.cloned) return;
            t.dataset.cloned = '1';
            var kids = Array.prototype.slice.call(t.children);
            kids.forEach(function (k) { t.appendChild(k.cloneNode(true)); });
        });
    }

    /* ---------- parallax content ---------- */
    function parallax() {
        if (reduce) return;
        var els = document.querySelectorAll('[data-parallax]');
        if (!els.length) return;
        window.addEventListener('scroll', function () {
            var y = window.scrollY;
            els.forEach(function (el) {
                var s = parseFloat(el.getAttribute('data-parallax')) || 0.15;
                el.style.transform = 'translateY(' + (y * s).toFixed(1) + 'px)';
            });
        }, { passive: true });
    }

    /* ---------- toast helper ---------- */
    window.Axiom = window.Axiom || {};
    window.Axiom.toast = function (msg) {
        var host = document.querySelector('.mg-toast-host');
        if (!host) { host = document.createElement('div'); host.className = 'mg-toast-host'; document.body.appendChild(host); }
        var t = document.createElement('div');
        t.className = 'mg-toast';
        t.textContent = msg;
        host.appendChild(t);
        setTimeout(function () { t.style.transition = 'opacity .4s, transform .4s'; t.style.opacity = '0'; t.style.transform = 'translateY(10px)'; }, 3200);
        setTimeout(function () { t.remove(); }, 3700);
    };

    function enter() {
        if (reduce) return;
        var p = document.querySelector('.fx-page');
        if (p) p.classList.add('mg-enter');
    }

    function boot() {
        backdrop();
        enter();
        charts();
        reveal();
        counters();
        tilt();
        marquee();
        parallax();
        if (window.Axiom) window.Axiom.refresh = function () { reveal(); counters(); tilt(); };
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
