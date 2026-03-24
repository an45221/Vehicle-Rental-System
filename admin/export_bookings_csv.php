<?php
session_cache_limiter('private_no_expire');
session_start();
require '../config.php';

/* 🔐 Admin auth */
if (!isset($_SESSION['admin_logged_in'])) {
    exit('Unauthorized');
}

/* 📌 Fetch booking data */
$sql = "
SELECT 
    b.id,
    u.username,
    u.email,
    COALESCE(v.vehicle_name, b.vehicle_name) AS vehicle_name,
    b.pickup,
    b.drop_location,
    b.pickup_date,
    b.return_date,
    b.price,
    b.payment_status,
    b.booking_status
FROM bookings b
LEFT JOIN users u ON b.user_id = u.id
LEFT JOIN vehicles v ON b.vehicle_id = v.id
ORDER BY b.id DESC
";

$result = $conn->query($sql);

/* 📂 CSV headers */
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=bookings_export.csv');

$output = fopen('php://output', 'w');

/* 🧾 CSV column headers */
fputcsv($output, [
    'Booking ID',
    'Username',
    'Email',
    'Vehicle',
    'Pickup',
    'Drop',
    'Pickup Date',
    'Return Date',
    'Total Amount',
    'Payment Status',
    'Booking Status'
]);

/* 📊 CSV rows */
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        'SCPL-' . $row['id'],
        $row['username'],
        $row['email'],
        $row['vehicle_name'],
        $row['pickup'],
        $row['drop_location'],
        $row['pickup_date'],
        $row['return_date'],
        $row['price'],
        $row['payment_status'],
        $row['booking_status']
    ]);
}

fclose($output);
exit;
