<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';
require 'review_helper.php';

/* ✅ Validate request */
if (!isset($_POST['vehicle_id'])) {
    header("Location: search.php");
    exit;
}

$vehicle_id = (int) $_POST['vehicle_id'];

/* ✅ Validate booking session */
if (
    empty($_SESSION['pickup']) ||
    empty($_SESSION['drop']) ||
    empty($_SESSION['pickup_date']) ||
    empty($_SESSION['return_date'])
) {
    die("Session expired. Please search again.");
}

/* ✅ Assign session values */
$pickup       = $_SESSION['pickup'];
$drop         = $_SESSION['drop'];
$pickup_date  = $_SESSION['pickup_date'];
$return_date  = $_SESSION['return_date'];

/* ✅ Fetch vehicle */
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Vehicle not found");
}

$vehicle = $result->fetch_assoc();

/* ✅ Vehicle data */
$vehicle_name   = $vehicle['vehicle_name'];
$vehicle_type   = $vehicle['vehicle_type'];
$price_per_day  = (float) $vehicle['price'];
$seats          = $vehicle['seats'];
$fuel           = $vehicle['fuel_type'];
$transmission   = $vehicle['transmission'];
$image          = $vehicle['image'];

/* ✅ Calculate days */
$start = new DateTime($pickup_date);
$end   = new DateTime($return_date);
$days  = $start->diff($end)->days;
$days  = max(1, $days); // minimum 1 day

/* ✅ Calculate total */
$total_amount = $days * $price_per_day;

/* ✅ Get vehicle reviews */
$rating_stats = getVehicleRatingStats($conn, $vehicle_id);
$reviews = getVehicleReviews($conn, $vehicle_id, 5);
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Select Car</title>
    <style>
        /* =====================
           VARIABLES & RESET
        ===================== */
:root {
    --primary: #1f8f4c;
    --primary-light: #2da85d;
    --secondary: #ff8c00;
    --secondary-light: #ffb347;
    --accent: #ffc107;
    --accent-dark: #dbbe34;
    --success: #28a745;
    --danger: #dc3545;
    --light-bg: #f8fafc;
    --light: #f5f7fa;
    --border: #e0e7ff;
    --text-dark: #1a202c;
    --text-muted: #6b7280;
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
    --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.15);
    --radius: 12px;
    --radius-lg: 16px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', 'Segoe UI', sans-serif;
}

html {
    scroll-behavior: smooth;
}

