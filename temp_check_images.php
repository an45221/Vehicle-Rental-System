<?php
require 'config.php';
$images = ['1770647770_tatanexon.png', '1770648396_MahindraScorpio.jpg', '1770647565_1770450125_mg.jpg'];
foreach($images as $img) {
    $stmt = $conn->prepare("SELECT id, vehicle_name, price FROM vehicles WHERE image LIKE ? LIMIT 1");
    $term = "%$img%";
    $stmt->bind_param("s", $term);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()) {
        echo "IMG: $img | ID: " . $row['id'] . " | Name: " . $row['vehicle_name'] . " | Price: " . $row['price'] . "\n";
    }
}
?>
