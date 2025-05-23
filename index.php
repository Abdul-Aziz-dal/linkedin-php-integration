<?php
session_start();

if (isset($_SESSION['access_token']) && isset($_SESSION['linkedin_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login with LinkedIn</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #0077b5, #004471);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }

        h1 {
            margin-bottom: 10px;
        }

        p {
            margin-bottom: 40px;
            font-weight: 300;
            max-width: 500px;
        }

        .login-btn {
            background: white;
            color: #0077b5;
            border: none;
            padding: 12px 24px;
            font-size: 18px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            text-decoration:none;
        }

        .login-btn:hover {
            background: #f2f2f2;
        }

        .login-btn img {
            height: 28px;
            margin-right: 12px;
        }
    </style>
</head>
<body>

    <h1>Welcome to LinkedIn Poster</h1>
    <p>Click the button below to sign in with LinkedIn and start sharing your posts directly.</p>

    <a href="login.php" class="login-btn">
        <img src="https://cdn-icons-png.flaticon.com/512/174/174857.png" alt="LinkedIn">
        Sign in with LinkedIn
    </a>

</body>
</html>
