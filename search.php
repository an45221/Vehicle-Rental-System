<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: home.php");
    exit;
}

/* =========================
   GET POST DATA FIRST (VERY IMPORTANT)
========================= */

$pickup = trim($_POST['pickup'] ?? '');
$drop = trim($_POST['drop'] ?? '');
$pickup_date = $_POST['pickup_date'] ?? '';
$pickup_time = $_POST['pickup_time'] ?? '';
$return_date = $_POST['return_date'] ?? '';
$return_time = $_POST['return_time'] ?? '';

/* =========================
   EMPTY FIELD VALIDATION
========================= */

if (
    empty($pickup) || empty($drop) ||
    empty($pickup_date) || empty($pickup_time) ||
    empty($return_date) || empty($return_time)
) {
    echo "<script>alert('⚠ Please fill all required fields.'); window.location.href='home.php';</script>";
    exit;
}

/* =========================
   COMBINE DATE + TIME
========================= */

$pickup_datetime = strtotime("$pickup_date $pickup_time");
$return_datetime = strtotime("$return_date $return_time");

/* =========================
   DATE VALIDATION
========================= */

if ($return_datetime <= $pickup_datetime) {
    echo "<script>alert('❌ Return must be AFTER pickup.'); window.location.href='home.php';</script>";
    exit;
}

/* =========================
   MINIMUM HOURS VALIDATION
========================= */

$diff_seconds = $return_datetime - $pickup_datetime;
$diff_hours   = $diff_seconds / 3600;

$MIN_HOURS = 7;

if ($diff_hours < $MIN_HOURS) {
    echo "<script>alert('❌ Minimum rental time is $MIN_HOURS hours.'); window.location.href='home.php';</script>";
    exit;
}

/* =========================
   SAVE TO SESSION
========================= */

$_SESSION['pickup'] = $pickup;
$_SESSION['drop'] = $drop;
$_SESSION['pickup_date'] = $pickup_date;
$_SESSION['pickup_time'] = $pickup_time;
$_SESSION['return_date'] = $return_date;
$_SESSION['return_time'] = $return_time;

/* =========================
   FETCH VEHICLES
========================= */

$vehicleQuery = "SELECT * FROM vehicles WHERE status = 'available'";
$vehicleResult = mysqli_query($conn, $vehicleQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Vehicles</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- YOUR EXISTING STYLE BLOCK REMAINS UNCHANGED -->
     <style>
        /* =====================
           GLOBAL RESET & VARIABLES
        ===================== */
:root {
    --primary: #1f8f4c;
    --primary-dark: #187a3d;
    --primary-light: #2da85d;
    --secondary: #ff8c00;
    --secondary-dark: #e67e00;
    --accent: #f4d33d;
    --success: #28a745;
    --danger: #dc3545;
    --info: #17a2b8;
    --light-bg: #f8fafc;
    --border-color: #e0e7ff;
    --text-dark: #1a202c;
    --text-muted: #6b7280;
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
    --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.15);
    --radius: 12px;
    --radius-lg: 16px;
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
    min-height: 100vh;
    padding: 20px;
}

/* =====================
   PAGE HEADER
===================== */
.search-title {
    text-align: center;
    margin-bottom: 35px;
    font-size: 32px;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* =====================
   MAIN LAYOUT
===================== */
.search-container {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 28px;
    max-width: 1400px;
    margin: 20px auto;
    align-items: start;
}

/* =====================
   LEFT SIDEBAR
===================== */
.search-booking-details {
    background: #ffffff;
    padding: 24px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    position: sticky;
    top: 20px;
    transition: var(--transition);
}

.search-booking-details:hover {
    box-shadow: var(--shadow-lg);
}

.search-booking-details h3 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 18px;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--primary);
    padding-bottom: 12px;
}

.search-booking-details h3::before {
    content: "📍";
    font-size: 18px;
}

#viewDetails {
    display: block;
}

#viewDetails p {
    font-size: 13px;
    margin-bottom: 12px;
    line-height: 1.6;
    color: var(--text-muted);
}

#viewDetails p strong {
    color: var(--text-dark);
    display: block;
    font-weight: 600;
    margin-bottom: 3px;
}

/* Edit Button */
.edit-btn {
    margin-top: 18px;
    width: 100%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    border: none;
    padding: 12px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    letter-spacing: 0.5px;
    transition: var(--transition);
    box-shadow: 0 2px 8px rgba(31, 143, 76, 0.2);
}

