<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';
require 'booking_helper.php';
require 'email_helper.php';

/* ======================
   BASIC SAFETY CHECK
   ====================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// If request is POST, process payment then redirect (Post/Redirect/Get)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount  = $_POST['amount'] ?? 0;
    $method  = $_POST['method'] ?? 'Unknown';
    $remarks = $_POST['remarks'] ?? '';
    $booking_id = $_POST['booking_id'] ?? null;

    if (!$booking_id) {
        die("Booking ID not found");
    }

    $booking_id = (int) $booking_id;

    // Fetch booking to ensure ownership
    $get_booking = $conn->prepare(
        "SELECT b.*, v.vehicle_name, v.id as vehicle_id
         FROM bookings b
         LEFT JOIN vehicles v ON b.vehicle_id = v.id
         WHERE b.id = ? AND b.user_id = ?"
    );

    if (!$get_booking) {
        die("Database error: " . $conn->error);
    }

    $get_booking->bind_param("ii", $booking_id, $user_id);
    $get_booking->execute();
    $booking = $get_booking->get_result()->fetch_assoc();
    $get_booking->close();

    if (!$booking) {
        die("Booking not found");
    }

    // Update booking to Paid + Booked
    $update = $conn->prepare(
        "UPDATE bookings 
         SET payment_status = 'Paid', 
            booking_status = 'Booked'
         WHERE id = ? AND user_id = ?"
    );

    if (!$update) {
        die("Database error: " . $conn->error);
    }

    $update->bind_param("ii", $booking_id, $user_id);
    if (!$update->execute()) {
        die("Payment update failed: " . $update->error);
    }
    $update->close();

    // Generate transaction id and timestamp and store a flash in session
    $transaction_id = "GR-" . rand(100000, 999999);
    $date = date("d M Y, h:i A");

    $_SESSION['payment_flash'] = [
        'booking_id' => $booking_id,
        'transaction_id' => $transaction_id,
        'date' => $date,
        'amount' => $amount,
        'method' => $method,
        'remarks' => $remarks
    ];

    // Send confirmation email
    $email_sent = sendBookingConfirmationEmail($conn, $user_id, $booking_id, $_SESSION['payment_flash']);
    $_SESSION['payment_flash']['email_sent'] = $email_sent;

    // Optional: clear search session
    unset(
        $_SESSION['pickup'],
        $_SESSION['drop'],
        $_SESSION['pickup_date'],
        $_SESSION['pickup_time'],
        $_SESSION['return_date'],
        $_SESSION['return_time']
    );

    // Redirect to GET version to avoid browser "Document Expired" on back
    header('Location: payment_success.php?booking_id=' . $booking_id);
    exit;
}

// If GET, show the payment success page. Try to read flash data if available.
$flash = $_SESSION['payment_flash'] ?? null;
$booking_id = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : ($flash['booking_id'] ?? null);

if (!$booking_id) {
    die('Booking ID not specified');
}

// Fetch booking to display
$get_booking = $conn->prepare(
    "SELECT b.*, v.vehicle_name, v.id as vehicle_id
     FROM bookings b
     LEFT JOIN vehicles v ON b.vehicle_id = v.id
     WHERE b.id = ? AND b.user_id = ?"
);

if (!$get_booking) {
    die("Database error: " . $conn->error);
}

$get_booking->bind_param("ii", $booking_id, $user_id);
$get_booking->execute();
$booking = $get_booking->get_result()->fetch_assoc();
$get_booking->close();

if (!$booking) {
    die("Booking not found");
}

// Prefer flash values for transaction details, fallback to defaults
$transaction_id = $flash['transaction_id'] ?? ('GR-' . rand(100000, 999999));
$date = $flash['date'] ?? date("d M Y, h:i A");
$amount = $flash['amount'] ?? $booking['price'];
$method = $flash['method'] ?? 'Unknown';
$remarks = $flash['remarks'] ?? '';
$email_sent = $flash['email_sent'] ?? false;

// Clear flash so refreshing won't repeat
if ($flash) {
    unset($_SESSION['payment_flash']);
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Payment Successful</title>

<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: #eef3f0;
}

/* SUCCESS CARD */
.success-box {
    max-width: 600px;
    margin: 80px auto;
    background: white;
    border-radius: 14px;
    padding: 35px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    text-align: center;
}

/* ICON */
.check {
    width: 90px;
    height: 90px;
    background: #28a745;
    color: white;
    font-size: 50px;
    border-radius: 50%;
    line-height: 90px;
    margin: 0 auto 20px;
}

/* TEXT */
.success-box h2 {
    color: #28a745;
    margin-bottom: 10px;
    font-size: 28px;
}

.success-box p {
    color: #666;
    font-size: 14px;
}

/* EMAIL NOTIFICATION */
.email-notification {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    padding: 12px;
    margin: 20px 0;
    font-size: 13px;
    color: #155724;
}

.email-notification.error {
    background: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

/* DETAILS */
.summary {
    margin-top: 30px;
    text-align: left;
    background: #f7f9f8;
    padding: 20px;
    border-radius: 10px;
}

.summary div {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
    border-bottom: 1px solid #e0e0e0;
}

.summary div:last-child {
    border-bottom: none;
}

.summary div span:last-child {
    font-weight: 600;
    color: #333;
}

/* BUTTON */
.home-btn {
    margin-top: 30px;
    padding: 12px 30px;
    background: #1f8f4c;
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: bold;
    display: inline-block;
    transition: background 0.3s;
}

.home-btn:hover {
    background: #187a3d;
}
</style>
</head>

<body>

<div class="success-box">
    <div class="check">✔</div>
    <h2>Payment Successful</h2>
    <p>Your booking has been confirmed and is now BOOKED</p>

    <?php if ($email_sent): ?>
        <div class="email-notification">
            ✓ A confirmation email has been sent to your registered email address.
        </div>
    <?php else: ?>
        <div class="email-notification error">
            ⚠ We couldn't send the confirmation email, but your booking is confirmed. You can view details in "My Bookings".
        </div>
    <?php endif; ?>

    <div class="summary">
        <div><span>Vehicle</span><span><?= htmlspecialchars($booking['vehicle_name'] ?? 'Unknown Vehicle') ?></span></div>
        <div><span>Pickup Location</span><span><?= htmlspecialchars($booking['pickup'] ?? '') ?></span></div>
        <div><span>Drop Location</span><span><?= htmlspecialchars($booking['drop_location'] ?? '') ?></span></div>
        <div><span>Pickup Date</span><span><?= date('d M Y', strtotime($booking['pickup_date'])) ?></span></div>
        <div><span>Return Date</span><span><?= date('d M Y', strtotime($booking['return_date'])) ?></span></div>
        <div><span>Payment Method</span><span><?= htmlspecialchars($method) ?></span></div>
        <div><span>Amount Paid</span><span>NPR <?= number_format($amount, 2) ?></span></div>
        <div><span>Transaction ID</span><span><?= $transaction_id ?></span></div>
        <div><span>Booking ID</span><span>SCPL-<?= $booking_id ?></span></div>
    </div>

    <div style="display: flex; justify-content: center; gap: 15px; margin-top: 30px;">
        <a href="mybooking.php" class="home-btn">View My Bookings</a>
        <a href="home.php" class="home-btn" style="background: #0d6efd;">Back to Home</a>
    </div>
</div>

</body>
</html>

