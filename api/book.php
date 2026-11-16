<?php
// Patient API: create an appointment booking (recommendation: one active ticket per user)
require_once __DIR__ . '/../includes/bootstrap.php';
$u = api_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST only'], 405);

$serviceId = (int)($_POST['service_id'] ?? 0);
$type = in_array($_POST['type'] ?? '', ['appt', 'priority'], true) ? $_POST['type'] : 'appt';
$slotDate = $_POST['slot_date'] ?? '';
$slotTime = substr($_POST['slot_time'] ?? '', 0, 10);
$notes = substr(trim($_POST['notes'] ?? ''), 0, 255);

$svc = db()->prepare('SELECT id FROM services WHERE id = ?');
$svc->execute([$serviceId]);
if (!$svc->fetch()) json_out(['error' => 'Unknown service'], 422);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $slotDate) || $slotDate < date('Y-m-d')) {
    json_out(['error' => 'Please choose a valid future date'], 422);
}

if (active_ticket($u['id'])) {
    json_out(['error' => 'You already have an active ticket. Cancel it first to book again.'], 409);
}

$prefix = $type === 'priority' ? 'P' : 'A';
$number = next_number($prefix);
$stmt = db()->prepare(
    'INSERT INTO queue_tickets (queue_number, user_id, service_id, type, slot_date, slot_time, notes)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$number, $u['id'], $serviceId, $type, $slotDate, $slotTime, $notes]);
$ticketId = (int)db()->lastInsertId();

// Notify staff that a new booking arrived
$staff = db()->query("SELECT id FROM users WHERE role = 'staff'")->fetchAll();
foreach ($staff as $s) {
    add_notification((int)$s['id'], 'New booking 📅', $u['name'] . ' booked a ' . ($type === 'priority' ? 'priority ' : '') . 'slot (' . $number . ')');
}

$t = db()->prepare('SELECT * FROM queue_tickets WHERE id = ?');
$t->execute([$ticketId]);
$ticket = $t->fetch();
json_out([
    'ok' => true,
    'ticket' => [
        'id' => $ticket['id'], 'number' => $number, 'type' => $type,
        'slot_date' => $slotDate, 'slot_time' => $slotTime,
        'position' => queue_position($ticket), 'est_minutes' => est_minutes($ticket),
    ],
]);
