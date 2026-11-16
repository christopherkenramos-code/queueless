/* QueueLess staff dashboard logic */
(function () {
  const $ = id => document.getElementById(id);
  const tc = document.createElement('div'); tc.id = 'toasts'; document.body.appendChild(tc);

  function toast(title, msg, type) {
    const d = document.createElement('div');
    d.className = 'toast ' + (type || '');
    d.innerHTML = '<b></b><span></span>';
    d.querySelector('b').textContent = title;
    d.querySelector('span').textContent = msg;
    tc.appendChild(d);
    setTimeout(() => { d.style.opacity = '0'; d.style.transition = '.5s'; setTimeout(() => d.remove(), 500); }, 4500);
  }
  function esc(s) { return String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c])); }
  function timeAgo(t) {
    const s = (Date.now() - new Date(t.replace(' ', 'T')).getTime()) / 1000;
    if (s < 60) return 'just now';
    if (s < 3600) return Math.floor(s / 60) + 'm ago';
    return Math.floor(s / 3600) + 'h ago';
  }

  window.callNext = async () => {
    const r = await fetch('../api/call.php', { method: 'POST' });
    const d = await r.json();
    if (!d.ok) return toast('Cannot call', d.error || '', 'warn');
    toast('Calling ' + d.number, 'Client notified automatically.');
    poll();
  };
  window.callOne = async id => {
    const fd = new FormData(); fd.append('id', id);
    const r = await fetch('../api/call.php', { method: 'POST', body: fd });
    const d = await r.json();
    if (!d.ok) return toast('Cannot call', d.error || '', 'warn');
    toast('Calling ' + d.number, 'Client notified automatically.');
    poll();
  };
  window.resolveCurrent = async outcome => {
    const fd = new FormData(); fd.append('outcome', outcome);
    const r = await fetch('../api/resolve.php', { method: 'POST', body: fd });
    const d = await r.json();
    if (!d.ok) return toast('Action failed', d.error || '', 'err');
    toast(outcome === 'served' ? '✔ ' + d.number + ' served' : d.number + ' marked no-show',
      outcome === 'served' ? 'Logged to today\'s report.' : 'Client was notified.');
    poll();
  };
  window.resolveOne = resolveCurrent;
  window.setAnnounce = async () => {
    const fd = new FormData(); fd.append('message', $('announceTxt').value);
    const r = await fetch('../api/announce.php', { method: 'POST', body: fd });
    const d = await r.json();
    if (!d.ok) return toast('Announcement failed', d.error || '', 'err');
    $('announceTxt').value = '';
    toast('Announcement posted', 'Now showing on the display board 🖥');
  };

  async function poll() {
    try {
      const r = await fetch('../api/staff_queue.php', { cache: 'no-store' });
      const d = await r.json();
      if (d.error) return;
      $('stWaiting').textContent = d.stats.waiting;
      $('stCalled').textContent = d.stats.called;
      $('stServed').textContent = d.stats.served_today;
      $('stAvg').textContent = d.stats.avg_wait !== null ? d.stats.avg_wait + 'm' : '—';
      const cur = d.rows.find(x => x.status === 'called');
      $('staffNow').textContent = cur ? cur.number : '—';
      $('staffNowSvc').textContent = cur ? cur.name + ' · ' + cur.service : 'idle';
      $('staffNowSvc').className = 'chip ' + (cur ? 'called' : 'wait');
      $('queueTable').innerHTML = d.rows.length ? d.rows.map(t =>
        '<tr><td><b style="color:var(--g700)">' + esc(t.number) + '</b></td><td>' + esc(t.name) + '</td>' +
        '<td>' + esc(t.service) + '</td><td>' +
        (t.type === 'priority' ? '⭐ Priority' : t.type === 'appt' ? '📅 Appt' : '🚶 Walk-in') + '</td>' +
        '<td>' + timeAgo(t.booked_at) + '</td><td><span class="chip ' + t.status + '">' + t.status + '</span></td>' +
        '<td>' + (t.status === 'waiting'
          ? '<button class="btn btn-teal btn-sm" onclick="callOne(' + t.id + ')">Call</button>'
          : '<button class="btn btn-ghost btn-sm" onclick="resolveCurrent(\'served\')">✔ Served</button> <button class="btn btn-danger btn-sm" onclick="resolveCurrent(\'no-show\')">✖</button>') +
        '</td></tr>').join('')
        : '<tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">Queue is empty 🌿</td></tr>';
    } catch (e) {}
  }
  poll();
  setInterval(poll, 4000);
})();
