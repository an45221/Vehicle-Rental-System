<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Encrypt password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if username or email already exists
    $check = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $checkResult = mysqli_query($conn, $check);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>alert('Username or email already exists. Try another.'); window.location.href='signup.html';</script>";
        exit();
    }

    // Insert into DB
    $sql = "INSERT INTO users (fullname, username, email, password)
            VALUES ('$fullname', '$username', '$email', '$hashedPassword')";

    if (mysqli_query($conn, $sql)) {
        header("Location: login.html?signup=success");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
