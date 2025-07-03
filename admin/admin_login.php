<?php
session_start();

// Hashed password for '12345'
$admin_user = "admin";
$admin_hash = "$2y$10$1OLTZSOZJVsReqWiHQaqP.FeoQ//ik03Y4yA5O/z4W4BIPb52u1cS";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? 'admin';
    $p = $_POST['password'] ?? '12345';

    if ($u === $admin_user && password_verify($p, $admin_hash)) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $err = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #dfe9f3, #ffffff);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 70%;
            max-width: 500px;
            background: white;
            padding: 40px 50px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            border-radius: 16px;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 14px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 14px;
            background-color: #3498db;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #2980b9;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Admin Login</h2>
    <?php if (!empty($err)) echo "<p class='error'>$err</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Admin Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Login">
    </form>
</div>
</body>
</html>
