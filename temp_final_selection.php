<?php
require 'config.php';
$exclude = [24, 28, 26];
$sql = "SELECT id, vehicle_name, price, image FROM vehicles WHERE status = 'available' AND id NOT IN (" . implode(',', $exclude) . ") ORDER BY price DESC LIMIT 3";
$res = $conn->query($sql);
echo "NEW_VEHICLES_START\n";
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Name: " . $row['vehicle_name'] . " | Price: " . $row['price'] . " | Image: " . $row['image'] . "\n";
}
echo "NEW_VEHICLES_END\n";
?>
