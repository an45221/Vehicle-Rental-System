<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';

$user_id = $_SESSION['user_id'];
$fullname = $_POST['fullname'];
$username = $_POST['username'];

$sql = "UPDATE users SET fullname=?, username=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $fullname, $username, $user_id);
$stmt->execute();

header("Location: profile.php");
exit;
