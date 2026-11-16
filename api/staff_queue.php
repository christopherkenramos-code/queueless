<?php
// Staff API: full live queue + today's stats
require_once __DIR__ . '/../includes/bootstrap.php';
api_staff();
archive_stale_tickets();

$rows = db()->query(
    "SELECT q.id, q.queue_number AS number, q.type, q.status, q.booked_at,
            u.name, s.name AS service, s.avg_minutes,
            TIMESTAMPDIFF(MINUTE, q.booked_at, COALESCE(q.served_at, NOW())) AS wait_minutes
     FROM queue_tickets q
     JOIN users u ON u.id = q.user_id
     JOIN services s ON s.id = q.service_id
     WHERE q.status IN ('waiting','called')
     ORDER BY (q.type = 'priority') DESC, q.booked_at ASC"
)->fetchAll();

$servedToday = (int)db()->query(
    "SELECT COUNT(*) FROM queue_tickets WHERE status = 'served' AND DATE(served_at) = CURDATE()"
)->fetchColumn();

$avgWait = db()->query(
    "SELECT ROUND(AVG(TIMESTAMPDIFF(MINUTE, booked_at, served_at)), 1)
     FROM queue_tickets WHERE status = 'served' AND DATE(served_at) = CURDATE()"
)->fetchColumn();

json_out([
    'rows' => $rows,
    'stats' => [
        'waiting' => count(array_filter($rows, fn($r) => $r['status'] === 'waiting')),
        'called' => count(array_filter($rows, fn($r) => $r['status'] === 'called')),
        'served_today' => $servedToday,
        'avg_wait' => $avgWait !== null ? (float)$avgWait : null,
    ],
]);
