<?php
session_start();

// Handle logout if POST contains 'logout'
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

$err = '';

// Handle login form submit (only if logout is not triggered)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['logout'])) {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    $l = trim($_POST['level'] ?? '');

    $valid = false;
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
</head>
<body>
<div class="container">
    <h2>Hindi Quiz Login</h2>
    <?php if (!empty($err)): ?>
        <p class="error"><?= htmlspecialchars($err) ?></p>
    <?php endif; ?>
    <form method="POST">
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
