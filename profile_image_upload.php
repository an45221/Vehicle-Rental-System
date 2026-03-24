<?php
session_cache_limiter('private_no_expire');
session_start();

$targetDir = "uploads/";
$fileName = time() . "_" . basename($_FILES["profile_image"]["name"]);
$targetFile = $targetDir . $fileName;

move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile);

$_SESSION['profile_image'] = $targetFile;
header("Location: profile.php");
?>