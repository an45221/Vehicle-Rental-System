<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

if (!isset($_SESSION['admin_logged_in'])) exit;

$id = (int)$_GET['id'];

$conn->query("
    UPDATE users 
    SET status = IF(status='active','blocked','active') 
    WHERE id = $id
");

header("Location: manage_user.php");
?>
