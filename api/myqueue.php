<?php
// Patient API: my active ticket (position, ETA, status) + recent notifications
require_once __DIR__ . '/../includes/bootstrap.php';
$u = api_login();
$t = active_ticket($u['id']);

$notifications = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 15');
$notifications->execute([$u['id']]);

$svcName = null;
if ($t) {
    $s = db()->prepare('SELECT name FROM services WHERE id = ?');
    $s->execute([$t['service_id']]);
    $svcName = $s->fetchColumn();
}
json_out([
    'ticket' => $t ? [
        'id' => (int)$t['id'],
        'number' => $t['queue_number'],
        'type' => $t['type'],
        'status' => $t['status'],
        'service' => $svcName,
        'slot_date' => $t['slot_date'],
        'slot_time' => $t['slot_time'],
        'position' => queue_position($t),
        'est_minutes' => est_minutes($t),
        'booked_at' => $t['booked_at'],
    ] : null,
    'notifications' => $notifications->fetchAll(),
    'waiting_total' => count(waiting_tickets()),
]);
