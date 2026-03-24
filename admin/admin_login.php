<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Go Rent - Admin Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="form-wrapper">

    <div class="form-logo-box">
        <img src="../images/smallcarlogo.png" class="form-logo-img">
        <h3 class="form-logo-text">Admin <span>SIGN IN</span></h3>
    </div>

<?php
    session_cache_limiter('private_no_expire');
    session_start();
    if(isset($_SESSION['flash'])) 
        {
        echo "<p style='color:red;'>".$_SESSION['flash']."</p>";
        unset($_SESSION['flash']);
    }
?>

    <form method="POST" class="form" action="admin_login_process.php">

        <label class="form-label">Username</label>
        <input type="text" class="form-input" name="username" required>

        <label class="form-label">Password</label>
            <div class="form-password-container">     
                <input type="password" class="form-input pass-field" name="password" required>
                <img src="https://cdn-icons-png.flaticon.com/512/709/709612.png" class="form-eye-icon">
            </div>

        <button type="submit" class="form-btn">Login</button>

    </form>

</div>

</body>
</html>

