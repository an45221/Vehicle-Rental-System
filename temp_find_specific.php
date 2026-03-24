<?php
require 'config.php';
$names = ['Kia Sportage', 'Mahindra Scorpio', 'MG ZS EV', 'Scorpio', 'MG'];
foreach($names as $name) {
    $stmt = $conn->prepare("SELECT id, vehicle_name, price, image FROM vehicles WHERE vehicle_name LIKE ? LIMIT 1");
    $term = "%$name%";
    $stmt->bind_param("s", $term);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()) {
        echo "FOUND: ID: " . $row['id'] . " | Name: " . $row['vehicle_name'] . " | Price: " . $row['price'] . " | Image: " . $row['image'] . "\n";
    } else {
        echo "NOT FOUND: $name\n";
    }
}
?>
