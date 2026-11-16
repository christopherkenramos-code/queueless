<?php
// Staff API: mark the called ticket as served or no-show
require_once __DIR__ . '/../includes/bootstrap.php';
api_staff();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST only'], 405);
$outcome = ($_POST['outcome'] ?? '') === 'no-show' ? 'no-show' : 'served';

$t = db()->query("SELECT * FROM queue_tickets WHERE status = 'called' LIMIT 1")->fetch();
if (!$t) json_out(['error' => 'No client is currently being served.'], 404);

db()->prepare('UPDATE queue_tickets SET status = ?, served_at = NOW() WHERE id = ?')->execute([$outcome, $t['id']]);
if ($outcome === 'served') {
    add_notification((int)$t['user_id'], '✔ Service complete',
        'Thank you for visiting MHO Naguilian. Please rate your experience on the SUS & UAT forms. 🌿');
} else {
    add_notification((int)$t['user_id'], 'Ticket skipped',
        'Your number ' . $t['queue_number'] . ' was marked no-show. Book again anytime.');
}
json_out(['ok' => true, 'outcome' => $outcome, 'number' => $t['queue_number']]);
