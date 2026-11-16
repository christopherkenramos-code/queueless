/* QueueLess patient dashboard logic */
(function () {
  const $ = id => document.getElementById(id);

  function toast(title, msg, type) {
    const box = $('toasts');
    if (!box) return;
    const d = document.createElement('div');
    d.className = 'toast ' + (type || '');
    d.innerHTML = '<b></b><span></span>';
    d.querySelector('b').textContent = title;
    d.querySelector('span').textContent = msg;
    box.appendChild(d);
    setTimeout(() => { d.style.opacity = '0'; d.style.transition = '.5s'; setTimeout(() => d.remove(), 500); }, 4500);
  }

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

  window.switchTab = function (t, btn) {
    ['book', 'queue', 'notif'].forEach(x => { $('tab-' + x).style.display = x === t ? 'block' : 'none'; });
    document.querySelectorAll('.tabs button').forEach(b => b.classList.toggle('on', b === btn));
    if (t === 'queue') poll(true);
    if (t === 'notif') poll(true);
  };

  // ---- booking ----
  $('bookForm').addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const r = await fetch('../api/book.php', { method: 'POST', body: fd });
    const d = await r.json();
    if (!d.ok) return toast('Booking failed', d.error || 'Please try again', 'err');
    $('tmNum').textContent = d.ticket.number;
    $('tmMeta').textContent = (d.ticket.type === 'priority' ? 'Priority lane · ' : '') +
      document.querySelector('#bkService option:checked').textContent.split(' (~')[0] +
      ' · ' + d.ticket.slot_date + ' · ' + d.ticket.slot_time + ' slot';
    $('tmEta').textContent = 'You are #' + d.ticket.position + ' in line · estimated wait ~' + d.ticket.est_minutes + ' min once you check in.';
    $('ticketModal').classList.add('show');
    e.target.reset();
    $('bkDate').value = TODAY;
  });

  window.walkIn = async function () {
    const r = await fetch('../api/walkin.php', { method: 'POST' });
    const d = await r.json();
    if (!d.ok) return toast('Walk-in failed', d.error || 'Please try again', 'err');
    $('tmNum').textContent = d.ticket.number;
    $('tmMeta').textContent = 'Walk-in · General Consultation';
    $('tmEta').textContent = 'You are #' + d.ticket.position + ' in line · estimated wait ~' + d.ticket.est_minutes + ' min. Wait anywhere — we will notify you!';
    $('ticketModal').classList.add('show');
    poll(true);
  };

  window.closeModal = function () {
    $('ticketModal').classList.remove('show');
    switchTab('queue', document.querySelector('[data-tab=queue]'));
  };

  window.cancelTicket = async function (id) {
    const fd = new FormData(); fd.append('id', id);
    const r = await fetch('../api/cancel.php', { method: 'POST', body: fd });
    const d = await r.json();
    if (!d.ok) return toast('Cancel failed', d.error || '', 'err');
    toast('Ticket cancelled', 'Removed from the queue.', 'warn');
    poll(true);
  };

  // ---- live polling ----
  let lastStatus = null, lastNotifId = 0;

  function timeAgo(t) {
    const s = (Date.now() - new Date(t.replace(' ', 'T')).getTime()) / 1000;
    if (s < 60) return 'just now';
    if (s < 3600) return Math.floor(s / 60) + 'm ago';
    return Math.floor(s / 3600) + 'h ago';
  }

  function esc(s) { return String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c])); }

  function drawQline(pos, total, youId) {
    if (!pos) return '';
    const n = Math.min(total, 6);
    let viz = '<div class="qline"><svg viewBox="0 0 640 120">';
    for (let i = 0; i < n; i++) {
      const x = 40 + i * 86, y = 62, isYou = (i + 1) === pos;
      viz += '<circle cx="' + x + '" cy="' + y + '" r="20" fill="' + (isYou ? '#22c55e' : '#dcfce7') +
        '" stroke="' + (isYou ? '#15803d' : '#86efac') + '" stroke-width="2"/>' +
        '<circle cx="' + x + '" cy="' + (y - 4) + '" r="7" fill="' + (isYou ? '#fff' : '#15803d') + '"/>' +
        '<path d="M ' + (x - 10) + ' ' + (y + 18) + ' a 10 10 0 0 1 20 0" fill="' + (isYou ? '#fff' : '#15803d') + '"/>' +
        '<text x="' + x + '" y="104" text-anchor="middle" font-size="11" font-weight="700" fill="' + (isYou ? '#15803d' : '#6b8f7b') + '" font-family="inherit">' + (i + 1) + '</text>';
    }
    if (total > 6) viz += '<text x="' + (40 + 6 * 86) + '" y="66" font-size="20" fill="#86efac">+' + (total - 6) + '</text>';
    viz += '<path d="M 600 40 h 22 l -8 -8 M 622 40 l -8 8" stroke="#0d9488" stroke-width="3" fill="none" stroke-linecap="round"/>' +
      '<text x="598" y="80" font-size="11" fill="#0d9488" font-weight="700">COUNTER</text></svg></div>';
    return viz;
  }

  function renderTicket(t, total) {
    const box = $('myTicket');
    if (!t) {
      box.innerHTML = '<div class="empty"><div class="big">🎫</div><h3 style="color:var(--g900)">No active queue ticket</h3>' +
        '<p style="margin:8px 0 18px">Book an appointment or grab a walk-in number to get started.</p>' +
        '<button class="btn btn-primary" onclick="switchTab(\'book\',document.querySelector(\'[data-tab=book]\'))">Book / Walk-in</button></div>';
      return;
    }
    const circ = 2 * Math.PI * 54;
    const pct = t.status === 'called' ? 100 : Math.max(6, Math.round((1 - t.position / (total + 1)) * 100));
    const statusChip = t.status === 'called'
      ? '<span class="chip called">🔔 NOW SERVING — PLEASE PROCEED</span>'
      : '<span class="chip wait">WAITING · Position #' + t.position + '</span>';
    const etaText = t.status === 'called'
      ? 'Head to the service counter now — your number is on the board!'
      : 'Approximately <b>' + t.est_minutes + ' min</b> remaining. We will notify you before your turn.';
    box.innerHTML = '<div class="ticket">' +
      '<div><div style="font-size:.7rem;font-weight:700;letter-spacing:2px;color:var(--teal);text-transform:uppercase">Your Queue Number</div>' +
      '<div class="qnum">' + esc(t.number) + '</div>' + statusChip + '</div>' +
      '<div class="qmeta"><h3>' + esc(t.service) + '</h3>' +
      '<p>' + (t.type === 'walkin' ? '🚶 Walk-in' : '📅 ' + esc(t.slot_date || '') + ' · ' + esc(t.slot_time || '') + ' slot') +
      (t.type === 'priority' ? ' · ⭐ Priority' : '') + '</p>' +
      '<p style="margin-top:8px;font-weight:600;color:var(--g700)">' + etaText + '</p>' +
      (t.status === 'waiting' ? '<button class="btn btn-danger btn-sm" style="margin-top:12px" onclick="cancelTicket(' + t.id + ')">Cancel ticket</button>' : '') +
      '</div>' +
      '<div class="ring-wrap"><svg width="130" height="130"><circle cx="65" cy="65" r="54" fill="none" stroke="#e5f6ec" stroke-width="11"/>' +
      '<circle cx="65" cy="65" r="54" fill="none" stroke="url(#g1)" stroke-width="11" stroke-linecap="round" stroke-dasharray="' + circ +
      '" stroke-dashoffset="' + (circ * (1 - pct / 100)) + '" style="transition:stroke-dashoffset 1s ease"/>' +
      '<defs><linearGradient id="g1"><stop offset="0" stop-color="#16a34a"/><stop offset="1" stop-color="#0d9488"/></linearGradient></defs></svg>' +
      '<div class="center"><b>' + (t.status === 'called' ? 'GO!' : t.est_minutes + 'm') + '</b><span>' + (t.status === 'called' ? 'Proceed' : 'Est. wait') + '</span></div></div></div>' +
      (t.status === 'waiting' ? drawQline(t.position, total) : '');
  }

  function renderNotifs(list) {
    const box = $('notifList');
    box.innerHTML = list.length
      ? list.map(n => '<div class="notif-item"><div class="ic">🔔</div><div><b>' + esc(n.title) + '</b><p>' + esc(n.message) + '</p></div><time>' + timeAgo(n.created_at) + '</time></div>').join('')
      : '<div class="empty" style="padding:24px"><p>No notifications yet — you\'re all caught up! 🌿</p></div>';
  }

  async function poll(manual) {
    try {
      const r = await fetch('../api/myqueue.php', { cache: 'no-store' });
      const d = await r.json();
      if (d.error) return;
      renderTicket(d.ticket, d.waiting_total);
      renderNotifs(d.notifications);
      if (d.ticket && d.ticket.status === 'called' && lastStatus === 'waiting') {
        beep();
        toast("🔔 It's your turn!", 'Your number ' + d.ticket.number + ' is now being served. Please proceed to the counter.');
      }
      if (d.ticket) lastStatus = d.ticket.status;
    } catch (e) { if (manual) toast('Connection issue', 'Could not reach the server.', 'err'); }
  }

  // toast container
  const tc = document.createElement('div'); tc.id = 'toasts'; document.body.appendChild(tc);

  poll(true);
  setInterval(() => poll(false), 4000);
})();
