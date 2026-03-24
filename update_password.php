<?php
include 'config.php';

$email = $_POST['email'];
$pass = $_POST['password'];
$confirm = $_POST['confirm'];

if ($pass !== $confirm) {
    die("Passwords do not match");
}

$hashed = password_hash($pass, PASSWORD_DEFAULT);

$sql = "UPDATE users 
        SET password=?, reset_code=NULL, reset_expiry=NULL 
        WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hashed, $email);
$stmt->execute();

echo "
Password updated successfully.<br>
<a href='login.html'>Go to Login</a>
";
?>
