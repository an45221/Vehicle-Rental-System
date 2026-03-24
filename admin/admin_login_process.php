<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_login.php");
    exit;
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

$stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();

    if (password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header("Location: admin_dashboard.php");
        exit;
    }
}

$_SESSION['flash'] = "Invalid admin credentials!";
header("Location: admin_login.php");
exit;
