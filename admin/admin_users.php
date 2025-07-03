<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$file = __DIR__ . "/AppFiles/PwdFile.txt";
$data = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$error = '';

function usernameExists($username, $data) {
    foreach ($data as $line) {
        if (str_contains($line, ' ' . $username . ' ')) {
            return true;
        }
    }
    return false;
}

// Add User
if (isset($_POST['add'])) {
    $level = trim($_POST['level']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (usernameExists($username, $data)) {
        $error = "Username already exists. Please choose a different one.";
    } else {
        $newLine = "$level $username $password";
        file_put_contents($file, $newLine . "\n", FILE_APPEND);
        header("Location: admin_users.php");
        exit();
    }
}

// Update User
if (isset($_POST['update'])) {
    $index = intval($_POST['index']);
    $level = trim($_POST['level']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $existing = $data;
    unset($existing[$index]);

    if (usernameExists($username, $existing)) {
        $error = "Username already exists. Please choose a different one.";
    } else {
        $data[$index] = "$level $username $password";
        file_put_contents($file, implode("\n", $data) . "\n");
        header("Location: admin_users.php");
        exit();
    }
}

// Delete User
if (isset($_POST['delete'])) {
    $index = intval($_POST['index']);
    unset($data[$index]);
    file_put_contents($file, implode("\n", $data) . "\n");
    header("Location: admin_users.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            padding: 30px;
            margin: 0;
        }
        .top-nav {
            margin-bottom: 30px;
        }
        .nav-btn {
            padding: 10px 20px;
            background-color: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s ease;
        }
        .nav-btn:hover {
            background-color: #1a252f;
        }
        h2 {
            color: #34495e;
            margin-bottom: 10px;
        }
        .error {
            background: #ffdddd;
            color: #c0392b;
            padding: 10px;
            margin-bottom: 20px;
            border-left: 5px solid #e74c3c;
            border-radius: 8px;
        }
        .add-form {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .add-form label {
            margin-right: 15px;
        }
        .add-form input[type=text] {
            padding: 8px;
            margin-right: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .action-btn.add {
            background-color: #27ae60;
            color: white;
        }
        .action-btn.add:hover {
            background-color: #1e8449;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ecf0f1;
        }
        th {
            background-color: #2980b9;
            color: white;
        }
        td input[type=text] {
            padding: 6px;
            width: 90px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .btn-update {
            background-color: #f39c12;
            color: white;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        .btn-update:hover {
            background-color: #d68910;
        }
        .btn-delete:hover {
            background-color: #c0392b;
        }
        @media (max-width: 768px) {
            .add-form label, .add-form input, .action-btn {
                display: block;
                margin-bottom: 10px;
            }
            td input[type=text] {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="top-nav">
        <a href="admin_dashboard.php" class="nav-btn">⬅ Back to Dashboard</a>
    </div>

    <h2>Manage Users</h2>

    <?php if ($error): ?>
        <div class="error">⚠️ <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="add-form">
        <label>Level: <input type="text" name="level" value="L2" required></label>
        <label>Username: <input type="text" name="username" required></label>
        <label>Password: <input type="text" name="password" required></label>
        <button type="submit" name="add" class="action-btn add">➕ Add User</button>
    </form>

    <table>
        <tr><th>#</th><th>Level</th><th>Username</th><th>Password</th><th>Actions</th></tr>
        <?php foreach ($data as $i => $line): 
            list($level, $username, $password) = explode(' ', $line, 3);
        ?>
        <tr>
            <form method="POST">
                <td><?= $i + 1 ?></td>
                <td><input type="text" name="level" value="<?= htmlspecialchars($level) ?>"></td>
                <td><input type="text" name="username" value="<?= htmlspecialchars($username) ?>"></td>
                <td><input type="text" name="password" value="<?= htmlspecialchars($password) ?>"></td>
                <td>
                    <input type="hidden" name="index" value="<?= $i ?>">
                    <button type="submit" name="update" class="action-btn btn-update">✏️ Update</button>
                    <button type="submit" name="delete" class="action-btn btn-delete">🗑 Delete</button>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
