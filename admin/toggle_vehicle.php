<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$id = (int)$_GET['id'];

$res = $conn->query("SELECT status FROM vehicles WHERE id = $id");
$row = $res->fetch_assoc();

$newStatus = ($row['status'] === 'available') 
            ? 'unavailable' 
            : 'available';

$stmt = $conn->prepare("
    UPDATE vehicles SET status = ? WHERE id = ?
");
$stmt->bind_param("si", $newStatus, $id);
$stmt->execute();

header("Location: manage_vehicles.php");
exit;
