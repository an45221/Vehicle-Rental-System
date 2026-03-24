<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) 
    {
    header("Location: login.php");
    exit;
}

/* 🚫 DOUBLE CHECK USER STATUS */
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT status, profile_image, username FROM users WHERE id = ?");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) 
    {
    // User deleted or session corrupted
    session_destroy();
    header("Location: login.php");
    exit;
}

$status = $user['status'];

if ($status === 'blocked') {
    session_destroy();
    header("Location: login.php");
    exit;
}

$profileImage = !empty($user['profile_image'])
    ? $user['profile_image']
    : 'images/profile.png';

$username = $user['username'] ?? 'User';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Go Rent</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* =========================
   GLOBAL STYLES
========================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background-image: url("images/backgroundimage.png");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    color: #222;

}
body::before {
    content: "";
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    z-index: -1;
}



/* =========================
   HEADER / NAVBAR
========================= */

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 50px;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 22px;
    font-weight: 700;
}

.logo img {
    width: 40px;
}

nav {
    display: flex;
    align-items: center;
    gap: 25px;
}

.nav-link {
    text-decoration: none;
    font-weight: 500;
    color: #333;
}

.nav-link:hover {
    color: #0d6efd;
}

/* Profile link */
.profile-link {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.profile-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #0d6efd;
}

.profile-link h3 {
    font-size: 15px;
    color: #777;
}

/* Auth button */
.auth-btns button {
    padding: 8px 16px;
    border: none;
    background: #0d6efd;
    color: white;
    border-radius: 6px;
    cursor: pointer;
}

.auth-btns button:hover {
    background: #084298;
}

/* =========================
   BOOKING FORM
========================= */

.booking-box {
    max-width: 1100px;
    margin: 80px auto;
    padding: 35px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(15px);
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
}

.booking-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.input-group label {
    font-size: 14px;
    margin-bottom: 6px;
    display: block;
    color: #000;
}

