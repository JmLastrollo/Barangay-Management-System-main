<?php
$password = "admin123";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "Password to use in MongoDB: " . $hashed_password . "\n";
?>