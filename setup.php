<?php
require 'config.php';
$count=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE username='admin'")->fetchColumn();
if($count===0){
  $s=$pdo->prepare("INSERT INTO users(full_name,username,password,role,status) VALUES(?,?,?,'admin','approved')");
  $s->execute(['System Administrator','admin',password_hash('admin123',PASSWORD_DEFAULT)]);
  echo '<h2>Setup complete!</h2><p>Admin account created.</p><p>Username: <b>admin</b><br>Password: <b>admin123</b></p><p><a href="index.php">Go to login</a></p><p><b>Important:</b> Delete setup.php after setup and change the admin password by updating the account through a proper password-management feature before any real deployment.</p>';
} else {
  echo 'Admin account already exists. <a href="index.php">Go to login</a>.';
}
