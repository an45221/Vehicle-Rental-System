<?php
/**
 * Email Helper Functions
 * Handles sending emails using PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send booking confirmation email
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param int $booking_id Booking ID
 * @param array $transaction_details Transaction details array
 * @return bool True if email sent successfully, false otherwise
 */
function sendBookingConfirmationEmail($conn, $user_id, $booking_id, $transaction_details = []) {
    // Fetch user details
    $user_stmt = $conn->prepare("SELECT email, username FROM users WHERE id = ?");
    if (!$user_stmt) {
        return false;
    }
    
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();
    
    if (!$user_result) {
        return false;
    }
    
    $user_email = $user_result['email'];
    $user_name = $user_result['username'];
    
    // Fetch booking details
    $booking_stmt = $conn->prepare("
        SELECT b.*, v.vehicle_name 
        FROM bookings b 
        LEFT JOIN vehicles v ON b.vehicle_id = v.id 
        WHERE b.id = ? AND b.user_id = ?
    ");
    if (!$booking_stmt) {
        return false;
    }
    
    $booking_stmt->bind_param("ii", $booking_id, $user_id);
    $booking_stmt->execute();
    $booking = $booking_stmt->get_result()->fetch_assoc();
    $booking_stmt->close();
    
    if (!$booking) {
        return false;
    }
    
    // Extract transaction details
    $transaction_id = $transaction_details['transaction_id'] ?? ('GR-' . rand(100000, 999999));
    $payment_method = $transaction_details['method'] ?? 'Unknown';
    $amount_paid = $transaction_details['amount'] ?? $booking['price'];
    $payment_date = $transaction_details['date'] ?? date("d M Y, h:i A");
    
    // Format dates and times
    $pickup_date = date("d M Y", strtotime($booking['pickup_date']));
    $return_date = date("d M Y", strtotime($booking['return_date']));
    
    // Build HTML email content
    $email_subject = "Booking Confirmation - SCPL-" . $booking_id;
    
    $email_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                background: #f5f5f5;
                margin: 0;
                padding: 0;
            }
            .email-container {
                max-width: 600px;
                margin: 20px auto;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px 20px;
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
            }
            .content {
                padding: 30px 20px;
            }
            .greeting {
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 20px;
                color: #333;
            }
            .section {
                margin-bottom: 25px;
            }
            .section-title {
                font-size: 16px;
                font-weight: 700;
                color: #667eea;
                margin-bottom: 12px;
                border-bottom: 2px solid #667eea;
                padding-bottom: 8px;
            }
            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid #eee;
            }
            .detail-row:last-child {
                border-bottom: none;
            }
            .detail-label {
                font-weight: 600;
                color: #555;
            }
            .detail-value {
                text-align: right;
                color: #333;
            }
            .status-badge {
                display: inline-block;
                background: #28a745;
                color: white;
                padding: 8px 16px;
                border-radius: 20px;
                font-weight: 600;
                font-size: 14px;
            }
            .footer {
                background: #f9f9f9;
                padding: 20px;
                text-align: center;
                border-top: 1px solid #eee;
                font-size: 13px;
                color: #666;
            }
            .footer a {
                color: #667eea;
                text-decoration: none;
            }
            .footer a:hover {
                text-decoration: underline;
            }
            .note {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 12px 15px;
                margin: 20px 0;
                border-radius: 4px;
                font-size: 14px;
                color: #856404;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='header'>
                <h1>✓ Booking Confirmed</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px;'>Your reservation is confirmed!</p>
            </div>
            
            <div class='content'>
                <div class='greeting'>
                    Hi " . htmlspecialchars($user_name) . ",
                </div>
                
                <p>Your booking has been successfully confirmed 🎉</p>
                
                <!-- Booking Details Section -->
                <div class='section'>
                    <div class='section-title'>Booking Details</div>
                    <div class='detail-row'>
                        <span class='detail-label'>Booking ID:</span>
                        <span class='detail-value'><strong>SCPL-" . $booking_id . "</strong></span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Vehicle:</span>
                        <span class='detail-value'>" . htmlspecialchars($booking['vehicle_name']) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Pickup Location:</span>
                        <span class='detail-value'>" . htmlspecialchars($booking['pickup']) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Drop Location:</span>
                        <span class='detail-value'>" . htmlspecialchars($booking['drop_location']) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Pickup Date:</span>
                        <span class='detail-value'>" . $pickup_date . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Return Date:</span>
                        <span class='detail-value'>" . $return_date . "</span>
                    </div>
                </div>
                
                <!-- Payment Details Section -->
                <div class='section'>
                    <div class='section-title'>Payment Details</div>
                    <div class='detail-row'>
                        <span class='detail-label'>Amount Paid:</span>
                        <span class='detail-value'><strong>NPR " . number_format($amount_paid, 2) . "</strong></span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Payment Method:</span>
                        <span class='detail-value'>" . htmlspecialchars($payment_method) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Transaction ID:</span>
                        <span class='detail-value'>" . htmlspecialchars($transaction_id) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='detail-label'>Status:</span>
                        <span class='detail-value'><span class='status-badge'>Confirmed</span></span>
                    </div>
                </div>
                
                <!-- Note -->
                <div class='note'>
                    📍 <strong>Please show this email at the time of pickup/arrival.</strong>
                </div>
                
                <p>Thank you for choosing us! We look forward to serving you.</p>
                
                <p style='margin-top: 30px;'>
                    If you have any questions or need assistance, please don't hesitate to contact us.
                </p>
            </div>
            
            <div class='footer'>
                <p style='margin: 0 0 10px 0;'>
                    <strong>GoRent - Vehicle Rental Service</strong>
                </p>
                <p style='margin: 0;'>
                    📧 Email: <a href='mailto:support@gorent.com'>support@gorent.com</a> | 
                    📱 Phone: +977-1-XXXX-XXXX
                </p>
                <p style='margin: 15px 0 0 0; font-size: 12px; color: #999;'>
                    This is an automated confirmation email. Please do not reply to this email.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    try {
        // Load PHPMailer
        require 'PHPMailer-master/PHPMailer-master/src/Exception.php';
        require 'PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
        require 'PHPMailer-master/PHPMailer-master/src/SMTP.php';
        
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'anuragbudha7@gmail.com';  // CHANGE: Use your Gmail address
        $mail->Password   = 'xbxaxspkfjeihmum';    // CHANGE: Use 16-char app password from https://myaccount.google.com/apppasswords
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('anuragbudha7@gmail.com', 'GoRent');
        $mail->addAddress($user_email, $user_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $email_subject;
        $mail->Body    = $email_body;
        
        // Send email
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Log the error but don't fail the booking
        error_log("Email sending failed for booking $booking_id: " . $e->getMessage());
        return false;
    }
}

/**
 * Send cancellation email
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param int $booking_id Booking ID
 * @param string $cancel_reason Reason for cancellation
 * @return bool True if email sent successfully
 */
function sendCancellationEmail($conn, $user_id, $booking_id, $cancel_reason = '') {
    // Fetch user details
    $user_stmt = $conn->prepare("SELECT email, username FROM users WHERE id = ?");
    if (!$user_stmt) {
        return false;
    }
    
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();
    
    if (!$user_result) {
        return false;
    }
    
    $user_email = $user_result['email'];
    $user_name = $user_result['username'];
    
    // Fetch booking details
    $booking_stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    if (!$booking_stmt) {
        return false;
    }
    
    $booking_stmt->bind_param("ii", $booking_id, $user_id);
    $booking_stmt->execute();
    $booking = $booking_stmt->get_result()->fetch_assoc();
    $booking_stmt->close();
    
    if (!$booking) {
        return false;
    }
    
    $email_subject = "Booking Cancelled - SCPL-" . $booking_id;
    
    $email_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
            .header { background: #dc3545; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
            .content { padding: 20px; border: 1px solid #ddd; border-radius: 0 0 8px 8px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2 style='margin: 0;'>Booking Cancelled</h2>
            </div>
            <div class='content'>
                <p>Hi " . htmlspecialchars($user_name) . ",</p>
                
                <p>Your booking has been cancelled.</p>
                
                <p><strong>Booking Details:</strong></p>
                <ul>
                    <li>Booking ID: SCPL-" . $booking_id . "</li>
                    <li>Vehicle: " . htmlspecialchars($booking['vehicle_name']) . "</li>
                    <li>Booking Date: " . date('d M Y', strtotime($booking['pickup_date'])) . "</li>
                </ul>
                
                " . (!empty($cancel_reason) ? "<p><strong>Reason:</strong> " . htmlspecialchars($cancel_reason) . "</p>" : "") . "
                
                <p>If you have any questions, please contact us at support@gorent.com</p>
                
                <p>Thank you for using GoRent!</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    try {
        require 'PHPMailer-master/PHPMailer-master/src/Exception.php';
        require 'PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
        require 'PHPMailer-master/PHPMailer-master/src/SMTP.php';
        
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'anuragbudha7@gmail.com';  // CHANGE: Use your Gmail address
        $mail->Password   = 'xbxaxspkfjeihmum';    // CHANGE: Use 16-char app password from https://myaccount.google.com/apppasswords
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        $mail->setFrom('anuragbudha5@gmail.com', 'GoRent');

        $mail->addAddress($user_email, $user_name);
        
        $mail->isHTML(true);
        $mail->Subject = $email_subject;
        $mail->Body    = $email_body;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Cancellation email failed for booking $booking_id: " . $e->getMessage());
        return false;
    }
}
?>
