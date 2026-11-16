<?php
// Patient API: join the queue as a walk-in right now
require_once __DIR__ . '/../includes/bootstrap.php';
$u = api_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST only'], 405);
if (active_ticket($u['id'])) json_out(['error' => 'You already have an active ticket.'], 409);

$number = next_number('W');
$stmt = db()->prepare(
    "INSERT INTO queue_tickets (queue_number, user_id, service_id, type) VALUES (?, ?, 1, 'walkin')"
);
$stmt->execute([$number, $u['id']]);

$staff = db()->query("SELECT id FROM users WHERE role = 'staff'")->fetchAll();
foreach ($staff as $s) {
    add_notification((int)$s['id'], 'New walk-in 🚶', $u['name'] . ' joined the queue (' . $number . ')');
}
$t = db()->prepare('SELECT * FROM queue_tickets WHERE queue_number = ? AND DATE(booked_at) = CURDATE()');
$t->execute([$number]);
$ticket = $t->fetch();
json_out(['ok' => true, 'ticket' => [
    'id' => $ticket['id'], 'number' => $number, 'type' => 'walkin',
    'position' => queue_position($ticket), 'est_minutes' => est_minutes($ticket),
]]);
