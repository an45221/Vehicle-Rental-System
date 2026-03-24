<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';

/* 🔐 LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* 📌 FETCH USER DATA */
$stmt = $conn->prepare("SELECT fullname, username, email, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$profileImage = $user['profile_image'] ?: 'images/profile.png';
?>
<!DOCTYPE html>
<html>
<head>
<title>My Profile</title>

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: #f1f4f9;
}

.dashboard {
    max-width: 1100px;
    margin: 40px auto;
    display: flex;
    gap: 25px;
}

/* SIDEBAR */
.sidebar {
    width: 260px;
    background: white;
    border-radius: 14px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,.05);
}

.sidebar img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #0d6efd;
}

.sidebar h3 {
    margin: 10px 0 5px;
}

.sidebar p {
    font-size: 14px;
    color: #777;
}

/* CONTENT */
.content {
    flex: 1;
    background: white;
    border-radius: 14px;
    padding: 30px;
    box-shadow: 0 10px 25px rgba(0,0,0,.05);
}

.content h2 {
    margin-top: 0;
}

/* FORM */
label {
    display: block;
    margin-top: 18px;
    font-weight: 600;
}

input {
    width: 100%;
    padding: 11px;
    margin-top: 6px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

input[readonly] {
    background: #f0f0f0;
}

.profile-img-lg {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 10px;
}

/* BUTTONS */
.btn {
    padding: 10px 18px;
    border-radius: 8px;
    border: none;
    color: white;
    cursor: pointer;
    margin-top: 15px;
    font-size: 14px;
}

.edit { background: #0d6efd; }
.save { background: #28a745; display:none; }
.cancel { background: #dc3545; display:none; }

@media (max-width: 900px) {
    .dashboard { flex-direction: column; }
    .sidebar { width: 100%; }
}
</style>
</head>

<body>

<div class="dashboard">

<!-- SIDEBAR -->
<div class="sidebar">
    <img src="<?= $profileImage ?>">
    <h3><?= htmlspecialchars($user['username']) ?></h3>
    <p><?= htmlspecialchars($user['email']) ?></p>
</div>

<!-- MAIN CONTENT -->
<div class="content">
<h2>My Profile</h2>

<form action="upload_profile.php" method="POST" enctype="multipart/form-data" id="profileForm">

    <img src="<?= $profileImage ?>" class="profile-img-lg">

    <label>Profile Photo</label>
    <input type="file" name="profile_image" accept="image/*">

    <label>Full Name</label>
    <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" readonly>

    <label>Username</label>
    <input type="text" value="<?= htmlspecialchars($user['username']) ?>" readonly>

    <label>Email</label>
    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>

    <button type="button" class="btn edit" onclick="enableEdit()">Edit Profile</button>
    <button type="submit" class="btn save">Save</button>
    <button type="button" class="btn cancel" onclick="location.reload()">Cancel</button>

</form>
</div>

</div>

<script>
function enableEdit() {
    document.querySelector("input[name='fullname']").removeAttribute("readonly");
    document.querySelector(".edit").style.display = "none";
    document.querySelector(".save").style.display = "inline-block";
    document.querySelector(".cancel").style.display = "inline-block";
}
</script>

</body>
</html>
