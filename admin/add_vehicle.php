<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $vehicle_name  = $_POST['vehicle_name'];
    $vehicle_type  = $_POST['vehicle_type'];
    $seats         = $_POST['seats'];
    $fuel_type     = $_POST['fuel_type'];
    $transmission  = $_POST['transmission'];
    $price         = $_POST['price'];

    // IMAGE UPLOAD
    $image_name = time() . "_" . $_FILES['image']['name'];
    $target = "../images/" . $image_name;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {

        $image_path = "images/" . $image_name;
        $status = "available";

        $stmt = $conn->prepare("
            INSERT INTO vehicles
            (vehicle_name, vehicle_type, seats, fuel_type, transmission, price, image, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssississ",
            $vehicle_name,
            $vehicle_type,
            $seats,
            $fuel_type,
            $transmission,
            $price,
            $image_path,
            $status
        );

        if ($stmt->execute()) {
            $message = "Vehicle added successfully!";
        } else {
            $message = "Database error!";
        }
    } else {
        $message = "Image upload failed!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Vehicle</title>
    <style>
        body { background:#f4f6f8; font-family:Arial; }
        .container {
            width: 450px;
            margin: 40px auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
        }
        h2 { text-align:center; color:#0d6efd; }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #0d6efd;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
        }
        .msg { text-align:center; margin-bottom:10px; color:green; }
        a { display:block; text-align:center; margin-top:10px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Add Vehicle</h2>

    <?php if ($message): ?>
        <p class="msg"><?= $message; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="vehicle_name" placeholder="Vehicle Name" required>

        <select name="vehicle_type" required>
            <option value="">Select Type</option>
            <option>SUV</option>
            <option>Sedan</option>
            <option>Hatchback</option>
        </select>

        <input type="number" name="seats" placeholder="Seats" required>

        <select name="fuel_type" required>
            <option>Petrol</option>
            <option>Diesel</option>
            <option>Electric</option>
        </select>

        <select name="transmission" required>
            <option>Automatic</option>
            <option>Manual</option>
        </select>

        <input type="number" name="price" placeholder="Price per day" required>

        <input type="file" name="image" required>

        <button type="submit">Add Vehicle</button>
    </form>

    <a href="manage_vehicles.php">← Back to Manage Vehicles</a>
</div>

</body>
</html>
