<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login();
if (!in_array($u['role'], ['staff', 'admin'])) { /* client ok */ } else { header('Location: ../staff/dashboard.php'); exit; }
$services = db()->query('SELECT * FROM services ORDER BY id')->fetchAll();
$slots = ['08:00 AM','08:30 AM','09:00 AM','09:30 AM','10:00 AM','10:30 AM','11:00 AM','01:00 PM','01:30 PM','02:00 PM','02:30 PM','03:00 PM','03:30 PM','04:00 PM'];
$assetBase = '../'; $homeLink = '../index.php'; $boardLink = '../board.php'; $logoutLink = '../logout.php';
$pageTitle = 'My Queue — QueueLess';
include __DIR__ . '/../includes/header.php';
?>
<div class="app">
  <div class="app-head">
    <div><h2>Hello, <?= e(explode(' ', $u['name'])[0]) ?>! 🌿</h2><p>Here's your virtual queue hub. Wait anywhere — we'll call you when it's time.</p></div>
  </div>
  <div class="tabs">
    <button class="on" data-tab="book" onclick="switchTab('book',this)">📅 Book / Walk-in</button>
    <button data-tab="queue" onclick="switchTab('queue',this)">🎫 My Queue</button>
    <button data-tab="notif" onclick="switchTab('notif',this)">🔔 Notifications</button>
  </div>

  <div id="tab-book">
    <div class="grid2">
      <div class="card">
        <h3 style="color:var(--g900);margin-bottom:4px">Book an Appointment</h3>
        <p style="font-size:.82rem;color:var(--muted);margin-bottom:18px">Reserve a slot — arrive just before your turn.</p>
        <form id="bookForm">
          <div class="field"><label>Service</label>
            <select name="service_id" id="bkService">
              <?php foreach ($services as $s): ?>
              <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?> (~<?= (int)$s['avg_minutes'] ?> min)</option>
              <?php endforeach; ?>
            </select></div>
          <div class="field"><label>Priority lane</label>
            <select name="type">
              <option value="appt">Regular (A-number)</option>
              <option value="priority">Priority — senior / PWD / pregnant (P-number, served first)</option>
            </select></div>
          <div class="field"><label>Preferred Date</label><input type="date" name="slot_date" id="bkDate" required></div>
          <div class="field"><label>Time Slot</label>
            <select name="slot_time"><?php foreach ($slots as $sl): ?><option><?= e($sl) ?></option><?php endforeach; ?></select></div>
          <div class="field"><label>Notes for the health office (optional)</label><textarea name="notes" rows="2" placeholder="e.g., follow-up checkup, bring lab results…"></textarea></div>
          <button class="btn btn-primary" style="width:100%;justify-content:center" type="submit">Confirm Booking</button>
        </form>
      </div>
      <div class="card" style="display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;background:linear-gradient(150deg,#fff,#f0fdf6)">
        <div style="width:74px;height:74px;border-radius:22px;background:linear-gradient(135deg,var(--g500),var(--teal));display:grid;place-items:center;margin-bottom:16px;box-shadow:var(--shadow-lg);font-size:2rem">🚶</div>
        <h3 style="color:var(--g900)">No internet? No problem.</h3>
        <p style="font-size:.84rem;color:var(--muted);margin:8px 0 20px;line-height:1.7">Walk-in clients get a queue number on-site — both flows merge into one fair queue with published standards.</p>
        <button class="btn btn-teal" onclick="walkIn()">Get Walk-in Number Now</button>
      </div>
    </div>
  </div>

  <div id="tab-queue" style="display:none">
    <div class="card" id="myTicket"><div class="empty"><div class="big">⏳</div><p>Loading your ticket…</p></div></div>
  </div>

  <div id="tab-notif" style="display:none">
    <div class="card"><h3 style="color:var(--g900);margin-bottom:10px">Notifications</h3><div id="notifList"></div></div>
  </div>
</div>

<div class="modal-bg" id="ticketModal">
  <div class="modal">
    <p style="font-size:.8rem;font-weight:700;letter-spacing:2px;color:var(--teal);text-transform:uppercase">Booking Confirmed</p>
    <div class="big-q" id="tmNum">A-001</div>
    <p style="font-size:.86rem;color:var(--muted)" id="tmMeta"></p>
    <p style="font-size:.8rem;color:var(--muted);margin-top:10px" id="tmEta"></p>
    <div style="margin-top:22px"><button class="btn btn-primary" onclick="closeModal()">Track My Queue</button></div>
  </div>
</div>

<script>
const SLOTS = <?= json_encode($slots) ?>;
const TODAY = new Date().toISOString().slice(0, 10);
document.getElementById('bkDate').value = TODAY;
document.getElementById('bkDate').min = TODAY;
</script>
<script src="../assets/js/patient.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
