ini_set('display_errors', 1); error_reporting(E_ALL);
<?php
require_once __DIR__ . '/includes/bootstrap.php';
$services = db()->query('SELECT * FROM services ORDER BY id')->fetchAll();
$pageTitle = 'QueueLess — Skip the line, not the care';
include __DIR__ . '/includes/header.php';
?>
<div class="view active">
  <header class="hero">
    <canvas id="leafCanvas"></canvas>
    <div class="blob b1"></div><div class="blob b2"></div>
    <div>
      <span class="badge"><span class="pulse"></span> Live queue is open today · 8:00 AM – 5:00 PM</span>
      <h1>Skip the line,<br><span class="grad">not the care.</span></h1>
      <p>QueueLess is the smart appointment and virtual queue system of the Municipal Health Office of Naguilian. Book online or walk in, get your queue number, and track your turn in real time — no more hours lost in the waiting area.</p>
      <div class="hero-cta">
        <a class="btn btn-primary" href="<?= $u ? ($u['role']==='staff' ? 'staff/dashboard.php' : 'patient/dashboard.php') : 'login.php' ?>">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="3" stroke="#fff" stroke-width="2"/><path d="M8 3v4M16 3v4M3 10h18" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
          Book an Appointment</a>
        <a class="btn btn-ghost" href="board.php">View Display Board</a>
      </div>
      <div class="hero-stats">
        <div class="hstat"><b data-count="18" data-suffix=" min">0</b><span>Average wait today</span></div>
        <div class="hstat"><b data-count="1240" data-suffix="">0</b><span>Patients served this year</span></div>
        <div class="hstat"><b data-count="96" data-suffix="%">0</b><span>Patient satisfaction</span></div>
      </div>
    </div>
    <div class="hero-right">
      <div class="board-card">
        <div class="board-head">
          <b><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h10M4 18h7" stroke="#15803d" stroke-width="2.2" stroke-linecap="round"/></svg> Live Queue Board</b>
          <span class="live-chip"><i></i>LIVE</span>
        </div>
        <div class="now-serving"><small>Now Serving</small><div class="num" id="heroNow">—</div></div>
        <div class="mini-q" id="heroNext"><div class="slot"><b>—</b><span>open</span></div></div>
      </div>
      <div class="float-chip"><div class="ic">🔔</div><span>Your turn is coming — we'll notify you</span></div>
      <div class="float-chip fc2"><div class="ic">⏱</div><span>Estimated waiting time updates in real time</span></div>
    </div>
  </header>

  <section style="padding-top:0">
    <div class="stats-band reveal">
      <div class="stat"><b><em data-count="68" data-suffix="%">0</em></b><span>Shorter waits vs. manual queue</span></div>
      <div class="stat"><b data-count="6" data-suffix="">0</b><span>Health services offered</span></div>
      <div class="stat"><b data-count="30" data-suffix="s">0</b><span>Average notification delay</span></div>
      <div class="stat"><b data-count="0" data-suffix="">0</b><span>Missed turns with alerts</span></div>
    </div>
  </section>

  <section style="padding-top:10px">
    <p class="sec-tag">How It Works</p>
    <h2 class="sec-title">From booking to being served,<br>in four simple steps</h2>
    <p class="sec-sub">Aligned with the Citizens' Charter service standards under RA 11032 and the digitalization push of RA 12254 (E-Governance Act).</p>
    <div class="steps">
      <div class="step reveal"><div class="ic">👤</div><h4>Register Online</h4><p>Create your free account in seconds — name, email, and a password. Your health office visits stay linked to one profile.</p></div>
      <div class="step reveal"><div class="ic">📅</div><h4>Book or Walk In</h4><p>Pick a service, date and time slot — or grab a walk-in number on the spot. Both flows merge into one fair, live queue.</p></div>
      <div class="step reveal"><div class="ic">📡</div><h4>Track in Real Time</h4><p>Watch your position, estimated wait, and queue number from anywhere — at home, at work, or across the street.</p></div>
      <div class="step reveal"><div class="ic">🔔</div><h4>Get Notified</h4><p>Automated alerts tell you when your turn approaches — arrive just in time, never miss your number.</p></div>
    </div>
  </section>

  <section style="background:linear-gradient(180deg,transparent,#e9f9f0 30%,transparent)">
    <p class="sec-tag">Our Services</p>
    <h2 class="sec-title">Municipal Health Office Services</h2>
    <p class="sec-sub">Published processing standards under the facility's Citizens' Charter — now with measurable, predictable waiting times.</p>
    <div class="services">
      <?php foreach ($services as $s): ?>
      <div class="svc reveal"><div class="ic"><?= ['🩺','💉','🦷','🧪','📄','🤱'][($s['id']-1) % 6] ?></div>
        <div><h4><?= e($s['name']) ?></h4><p><?= e($s['description']) ?></p><span class="eta">⏱ ~<?= (int)$s['avg_minutes'] ?> min service</span></div></div>
      <?php endforeach; ?>
    </div>
  </section>

  <section>
    <div class="cta-band reveal">
      <h2>Ready to wait less, live more?</h2>
      <p>Join the virtual queue today — it takes less than a minute.</p>
      <a class="btn btn-primary btn-lg" style="padding:15px 36px" href="<?= $u ? 'patient/dashboard.php' : 'register.php' ?>">Get Started — It's Free</a>
    </div>
  </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="assets/js/leaves.js"></script>
<script src="assets/js/home.js"></script>