body {
    background: linear-gradient(135deg, #f8fafc 0%, #f0f4f8 100%);
    color: var(--text-dark);
    min-height: 100vh;
    padding: 20px;
}

/* =====================
   CONTAINER & PAGE LAYOUT
===================== */
.container {
    max-width: 1000px;
    margin: 0 auto;
    background: #ffffff;
    box-shadow: var(--shadow-md);
    border-radius: var(--radius-lg);
    padding: 40px;
    border: 1px solid var(--border);
}

h2 {
    text-align: center;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--text-dark);
    text-transform: uppercase;
    font-size: 28px;
    letter-spacing: 1px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

h4 {
    text-align: center;
    font-weight: 600;
    color: var(--secondary);
    margin-top: 0;
    margin-bottom: 25px;
    font-size: 18px;
    letter-spacing: 0.5px;
}

/* =====================
   VEHICLE CARD SECTION
===================== */
.vehicle-card {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 40px;
    align-items: start;
    margin-top: 20px;
}

.vehicle-image {
    width: 100%;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    object-fit: cover;
    aspect-ratio: 4/3;
    transition: var(--transition);
    border: 1px solid var(--border);
}

.vehicle-image:hover {
    transform: scale(1.02);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
}

.vehicle-info {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.details {
    font-size: 15px;
    line-height: 1.8;
    color: var(--text-muted);
}

.details span {
    display: block;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    padding: 10px 14px;
    background: var(--light-bg);
    border-radius: 8px;
    border-left: 3px solid var(--primary);
}

.details span strong {
    color: var(--text-dark);
    font-weight: 600;
}

.estimated-box {
    background: linear-gradient(135deg, var(--accent) 0%, #ffd966 100%);
    padding: 20px;
    border-radius: var(--radius);
    font-weight: 600;
    text-align: center;
    color: #1a1a1a;
    box-shadow: 0 4px 16px rgba(255, 193, 7, 0.2);
    font-size: 16px;
    border: none;
}

.estimated-box strong {
    display: block;
    font-size: 24px;
    margin-top: 8px;
}

.book-btn {
    padding: 14px 32px;
    background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
    border: none;
    color: white;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.5px;
    border-radius: 10px;
    cursor: pointer;
    transition: var(--transition);
    align-self: center;
    width: 100%;
    max-width: 280px;
    box-shadow: 0 6px 16px rgba(255, 140, 0, 0.25);
    text-transform: uppercase;
    position: relative;
    overflow: hidden;
}

.book-btn::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.book-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(255, 140, 0, 0.35);
}

.book-btn:active::before {
    width: 300px;
    height: 300px;
}

/* =====================
   PRICING INFO
===================== */
.vehicle-info > form > p {
    font-size: 14px;
    color: var(--text-muted);
    margin: 0;
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
}

.vehicle-info > form > p strong {
    color: var(--text-dark);
}

.vehicle-info > form > p:last-of-type {
    border-bottom: none;
    font-size: 16px;
    font-weight: 700;
    color: var(--primary);
    margin-top: 10px;
    padding: 12px 0;
}

/* =====================
   NOTE SECTION
===================== */
.note {
    background: linear-gradient(135deg, #fff9e6 0%, #fff5cc 100%);
    border-left: 4px solid var(--secondary);
    padding: 20px;
    margin-top: 40px;
    font-size: 13px;
    color: #654321;
    line-height: 1.6;
    border-radius: var(--radius);
    border: 1px solid rgba(255, 140, 0, 0.2);
    box-shadow: 0 2px 8px rgba(255, 140, 0, 0.1);
}

.note strong {
    color: #333;
    font-weight: 700;
}

/* =====================
   REVIEWS SECTION
===================== */
.reviews-section {
    margin-top: 50px;
    padding-top: 40px;
    border-top: 2px solid var(--border);
}

.reviews-section h3 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 30px;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.rating-summary {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 30px;
    align-items: start;
    margin-bottom: 40px;
    padding: 28px;
    background: linear-gradient(135deg, var(--light-bg) 0%, #f0f4f8 100%);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
}

.rating-overview {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.average-rating {
    font-size: 56px;
    font-weight: 700;
    background: linear-gradient(135deg, var(--accent) 0%, var(--secondary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.star-rating {
    display: flex;
    align-items: center;
    gap: 3px;
    justify-content: center;
    flex-wrap: wrap;
}

.star {
    font-size: 20px;
    color: #e0e0e0;
    cursor: default;
    transition: var(--transition);
}

.star.filled {
    color: var(--accent);
}

.star.half {
    color: var(--accent);
}

.rating-value {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 600;
}

.total-reviews {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 500;
}

.rating-distribution {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.rating-row {
    display: grid;
    grid-template-columns: 50px 1fr 40px;
    gap: 12px;
    align-items: center;
}

.rating-label {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 600;
    text-align: center;
}

.rating-bar {
    height: 10px;
    background-color: #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
}

.rating-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--secondary) 0%, var(--secondary-light) 100%);
    border-radius: 8px;
    transition: width 0.6s ease;
}

.rating-count {
    text-align: right;
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 600;
}

/* =====================
   REVIEWS LIST
===================== */
.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.review-item {
    background: linear-gradient(135deg, #fff 0%, var(--light-bg) 100%);
    padding: 18px;
    border-radius: var(--radius);
    border: 1.5px solid var(--border);
    border-left: 4px solid var(--secondary);
    transition: var(--transition);
}

.review-item:hover {
    transform: translateX(4px);
    box-shadow: var(--shadow-md);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    gap: 15px;
}

.reviewer-name {
    font-weight: 700;
    color: var(--text-dark);
    font-size: 14px;
}

.review-date {
    font-size: 12px;
    color: var(--text-muted);
    white-space: nowrap;
}

.review-comment {
    color: var(--text-muted);
    font-size: 14px;
    line-height: 1.6;
    margin-top: 8px;
}

.no-reviews {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-muted);
    background: linear-gradient(135deg, var(--light-bg) 0%, #f0f4f8 100%);
    border-radius: var(--radius-lg);
    border: 1.5px dashed var(--border);
    font-size: 16px;
    font-weight: 500;
}

/* =====================
   RESPONSIVE DESIGN
===================== */
@media (max-width: 900px) {
    .container {
        padding: 28px;
    }

    .vehicle-card {
        grid-template-columns: 1fr;
        gap: 28px;
    }

    .rating-summary {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

@media (max-width: 640px) {
    .container {
        padding: 18px;
        border-radius: 12px;
    }

    h2 {
        font-size: 22px;
        margin-bottom: 5px;
    }

    h4 {
        font-size: 16px;
        margin-bottom: 20px;
    }

    .vehicle-card {
        gap: 18px;
    }

    .vehicle-image {
        border-radius: var(--radius);
    }

    .book-btn {
        max-width: 100%;
        padding: 12px 24px;
        font-size: 13px;
    }

    .details span {
        flex-direction: column;
    }

    .reviews-section h3 {
        font-size: 20px;
    }

    .rating-summary {
        padding: 18px;
    }

    .average-rating {
        font-size: 42px;
    }

    .rating-row {
        grid-template-columns: 40px 1fr 35px;
        gap: 8px;
    }

    .review-item {
        padding: 14px;
    }

    .note {
        font-size: 12px;
        padding: 16px;
    }
}
    </style>
</head>

<body>
    <div class="container">
        <h2>Vehicle Details</h2>
        <h4><?= $vehicle_name ?></h4>
        <div class="vehicle-card">
            <img src="<?= $image ?>" alt="<?= $vehicle_name ?>" class="vehicle-image" />
            <div class="vehicle-info">
                <div class="details">
                    <span><strong>Seats:</strong> <?= $seats ?></span>
                    <span><strong>Fuel:</strong> <?= $fuel ?></span>
                    <span><strong>Transmission:</strong> <?= $transmission ?></span>
                    <div class="estimated-box">
                       Estimated Amount: <strong>NPR <?= number_format($total_amount, 2) ?></strong>
                    </div>
                </div>

                <form id="bookingForm" action="confirm.php" method="POST">

                    <input type="hidden" name="vehicle_id" value="<?= $vehicle_id ?>">
                    <input type="hidden" name="vehicle_name" value="<?= $vehicle_name ?>">
                    <input type="hidden" name="price" value="<?= $total_amount ?>">

                    <input type="hidden" name="pickup" value="<?= $_SESSION['pickup'] ?>">
                    <input type="hidden" name="drop_location" value="<?= $_SESSION['drop'] ?>">
                    <input type="hidden" name="pickup_date" value="<?= $_SESSION['pickup_date'] ?>">
                    <input type="hidden" name="return_date" value="<?= $_SESSION['return_date'] ?>">
                    <p><strong>Price per day:</strong> NPR <?= number_format($price_per_day, 2) ?></p>
                    <p><strong>Total days:</strong> <?= $days ?></p>
                    <p><strong>Total amount:</strong> NPR <?= number_format($total_amount, 2) ?></p>

                    <div style="display: flex; gap: 15px; margin-top: 15px;">
                        <button type="button" class="book-btn" onclick="openConfirmPopup()">
                            Confirm Book & Pay
                        </button>
                        <a href="home.php" class="book-btn" style="background: #0d6efd; text-decoration: none; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 16px rgba(13, 110, 253, 0.25);">
                            Back to Home
                        </a>
                    </div>

                </form>

            </div>
        </div>
        <div class="note">
            <strong>Note (For Outside Kathmandu Valley Only):</strong><br />
            The displayed fuel price is only for the two-way trip distance between the departure and
            arrival destinations. An extra fuel charge at the per-kilometer specified rate will be applied
            for any additional kilometers covered during the booking period. This charge will be added to
            your total price at the end of your booking.
        </div>

        <!-- ============ REVIEWS SECTION ============ -->
        <div class="reviews-section">
            <h3>📋 Customer Reviews</h3>
            
            <?php if ($rating_stats['total_reviews'] > 0): ?>
                <div class="rating-summary">
                    <div class="rating-overview">
                        <div class="average-rating"><?= round($rating_stats['avg_rating'], 1) ?></div>
                        <div class="star-rating">
                            <?php 
                            $avg = round($rating_stats['avg_rating']);
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $avg ? '<span class="star filled">★</span>' : '<span class="star">☆</span>';
                            }
                            ?>
                            <span class="rating-value"><?= $avg ?>/5</span>
                        </div>
                        <div class="total-reviews">Based on <?= $rating_stats['total_reviews'] ?> reviews</div>
                    </div>

                    <div class="rating-distribution">
                        <?php
                        $ratings = [
                            5 => $rating_stats['five_star'],
                            4 => $rating_stats['four_star'],
                            3 => $rating_stats['three_star'],
                            2 => $rating_stats['two_star'],
                            1 => $rating_stats['one_star']
                        ];

                        foreach ($ratings as $star => $count) {
                            $percentage = ($rating_stats['total_reviews'] > 0) ? 
                                (($count / $rating_stats['total_reviews']) * 100) : 0;
                        ?>
                            <div class="rating-row">
                                <div class="rating-label"><?= $star ?> ★</div>
                                <div class="rating-bar">
                                    <div class="rating-bar-fill" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <div class="rating-count"><?= $count ?></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="reviews-list">
                    <?php while ($review = $reviews->fetch_assoc()): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div>
                                    <div class="reviewer-name"><?= htmlspecialchars($review['name']) ?></div>
                                    <div class="star-rating" style="margin: 5px 0;">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $review['rating'] ? 
                                                '<span class="star filled">★</span>' : 
                                                '<span class="star">☆</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="review-date">
                                    <?= date('M d, Y', strtotime($review['created_at'])) ?>
                                </div>
                            </div>
                            <?php if ($review['comment']): ?>
                                <div class="review-comment"><?= htmlspecialchars($review['comment']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-reviews">
                    <p>⭐ No reviews yet. Be the first to review this vehicle!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="confirmPopup" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); justify-content:center; align-items:center; z-index:9999; animation: fadeIn 0.3s ease;">
        <div style="background:#fff; padding:32px; border-radius:16px; width:100%; max-width:400px; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,0.2); animation: slideUp 0.3s ease;">
            <h3 style="font-size:22px; font-weight:700; color:#1a202c; margin-bottom:12px;">Confirm Booking</h3>
            <p style="margin:20px 0; color:#6b7280; font-size:15px; line-height:1.6;">
                ⚠️ You are about to book this vehicle and proceed with payment. Are you sure?
            </p>
            <div style="display:flex; gap:12px; margin-top:28px;">
                <button onclick="confirmBooking()" style="flex:1; background:linear-gradient(135deg, #28a745, #20c997); color:#fff; padding:12px 16px; border:none; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer; letter-spacing:0.5px; transition:all 0.3s; box-shadow:0 4px 12px rgba(40, 167, 69, 0.25);">
                    ✓ CONFIRM
                </button>
                <button onclick="closeConfirmPopup()" style="flex:1; background:linear-gradient(135deg, #6c757d, #5a6268); color:#fff; padding:12px 16px; border:none; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer; letter-spacing:0.5px; transition:all 0.3s; box-shadow:0 4px 12px rgba(108, 117, 125, 0.25);">
                    ✕ CANCEL
                </button>
            </div>
        </div>
        <style>
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from { transform: translateY(20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            #confirmPopup button:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
            }
        </style>
    </div>

    <script>
        function openConfirmPopup() {
            document.getElementById("confirmPopup").style.display = "flex";
        }

        function closeConfirmPopup() {
            document.getElementById("confirmPopup").style.display = "none";
        }

        function confirmBooking() {
            document.getElementById("bookingForm").submit();
        }
    </script>
</body>

</html>