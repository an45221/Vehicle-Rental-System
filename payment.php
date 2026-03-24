<?php
require 'config.php';
require 'booking_helper.php';

$booking_id = $_GET['booking_id'] ?? null;

// Clean up any expired bookings
cleanupExpiredBookings($conn, 30); // 30 minute expiration

// Check if this booking is expired
if ($booking_id && isBookingExpired($conn, $booking_id, 30)) {
    cancelExpiredBooking($conn, $booking_id);
    die("Your booking has expired. The 30-minute payment window has passed. Please book again.");
}

// fetch booking price safely
$stmt = $conn->prepare("SELECT price FROM bookings WHERE id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    die("Booking not found");
}

$price = $booking['price'];
$remaining_minutes = getRemainingTime($conn, $booking_id, 30);
?>

<style>
    /* =====================
       VARIABLES & RESET
    ===================== */
:root {
    --primary: #1f8f4c;
    --primary-light: #2da85d;
    --primary-dark: #187a3d;
    --secondary: #ff8c00;
    --secondary-light: #ffb347;
    --accent: #ffc107;
    --success: #28a745;
    --danger: #dc3545;
    --warning: #ffc107;
    --light: #f8fafc;
    --light-bg: #f0f4f8;
    --border: #e0e7ff;
    --text-dark: #1a202c;
    --text-muted: #6b7280;
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
    --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.15);
    --radius: 12px;
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
    margin: 0;
}

/* =====================
   HEADER
===================== */
.header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    padding: 24px;
    text-align: center;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 0.5px;
    box-shadow: var(--shadow-md);
    text-transform: uppercase;
}

/* =====================
   TIMER SECTION
===================== */
.timer-container {
    max-width: 600px;
    margin: 24px auto;
    padding: 0 20px;
}

.timer-box {
    background: linear-gradient(135deg, #fff9e6 0%, #fff5cc 100%);
    border: 2px solid var(--warning);
    color: #654321;
    padding: 18px 20px;
    border-radius: var(--radius);
    text-align: center;
    box-shadow: 0 2px 12px rgba(255, 193, 7, 0.15);
    animation: slideDown 0.4s ease;
}

.timer-box strong {
    font-size: 16px;
    font-weight: 700;
    display: block;
    margin-bottom: 8px;
    color: #333;
}

#timerDisplay {
    font-size: 28px;
    font-weight: 700;
    color: var(--secondary);
    font-family: 'Courier New', monospace;
}

.timer-box small {
    display: block;
    margin-top: 8px;
    font-size: 12px;
    color: #654321;
    opacity: 0.9;
}

/* =====================
   PAYMENT OPTIONS
===================== */
.options {
    max-width: 700px;
    margin: 40px auto 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 24px;
    padding: 0 20px;
}

.option-card {
    background: white;
    padding: 32px 24px;
    border-radius: var(--radius);
    text-align: center;
    cursor: pointer;
    box-shadow: var(--shadow-md);
    border: 2px solid var(--border);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.option-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    transform: scaleX(0);
    transform-origin: left;
    transition: var(--transition);
}

.option-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary);
}

.option-card:hover::before {
    transform: scaleX(1);
}

.option-card h3 {
    margin-top: 16px;
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
    letter-spacing: 0.5px;
}

.option-card img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    transition: var(--transition);
}

.option-card:hover img {
    transform: scale(1.1);
}

/* =====================
   SLIDE PANEL
===================== */
.panel {
    position: fixed;
    top: 0;
    right: -420px;
    width: 420px;
    max-width: 100%;
    height: 100vh;
    background: white;
    box-shadow: -5px 0 20px rgba(0, 0, 0, 0.2);
    transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 9999;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.panel.active {
    right: 0;
}

.panel-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    padding: 20px;
    font-size: 18px;
    font-weight: 700;
    display: flex;
    justify-content: space-between;
    align-items: center;
    letter-spacing: 0.5px;
    flex-shrink: 0;
    box-shadow: var(--shadow-md);
}

.panel-header span:last-child {
    cursor: pointer;
    font-size: 24px;
    transition: var(--transition);
    padding: 4px 8px;
    border-radius: 6px;
}

.panel-header span:last-child:hover {
    background: rgba(255, 255, 255, 0.2);
}

.panel-body {
    padding: 28px;
    flex: 1;
    overflow-y: auto;
}

.field {
    margin-bottom: 20px;
}

.field label {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-dark);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 8px;
}

.field input,
.field textarea {
    width: 100%;
    padding: 11px 14px;
    border-radius: 8px;
    border: 1.5px solid var(--border);
    background: var(--light);
    color: var(--text-dark);
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    transition: var(--transition);
}

.field input:focus,
.field textarea:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 3px rgba(31, 143, 76, 0.1);
}

.field textarea {
    resize: vertical;
    min-height: 100px;
}

.bottom-actions {
    display: flex;
    gap: 12px;
    margin-top: 28px;
    flex-shrink: 0;
    padding-top: 20px;
    border-top: 1.5px solid var(--border);
}

.bottom-actions button {
    flex: 1;
    padding: 13px 16px;
    border: none;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    border-radius: 8px;
    transition: var(--transition);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    position: relative;
    overflow: hidden;
}

