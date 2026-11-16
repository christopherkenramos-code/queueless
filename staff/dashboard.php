<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_staff();
archive_stale_tickets(); // recommendation: auto-reset stale queue at the start of each day
$assetBase = '../'; $homeLink = '../index.php'; $boardLink = '../board.php'; $logoutLink = '../logout.php';
$pageTitle = 'Staff Dashboard — QueueLess';
include __DIR__ . '/../includes/header.php';
?>
<div class="app">
  <div class="app-head">
    <div><h2>Staff Dashboard</h2><p>Municipal Health Office of Naguilian — Queue Operations</p></div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <a class="btn btn-ghost btn-sm" href="../board.php" target="_blank">🖥 Open Display Board</a>
      <a class="btn btn-ghost btn-sm" href="export.php">📥 Export Today's CSV</a>
    </div>
  </div>
  <div class="stat-cards">
    <div class="stat-card"><b id="stWaiting">0</b><span>Waiting now</span></div>
    <div class="stat-card"><b id="stCalled">0</b><span>Being served</span></div>
    <div class="stat-card"><b id="stServed">0</b><span>Served today</span></div>
    <div class="stat-card"><b id="stAvg">—</b><span>Avg. wait today</span></div>
  </div>
  <div class="card" style="margin-bottom:22px">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
      <div><h3 style="color:var(--g900)">Now Serving</h3><p style="font-size:.82rem;color:var(--muted)">Call the next client when the current service is done.</p></div>
      <div style="text-align:right"><div class="qnum" id="staffNow" style="font-size:2.6rem">—</div><div class="chip wait" id="staffNowSvc">idle</div></div>
    </div>
    <div class="staff-controls">
      <button class="btn btn-primary" onclick="callNext()">▶ Call Next Client</button>
      <button class="btn btn-ghost" onclick="resolveCurrent('served')">✔ Mark Served</button>
      <button class="btn btn-ghost" style="color:var(--red);border-color:#fecaca" onclick="resolveCurrent('no-show')">✖ No-show</button>
    </div>
    <div class="announce-input">
      <input id="announceTxt" placeholder="📢 Board announcement — e.g., 'Dental service resumes at 1:00 PM'">
      <button class="btn btn-teal btn-sm" onclick="setAnnounce()">Announce</button>
    </div>
  </div>
  <div class="card">
    <h3 style="color:var(--g900);margin-bottom:12px">Live Queue</h3>
    <div style="overflow-x:auto"><table>
      <thead><tr><th>Number</th><th>Client</th><th>Service</th><th>Type</th><th>Booked</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody id="queueTable"></tbody>
    </table></div>
  </div>
</div>
<script src="../assets/js/staff.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
