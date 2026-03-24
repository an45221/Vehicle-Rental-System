<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$id = (int)$_GET['id'];

// FETCH VEHICLE
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Vehicle not found");
}

$vehicle = $result->fetch_assoc();
$message = "";

// UPDATE VEHICLE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $vehicle_name = $_POST['vehicle_name'];
    $vehicle_type = $_POST['vehicle_type'];
    $seats        = $_POST['seats'];
    $fuel_type    = $_POST['fuel_type'];
    $transmission = $_POST['transmission'];
    $price        = $_POST['price'];
    $status       = $_POST['status'];

    // IMAGE UPDATE (OPTIONAL)
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../images/" . $image_name);
        $image_path = "images/" . $image_name;
    } else {
        $image_path = $vehicle['image'];
    }

    $stmt = $conn->prepare("
        UPDATE vehicles 
        SET vehicle_name=?, vehicle_type=?, seats=?, fuel_type=?, transmission=?, price=?, image=?, status=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "ssississi",
        $vehicle_name,
        $vehicle_type,
        $seats,
        $fuel_type,
        $transmission,
        $price,
        $image_path,
        $status,
        $id
    );

    if ($stmt->execute()) {
        $message = "Vehicle updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Vehicle</title>
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
        img {
            width: 100%;
            border-radius: 5px;
            margin-bottom: 10px;
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
    <h2>Edit Vehicle</h2>

    <?php if ($message): ?>
        <p class="msg"><?= $message; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="vehicle_name" value="<?= htmlspecialchars($vehicle['vehicle_name']); ?>" required>

        <select name="vehicle_type">
            <option <?= $vehicle['vehicle_type']=='SUV'?'selected':''; ?>>SUV</option>
            <option <?= $vehicle['vehicle_type']=='Sedan'?'selected':''; ?>>Sedan</option>
            <option <?= $vehicle['vehicle_type']=='Hatchback'?'selected':''; ?>>Hatchback</option>
        </select>

        <input type="number" name="seats" value="<?= $vehicle['seats']; ?>" required>

        <select name="fuel_type">
            <option <?= $vehicle['fuel_type']=='Petrol'?'selected':''; ?>>Petrol</option>
            <option <?= $vehicle['fuel_type']=='Diesel'?'selected':''; ?>>Diesel</option>
            <option <?= $vehicle['fuel_type']=='Electric'?'selected':''; ?>>Electric</option>
        </select>

        <select name="transmission">
            <option <?= $vehicle['transmission']=='Automatic'?'selected':''; ?>>Automatic</option>
            <option <?= $vehicle['transmission']=='Manual'?'selected':''; ?>>Manual</option>
        </select>

        <input type="number" name="price" value="<?= $vehicle['price']; ?>" required>

        <select name="status">
            <option value="available" <?= $vehicle['status']=='available'?'selected':''; ?>>Available</option>
            <option value="unavailable" <?= $vehicle['status']=='unavailable'?'selected':''; ?>>Unavailable</option>
        </select>

        <img src="../<?= $vehicle['image']; ?>">

        <input type="file" name="image">

        <button type="submit">Update Vehicle</button>
    </form>

    <a href="manage_vehicles.php">← Back</a>
</div>

</body>
</html>
