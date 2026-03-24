<?php
require 'config.php';
session_start();
$user_id = $_SESSION['user_id'] ?? null;

echo "Session User ID: " . var_export($user_id, true) . "<br><br>";

if ($user_id) {
    $sql = "SELECT * FROM bookings WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "Found " . $result->num_rows . " bookings for this user.<br><br>";
    
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Vehicle: " . $row['vehicle_name'] . " | Status: " . $row['booking_status'] . " | Created: " . $row['created_at'] . "<br>";
    }
} else {
    echo "Not logged in.";
}
?>
