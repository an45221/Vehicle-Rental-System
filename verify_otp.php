<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
</head>
<body>

<h2>Verify OTP</h2>

<form action="check_otp.php" method="POST">
    <input type="hidden" name="email" value="<?php echo $_GET['email']; ?>">

    <label>Enter OTP</label><br>
    <input type="text" name="otp" required><br><br>

    <button type="submit">Verify</button>
</form>

</body>
</html>
