<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST['username'], $_POST['password'])) {
        die("Invalid request");
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        /* 🚫 STEP 1: CHECK IF USER IS BLOCKED */
        if ($user['status'] === 'blocked') {
            echo "<script>
                alert('❌ Your account is blocked. Please contact admin.');
                window.location.href='login.html';
            </script>";
            exit;
        }

        /* 🔐 STEP 2: CHECK PASSWORD */
        if (password_verify($password, $user['password'])) {
            session_cache_limiter('private_no_expire');
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];

            header("Location: home.php");
            exit;
        } else {
            echo "<script>alert('Invalid password'); window.location.href='login.html';</script>";
        }
    } else {
        echo "<script>alert('User not found'); window.location.href='login.html';</script>";
    }
}
?>


