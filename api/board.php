<?php
// Public endpoint: current board state (no auth required — for the display screen)
require_once __DIR__ . '/../includes/bootstrap.php';
archive_stale_tickets();
$now = db()->query(
    "SELECT q.queue_number AS number, u.name, s.name AS service
     FROM queue_tickets q
     JOIN users u ON u.id = q.user_id
     JOIN services s ON s.id = q.service_id
     WHERE q.status = 'called' ORDER BY q.called_at DESC LIMIT 1"
)->fetch();
$next = db()->query(
    "SELECT q.queue_number AS number, u.name, s.name AS service
     FROM queue_tickets q
     JOIN users u ON u.id = q.user_id
     JOIN services s ON s.id = q.service_id
     WHERE q.status = 'waiting'
     ORDER BY (q.type = 'priority') DESC, q.booked_at ASC LIMIT 4"
)->fetchAll();
json_out([
    'now' => $now ?: null,
    'next' => $next,
    'announcement' => latest_announcement(),
    'server_time' => date('h:i:s A'),
]);
