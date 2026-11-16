<?php
require_once __DIR__ . '/includes/bootstrap.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 4) {
        $error = 'Please provide a name, a valid email, and a password of at least 4 characters.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'That email is already registered. Try signing in.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = db()->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'client')");
            $stmt->execute([$name, $email, $hash]);
            $uid = (int)db()->lastInsertId();
            add_notification($uid, 'Account created 🎉', 'Welcome to QueueLess, ' . $name . '!');
            session_regenerate_id(true);
            $_SESSION['uid'] = $uid;
            header('Location: patient/dashboard.php');
            exit;
        }
    }
}
$pageTitle = 'Create account — QueueLess';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h2>Create your account</h2>
    <p>One profile for every MHO Naguilian visit.</p>
    <?php if ($error): ?><p class="alert err"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
      <div class="field"><label>Full Name</label><input name="name" required placeholder="Juan M. Dela Cruz"></div>
      <div class="field"><label>Email</label><input name="email" type="email" required placeholder="you@example.com"></div>
      <div class="field"><label>Password</label><input name="password" type="password" minlength="4" required placeholder="Minimum 4 characters"></div>
      <button class="btn btn-primary" style="width:100%;justify-content:center" type="submit">Create Account</button>
    </form>
    <p class="auth-switch">Already registered? <a href="login.php">Sign in</a></p>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
