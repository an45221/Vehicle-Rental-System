<?php
require 'config.php';
$sql = "SELECT * FROM bookings ORDER BY id DESC LIMIT 10";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "Last 10 Bookings:\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']} | UserID: {$row['user_id']} | Vehicle: {$row['vehicle_name']} | Status: {$row['booking_status']} | Created: {$row['created_at']}\n";
    }
} else {
    echo "No bookings found in the database.\n";
}
?>
