<?php
require 'config.php';
if (!empty($_SESSION['user'])) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
    $stmt->execute([trim($_POST['username'] ?? '')]);
    $u = $stmt->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        if ($u['status'] !== 'approved') {
            $error = 'Your account is not approved yet. Contact the election administrator.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id'=>$u['id'], 'name'=>$u['full_name'], 'role'=>$u['role']];
            redirect('dashboard.php');
        }
    } else $error = 'Incorrect username or password.';
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Online Voting System</title><link rel="stylesheet" href="style.css"></head><body>
<div class="auth"><div class="brand">🗳️ <span>Online Voting System</span></div><h1>Sign in</h1>
<p class="muted">Secure classroom election demo</p>
<?php if($error): ?><div class="alert"><?=e($error)?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<label>Username</label><input name="username" required autocomplete="username">
<label>Password</label><input type="password" name="password" required autocomplete="current-password">
<button class="full">Log in</button></form>
<p class="center">New voter? <a href="register.php">Create an account</a></p>
<p class="tiny center">For learning and classroom demonstrations only.</p></div></body></html>
