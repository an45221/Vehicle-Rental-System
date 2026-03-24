<?php
/**
 * Cancellation Feedback Page
 * Ask user for feedback when they cancel a booking
 */

session_cache_limiter('private_no_expire');
session_start();
require 'config.php';
require 'review_helper.php';

// 🔐 USER AUTH
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$booking_id = intval($_GET['booking_id'] ?? 0);

if ($booking_id === 0) {
    header("Location: mybooking.php");
    exit;
}

// Get booking details
$stmt = $conn->prepare("
    SELECT b.*, v.vehicle_name, v.id as vehicle_id
    FROM bookings b
    JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.id = ? AND b.user_id = ? AND b.booking_status = 'cancelled'
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header("Location: mybooking.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancelled - Tell Us Why</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 550px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 8px;
        }

        .header p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .success-badge {
            display: inline-block;
            background: #d4edda;
            color: #155724;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .booking-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #667eea;
        }

        .booking-summary p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }

        .booking-summary strong {
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .feedback-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .feedback-option {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .feedback-option:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .feedback-option input[type="radio"] {
            margin-right: 10px;
            cursor: pointer;
        }

        .feedback-option input[type="radio"]:checked + label {
            color: #667eea;
            font-weight: 600;
        }

        .feedback-option.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .feedback-option label {
            margin: 0;
            cursor: pointer;
            flex: 1;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
            transition: border-color 0.3s ease;
        }

        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .char-count {
            text-align: right;
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-skip {
            background: #f0f0f0;
            color: #333;
        }

        .btn-skip:hover {
            background: #e0e0e0;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 12px;
            border-radius: 4px;
            margin-top: 20px;
            font-size: 13px;
            color: #1565c0;
            line-height: 1.5;
        }

        @media (max-width: 600px) {
            .container {
                padding: 25px;
            }

            .header h2 {
                font-size: 20px;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="success-badge">✅ Booking Cancelled</div>
            <h2>We'd Like Your Feedback</h2>
            <p>Your feedback helps us improve our service. It takes just a minute!</p>
        </div>

        <div class="booking-summary">
            <p><strong>Vehicle:</strong> <?= htmlspecialchars($booking['vehicle_name']) ?></p>
            <p><strong>Booking ID:</strong> SCPL-<?= $booking_id ?></p>
        </div>

        <form id="feedbackForm" method="POST" action="save_cancellation_feedback.php">
            <input type="hidden" name="booking_id" value="<?= $booking_id ?>">

            <div class="form-group">
                <label>Why did you cancel the booking? *</label>
                <div class="feedback-options" id="reasonOptions">
                    <div class="feedback-option">
                        <input type="radio" id="reason1" name="cancel_feedback" value="Found better price" required>
                        <label for="reason1">Found a better price elsewhere</label>
                    </div>
                    <div class="feedback-option">
                        <input type="radio" id="reason2" name="cancel_feedback" value="Inconvenient location" required>
                        <label for="reason2">Inconvenient pickup/drop-off location</label>
                    </div>
                    <div class="feedback-option">
                        <input type="radio" id="reason3" name="cancel_feedback" value="Vehicle not available" required>
                        <label for="reason3">Required vehicle not available</label>
                    </div>
                    <div class="feedback-option">
                        <input type="radio" id="reason4" name="cancel_feedback" value="Poor service" required>
                        <label for="reason4">Poor customer service</label>
                    </div>
                    <div class="feedback-option">
                        <input type="radio" id="reason5" name="cancel_feedback" value="Other" required>
                        <label for="reason5">Other reason</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="additionalFeedback">Additional Comments (Optional)</label>
                <textarea id="additionalFeedback" name="additional_feedback" placeholder="Please share any additional feedback or suggestions..."></textarea>
                <div class="char-count"><span id="charCount">0</span>/500</div>
            </div>

            <div class="info-box">
                <strong>💡 Tip:</strong> Your feedback helps us identify areas for improvement and provide better service in the future.
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Submit Feedback</button>
                <button type="button" class="btn-skip" onclick="skipFeedback()">Skip for Now</button>
            </div>
        </form>
    </div>

    <script>
        // Handle feedback option selection
        const options = document.querySelectorAll('.feedback-option');
        options.forEach(option => {
            option.addEventListener('click', () => {
                options.forEach(o => o.classList.remove('selected'));
                option.classList.add('selected');
                option.querySelector('input[type="radio"]').checked = true;
            });
        });

        // Handle character count
        const textarea = document.getElementById('additionalFeedback');
        const charCount = document.getElementById('charCount');
        textarea.addEventListener('input', () => {
            charCount.textContent = textarea.value.length;
            if (textarea.value.length > 500) {
                textarea.value = textarea.value.substring(0, 500);
                charCount.textContent = '500';
            }
        });

        // Form submission
        document.getElementById('feedbackForm').addEventListener('submit', (e) => {
            const selected = document.querySelector('input[name="cancel_feedback"]:checked');
            if (!selected) {
                e.preventDefault();
                alert('Please select a reason for cancellation');
            }
        });

        // Skip feedback
        function skipFeedback() {
            window.location.href = 'mybooking.php?status=cancelled';
        }
    </script>
</body>
</html>
