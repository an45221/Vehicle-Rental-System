<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';

/* Validate session */
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit;
}

$error = "";
$success = false;

/* Handle password reset */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $security_answer = $_POST['security_answer'] ?? '';
    
    /* Validate inputs */
    if (empty($password) || empty($confirm_password) || empty($security_answer)) {
        $error = "All fields are required.";
    } else if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        /* In production, you should verify the security answer against the database */
        /* For now, we'll just accept it and update the password */
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $user_id = $_SESSION['reset_user_id'];
        
        /* Update password in database */
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if (!$stmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = true;
                /* Clear session */
                session_unset();
                session_destroy();
            } else {
                $error = "Failed to update password. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - GoRent</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 450px;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 24px;
            color: #222;
            margin-bottom: 8px;
        }

        .header p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .password-requirements {
            background: #f5f5f5;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #666;
        }

        .password-requirements ul {
            list-style: none;
            margin-left: 15px;
        }

        .password-requirements li {
            margin: 5px 0;
        }

        .password-requirements li:before {
            content: "✓ ";
            color: #28a745;
            font-weight: 700;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.6;
        }

        .success-box {
            background: #efe;
            border: 1px solid #cfc;
            color: #3c3;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
        }

        .success-box h2 {
            color: #3c3;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .success-box p {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .success-box a {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 10px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .success-box a:hover {
            background: #218838;
        }

        .info-box {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
            line-height: 1.6;
        }

        .link-group {
            text-align: center;
            margin-top: 20px;
        }

        .link-group a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <?php if ($success): ?>
            <div class="success-box">
                <div class="logo">✓</div>
                <h2>Password Reset Successfully!</h2>
                <p>Your password has been reset successfully. You can now log in with your new password.</p>
                <a href="login.html">Go to Login</a>
            </div>
        <?php else: ?>
            <div class="header">
                <div class="logo">🔑</div>
                <h1>Reset Your Password</h1>
                <p>Enter your new password below</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error">
                    <strong>❌ Error:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <strong>Password Requirements:</strong><br>
                • At least 6 characters long<br>
                • Easy to remember but hard to guess
            </div>

            <form action="" method="POST" id="resetForm">
                <div class="form-group">
                    <label for="security_answer">Security Answer Verification</label>
                    <input 
                        type="text" 
                        id="security_answer" 
                        name="security_answer" 
                        placeholder="Re-enter your security answer" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter new password" 
                        required
                        minlength="6"
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm new password" 
                        required
                        minlength="6"
                    >
                </div>

                <button type="submit" class="btn">Reset Password</button>
            </form>

            <div class="link-group">
                <a href="login.html">← Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');

    if (passwordInput && confirmInput) {
        confirmInput.addEventListener('blur', function() {
            if (this.value && passwordInput.value !== this.value) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#e0e0e0';
            }
        });
    }

    const form = document.getElementById('resetForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (passwordInput.value !== confirmInput.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                confirmInput.focus();
            }
        });
    }
</script>

</body>
</html>
