<?php
// ---------- Output & security helpers ----------
function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function json_out($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// ---------- Auth ----------
function current_user(): ?array {
    if (empty($_SESSION['uid'])) return null;
    $stmt = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['uid']]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function require_login(): array {
    $u = current_user();
    if (!$u) { header('Location: ../login.php'); exit; }
    return $u;
}

function require_staff(): array {
    $u = require_login();
    if (!in_array($u['role'], ['staff', 'admin'])) { header('Location: ../patient/dashboard.php'); exit; }
    return $u;
}

function api_login(): array {
    $u = current_user();
    if (!$u) json_out(['error' => 'Authentication required'], 401);
    return $u;
}

function api_staff(): array {
    $u = api_login();
    if (!in_array($u['role'], ['staff', 'admin'])) json_out(['error' => 'Staff only'], 403);
    return $u;
}

// ---------- Queue engine ----------
function next_number(string $prefix): string {
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO counters (day, prefix, value) VALUES (CURDATE(), ?, 1)
         ON DUPLICATE KEY UPDATE value = value + 1'
    );
    $stmt->execute([$prefix]);
    $v = (int)$pdo->query(
        "SELECT value FROM counters WHERE day = CURDATE() AND prefix = " . $pdo->quote($prefix)
    )->fetchColumn();
    return $prefix . '-' . str_pad((string)$v, 3, '0', STR_PAD_LEFT);
}

/** All waiting tickets in serving order: priority first, then FIFO. */
function waiting_tickets(): array {
    return db()->query(
        "SELECT * FROM queue_tickets WHERE status = 'waiting'
         ORDER BY (type = 'priority') DESC, booked_at ASC"
    )->fetchAll();
}

function active_ticket(int $uid): ?array {
    $stmt = db()->prepare(
        "SELECT * FROM queue_tickets
         WHERE user_id = ? AND status IN ('waiting','called')
         ORDER BY booked_at DESC LIMIT 1"
    );
    $stmt->execute([$uid]);
    $t = $stmt->fetch();
    return $t ?: null;
}

/** 1-based position of a waiting ticket; 0 if not waiting. */
function queue_position(array $t): int {
    if ($t['status'] !== 'waiting') return 0;
    foreach (waiting_tickets() as $i => $w) {
        if ($w['id'] === $t['id']) return $i + 1;
    }
    return 0;
}

/** Estimated minutes until service, based on per-service average durations. */
function est_minutes(array $t): int {
    $svc = db()->prepare('SELECT avg_minutes FROM services WHERE id = ?');
    $svc->execute([$t['service_id']]);
    $own = (int)$svc->fetchColumn();
    if ($t['status'] === 'called') return 0;
    $ahead = array_slice(waiting_tickets(), 0, max(0, queue_position($t) - 1));
    $mins = 0;
    foreach ($ahead as $w) {
        $s = db()->prepare('SELECT avg_minutes FROM services WHERE id = ?');
        $s->execute([$w['service_id']]);
        $mins += (int)$s->fetchColumn();
    }
    return $mins + $own;
}

function add_notification(int $uid, string $title, string $message): void {
    $stmt = db()->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
    $stmt->execute([$uid, $title, $message]);
}

function latest_announcement(): string {
    return (string)db()->query('SELECT message FROM announcements ORDER BY id DESC LIMIT 1')->fetchColumn();
}

/** Auto-archive stale tickets (recommendation: daily auto-reset). */
function archive_stale_tickets(): void {
    db()->query(
        "UPDATE queue_tickets SET status = 'no-show'
         WHERE status IN ('waiting','called') AND DATE(booked_at) < CURDATE()"
    );
}
