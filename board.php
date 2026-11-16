<?php
require_once __DIR__ . '/includes/bootstrap.php';
// Public queue display board (full screen)
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Queue Display — MHO Naguilian</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="board-view" style="min-height:100vh">
  <div class="board-top">
    <h1>🌿 MHO Naguilian — Queue Display</h1>
    <div style="display:flex;align-items:center;gap:18px">
      <span class="live-chip" style="background:rgba(239,68,68,.15)"><i></i>LIVE</span>
      <div class="board-clock" id="clock">--:--</div>
      <a class="btn btn-ghost btn-sm" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2);color:#fff" href="index.php">← Back</a>
    </div>
  </div>
  <div class="board-main">
    <div class="bv-now">
      <div><small>Now Serving</small><div class="num" id="bvNow">—</div><div class="svc" id="bvSvc">Please wait for your number</div></div>
    </div>
    <div class="bv-list" id="bvList"></div>
  </div>
  <div class="ticker"><span id="tickerTxt">Welcome…</span></div>
</div>
<script src="assets/js/board.js"></script>
</body>
</html>
