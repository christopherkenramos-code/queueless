/* QueueLess public display board — polls every 3s */
(function () {
  const clock = document.getElementById('clock');
  setInterval(() => { clock.textContent = new Date().toLocaleTimeString('en-PH'); }, 1000);
  clock.textContent = new Date().toLocaleTimeString('en-PH');

  let lastNow = null, firstBeep = true;
  function beep() {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const o = ctx.createOscillator(), g = ctx.createGain();
      o.connect(g); g.connect(ctx.destination);
      o.frequency.value = 880; g.gain.value = .12;
      o.start(); setTimeout(() => { o.frequency.value = 1174; }, 160);
      setTimeout(() => { o.stop(); ctx.close(); }, 420);
    } catch (e) {}
  }

  async function poll() {
    try {
      const r = await fetch('api/board.php', { cache: 'no-store' });
      const d = await r.json();
      document.getElementById('bvNow').textContent = d.now ? d.now.number : '—';
      document.getElementById('bvSvc').textContent = d.now
        ? d.now.name.split(' ')[0] + ' · ' + d.now.service
        : 'Please wait for your number';
      document.getElementById('tickerTxt').textContent = d.announcement || '';

      const list = document.getElementById('bvList');
      list.innerHTML = (d.next && d.next.length)
        ? d.next.map((t, i) => '<div class="bv-item" style="animation-delay:' + (i * .08) + 's"><div><b>' +
            t.number + '</b><br><span>' + t.name + '</span></div><span>' + t.service + '</span></div>').join('')
        : '<div class="bv-item"><span style="color:#a7d7bd">No one else waiting 🌿</span></div>';

      if (d.now && d.now.number !== lastNow) {
        if (!firstBeep) beep();
        firstBeep = false;
        lastNow = d.now.number;
      }
    } catch (e) {}
  }
  poll(); setInterval(poll, 3000);
})();