.edit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(31, 143, 76, 0.3);
}

.edit-btn:active {
    transform: translateY(0);
}

/* =====================
   EDIT FORM
===================== */
#searchEditForm {
    display: none;
}

#searchEditForm label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    margin-top: 14px;
    margin-bottom: 6px;
    color: var(--text-dark);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

#searchEditForm input {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 2px;
    border-radius: 8px;
    border: 1.5px solid var(--border-color);
    background: var(--light-bg);
    color: var(--text-dark);
    font-size: 13px;
    transition: var(--transition);
}

#searchEditForm input:focus {
    outline: none;
    border-color: var(--primary);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(31, 143, 76, 0.1);
}

.search-edit-actions {
    display: flex;
    gap: 10px;
    margin-top: 18px;
}

.search-again,
.search-cancel-edit {
    flex: 1;
    padding: 11px 14px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: var(--transition);
}

.search-again {
    background: linear-gradient(135deg, var(--success) 0%, #20c997 100%);
    color: #fff;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
}

.search-again:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(40, 167, 69, 0.3);
}

.search-cancel-edit {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: #fff;
    box-shadow: 0 2px 8px rgba(108, 117, 125, 0.2);
}

.search-cancel-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(108, 117, 125, 0.3);
}

/* =====================
   RIGHT VEHICLE LIST
===================== */
.search-vehicle-list {
    background: #ffffff;
    padding: 32px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
}

.vehicle-grid {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

/* =====================
   VEHICLE CARD
===================== */
.search-vehicle-card {
    display: grid;
    grid-template-columns: 160px 1fr 140px 130px;
    gap: 20px;
    align-items: center;
    background: linear-gradient(135deg, #fff 0%, var(--light-bg) 100%);
    padding: 20px;
    border-radius: 12px;
    border: 1.5px solid var(--border-color);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.search-vehicle-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
    transform: scaleX(0);
    transform-origin: left;
    transition: var(--transition);
}

.search-vehicle-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-light);
}

.search-vehicle-card:hover::before {
    transform: scaleX(1);
}

