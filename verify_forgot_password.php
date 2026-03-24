<?php
session_cache_limiter('private_no_expire');
session_start();
require 'config.php';

/* Check if email is provided */
if (!isset($_POST['email'])) {
    header("Location: forgot_password.php");
    exit;
}

$email = trim($_POST['email']);

/* Validate email exists */
$stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $error = "No account found with this email address.";
} else {
    $user = $result->fetch_assoc();
    /* Store in session for next step */
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_user_id'] = $user['id'];
    $_SESSION['reset_username'] = $user['username'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - GoRent</title>
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
            font-size: 32px;
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

        .success-badge {
            background: #efe;
            border: 1px solid #cfc;
            color: #3c3;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
            font-weight: 600;
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

        .link-group a:hover {
            text-decoration: underline;
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
        <div class="header">
            <div class="logo">🔐</div>
            <h1>Verify Your Account</h1>
            <p>Answer the security question to reset your password</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error">
                <strong>❌ Error:</strong> <?= htmlspecialchars($error) ?><br><br>
                <a href="forgot_password.php" style="color: #c33; font-weight: 600;">Try again with a different email</a>
            </div>
        <?php else: ?>
            <div class="success-badge">
                ✓ Account found for: <?= htmlspecialchars($_SESSION['reset_username']) ?>
            </div>

            <div class="info-box">
                <strong>Security Verification:</strong><br>
                Please answer the security question below to confirm your identity and reset your password.
            </div>

            <form action="reset_password.php" method="POST" id="verifyForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?= htmlspecialchars($_SESSION['reset_username']) ?>" 
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="security_answer">Security Question: What is your favorite vehicle brand?</label>
                    <input 
                        type="text" 
                        id="security_answer" 
                        name="security_answer" 
                        placeholder="Enter your answer" 
                        required
                    >
                </div>

                <button type="submit" class="btn">Verify & Reset Password</button>
            </form>

            <div class="link-group">
                <a href="forgot_password.php">← Back to Forgot Password</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>