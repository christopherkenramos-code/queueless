<?php
require_once __DIR__ . '/includes/bootstrap.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Allow typing 'admin', 'staff', or 'patient' directly, as well as full email
    if ($input === 'admin') {
        $stmt = db()->prepare("SELECT * FROM users WHERE email IN ('admin@mho-naguilian.gov.ph', 'admin@queueless.com') LIMIT 1");
        $stmt->execute();
    } elseif ($input === 'staff') {
        $stmt = db()->prepare("SELECT * FROM users WHERE email = 'staff@mho-naguilian.gov.ph' LIMIT 1");
        $stmt->execute();
    } elseif ($input === 'patient') {
        $stmt = db()->prepare("SELECT * FROM users WHERE email = 'patient@email.com' LIMIT 1");
        $stmt->execute();
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$input]);
    }
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = $user['id'];
        header('Location: ' . (in_array($user['role'], ['staff', 'admin']) ? 'staff/dashboard.php' : 'patient/dashboard.php'));
        exit;
    }
    $error = 'Invalid email or password.';
}
$pageTitle = 'Sign in — QueueLess';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h2>Welcome back 🌿</h2>
    <p>Sign in to book, track, or manage the queue.</p>
    <?php if ($error): ?><p class="alert err"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
      <div class="field"><label>Email</label><input name="email" type="email" required placeholder="e.g. patient@email.com"></div>
      <div class="field"><label>Password</label><input name="password" type="password" required placeholder="••••••••"></div>
      <button class="btn btn-primary" style="width:100%;justify-content:center" type="submit">Sign In</button>
    </form>
    <p class="auth-switch">No account yet? <a href="register.php">Register</a></p>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
