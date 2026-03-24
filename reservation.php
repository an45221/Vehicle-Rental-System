<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';

// Get vehicle data passed from home page or check if user is logged in
if (!isset($_GET['vehicle_id'])) {
    header("Location: home.php");
    exit;
}

$vehicle_id = (int) $_GET['vehicle_id'];

// Fetch vehicle details
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $vehicle_id);
if (!$stmt->execute()) {
    die("Execute error: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Vehicle not found");
}

$vehicle = $result->fetch_assoc();

// Store vehicle info in session for later use
$_SESSION['selected_vehicle_id'] = $vehicle_id;
$_SESSION['selected_vehicle_name'] = $vehicle['vehicle_name'];
$_SESSION['selected_vehicle_price'] = $vehicle['price'];
$_SESSION['selected_vehicle_image'] = $vehicle['image'];

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Vehicle - GoRent</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            font-size: 24px;
            font-weight: 700;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            transition: opacity 0.3s;
        }

        .navbar a:hover {
            opacity: 0.8;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 0 20px;
        }

        .vehicle-summary {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .vehicle-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .vehicle-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #222;
        }

        .vehicle-type {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .vehicle-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 2px solid #eee;
        }

        .detail-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
        }

        .detail-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .price-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .price-per-day {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .price-amount {
            font-size: 32px;
            font-weight: 700;
        }

        .total-price {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .total-price-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .total-price-value {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
        }

        .reservation-form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #222;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .auth-notice {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .auth-notice a {
            color: #0056b3;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-notice a:hover {
            text-decoration: underline;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .terms-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .terms-checkbox input {
            width: auto;
            margin-right: 10px;
            cursor: pointer;
        }

        .terms-checkbox label {
            margin: 0;
            cursor: pointer;
        }

        .terms-checkbox a {
            color: #667eea;
            text-decoration: none;
        }

        .terms-checkbox a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }

            .vehicle-summary {
                position: static;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }

        .success {
            color: #28a745;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
        <a href="home.php" style="text-decoration: none; display: flex; align-items: center; gap: 5px; color: inherit;">
            <h1 style="display: flex; align-items: center; gap: 5px;">
                <img src="images/smallcarlogo.png" alt="Logo" style="width: 40px; height: 40px;">
                Go<span>Rent</span>
            </h1>
        </a>
        <div>
            <?php if ($user_id): ?>
                <a href="myprofile.php">Profile</a>
                <a href="mybooking.php">My Bookings</a>
                <a href="logout.html">Logout</a>
            <?php else: ?>
                <a href="login.html">Login</a>
                <a href="signup.html">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="container">
        <!-- Vehicle Summary -->
        <div class="vehicle-summary">
            <img src="<?= htmlspecialchars($vehicle['image']) ?>" alt="<?= htmlspecialchars($vehicle['vehicle_name']) ?>" class="vehicle-image">
            
            <h2 class="vehicle-name"><?= htmlspecialchars($vehicle['vehicle_name']) ?></h2>
            <p class="vehicle-type"><?= htmlspecialchars($vehicle['vehicle_type']) ?></p>

            <div class="vehicle-details">
                <div class="detail-item">
                    <div class="detail-label">Seats</div>
                    <div class="detail-value"><?= $vehicle['seats'] ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Fuel Type</div>
                    <div class="detail-value"><?= htmlspecialchars($vehicle['fuel_type']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Transmission</div>
                    <div class="detail-value"><?= htmlspecialchars($vehicle['transmission']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Availability</div>
                    <div class="detail-value">Available</div>
                </div>
            </div>

            <div class="price-section">
                <div class="price-per-day">Price per day</div>
                <div class="price-amount">NPR <?= number_format($vehicle['price'], 2) ?></div>
            </div>

            <div class="total-price">
                <div class="total-price-label">Estimated Total</div>
                <div class="total-price-value" id="estimatedTotal">NPR 0</div>
                <small style="color: #999; margin-top: 5px; display: block;">*Based on rental duration</small>
            </div>
        </div>

        <!-- Reservation Form -->
        <div class="reservation-form">
            <h2 class="form-title">Book Your Vehicle</h2>

            <?php if (!$user_id): ?>
                <div class="auth-notice">
                    <strong>⚠️ Please Log In</strong><br>
                    You need to be logged in to make a reservation. 
                    <a href="login.html">Log in here</a> or <a href="signup.html">create an account</a>
                </div>
            <?php endif; ?>

            <form id="reservationForm" method="POST" action="confirm.php" onsubmit="return validateForm()">
                <input type="hidden" name="vehicle_id" value="<?= $vehicle_id ?>">
                <input type="hidden" name="vehicle_name" value="<?= htmlspecialchars($vehicle['vehicle_name']) ?>">
                <input type="hidden" name="price" value="<?= $vehicle['price'] ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="pickup_location">Pickup Location *</label>
                        <select id="pickup_location" name="pickup" required <?= !$user_id ? 'disabled' : '' ?>>
                            <option value="">Select pickup location</option>
                            <option value="Kathmandu">Kathmandu</option>
                            <option value="Pokhara">Pokhara</option>
                            <option value="Nagarkot">Nagarkot</option>
                            <option value="Bhaktapur">Bhaktapur</option>
                            <option value="Biratnagar">Biratnagar</option>
                            <option value="Janakpur">Janakpur</option>
                        </select>
                        <span class="error" id="pickupError"></span>
                    </div>

                    <div class="form-group">
                        <label for="drop_location">Drop Location *</label>
                        <select id="drop_location" name="drop_location" required <?= !$user_id ? 'disabled' : '' ?>>
                            <option value="">Select drop location</option>
                            <option value="Kathmandu">Kathmandu</option>
                            <option value="Pokhara">Pokhara</option>
                            <option value="Nagarkot">Nagarkot</option>
                            <option value="Bhaktapur">Bhaktapur</option>
                            <option value="Biratnagar">Biratnagar</option>
                            <option value="Janakpur">Janakpur</option>
                        </select>
                        <span class="error" id="dropError"></span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="pickup_date">Pickup Date *</label>
                        <input type="date" id="pickup_date" name="pickup_date" required <?= !$user_id ? 'disabled' : '' ?>>
                        <span class="error" id="pickupDateError"></span>
                    </div>

                    <div class="form-group">
                        <label for="return_date">Return Date *</label>
                        <input type="date" id="return_date" name="return_date" required <?= !$user_id ? 'disabled' : '' ?>>
                        <span class="error" id="returnDateError"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="special_requests">Special Requests (Optional)</label>
                    <textarea id="special_requests" name="special_requests" style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; resize: vertical; min-height: 80px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;" placeholder="Any special requests or preferences..." <?= !$user_id ? 'disabled' : '' ?>></textarea>
                </div>

                <div class="terms-checkbox">
                    <input type="checkbox" id="terms" name="terms" required <?= !$user_id ? 'disabled' : '' ?>>
                    <label for="terms">I agree to the <a href="#" target="_blank">Terms & Conditions</a> and <a href="#" target="_blank">Privacy Policy</a></label>
                </div>

                <button type="submit" class="submit-btn" <?= !$user_id ? 'disabled' : '' ?> id="submitBtn">
                    <?= $user_id ? 'Proceed to Payment' : 'Log In to Continue' ?>
                </button>
                
                <div style="text-align: center; margin-top: 15px;">
                    <a href="home.php" style="color: #667eea; text-decoration: none; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 5px;">
                        <span>←</span> Back to Home
                    </a>
                </div>
            </form>

            <?php if (!$user_id): ?>
                <div style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
                    <p>Don't have an account? <a href="signup.html" style="color: #667eea; text-decoration: none; font-weight: 600;">Sign up now</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const pricePerDay = <?= $vehicle['price'] ?>;
        const pickupDateInput = document.getElementById('pickup_date');
        const returnDateInput = document.getElementById('return_date');
        const estimatedTotalEl = document.getElementById('estimatedTotal');

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        pickupDateInput.min = today;
        returnDateInput.min = today;

        // Calculate estimated total when dates change
        function calculateTotal() {
            if (pickupDateInput.value && returnDateInput.value) {
                const pickupDate = new Date(pickupDateInput.value);
                const returnDate = new Date(returnDateInput.value);
                
                if (returnDate > pickupDate) {
                    const days = Math.ceil((returnDate - pickupDate) / (1000 * 60 * 60 * 24));
                    const total = days * pricePerDay;
                    estimatedTotalEl.textContent = 'NPR ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    document.querySelector('input[name="price"]').value = total;
                }
            }
        }

        pickupDateInput.addEventListener('change', calculateTotal);
        returnDateInput.addEventListener('change', calculateTotal);

        // Update return date minimum when pickup date changes
        pickupDateInput.addEventListener('change', function() {
            returnDateInput.min = this.value;
            if (returnDateInput.value && returnDateInput.value < this.value) {
                returnDateInput.value = '';
                estimatedTotalEl.textContent = 'NPR 0';
            }
        });

        function validateForm() {
            let isValid = true;

            // Clear previous errors
            document.querySelectorAll('.error').forEach(el => el.textContent = '');

            // Validate pickup location
            if (!document.getElementById('pickup_location').value) {
                document.getElementById('pickupError').textContent = 'Please select a pickup location';
                isValid = false;
            }

            // Validate drop location
            if (!document.getElementById('drop_location').value) {
                document.getElementById('dropError').textContent = 'Please select a drop location';
                isValid = false;
            }

            // Validate pickup date
            if (!document.getElementById('pickup_date').value) {
                document.getElementById('pickupDateError').textContent = 'Please select a pickup date';
                isValid = false;
            }

            // Validate return date
            if (!document.getElementById('return_date').value) {
                document.getElementById('returnDateError').textContent = 'Please select a return date';
                isValid = false;
            }

            // Validate date range
            if (document.getElementById('pickup_date').value && document.getElementById('return_date').value) {
                const pickupDate = new Date(document.getElementById('pickup_date').value);
                const returnDate = new Date(document.getElementById('return_date').value);
                
                if (returnDate <= pickupDate) {
                    document.getElementById('returnDateError').textContent = 'Return date must be after pickup date';
                    isValid = false;
                }
            }

            // Validate terms
            if (!document.getElementById('terms').checked) {
                alert('Please agree to the terms and conditions');
                isValid = false;
            }

            return isValid;
        }

        // Disable form if not logged in
        <?php if (!$user_id): ?>
            document.getElementById('submitBtn').addEventListener('click', function(e) {
                e.preventDefault();
                alert('Please log in to continue with your reservation');
                window.location.href = 'login.html';
            });
        <?php endif; ?>
    </script>
</body>

</html>
