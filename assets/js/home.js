/* QueueLess home page: scroll reveals, counters, live mini-board polling */
(function () {
  // scroll reveal + count-up
  const io = new IntersectionObserver(es => es.forEach(en => {
    if (!en.isIntersecting) return;
    en.target.classList.add('in');
    en.target.querySelectorAll('[data-count]').forEach(el => {
      if (el.dataset.done) return; el.dataset.done = 1;
      const target = +el.dataset.count, suf = el.dataset.suffix || '';
      const t0 = performance.now();
      (function step(now) {
        const p = Math.min(1, (now - t0) / 1400), e2 = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * e2).toLocaleString() + suf;
        if (p < 1) requestAnimationFrame(step);
      })(t0);
    });
  }), { threshold: .2 });
  document.querySelectorAll('.reveal').forEach(el => io.observe(el));

  // live mini-board
  async function poll() {
    try {
      const r = await fetch('api/board.php');
      const d = await r.json();
      const now = document.getElementById('heroNow');
      const next = document.getElementById('heroNext');
      if (!now || !next) return;
      now.textContent = d.now ? d.now.number : '—';
      if (d.next && d.next.length) {
        next.innerHTML = d.next.map((t, i) =>
          '<div class="slot' + (i === 0 ? ' next' : '') + '"><b>' + t.number + '</b><span>' +
          (i === 0 ? 'next' : 'in line') + '</span></div>').join('');
      } else {
        next.innerHTML = '<div class="slot"><b>—</b><span>open</span></div>';
      }
    } catch (e) { /* offline — keep last state */ }
  }
  poll(); setInterval(poll, 5000);
})();
