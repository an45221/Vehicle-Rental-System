<?php
session_cache_limiter('private_no_expire');
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

require '../config.php';

/* Vehicles */
$vehicleCount = 0;
$vehicleQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM vehicles");
if ($vehicleQuery) {
    $vehicleCount = mysqli_fetch_assoc($vehicleQuery)['total'];
}

/* Users */
$userCount = 0;
$userQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
if ($userQuery) {
    $userCount = mysqli_fetch_assoc($userQuery)['total'];
}

/* Bookings */
$bookingCount = 0;
$bookingQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings");
if ($bookingQuery) {
    $bookingCount = mysqli_fetch_assoc($bookingQuery)['total'];
}

/* Revenue */
$revenue = 0;
$revenueQuery = mysqli_query($conn, "SELECT SUM(price) AS total FROM bookings WHERE payment_status = 'Paid'");
if ($revenueQuery) {
    $revenue = mysqli_fetch_assoc($revenueQuery)['total'] ?? 0;
}

/* Recent Bookings */
$recentBookings = [];
$recentBookingQuery = mysqli_query($conn, "
    SELECT b.id, b.vehicle_name, b.user_id, u.name, b.pickup_date, b.return_date, b.booking_status, b.payment_status, b.price
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    ORDER BY b.id DESC
    LIMIT 6
");
if ($recentBookingQuery) {
    while ($row = mysqli_fetch_assoc($recentBookingQuery)) {
        $recentBookings[] = $row;
    }
}

/* Vehicle Status */
$vehicleStatus = [];
$vehicleStatusQuery = mysqli_query($conn, "
    SELECT status, COUNT(*) AS count FROM vehicles GROUP BY status
");
if ($vehicleStatusQuery) {
    while ($row = mysqli_fetch_assoc($vehicleStatusQuery)) {
        $vehicleStatus[] = $row;
    }
}

/* Booking Status */
$bookingStatus = [];
$bookingStatusQuery = mysqli_query($conn, "
    SELECT booking_status, COUNT(*) AS count FROM bookings GROUP BY booking_status
");
if ($bookingStatusQuery) {
    while ($row = mysqli_fetch_assoc($bookingStatusQuery)) {
        $bookingStatus[] = $row;
    }
}

/* Monthly Revenue */
/* Monthly Revenue - Last 6 Months */
$monthlyRevenue = [];

/* Get last 6 months including current */
for ($i = 5; $i >= 0; $i--) {
    $monthKey = date('Y-m', strtotime("-$i months"));
    $monthlyRevenue[$monthKey] = 0;
}

/* Get revenue from database */
$query = mysqli_query($conn, "
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, 
           SUM(price) AS amount
    FROM bookings
    WHERE payment_status = 'Paid'
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month
");

if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        if (isset($monthlyRevenue[$row['month']])) {
            $monthlyRevenue[$row['month']] = $row['amount'];
        }
    }
}


/* Active Bookings */
$activeBookings = 0;
$activeBookingQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings WHERE booking_status = 'Active'");
if ($activeBookingQuery) {
    $activeBookings = mysqli_fetch_assoc($activeBookingQuery)['total'];
}

/* Pending Payments */
$pendingPayments = 0;
$pendingPaymentQuery = mysqli_query($conn, "SELECT SUM(price) AS total FROM bookings WHERE payment_status = 'Unpaid'");
if ($pendingPaymentQuery) {
    $pendingPayments = mysqli_fetch_assoc($pendingPaymentQuery)['total'] ?? 0;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<style>
:root {
    --primary: #1f8f4c;
    --primary-light: #2da85d;
    --secondary: #ff8c00;
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

html {
    scroll-behavior: smooth;
}

body {
    background: linear-gradient(135deg, #f8fafc 0%, #f0f4f8 100%);
    color: var(--text-dark);
}

/* Layout */
.dashboard {
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 240px;
    background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
    color: #fff;
    padding: 24px 16px;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    box-shadow: var(--shadow-md);
}

.logo {
    text-align: center;
    margin-bottom: 35px;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 1px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.sidebar ul {
    list-style: none;
}

.sidebar li {
    padding: 12px 16px;
    margin: 8px 0;
    border-radius: 8px;
    transition: var(--transition);
    border-left: 3px solid transparent;
}

.sidebar li a {
    color: #e5e7eb;
    text-decoration: none;
    display: block;
    font-weight: 500;
    font-size: 14px;
    letter-spacing: 0.3px;
}

.sidebar li:hover {
    background: rgba(31, 143, 76, 0.15);
    border-left-color: var(--primary);
}

.sidebar .active {
    background: rgba(31, 143, 76, 0.2);
    border-left-color: var(--primary);
}

.logout {
    color: #fca5a5;
    transition: var(--transition);
}

.sidebar li:hover .logout {
    color: #fff;
}

/* Main */
.main {
    flex: 1;
    margin-left: 240px;
    padding: 32px;
}

/* Topbar */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}

.topbar h1 {
    font-size: 28px;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Stats Grid */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.card {
    padding: 24px;
    border-radius: var(--radius);
    color: #fff;
    box-shadow: var(--shadow-md);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: rgba(255, 255, 255, 0.2);
    transform: scaleX(0);
    transform-origin: left;
    transition: var(--transition);
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-lg);
}

.card:hover::before {
    transform: scaleX(1);
}

.card h3 {
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.9;
}

.card p {
    font-size: 32px;
    margin-top: 12px;
    font-weight: 700;
}

.card-label {
    font-size: 11px;
    opacity: 0.8;
    margin-top: 10px;
    font-weight: 500;
}

.blue { background: linear-gradient(135deg, #0d6efd, #0a58ca); }
.green { background: linear-gradient(135deg, #198754, #146c43); }
.orange { background: linear-gradient(135deg, #fd7e14, #d46e11); }
.red { background: linear-gradient(135deg, #dc3545, #bd2130); }
.purple { background: linear-gradient(135deg, #6f42c1, #5a32a3); }

/* Section Heading */
.section-title {
    font-size: 20px;
    font-weight: 700;
    margin-top: 40px;
    margin-bottom: 20px;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Two Column Layout */
.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 32px;
}

/* Recent Bookings Section */
.bookings-table {
    background: white;
    padding: 24px;
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border);
    overflow-x: auto;
}

.bookings-table table {
    width: 100%;
    border-collapse: collapse;
}

.bookings-table th {
    background: var(--light-bg);
    padding: 12px;
    text-align: left;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dark);
    border-bottom: 2px solid var(--border);
}

.bookings-table td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
    color: var(--text-muted);
}

.bookings-table tbody tr:hover {
    background: var(--light);
    transition: var(--transition);
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.status-active { background: #d1fae5; color: #065f46; }
.status-confirmed { background: #bfdbfe; color: #1e40af; }
.status-completed { background: #dcfce7; color: #166534; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.status-paid { background: #d1fae5; color: #065f46; }
.status-unpaid { background: #fed7aa; color: #92400e; }

/* Status Cards */
.status-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}

.status-card {
    background: white;
    padding: 20px;
    border-radius: var(--radius);
    border-left: 4px solid var(--border);
    box-shadow: var(--shadow-sm);
    text-align: center;
    transition: var(--transition);
}

.status-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.status-card h4 {
    font-size: 13px;
    color: var(--text-muted);
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.status-card p {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-dark);
}

.status-vehicle { border-left-color: #0d6efd; }
.status-active-count { border-left-color: #198754; }
.status-pending { border-left-color: #ffc107; }

/* Admin Box */
.admin-box {
    background: linear-gradient(135deg, white 0%, var(--light) 100%);
    padding: 24px;
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border);
    max-width: 500px;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 20px;
}

.avatar {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff;
    font-size: 28px;
    font-weight: 700;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(31, 143, 76, 0.25);
}

.admin-info h3 {
    font-size: 20px;
    color: var(--text-dark);
    margin-bottom: 6px;
}

.role-badge {
    display: inline-block;
    padding: 6px 14px;
    font-size: 12px;
    background: linear-gradient(135deg, var(--red), #bd2130);
    color: #fff;
    border-radius: 20px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Revenue Chart */
.chart-container {
    background: white;
    padding: 24px;
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border);
}

.chart-container h3 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 20px;
    color: var(--text-dark);
}

.revenue-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    height: 200px;
    gap: 12px;
}

.revenue-bar {
    flex: 1;
    background: linear-gradient(180deg, var(--secondary), var(--orange));
    border-radius: 8px 8px 0 0;
    position: relative;
    min-height: 20px;
    transition: var(--transition);
    cursor: pointer;
}

.revenue-bar:hover {
    opacity: 0.8;
    filter: brightness(1.1);
}

.revenue-label {
    font-size: 10px;
    color: var(--text-muted);
    text-align: center;
    margin-top: 8px;
    font-weight: 600;
}

.revenue-value {
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 11px;
    font-weight: 700;
    color: var(--text-dark);
    white-space: nowrap;
}

/* Responsive */
@media (max-width: 1024px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }

    .main {
        margin-left: 0;
        padding: 20px;
    }

    .sidebar {
        width: 200px;
    }
}

@media (max-width: 768px) {
    .dashboard {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        height: auto;
        padding: 16px;
        position: relative;
    }

    .main {
        margin-left: 0;
        padding: 16px;
    }

    .stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .card {
        padding: 16px;
    }

    .card h3 { font-size: 12px; }
    .card p { font-size: 24px; }

    .topbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .topbar h1 { font-size: 22px; }

    .bookings-table {
        font-size: 12px;
    }

    .bookings-table th,
    .bookings-table td {
        padding: 8px;
    }

    .status-cards {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .stats {
        grid-template-columns: 1fr;
    }

    .card p { font-size: 20px; }

    .topbar h1 { font-size: 18px; }
}
</style>
</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">GoRent</h2>
        <ul>
            <li class="active"><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="manage_vehicles.php">Vehicles</a></li>
            <li><a href="manage_bookings.php">Bookings</a></li>
            <li><a href="manage_user.php">Users</a></li>
            <li><a href="admin_logout.php" class="logout">Logout</a></li>
        </ul>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <header class="topbar">
            <h1>📊 Dashboard</h1>
            <span style="color: var(--text-dark); font-weight: 600;">Admin</span>
        </header>

        <!-- KEY STATS -->
        <section class="stats">
            <div class="card blue">
                <h3>Total Vehicles</h3>
                <p><?= $vehicleCount ?></p>
                <div class="card-label">In System</div>
            </div>

            <div class="card green">
                <h3>Total Bookings</h3>
                <p><?= $bookingCount ?></p>
                <div class="card-label">All Time</div>
            </div>

            <div class="card orange">
                <h3>Active Users</h3>
                <p><?= $userCount ?></p>
                <div class="card-label">Registered</div>
            </div>

            <div class="card red">
                <h3>Total Payment</h3>
                <p>Rs. <?= number_format($revenue) ?></p>
                <div class="card-label">Paid Bookings</div>
            </div>

            <div class="card purple">
                <h3>Active Bookings</h3>
                <p><?= $activeBookings ?></p>
                <div class="card-label">In Progress</div>
            </div>

            <div class="card orange">
                <h3>Pending Payments</h3>
                <p>Rs. <?= number_format($pendingPayments) ?></p>
                <div class="card-label">Unpaid</div>
            </div>
        </section>

        <!-- REVENUE CHART & STATUS CARDS -->
        <div class="grid-2">
            <!-- Revenue Chart -->
            <div class="chart-container">
                <h3>📈 Monthly Revenue Trend</h3>
                <div class="revenue-chart">
                 <?php 
                        $maxRevenue = !empty($monthlyRevenue) ? max($monthlyRevenue) : 0;

                        foreach ($monthlyRevenue as $monthKey => $amount):

                            $percentage = ($maxRevenue > 0) 
                                ? ($amount / $maxRevenue) * 100 
                                : 0;

                            $month = date('M', strtotime($monthKey . '-01'));
                        ?>
                            <div>
                                <div class="revenue-bar" 
                                    style="height: <?= max(10, $percentage) ?>%;">
                                    
                                    <div class="revenue-value">
                                        Rs. <?= number_format($amount / 1000, 0) ?>k
                                    </div>
                                </div>
                                <div class="revenue-label"><?= $month ?></div> ]
                            </div>
                        <?php endforeach; ?>


                </div>
            </div>

            <!-- Vehicle & Booking Status -->
            <div>
                <h2 class="section-title">📋 Status Breakdown</h2>
                <div class="status-cards">
                    <?php foreach ($vehicleStatus as $status): ?>
                        <div class="status-card status-vehicle">
                            <h4><?= ucfirst($status['status']) ?> Vehicles</h4>
                            <p><?= $status['count'] ?></p>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="status-card status-active-count">
                        <h4>Active Bookings</h4>
                        <p><?= $activeBookings ?></p>
                    </div>

                    <div class="status-card status-pending">
                        <h4>Pending Payments</h4>
                        <p>Rs. <?= number_format($pendingPayments / 1000, 0) ?>k</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOOKING STATUS BREAKDOWN -->
        <h2 class="section-title">🎫 Booking Status Distribution</h2>
        <div class="status-cards">
            <?php foreach ($bookingStatus as $status): ?>
                <div class="status-card">
                    <h4><?= ucfirst($status['booking_status']) ?></h4>
                    <p><?= $status['count'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- RECENT BOOKINGS TABLE -->
        <h2 class="section-title" style="margin-top: 40px;">📅 Recent Bookings</h2>
        <div class="bookings-table">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Vehicle</th>
                        <th>User</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentBookings)): ?>
                        <?php foreach ($recentBookings as $booking): ?>
                            <tr>
                                <td><strong>SCPL-<?= $booking['id'] ?></strong></td>
                                <td><?= htmlspecialchars($booking['vehicle_name']) ?></td>
                                <td><?= htmlspecialchars($booking['name'] ?? 'Guest') ?></td>
                                <td>
                                    <small><?= date('M d', strtotime($booking['pickup_date'])) ?> - <?= date('M d, Y', strtotime($booking['return_date'])) ?></small>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $booking['booking_status'])) ?>">
                                        <?= $booking['booking_status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($booking['payment_status']) ?>">
                                        <?= $booking['payment_status'] ?>
                                    </span>
                                </td>
                                <td><strong>Rs. <?= number_format($booking['price']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted);">No bookings found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ADMIN INFO -->
        <h2 class="section-title">👤 Admin Profile</h2>
        <section class="admin-box">
            <div class="admin-profile">
                <div class="avatar">
                    <?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="admin-info">
                    <h3><?= $_SESSION['admin_username'] ?? 'Admin' ?></h3>
                    <span class="role-badge">Super Admin</span>
                </div>
            </div>
        </section>

    </main>
</div>

</body>
</html>
