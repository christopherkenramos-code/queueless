<?php
// Staff API: call the next waiting ticket (optionally a specific one)
require_once __DIR__ . '/../includes/bootstrap.php';
$u = api_staff();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST only'], 405);

// Only one ticket can be "called" at a time
$busy = db()->query("SELECT id FROM queue_tickets WHERE status = 'called' LIMIT 1")->fetch();
if ($busy) json_out(['error' => 'Finish serving the current client first (mark served or no-show).'], 409);

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $stmt = db()->prepare("SELECT * FROM queue_tickets WHERE id = ? AND status = 'waiting'");
    $stmt->execute([$id]);
} else {
    $stmt = db()->query(
        "SELECT * FROM queue_tickets WHERE status = 'waiting'
         ORDER BY (type = 'priority') DESC, booked_at ASC LIMIT 1"
    );
}
$t = $stmt->fetch();
if (!$t) json_out(['error' => 'No clients waiting.'], 404);

db()->prepare("UPDATE queue_tickets SET status = 'called', called_at = NOW() WHERE id = ?")->execute([$t['id']]);
add_notification((int)$t['user_id'], "🔔 It's your turn!",
    'Your number ' . $t['queue_number'] . ' is now being served. Please proceed to the counter.');
json_out(['ok' => true, 'number' => $t['queue_number']]);
