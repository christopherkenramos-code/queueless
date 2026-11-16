<?php
// Patient API: cancel own active ticket
require_once __DIR__ . '/../includes/bootstrap.php';
$u = api_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST only'], 405);
$id = (int)($_POST['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM queue_tickets WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $u['id']]);
$t = $stmt->fetch();
if (!$t || !in_array($t['status'], ['waiting', 'called'], true)) {
    json_out(['error' => 'Ticket not found or already closed'], 404);
}
db()->prepare("UPDATE queue_tickets SET status = 'cancelled' WHERE id = ?")->execute([$id]);
add_notification($u['id'], 'Ticket cancelled', 'Your ticket ' . $t['queue_number'] . ' was cancelled. Book again anytime.');
json_out(['ok' => true]);
