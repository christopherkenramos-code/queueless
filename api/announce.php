<?php
// Staff API: post a board announcement
require_once __DIR__ . '/../includes/bootstrap.php';
api_staff();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST only'], 405);
$msg = trim($_POST['message'] ?? '');
if ($msg === '' || mb_strlen($msg) > 255) json_out(['error' => 'Message must be 1–255 characters.'], 422);
db()->prepare('INSERT INTO announcements (message) VALUES (?)')->execute([$msg]);
json_out(['ok' => true]);
