<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

/* 🔐 ADMIN AUTH CHECK */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

/* 📌 FETCH BOOKINGS */
$sql = "
SELECT 
    b.id,
    u.username,
    u.email,
    COALESCE(v.vehicle_name, b.vehicle_name, 'Deleted Vehicle') AS vehicle_name,
    b.pickup,
    b.drop_location,
    b.pickup_date,
    b.return_date,
    b.price,
    b.payment_status,
    b.booking_status,
    b.cancel_reason, 
    b.cancelled_at
FROM bookings b
LEFT JOIN users u ON b.user_id = u.id
LEFT JOIN vehicles v ON b.vehicle_id = v.id
ORDER BY b.id DESC
";



$result = $conn->query($sql);

/* ❗ SQL ERROR CHECK */
if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
:root {
    --primary: #1f8f4c;
    --primary-light: #2da85d;
    --blue: #0d6efd;
    --green: #198754;
    --orange: #fd7e14;
    --red: #dc3545;
    --warning: #ffc107;
    --light: #f8fafc;
    --light-bg: #f0f4f8;
    --border: #e0e7ff;
    --text-dark: #1a202c;
    --text-muted: #6b7280;
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
    --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.15);
    --radius: 12px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', 'Segoe UI', sans-serif;
}

body {
    background: linear-gradient(135deg, #f8fafc 0%, #f0f4f8 100%);
    color: var(--text-dark);
    padding: 20px;
    min-height: 100vh;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    background: white;
    padding: 32px;
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border);
}

h2 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 24px;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
}

.export-btn {
    padding: 11px 18px;
    background: linear-gradient(135deg, var(--green), #20c997);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: var(--transition);
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.25);
}

.export-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

table {
    width: 100%;
    border-collapse: collapse;
    overflow-x: auto;
}

th {
    background: linear-gradient(135deg, var(--light-bg), #e9ecef);
    padding: 14px 12px;
    text-align: left;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dark);
    border-bottom: 2px solid var(--border);
    white-space: nowrap;
}

td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    color: var(--text-muted);
    font-size: 13px;
}

tbody tr:hover {
    background: var(--light);
    transition: var(--transition);
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: white;
}

.badge.paid { background: #d1fae5; color: #065f46; }
.badge.unpaid { background: #fed7aa; color: #92400e; }
.badge.active { background: #bfdbfe; color: #1e40af; }
.badge.confirmed { background: #bfdbfe; color: #1e40af; }
.badge.completed { background: #d1fae5; color: #065f46; }
.badge.cancelled { background: #fee2e2; color: #991b1b; }
.badge.pending { background: #fef08a; color: #854d0e; }

a.btn {
    padding: 8px 14px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    transition: var(--transition);
    display: inline-block;
    border: none;
    cursor: pointer;
}

a.btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.approve { background: linear-gradient(135deg, var(--green), #20c997); box-shadow: 0 4px 12px rgba(25, 135, 84, 0.25); }
.cancel-btn { background: linear-gradient(135deg, var(--red), #bd2130); box-shadow: 0 4px 12px rgba(220, 53, 69, 0.25); }

@media (max-width: 768px) {
    .container {
        padding: 16px;
    }

    h2 {
        font-size: 20px;
        margin-bottom: 16px;
    }

    th, td {
        padding: 10px 8px;
        font-size: 12px;
    }

    a.btn {
        padding: 6px 10px;
        font-size: 11px;
    }

    table {
        font-size: 12px;
    }
}
    </style>
</head>

<body>

<div class="container">
    <h2>Manage Bookings</h2>


<div class="header-actions">
    <div></div>
    <a href="export_bookings_csv.php" class="export-btn">
        📊 Export CSV
    </a>
</div>

    <table>
        <thead>
        <tr>
            <th>📋 ID</th>
            <th>👤 User</th>
            <th>🚗 Vehicle</th>
            <th>🏁 Pickup Date</th>
            <th>🔙 Return Date</th>
            <th>📍 Pickup</th>
            <th>📍 Drop</th>
            <th>💰 Amount</th>
            <th>💳 Payment</th>
            <th>✅ Status</th>
            <th>⚠️ Reason</th>
            <th>⚙️ Actions</th>
        </tr>
        </thead>
        <tbody>

        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['username']); ?></td>
            <td><?= htmlspecialchars($row['vehicle_name']); ?></td>
            <td><?= htmlspecialchars($row['pickup']); ?></td>
            <td><?= htmlspecialchars($row['drop_location']); ?></td>
            <td><?= $row['pickup_date']; ?></td>
            <td><?= $row['return_date']; ?></td>
            <td>Rs. <?= number_format($row['price'], 2); ?></td>

            <td>
                <span class="badge <?= strtolower($row['payment_status']); ?>">
                  <?= ucfirst($row['payment_status']); ?>
                </span>

            </td>

            <td>
                <span class="badge <?= strtolower($row['booking_status']); ?>">
                  <?= ucfirst($row['booking_status']); ?>
                </span>

            </td>

            <td>
                <?php if ($row['booking_status'] === 'cancelled'): ?>
                    <?= htmlspecialchars($row['cancel_reason'] ?? 'No reason provided'); ?><br>
                    <small><?= $row['cancelled_at']; ?></small>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>


            <td>
                <?php if ($row['booking_status'] === 'Active'): ?>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a class="btn approve"
                           href="update_booking.php?id=<?= $row['id']; ?>&action=approve"
                           title="Approve Booking">
                           ✓ Approve
                        </a>
                        <a class="btn cancel-btn"
                           href="update_booking.php?id=<?= $row['id']; ?>&action=cancel"
                           onclick="return confirm('Are you sure you want to cancel this booking?');"
                           title="Cancel Booking">
                           ✕ Cancel
                        </a>
                    </div>
                <?php else: ?>
                    <span style="color: var(--text-muted);">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
