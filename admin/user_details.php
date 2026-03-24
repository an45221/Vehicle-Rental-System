<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

/* 🔐 ADMIN AUTH */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

/* ✅ VALIDATE ID */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID");
}

$user_id = (int) $_GET['id'];

/* 📌 FETCH USER */
$sql = "SELECT id, fullname, username, email, status 
        FROM users 
        WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found");
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Details</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f6f8;
        }
        .container {
            width: 450px;
            margin: 40px auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            color: #0d6efd;
        }
        p {
            font-size: 15px;
            margin: 10px 0;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 5px;
            color: white;
            font-size: 13px;
        }
        .active { background: #28a745; }
        .blocked { background: #dc3545; }

        a {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #0d6efd;
        }
    </style>
</head>

<body>

<div class="container">
    <h2>User Details</h2>

    <p><strong>ID:</strong> <?= $user['id']; ?></p>
    <p><strong>Full Name:</strong> <?= htmlspecialchars($user['fullname']); ?></p>
    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>

    <p>
        <strong>Status:</strong>
        <span class="badge <?= $user['status']; ?>">
            <?= ucfirst($user['status']); ?>
        </span>
    </p>

    <a href="manage_user.php">← Back to Manage Users</a>
</div>

</body>
</html>