.search-car-img {
    width: 150px;
    height: 120px;
    border-radius: 10px;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.search-vehicle-card:hover .search-car-img {
    transform: scale(1.05);
}

.search-vehicle-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.search-vehicle-info p {
    font-size: 13px;
    margin: 0;
    color: var(--text-muted);
    line-height: 1.5;
}

.search-vehicle-info p:first-child {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.vehicle-brand {
    font-size: 12px;
    font-weight: 600;
    color: var(--primary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* =====================
   PRICE BOX
===================== */
.search-vehicle-price {
    text-align: center;
    background: linear-gradient(135deg, var(--accent) 0%, #ffd966 100%);
    padding: 16px 12px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(244, 211, 61, 0.2);
}

.search-vehicle-price .label {
    font-size: 11px;
    color: #333;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.search-vehicle-price .amount {
    font-size: 20px;
    font-weight: 700;
    margin-top: 6px;
    color: #1a1a1a;
}

/* =====================
   BOOK BUTTON
===================== */
.search-book-btn {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    border: none;
    padding: 12px 16px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 12px;
    cursor: pointer;
    transition: var(--transition);
    width: 100%;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    box-shadow: 0 4px 12px rgba(31, 143, 76, 0.2);
    position: relative;
    overflow: hidden;
}

.search-book-btn::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.search-book-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(31, 143, 76, 0.4);
}

.search-book-btn:active::before {
    width: 300px;
    height: 300px;
}

/* =====================
   EMPTY STATE
===================== */
.vehicle-grid > p {
    text-align: center;
    font-weight: 600;
    color: var(--text-muted);
    padding: 40px 20px;
    font-size: 16px;
}

/* =====================
   RESPONSIVE DESIGN
===================== */
@media (max-width: 1024px) {
    .search-container {
        grid-template-columns: 1fr;
    }

    .search-booking-details {
        position: static;
    }
}

@media (max-width: 768px) {
    .search-vehicle-card {
        grid-template-columns: 120px 1fr 100px;
        gap: 12px;
        padding: 14px;
    }

    .search-car-img {
        width: 110px;
        height: 90px;
    }

    .search-vehicle-price {
        padding: 12px 8px;
    }

    .search-vehicle-price .amount {
        font-size: 16px;
    }

    .search-vehicle-info p {
        font-size: 12px;
    }

    .search-vehicle-info p:first-child {
        font-size: 15px;
    }

    .search-title {
        font-size: 24px;
        margin-bottom: 25px;
    }
}

@media (max-width: 480px) {
    body {
        padding: 10px;
    }

    .search-container {
        gap: 16px;
        margin: 10px auto;
    }

    .search-vehicle-list {
        padding: 16px;
    }

    .search-vehicle-card {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 12px;
    }

    .search-car-img {
        width: 100%;
        height: auto;
        max-width: 200px;
        margin: 0 auto;
    }

    .search-vehicle-price {
        padding: 12px;
    }

    .search-title {
        font-size: 20px;
    }

    .search-booking-details h3 {
        font-size: 14px;
    }
}

        </style>
</head>

<body>
<div class="search-container">

    <!-- LEFT SIDEBAR (UNCHANGED) -->
    <aside class="search-booking-details">
        <h3>BOOKING DETAILS</h3>

        <div id="viewDetails">
            <p><strong>Pickup:</strong> <?= $pickup ?></p>
            <p><strong>Drop:</strong> <?= $drop ?></p>
            <p><strong>Pick Up Date:</strong> <?= $pickup_date ?></p>
            <p><strong>Return Date:</strong> <?= $return_date ?></p>
            <p><strong>Pickup Time:</strong> <?= $pickup_time ?></p>
            <p><strong>Return Time:</strong> <?= $return_time ?></p>
            <button class="edit-btn" onclick="openEdit()">Edit Details</button>
        </div>

        <form id="searchEditForm" action="search.php" method="POST" style="display:none;">
            <label>Pickup Location</label>
            <input type="text" name="pickup" value="<?= $pickup ?>" required>

            <label>Drop Location</label>
            <input type="text" name="drop" value="<?= $drop ?>" required>

            <label>Pickup Date</label>
            <input type="date" name="pickup_date" value="<?= $pickup_date ?>" required>

            <label>Pickup Time</label>
            <input type="time" name="pickup_time" value="<?= $pickup_time ?>" required>

            <label>Return Date</label>
            <input type="date" name="return_date" value="<?= $return_date ?>" required>

            <label>Return Time</label>
            <input type="time" name="return_time" value="<?= $return_time ?>" required>

            <div class="search-edit-actions">
                <button type="submit" class="search-again">Search</button>
                <button type="button" class="search-cancel-edit" onclick="closeEdit()">Cancel</button>
            </div>
        </form>
    </aside>

    <!-- RIGHT VEHICLE LIST -->
    <section class="search-vehicle-list">
        <h2 class="search-title">AVAILABLE VEHICLES</h2>

        <div class="vehicle-grid">

            <?php if (mysqli_num_rows($vehicleResult) > 0) { ?>
                <?php while ($vehicle = mysqli_fetch_assoc($vehicleResult)) { ?>

                    <div class="search-vehicle-card">
                        <img src="<?= $vehicle['image']; ?>" class="search-car-img">

                        <div class="search-vehicle-info">
                            <p style="font-size:18px;">
                                <strong><?= $vehicle['vehicle_name']; ?></strong>
                            </p>
                            <p>Type: <?= $vehicle['vehicle_type']; ?></p>
                            <p>Seats: <?= $vehicle['seats']; ?></p>
                            <p>Fuel: <?= $vehicle['fuel_type']; ?></p>
                            <p>Transmission: <?= $vehicle['transmission']; ?></p>
                        </div>

                        <div class="search-vehicle-price">
                            <p class="label">PRICE</p>
                            <p class="amount">NPR <?= number_format($vehicle['price']); ?></p>
                        </div>

                        <form action="vehicle_profile.php" method="POST">
                            <input type="hidden" name="vehicle_id" value="<?= $vehicle['id']; ?>">
                            <button class="search-book-btn">BOOK</button>
                        </form>
                    </div>

                <?php } ?>
            <?php } else { ?>
                <p style="text-align:center;font-weight:600;">
                    No vehicles available at the moment.
                </p>
            <?php } ?>

        </div>
    </section>
</div>

<script>
function openEdit() {
    document.getElementById("viewDetails").style.display = "none";
    document.getElementById("searchEditForm").style.display = "block";
}
function closeEdit() {
    document.getElementById("searchEditForm").style.display = "none";
    document.getElementById("viewDetails").style.display = "block";
}
</script>

</body>
</html>
