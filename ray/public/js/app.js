document.addEventListener('DOMContentLoaded', function () {

  /* ---------- Feature tabs + carousel ---------- */
  const tabs = document.querySelectorAll('.feat-tab');
  const track = document.getElementById('carouselTrack');
  const slides = document.querySelectorAll('.carousel-slide');
  const slideBtns = document.querySelectorAll('.slide-btn');

  function goToTab(idx) {
    if (!track) return;
    tabs.forEach(t => t.classList.toggle('active', Number(t.dataset.tab) === idx));
    slides.forEach(s => s.classList.toggle('cs-active', Number(s.dataset.slide) === idx));
    slideBtns.forEach((b, i) => {
      const active = i === idx;
      b.classList.toggle('active-btn', active);
      b.style.opacity = active ? '1' : '0.4';
      b.style.transform = active ? 'scale(1)' : 'scale(0.95)';
    });
    track.style.transform = 'translateX(-' + idx * 100 + '%)';
  }

  tabs.forEach(tab => {
    tab.addEventListener('click', () => goToTab(Number(tab.dataset.tab)));
  });

  /* ---------- Feature accordion (slide 0): right pane sync ---------- */
  const accItems = document.querySelectorAll('.acc-item');
  const paneSlides = document.querySelectorAll('.acc-pane .media-slide');

  function activateAcc(idx) {
    accItems.forEach(item => item.classList.toggle('active', item.dataset.idx === String(idx)));
    paneSlides.forEach(s => s.classList.toggle('active', s.dataset.slide === String(idx)));
  }

  accItems.forEach(item => {
    const head = item.querySelector('.acc-head');
    if (!head) return;
    head.addEventListener('click', () => activateAcc(item.dataset.idx));
  });

  /* ---------- FAQ accordion ---------- */
  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(item => {
    const q = item.querySelector('.faq-q');
    if (!q) return;
    q.addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      faqItems.forEach(f => f.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });

  /* ---------- Scroll reveal ---------- */
  const revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach(el => io.observe(el));
    setTimeout(() => revealEls.forEach(el => el.classList.add('visible')), 2500);
  } else {
    revealEls.forEach(el => el.classList.add('visible'));
  }

  /* ---------- Points counter ---------- */
  const counters = document.querySelectorAll('.points-num[data-count]');
  function runCounter(el) {
    const target = parseInt(el.dataset.count, 10) || 0;
    const duration = 1400;
    const start = performance.now();
    function tick(now) {
      const t = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - t, 3);
      el.textContent = Math.round(target * eased).toLocaleString();
      if (t < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }
  if (counters.length && 'IntersectionObserver' in window) {
    const cio = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          runCounter(e.target);
          cio.unobserve(e.target);
        }
      });
    }, { threshold: 0.4 });
    counters.forEach(el => cio.observe(el));
  } else {
    counters.forEach(runCounter);
  }

});