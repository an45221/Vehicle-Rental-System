<?php
$booking_id = $_GET['booking_id'];
?>

<h2>Select Payment Method</h2>
<form action="payment_success.php" method="POST">
    <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
    <button type="submit">Pay with Khalti / Esewa</button>
</form>
