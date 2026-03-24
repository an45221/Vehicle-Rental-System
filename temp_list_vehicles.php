<?php
require 'config.php';
$res = $conn->query("SELECT id, vehicle_name, price, image FROM vehicles WHERE status = 'available' ORDER BY price DESC");
echo "VEHICLE_LIST_START\n";
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Name: " . $row['vehicle_name'] . " | Price: " . $row['price'] . " | Image: " . $row['image'] . "\n";
}
echo "VEHICLE_LIST_END\n";
?>
