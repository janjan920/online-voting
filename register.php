<?php
require 'config.php';
$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $name=trim($_POST['full_name']??''); $username=trim($_POST['username']??''); $password=$_POST['password']??'';
    if ($name==='' || $username==='' || strlen($password)<6) $error='Complete all fields. Password must be at least 6 characters.';
    else {
        try {
            $s=$pdo->prepare("INSERT INTO users(full_name,username,password,role,status) VALUES(?,?,?,'voter','pending')");
            $s->execute([$name,$username,password_hash($password,PASSWORD_DEFAULT)]);
            $success='Registration submitted. An administrator must approve your voter account before you can log in.';
        } catch(PDOException $e) { $error='That username is already taken.'; }
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Voter registration</title><link rel="stylesheet" href="style.css"></head><body><div class="auth"><h1>Voter registration</h1>
<?php if($error):?><div class="alert"><?=e($error)?></div><?php endif;?>
<?php if($success):?><div class="success"><?=e($success)?></div><?php endif;?>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<label>Full name</label><input name="full_name" required>
<label>Username</label><input name="username" required>
<label>Password (minimum 6 characters)</label><input type="password" name="password" minlength="6" required>
<button class="full">Submit registration</button></form><p class="center"><a href="index.php">Back to login</a></p></div></body></html>
