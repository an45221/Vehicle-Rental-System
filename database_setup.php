<?php
/**
 * Database Setup - Run this file once to create necessary tables
 * After running, delete this file or comment out the execution
 */

require 'config.php';

// Create reviews table
$reviews_table = " 
CREATE TABLE IF NOT EXISTS reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, 
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE, 
    UNIQUE KEY unique_booking_review (booking_id)
);
";

if ($conn->query($reviews_table) === TRUE) {
    echo "✅ Reviews table created successfully!<br>";
} else {
    echo "❌ Error creating reviews table: " . $conn->error . "<br>";
}

// Add average rating column to vehicles table if it doesn't exist
$check_column = $conn->query("SHOW COLUMNS FROM vehicles LIKE 'avg_rating'");
if ($check_column->num_rows === 0) {
    $alter_vehicles = "ALTER TABLE vehicles ADD COLUMN avg_rating DECIMAL(3,2) DEFAULT 0";
    if ($conn->query($alter_vehicles) === TRUE) {
        echo "✅ Added avg_rating column to vehicles table!<br>";
    } else {
        echo "❌ Error adding avg_rating column: " . $conn->error . "<br>";
    }
}

// Add total reviews column to vehicles table if it doesn't exist
$check_column = $conn->query("SHOW COLUMNS FROM vehicles LIKE 'total_reviews'");
if ($check_column->num_rows === 0) {
    $alter_vehicles = "ALTER TABLE vehicles ADD COLUMN total_reviews INT DEFAULT 0";
    if ($conn->query($alter_vehicles) === TRUE) {
        echo "✅ Added total_reviews column to vehicles table!<br>";
    } else {
        echo "❌ Error adding total_reviews column: " . $conn->error . "<br>";
    }
}

// Create cancellation feedback table
$feedback_table = "
CREATE TABLE IF NOT EXISTS cancellation_feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    reason VARCHAR(255),
    additional_comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_feedback (booking_id)
);
";

if ($conn->query($feedback_table) === TRUE) {
    echo "✅ Cancellation feedback table created successfully!<br>";
} else {
    echo "❌ Error creating cancellation feedback table: " . $conn->error . "<br>";
}

echo "<h3>Database setup complete!</h3>";
echo "<p>You can now delete or comment out the execution of this file.</p>";
?>
