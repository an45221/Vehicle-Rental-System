<?php
session_cache_limiter('private_no_expire');
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM bookings WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        .booking-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status {
            font-weight: 600;
        }
        .paid {
            color: green;
        }
        .unpaid {
            color: red;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .pay-btn {
            background: #28a745;
            color: white;
        }
        .cancel-btn {
            background: #dc3545;
            color: white;
        }
    </style>
</head>

<body style="background:#f4f4f4;">

<div style="width:80%; margin:100px auto;">
    <h2>Booking History</h2>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <div class="booking-card">
            <h3><?= $row['vehicle_name'] ?></h3>

            <p><strong>Pickup:</strong> <?= $row['pickup'] ?></p>
            <p><strong>Drop:</strong> <?= $row['drop_location'] ?></p>
            <p><strong>Pickup Date:</strong> <?= $row['pickup_date'] ?></p>
            <p><strong>Return Date:</strong> <?= $row['return_date'] ?></p>
            <p><strong>Price:</strong> NPR <?= $row['price'] ?></p>

            <p class="status">
                Payment Status:
                <span class="<?= strtolower($row['payment_status']) ?>">
                    <?= $row['payment_status'] ?>
                </span>
            </p>

            <!-- ACTION BUTTONS -->
            <?php if ($row['payment_status'] == 'Unpaid') { ?>
                <a href="pay.php?booking_id=<?= $row['id'] ?>">
                    <button class="btn pay-btn">Pay</button>
                </a>
            <?php } ?>

            <?php if ($row['booking_status'] == 'Active') { ?>
                <a href="cancel_booking.php?id=<?= $row['id'] ?>">
                    <button class="btn cancel-btn">Cancel</button>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
</div>

</body>
</html>

