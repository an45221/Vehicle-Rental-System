<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';

/* 🔐 LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* 🚫 BLOCK CHECK */
$sql = "SELECT status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user['status'] === 'blocked') {
    session_destroy();
    echo "<script>
        alert('Your account has been blocked by admin.');
        window.location.href='login.php';
    </script>";
    exit;
}
