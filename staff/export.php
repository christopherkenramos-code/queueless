<?php
// Staff: download today's served transactions as CSV (evidence for UAT / data analysis)
require_once __DIR__ . '/../includes/bootstrap.php';
require_staff();
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="queueless_report_' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['Queue No.', 'Client', 'Service', 'Type', 'Booked At', 'Served At', 'Wait (minutes)']);
$stmt = db()->query(
    "SELECT q.queue_number, u.name, s.name AS service, q.type, q.booked_at, q.served_at,
            TIMESTAMPDIFF(MINUTE, q.booked_at, q.served_at) AS wait_minutes
     FROM queue_tickets q
     JOIN users u ON u.id = q.user_id
     JOIN services s ON s.id = q.service_id
     WHERE q.status = 'served' AND DATE(q.served_at) = CURDATE()
     ORDER BY q.served_at"
);
while ($r = $stmt->fetch(PDO::FETCH_NUM)) fputcsv($out, $r);
fclose($out);
exit;
