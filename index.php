<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $l = $_POST['level'] ?? '';
    $valid = false;
    foreach (file("AppFiles/PwdFile.txt") as $line) {
        if (trim($line) === "$l $u $p") $valid = true;
    }
    if ($valid) {
        $_SESSION['user'] = $u;
        $_SESSION['level'] = $l;
        $_SESSION['start'] = time();
        file_put_contents("AppData/userlogfile.txt", "$l $u " . date('d/m/Y H:i:s') . " login\n", FILE_APPEND);
        header("Location: MainMenu.php"); exit();
    } else {
        $err = "Invalid credentials.";
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
        <?php if (!empty($err)) echo "<p class='error'>$err</p>"; ?>
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
