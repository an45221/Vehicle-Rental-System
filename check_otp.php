<?php
include 'config.php';

$email = $_POST['email'];
$otp = $_POST['otp'];

$sql = "SELECT * FROM users
        WHERE email=?
        AND reset_code=?
        AND reset_expiry > NOW()";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    header("Location: reset_password.php?email=$email");
    exit();
} else {
    echo "Invalid or expired OTP.";
}
?>