.input-group input {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.search-btn {
    grid-column: 1 / -1;
    padding: 14px;
    border: none;
    background: #ff6f00;
    color: white;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
}

.search-btn:hover {
    background: #e65c00;
}

/* =========================
   FEATURES SECTION
========================= */

.features-section {
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    padding: 70px 20px;
}

.features-container {
    max-width: 1200px;
    margin: auto;
}

.features-header {
    text-align: center;
    margin-bottom: 50px;
}

.features-header h2 {
    font-size: 36px;
    font-weight: 700;
}

.features-header p {
    color: #666;
    max-width: 600px;
    margin: auto;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 30px;
}

.feature-card {
    background: white;
    padding: 30px;
    border-radius: 14px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: 0.3s;
}

.feature-card:hover {
    transform: translateY(-8px);
}

.feature-icon {
    width: 60px;
    height: 60px;
    margin: auto auto 20px;
    background: linear-gradient(135deg, #ff6f00, #fbd52c);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
}

/* =========================
   VEHICLE OFFERS
========================= */

.vehicle-offers {
    padding: 80px 100px;
    background: #f9f9f9;
    
}

.offers-container {
    max-width: 1200px;
    margin: auto;
    text-align: center;
}

.offers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.offer-card {
    background: white;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    width: 100%;
}

.offer-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.offer-content {
    padding: 20px;
}

.offer-price {
    margin: 15px 0;
}

.price {
    font-size: 22px;
    font-weight: 700;
    color: #ff6f00;
}

.reserve-btn {
    padding: 10px 18px;
    background: #0d6efd;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

/* =========================
   TESTIMONIALS
========================= */

.testimonials {
    background: white;
    padding: 70px 20px;
}

.testimonials-grid {
    max-width: 1000px;
    margin: auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
}

.testimonial-card {
    padding: 25px;
    border-radius: 14px;
    background: #f8f9fa;
}

/* =========================
   FOOTER
========================= */

.footer {
    background: #111;
    color: #ccc;
}

.footer-top {
    padding: 100px 30px;
}

.footer-content {
    max-width: 1200px;
    margin: auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.footer a {
    color: #ccc;
    text-decoration: none;
}

.footer-bottom {
    background: #000;
    padding: 20px;
    text-align: center;
    font-size: 14px;
}

        </style>
</head>
<body class="home-page">
    <!-- NAVIGATION -->
    <header>
        <a href="home.php" style="text-decoration: none; display: flex; align-items: center; gap: 10px; color: inherit;">
            <div class="logo">
                <img src="images/smallcarlogo.png" alt="logo">
                <span>GO<strong>RENT</strong>.</span>
            </div>
        </a>
        <nav>
            <a href="#" class="nav-link">HOME</a>
            <a href="about.html" class="nav-link">ABOUT US</a>
            <a href="contact.html" class="nav-link">CONTACT US</a>
            <a href="mybooking.php" class="nav-link">MY BOOKINGS</a>
            <a href="profile.php" class="profile-link" style="display:flex; align-items:center;">
    <img 
        src="<?= htmlspecialchars($profileImage) ?>" 
        alt="Profile"
        class="profile-icon"
        style="
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #0d6efd;
        "
    >
    <h3 style="margin-left: 10px; color: #aa9f9fff;">
        <?= htmlspecialchars($username) ?>
    </h3>
</a>

            <div class="auth-btns">
                <a href="index.php">
                    <button class="login">Log Out</button>
                </a>
            </div>
        </nav>
    </header>

    <!-- BOOKING FORM -->
    <div class="booking-box">
        <form action="search.php" method="POST" class="booking-grid">
            <div class="input-group">
                <label for="pickup">Pickup location</label>
                <input type="text" name="pickup" id="pickup" placeholder="Pickup Location" required>
            </div>
            <div class="input-group">
                <label for="drop">Drop location</label>
                <input type="text" name="drop" id="drop" placeholder="Drop Location" required>
            </div>
            <div class="input-group">
                <label>Pickup date and time</label>
                <input type="date" name="pickup_date" id="pickup_date" required>
                <input type="time" name="pickup_time" id="pickup_time" required>
            </div>
            <div class="input-group">
                <label>Return date and time</label>
                <input type="date" name="return_date" id="return_date" required>
                <input type="time" name="return_time" id="return_time" required>
            </div>
            <button type="submit" class="search-btn">Search</button>
        </form>
    </div>
    
    <!-- VEHICLE OFFERS SECTION -->
    <section class="vehicle-offers">
        <div class="offers-container">
            <h2>Our Premium Vehicle Offers</h2>
            <div class="offers-grid">
                <div class="offer-card">
                    <div class="offer-image">
                        <img src="images/kia.jpg" alt="Kia Sportage">
                        <span class="offer-badge">Premium</span>
                    </div>
                    <div class="offer-content">
                        <h3>Kia Sportage</h3>
                        <p class="offer-description">High-performance SUV with luxury interior and advanced safety features</p>
                        <div class="offer-price">
                            <span class="price">Rs. 5,000</span>
                            <span class="period">/day</span>
                        </div>
                        <button type="button" class="reserve-btn" onclick="redirectToReservation(39)">Reserve Now</button>
                    </div>
                </div>
                <div class="offer-card">
                    <div class="offer-image">
                        <img src="images/MahindraScorpio.jpg" alt="Mahindra Scorpio">
                        <span class="offer-badge offer-badge-premium">Robust</span>
                    </div>
                    <div class="offer-content">
                        <h3>Mahindra Scorpio</h3>
                        <p class="offer-description">Powerful SUV built for rugged terrain and comfortable long journeys</p>
                        <div class="offer-price">
                            <span class="price">Rs. 3,000</span>
                            <span class="period">/day</span>
                        </div>
                        <button type="button" class="reserve-btn" onclick="redirectToReservation(42)">Reserve Now</button>
                    </div>
                </div>
                <div class="offer-card">
                    <div class="offer-image">
                        <img src="images/mg.jpg" alt="MG ZS EV">
                        <span class="offer-badge offer-badge-discount">Electric</span>
                    </div>
                    <div class="offer-content">
                        <h3>MG ZS EV</h3>
                        <p class="offer-description">Modern electric SUV with cutting-edge technology and zero emissions</p>
                        <div class="offer-price">
                            <span class="price">Rs. 2,900</span>
                            <span class="period">/day</span>
                        </div>
                        <button type="button" class="reserve-btn" onclick="redirectToReservation(41)">Reserve Now</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="features-section">
        <div class="features-container">
            <div class="features-header">
                <h2>Why Choose GoRent?</h2>
                <p>Experience seamless car rental with our trusted platform. Best prices, widest selection, and outstanding customer service.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🚗</div>
                    <h3>Wide Selection</h3>
                    <p>Choose from hundreds of vehicles in different categories, from economy to luxury cars for all your needs.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Best Prices</h3>
                    <p>Get competitive rates with transparent pricing. No hidden charges, just honest and affordable car rentals.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⏱️</div>
                    <h3>Easy Booking</h3>
                    <p>Book your car in minutes with our simple and intuitive booking system. Quick confirmation and instant details.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Safe & Secure</h3>
                    <p>Your data is protected with advanced security. All vehicles are insured and regularly maintained for your safety.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">24/7</div>
                    <h3>24/7 Support</h3>
                    <p>Our dedicated customer support team is available round the clock to assist with any queries or issues.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>Flexible Terms</h3>
                    <p>Flexible rental periods, easy cancellation policy, and various payment options to suit your convenience.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT COMPANY SECTION -->
    <section class="about-company">
        <div class="about-container">
            <div class="about-content">
                <h2>About GoRent</h2>
                <p>Welcome to GoRent, where quality meets convenience! With over a decade of experience in the car rental industry, we're committed to providing you with the best rental experience possible. Our extensive fleet of well-maintained vehicles, competitive pricing, and exceptional customer service make us your trusted partner for all your travel needs.</p>
                <p>Whether you're planning a weekend getaway, a business trip, or a long-term rental, we have the perfect vehicle for you. We pride ourselves on transparency, reliability, and customer satisfaction.</p>
                <div class="about-stats">
                    <div class="stat">
                        <h3>20+</h3>
                        <p>Vehicles</p>
                    </div>
                    <div class="stat">
                        <h3>200</h3>
                        <p>Happy Customers</p>
                    </div>
                    <div class="stat">
                        <h3>24/7</h3>
                        <p>Support</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    

    <!-- TESTIMONIALS SECTION -->
    <section class="testimonials">
        <div class="testimonials-container">
            <h2>What Our Customers Say</h2>
            <p class="section-subtitle">Real reviews from real customers</p>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"GoRent provided excellent service! The booking process was smooth and the car was in perfect condition. Highly recommended!"</p>
                    <div class="testimonial-author">
                        <h4>Rajesh Kumar</h4>
                        <p>Business Traveler</p>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"Best prices in the market with transparent billing. No hidden charges. I've rented multiple times and always satisfied!"</p>
                    <div class="testimonial-author">
                        <h4>Priya Singh</h4>
                        <p>Family Holiday</p>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"Customer support was incredibly helpful! They resolved my queries instantly. Makes renting so much easier!"</p>
                    <div class="testimonial-author">
                        <h4>Amit Patel</h4>
                        <p>Regular Customer</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER SECTION -->
    <footer class="footer">
        <div class="footer-top">
            <div class="footer-content">
                <div class="footer-logo">
                    <div class="logo">
                        <img src="images/smallcarlogo.png" alt="GoRent Logo">
                        <span>GO<strong>RENT</strong>.</span>
                    </div>
                    <p>Where quality meets convenience: Rent with us and enjoy the journey</p>
                </div>
                <div class="footer-contact">
                    <h4>Contact Info</h4>
                    <ul>
                        <li><span class="icon">📞</span> 123.456.7890</li>
                        <li><span class="icon">✉️</span> contact@rentacar.com</li>
                        <li><span class="icon">📍</span> 333-222-444</li>
                        <li><span class="icon">💬</span> skypeuser</li>
                        <li><span class="icon">📱</span> 567-888-999</li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#start">Start</a></li>
                        <li><a href="#reservation">Reservation</a></li>
                        <li><a href="#fleet">Fleet</a></li>
                        <li><a href="#locations">Locations</a></li>
                        <li><a href="contact.html">Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-social">
                    <h4>Follow Us</h4>
                    <div class="social-icons">
                        <a href="#" class="social-icon">f</a>
                        <a href="#" class="social-icon">🐦</a>
                        <a href="#" class="social-icon">📷</a>
                        <a href="#" class="social-icon">▶️</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 GoRent. All rights reserved. | Privacy Policy | Terms & Conditions</p>
        </div>
    </footer>

    <script>
        // Set minimum date to today
        const today = new Date().toISOString().split("T")[0];
        document.getElementById("pickup_date").setAttribute("min", today);
        document.getElementById("return_date").setAttribute("min", today);

        // Validate booking form
        document.querySelector(".booking-grid").addEventListener("submit", function(e) {
    const pickupDate = document.getElementById("pickup_date").value;
    const pickupTime = document.getElementById("pickup_time").value;
    const returnDate = document.getElementById("return_date").value;
    const returnTime = document.getElementById("return_time").value;

    const pickupDateTime = new Date(pickupDate + "T" + pickupTime);
    const returnDateTime = new Date(returnDate + "T" + returnTime);

    const diffMs = returnDateTime - pickupDateTime;
    const diffHours = diffMs / (1000 * 60 * 60);

    const MIN_HOURS = 7; // change to 8 if needed

    if (returnDateTime <= pickupDateTime) {
        alert("❌ Return must be after pickup.");
        e.preventDefault();
        return;
    }

    if (diffHours < MIN_HOURS) {
        alert("❌ Minimum rental time is " + MIN_HOURS + " hours.");
        e.preventDefault();
        return;
    }
});


        // Function to redirect to reservation page
        function redirectToReservation(vehicleId) {
            window.location.href = 'reservation.php?vehicle_id=' + vehicleId;
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC7hqd2rNXO1DKVkHSdDWV-xBYzkqx_F70&libraries=places&callback=initGoogle" async defer></script>
    
    <script>
    function initGoogle() {

    const pickupInput = document.getElementById("pickup");
    const dropInput = document.getElementById("drop");

    const options = {
        componentRestrictions: { country: "np" } // only Nepal
    };

    const pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput, options);
    const dropAutocomplete = new google.maps.places.Autocomplete(dropInput, options);

    pickupAutocomplete.setFields(["formatted_address", "geometry"]);
    dropAutocomplete.setFields(["formatted_address", "geometry"]);

    pickupAutocomplete.addListener("place_changed", () => {
        const place = pickupAutocomplete.getPlace();
        pickupInput.value = place.formatted_address;
    });

    dropAutocomplete.addListener("place_changed", () => {
        const place = dropAutocomplete.getPlace();
        dropInput.value = place.formatted_address;
    });
}
</script>

</body>
</html>
