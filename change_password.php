<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';

$user_id = $_SESSION['user_id'];
$current = $_POST['current_password'];
$new = $_POST['new_password'];

$result = mysqli_query($conn, "SELECT password FROM users WHERE id=$user_id");
$row = mysqli_fetch_assoc($result);

if (!password_verify($current, $row['password'])) {
    die("❌ Current password is incorrect");
}

$new_hash = password_hash($new, PASSWORD_DEFAULT);
mysqli_query($conn, "UPDATE users SET password='$new_hash' WHERE id=$user_id");

header("Location: profile.php");
exit;
