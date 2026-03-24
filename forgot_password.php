<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - GoRent</title>
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

        input::placeholder {
            color: #999;
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

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
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

        .info-box strong {
            color: #333;
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
            transition: color 0.3s;
        }

        .link-group a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            font-size: 13px;
            color: #999;
        }

        .error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .success {
            background: #efe;
            border: 1px solid #cfc;
            color: #3c3;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        @media (max-width: 480px) {
            .card {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="header">
            <div class="logo">🚗</div>
            <h1>Forgot Password?</h1>
            <p>Enter your email address and we'll help you reset your password</p>
        </div>

        <div class="info-box">
            <strong>How it works:</strong><br>
            Enter your email address, verify your identity, and create a new password instantly.
        </div>

        <form action="verify_forgot_password.php" method="POST" id="forgotForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="you@example.com" 
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="btn">Verify Email & Reset Password</button>
        </form>

        <div class="divider">OR</div>

        <div class="link-group">
            <p>Remember your password?</p>
            <a href="login.html">← Back to Login</a>
        </div>
    </div>
</div>

<script>
    document.getElementById('forgotForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        if (!email) {
            e.preventDefault();
            alert('Please enter your email address');
        }
    });
</script>

</body>
</html>