.pay {
    background: linear-gradient(135deg, var(--success) 0%, #20c997 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.25);
}

.pay:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.35);
}

.cancel {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.25);
}

.cancel:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(108, 117, 125, 0.35);
}

/* =====================
   ANIMATIONS
===================== */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* =====================
   RESPONSIVE DESIGN
===================== */
@media (max-width: 768px) {
    .options {
        grid-template-columns: 1fr;
        gap: 18px;
        margin: 30px auto 20px;
    }

    .option-card {
        padding: 24px 20px;
    }

    .panel {
        width: 100%;
        right: -100%;
    }

    .header {
        padding: 20px;
        font-size: 20px;
    }

    .timer-box {
        margin: 20px;
    }

    #timerDisplay {
        font-size: 24px;
    }

    .panel-body {
        padding: 20px;
    }

    .bottom-actions {
        gap: 10px;
    }

    .bottom-actions button {
        padding: 11px 14px;
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .header {
        padding: 16px;
        font-size: 18px;
    }

    .options {
        gap: 14px;
        padding: 0 12px;
        margin: 24px auto 16px;
    }

    .option-card {
        padding: 20px 16px;
    }

    .option-card img {
        width: 70px;
        height: 70px;
    }

    .option-card h3 {
        font-size: 16px;
        margin-top: 12px;
    }

    .timer-box {
        margin: 16px 12px;
        padding: 14px 16px;
    }

    #timerDisplay {
        font-size: 20px;
    }

    .panel-body {
        padding: 16px;
    }

    .field {
        margin-bottom: 16px;
    }

    .bottom-actions {
        margin-top: 20px;
    }
}
</style>
</head>

<body>

<div class="header">
    <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <a href="home.php" style="color: white; text-decoration: none; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 5px;">
            <span>←</span> BACK
        </a>
        <span>💳 Choose Payment Method</span>
        <div style="width: 60px;"></div> <!-- Spacer for symmetry -->
    </div>
</div>

<?php if ($remaining_minutes !== null): ?>
<div class="timer-container">
    <div class="timer-box">
        <strong>⏱️ Time Remaining:</strong>
        <div id="timerDisplay"><?= $remaining_minutes ?></div>
        <small>Complete payment within this time or your booking will be cancelled</small>
    </div>
</div>
<?php endif; ?>

<div class="options">
    <div class="option-card" onclick="openPanel('KHALTI')">
        <img src="images/khalti.png" alt="Khalti">
        <h3>KHALTI</h3>
    </div>

    <div class="option-card" onclick="openPanel('ESEWA')">
        <img src="images/esewaimg.png" alt="Esewa">
        <h3>ESEWA</h3>
    </div>
</div>

<!-- SLIDE PANEL -->
<div class="panel" id="panel">
    <form action="payment_success.php" method="POST">
        <div class="panel-header">
            <span id="methodTitle">Payment</span>
            <span style="cursor:pointer;" onclick="closePanel()">✕</span>
        </div>

        <div class="panel-body">
            <div class="field">
                <label>Selected Wallet</label>
                <input type="text" name="method" id="method" readonly>
            </div>

            <div class="field">
                <label>Payable Amount</label>
                <input type="text" name="amount" value="<?= $price ?>" readonly>
            </div>

            <div class="field">
                <label>Booking Remarks</label>
                <textarea name="remarks" rows="4" required></textarea>
            </div>

            <input type="hidden" name="vehicle" value="<?= $vehicle ?>">

            <div class="bottom-actions">
                <button type="submit" class="pay">PAY NOW</button>
                <input type="hidden" name="booking_id" value="<?= $booking_id ?>">

                <button type="button" class="cancel" onclick="closePanel()">CANCEL</button>
            </div>
        </div>
    </form>
</div>

<script>
function openPanel(method) {
    document.getElementById("method").value = method;
    document.getElementById("methodTitle").innerText = method + " Payment";
    document.getElementById("panel").classList.add("active");
}

function closePanel() {
    document.getElementById("panel").classList.remove("active");
}

// Countdown timer for payment expiration
<?php if ($remaining_minutes !== null): ?>
let remainingSeconds = <?= $remaining_minutes * 60 ?>;

function updateTimer() {
    if (remainingSeconds > 0) {
        remainingSeconds--;
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        
        const timerElement = document.getElementById('timerDisplay');
        if (timerElement) {
            timerElement.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        }
        
        // If time is running out, change color to red
        if (remainingSeconds < 300) { // Less than 5 minutes
            const timerContainer = timerElement.closest('.timer-box');
            if (timerContainer) {
                timerContainer.style.background = 'linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%)';
                timerContainer.style.borderColor = '#f5c6cb';
                timerContainer.style.color = '#721c24';
            }
        }
        
        setTimeout(updateTimer, 1000);
    } else {
        // Time expired
        const timerElement = document.getElementById('timerDisplay');
        if (timerElement) {
            timerElement.textContent = '0:00';
        }
        alert('Your booking has expired! The 30-minute payment window has passed.');
        window.location.href = 'mybooking.php';
    }
}

// Start the timer
updateTimer();
<?php endif; ?>
</script>

</body>
</html>
