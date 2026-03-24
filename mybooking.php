<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';
require 'booking_helper.php';
require 'review_helper.php';

// 🔐 USER AUTH
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Clean up expired bookings
cleanupExpiredBookings($conn, 30); // 30 minute expiration

// 📌 FETCH USER BOOKINGS + VEHICLE NAME
$sql = "
    SELECT b.*, COALESCE(v.vehicle_name, b.vehicle_name) AS vehicle_name
    FROM bookings b
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
";
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$status = $stmt->get_result()->fetch_assoc()['status'];

if ($status === 'blocked') {
    session_destroy();
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
    <style>
        /* My Bookings - page scoped styles */
        :root{
            --bg: #f6f8fa;
            --card: #ffffff;
            --muted: #6c757d;
            --accent: #1f8f4c;
            --danger: #dc3545;
            --primary: #0d6efd;
            --shadow: rgba(16,24,40,0.06);
        }

        body {
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 28px 18px;
            color: #243240;
        }

        h2 {
            text-align: center;
            margin: 0 0 22px 0;
            font-size: 22px;
            letter-spacing: 0.2px;
        }

        .booking-card {
            background: var(--card);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 16px;
            box-shadow: 0 6px 20px var(--shadow);
            border: 1px solid #e9eef3;
        }

        .booking-content {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 18px;
            align-items: start;
        }

        @media (max-width: 740px) {
            .booking-content { grid-template-columns: 1fr; }
        }

        .booking-card strong { display:block; font-size:16px; margin-bottom:8px; }

        .booking-info p {
            margin: 6px 0;
            font-size: 14px;
            color: #394e5a;
        }

        .booking-meta p { margin: 6px 0; font-size: 14px; }

        .actions { margin-top: 12px; display:flex; gap:10px; flex-wrap:wrap; }

        .btn {
            padding: 9px 14px;
            border-radius: 8px;
            text-decoration: none;
            color: #fff;
            font-size: 13px;
            border: none;
            cursor: pointer;
            letter-spacing: 0.2px;
        }

        .btn-cancel { background: var(--danger); }
        .btn-pay { background: var(--accent); }
        .btn-review { background: #ff8c00; }
        .btn-secondary { background: #6c757d; color: #fff; }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }
        .badge-paid { background: #28a745; }
        .badge-unpaid { background: #e74c3c; }
        .badge-active { background: var(--primary); }
        .badge-cancelled { background: var(--muted); opacity: 0.95; }
        
        /* Status badge classes matching HTML */
        .status-paid { display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 13px; font-weight: 600; color: #fff; background: #28a745; }
        .status-unpaid { display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 13px; font-weight: 600; color: #fff; background: #e74c3c; }
        .status-active { display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 13px; font-weight: 600; color: #fff; background: var(--primary); }
        .status-cancelled { display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 13px; font-weight: 600; color: #fff; background: var(--muted); opacity: 0.95; }
        .status-confirmed { display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 13px; font-weight: 600; color: #fff; background: #17a2b8; }
        .status-booked { display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 13px; font-weight: 600; color: #fff; background: #17a2b8; }

        .summary-row { display:flex; justify-content:space-between; gap:12px; padding:8px 0; }
        .summary-row span:first-child { color: #55636a; }

        /* Modal */
        .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:999; align-items:center; justify-content:center; }
        .modal-box { background:#fff; padding:20px; width:420px; max-width:92%; border-radius:10px; box-shadow:0 10px 30px rgba(14,20,30,0.12); }
        .modal-box textarea { width:100%; min-height:90px; padding:10px; border:1px solid #e3e7ea; border-radius:8px; resize:vertical; }
        .modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:12px; }

        /* small utilities */
        .muted { color: var(--muted); font-size:13px; }
    </style>
</head>
<body>
    <h2>My Bookings</h2>

    <?php if ($result->num_rows == 0): ?>
        <p style="text-align: center;">No bookings found.</p>
    <?php endif; ?>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="booking-card">
            <strong>Vehicle: <?= htmlspecialchars($row['vehicle_name']) ?></strong>
            <div class="booking-content">
                <!-- LEFT -->
                <div>
                    <p><strong>Booking ID:</strong> SCPL-<?= $row['id'] ?></p>
                    <p><strong>Start Date:</strong> <?= $row['pickup_date'] ?></p>
                    <p><strong>End Date:</strong> <?= $row['return_date'] ?></p>
                    <p><strong>Total Amount:</strong> NPR <?= number_format($row['price'], 2) ?></p>
                    <div class="actions">
                        <?php if ($row['booking_status'] === 'Active'): ?>
                            <button class="btn btn-cancel" onclick="openCancelModal(<?= $row['id'] ?>)">
                                CANCEL
                            </button>
                        <?php endif; ?>

                        <?php if ($row['payment_status'] === 'Unpaid' && $row['booking_status'] === 'Active'): ?>
                            <a class="btn btn-pay" href="pay.php?booking_id=<?= $row['id'] ?>">
                                PAY
                            </a>
                        <?php endif; ?>

                        <?php 
                        // Show review button for completed/cancelled bookings
                        if (in_array($row['booking_status'], ['Completed', 'Cancelled']) && $row['payment_status'] === 'Paid'): 
                            $has_review = hasUserReviewedBooking($conn, $row['id']);
                        ?>
                            <a class="btn btn-review" href="submit_review.php?booking_id=<?= $row['id'] ?>" title="<?= $has_review ? 'View your review' : 'Write a review' ?>">
                                <?= $has_review ? '⭐ VIEW REVIEW' : '⭐ WRITE REVIEW' ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- RIGHT -->
                <div>
                    <p><strong>From:</strong> <?= htmlspecialchars($row['pickup']) ?></p>
                    <p><strong>To:</strong> <?= htmlspecialchars($row['drop_location']) ?></p>
                    <p>
                        <strong>Payment Status:</strong>
                        <span class="<?= $row['payment_status'] === 'Paid' ? 'status-paid' : 'status-unpaid' ?>">
                            <?= $row['payment_status'] ?>
                        </span>
                    </p>
                    <p>
                        <strong>Booking Status:</strong>
                        <span class="<?php 
                            if ($row['booking_status'] === 'Cancelled') {
                                echo 'status-cancelled';
                            } elseif ($row['booking_status'] === 'Confirmed' || $row['booking_status'] === 'Booked') {
                                // Treat both 'Confirmed' and 'Booked' as the same success-state badge
                                echo $row['booking_status'] === 'Booked' ? 'status-booked' : 'status-confirmed';
                            } else {
                                echo 'status-active';
                            }
                        ?>">
                            <?= $row['booking_status'] ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    <?php endwhile; ?>

    <!-- CANCEL MODAL -->
    <div id="cancelModal" class="modal">
        <div class="modal-box">
            <p><strong>Are you sure you want to cancel this booking?</strong></p>
            <form method="POST" action="cancel_booking.php">
                <input type="hidden" name="booking_id" id="cancelBookingId">
                <textarea name="cancel_reason" placeholder="Reason for cancellation..." required></textarea>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">No</button>
                    <button type="submit" class="btn btn-cancel">Yes, Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCancelModal(id) {
            document.getElementById('cancelBookingId').value = id;
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }
    </script>
</body>
</html>