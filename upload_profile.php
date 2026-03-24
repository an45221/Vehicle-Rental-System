<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$fullname = trim($_POST['fullname']);

/* IMAGE UPLOAD */
$imagePath = null;

if (!empty($_FILES['profile_image']['name'])) {
    $folder = "uploads/";
    if (!is_dir($folder)) mkdir($folder);

    $filename = time() . "_" . basename($_FILES['profile_image']['name']);
    $target = $folder . $filename;

    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
        $imagePath = $target;
    }
}

/* UPDATE QUERY */
if ($imagePath) {
    $stmt = $conn->prepare("UPDATE users SET fullname = ?, profile_image = ? WHERE id = ?");
    $stmt->bind_param("ssi", $fullname, $imagePath, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET fullname = ? WHERE id = ?");
    $stmt->bind_param("si", $fullname, $user_id);
}

$stmt->execute();
$stmt->close();

header("Location: profile.php");
exit;
