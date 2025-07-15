<?php
session_start();

$err = ''; // Prevents undefined variable warning

// Check only on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    $l = trim($_POST['level'] ?? '');

    $valid = false;

    // Check against credentials stored in PwdFile.txt
    $lines = file("AppFiles/PwdFile.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (trim($line) === "$l $u $p") {
            $valid = true;
            break;
        }
    }

    if ($valid) {
        $_SESSION['user'] = $u;
        $_SESSION['level'] = $l;
        $_SESSION['start'] = time();
        file_put_contents("AppData/userlogfile.txt", "$l $u " . date('d/m/Y H:i:s') . " login\n", FILE_APPEND);
        header("Location: MainMenu.php");
        exit();
    } else {
        $err = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
      .error {
        color: red;
        font-weight: bold;
        text-align: center;
        margin-bottom: 10px;
      }
      .container {
        max-width: 400px;
        margin: 60px auto;
        padding: 30px;
        border: 1px solid #ccc;
        border-radius: 8px;
        background-color: #f9f9f9;
        font-family: Arial, sans-serif;
      }
      .form-group {
        margin-bottom: 15px;
      }
      label {
        display: block;
        font-weight: bold;
      }
      input[type="text"],
      input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #aaa;
        border-radius: 4px;
      }
      input[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: #2c3e50;
        color: white;
        border: none;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
      }
      input[type="submit"]:hover {
        background-color: #1a252f;
      }
    </style>
</head>
<body>
<div class="container">
    <h2>Hindi Quiz Login</h2>
    
    <?php if (!empty($err)): ?>
        <p class="error"><?= htmlspecialchars($err) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username:</label>
            <input name="username" id="username" type="text" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input name="password" id="password" type="password" required>
        </div>

        <div class="form-group">
            <label for="level">Level:</label>
            <input name="level" id="level" value="L2" readonly>
        </div>

        <input type="submit" value="Login">
    </form>
</div>
</body>
</html>
