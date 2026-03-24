<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

if (!isset($_SESSION['admin_logged_in'])) exit;

$id = (int)$_GET['id'];
$conn->query("DELETE FROM users WHERE id = $id");

header("Location: manage_users.php");
?>