# Email Configuration Guide

## Overview
Your application now sends booking confirmation emails to users after they complete payment. This guide explains how to configure it.

## Files Created/Modified

1. **`email_helper.php`** (NEW) - Contains email sending functions
2. **`payment_success.php`** (MODIFIED) - Sends confirmation email after payment

## Configuration Steps

### 1. Gmail SMTP Setup (Recommended)

The email system uses Gmail's SMTP server by default. Follow these steps:

#### Step 1: Enable 2-Factor Authentication on Gmail
- Go to [Google Account Settings](https://myaccount.google.com/security)
- Scroll to "2-Step Verification" and enable it
- Complete the verification process

#### Step 2: Generate App Password
- Go to [App passwords](https://myaccount.google.com/apppasswords)
- Select "Mail" and "Windows Computer" (or your device)
- Google will generate a 16-character password
- Copy this password

#### Step 3: Update email_helper.php
Open `email_helper.php` and find these lines (around line 72-75):

```php
$mail->Username   = 'your-email@gmail.com';  // Change this
$mail->Password   = 'your-app-password';      // Change this
```

Replace:
- `your-email@gmail.com` → Your actual Gmail address
- `your-app-password` → The 16-character password from Step 2

**Example:**
```php
$mail->Username   = 'gorent.service@gmail.com';
$mail->Password   = 'abcd efgh ijkl mnop';  // 16-char app password (spaces are fine)
```

Also update the "From" address (around line 80):
```php
$mail->setFrom('noreply@gorent.com', 'GoRent');
```

### 2. Alternative: Use Other SMTP Providers

If you want to use a different email service, update these settings in `email_helper.php`:

**For Office 365/Outlook:**
```php
$mail->Host       = 'smtp.office365.com';
$mail->Port       = 587;
```

**For SendGrid:**
```php
$mail->Host       = 'smtp.sendgrid.net';
$mail->Username   = 'apikey';
$mail->Password   = 'YOUR_SENDGRID_API_KEY';
$mail->Port       = 587;
```

**For Custom SMTP:**
```php
$mail->Host       = 'your-smtp-server.com';
$mail->Port       = 587;  // or 465 for SSL
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // or ENCRYPTION_SMTPS
```

## Email Templates

### Booking Confirmation Email
Sent automatically after payment is confirmed. Includes:
- Booking ID
- Vehicle details
- Pickup/Drop locations
- Pickup/Return dates
- Payment information
- Transaction ID
- Booking status

### Cancellation Email
A `sendCancellationEmail()` function is also available for use when users cancel bookings:

```php
sendCancellationEmail($conn, $user_id, $booking_id, "User requested cancellation");
```

You can call this from `cancel_booking.php` if needed.

## Testing Email Sending

### Test Method 1: Complete a test booking
1. Go through the booking process
2. Complete payment
3. Check the user's email inbox
4. Look for "Booking Confirmation" email

### Test Method 2: Direct function call
Create a test file `test_email.php`:

```php
<?php
session_start();
require 'config.php';
require 'email_helper.php';

// Set a test user session
$_SESSION['user_id'] = 1;  // Replace with actual user ID

// Send test email for booking ID
$result = sendBookingConfirmationEmail($conn, 1, 1, [
    'transaction_id' => 'TEST-123456',
    'date' => date("d M Y, h:i A"),
    'amount' => 500,
    'method' => 'eSewa',
    'remarks' => 'Test email'
]);

if ($result) {
    echo "Email sent successfully!";
} else {
    echo "Email failed to send.";
}
?>
```

## Troubleshooting

### Email not sending?

1. **Check credentials**
   - Verify Gmail username and app password are correct
   - Test manually with Gmail: try sending an email with these credentials from another app

2. **Check error logs**
   - Look in your PHP error logs (usually in `/xampp/apache/logs/`)
   - The email system logs errors but doesn't break the booking

3. **Firewall/Network**
   - Ensure your server can access smtp.gmail.com on port 587
   - Check with your hosting provider if SMTP is blocked

4. **Gmail Security**
   - If you still get errors, visit [Less secure app access](https://myaccount.google.com/lesssecureapps) and enable it
   - Note: Google may disable this setting again automatically

### Email subject/content not showing?
- Check if database user email is correctly stored
- Verify booking data exists in database

## Email Display in UI

After payment, users will see:
- ✓ **Green message** if email was sent successfully
- ⚠ **Red message** if email failed (but booking is still confirmed)

This ensures the booking never fails even if email sending has issues.

## Security Notes

1. **Never commit credentials** - Don't push your email password to Git
2. **Use environment variables** (optional upgrade):
   ```php
   $mail->Username = getenv('GMAIL_USERNAME');
   $mail->Password = getenv('GMAIL_PASSWORD');
   ```

3. **Encrypt sensitive data** - For production, consider encrypting stored credentials

## Next Steps (Optional Enhancements)

1. **Schedule email resend** - For failed emails, create a cron job to retry
2. **Email templates** - Move to `.html` template files
3. **Admin notifications** - Send a copy to admin@gorent.com
4. **SMS integration** - Add SMS notifications alongside email
5. **Email verification** - Verify user emails before sending confirmations

---

**Need help?** Check the browser console in payment_success.php page, or review `email_helper.php` error logging (line 87).
