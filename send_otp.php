<?php
include 'config.php';

$email = $_POST['email'];

$sql = "SELECT id FROM users WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Email not found. <a href='forgot_password.php'>Try again</a>");
}

$otp = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

$update = "UPDATE users SET reset_code=?, reset_expiry=? WHERE email=?";
$stmt2 = $conn->prepare($update);
$stmt2->bind_param("sss", $otp, $expiry, $email);
$stmt2->execute();

/*
COLLEGE MODE (no email):
Show OTP directly
*/
echo "
OTP sent successfully.<br>
<b>Your OTP:</b> $otp <br>
<a href='verify_otp.php?email=$email'>Verify OTP</a>
";
?>
