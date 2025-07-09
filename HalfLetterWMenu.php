<?php
session_start();

// 🛡️ Block access if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// ✅ Allow access to quiz once, and clear old flag so user can retake
$_SESSION['allow_quiz'] = true;
unset($_SESSION['quiz_done']); // Enable fresh quiz session

$menus = [
    ["name" => "Ka half words", "folder" => "KaImages"],
    ["name" => "Ch Half words", "folder" => "ChaImages"],
    ["name" => "Ta half words", "folder" => "TaImages"],
    ["name" => "Tha half words", "folder" => "ThaImages"],
    ["name" => "Pa Half words", "folder" => "PaImages"],
    ["name" => "All Random words", "folder" => "None"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Quiz</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0f4f7, #e0ebf5);
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 50px 60px;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 85%;
            max-width: 1000px;
            text-align: center;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .button {
            padding: 20px;
            font-size: 18px;
            border: none;
            background-color: #3498db;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
        }
        .button:hover {
            background-color: #2980b9;
        }
        @media (max-width: 700px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user']) ?></h2>
    <p>Level: <?= htmlspecialchars($_SESSION['level']) ?></p>

    <div class="grid">
        <?php foreach ($menus as $m): ?>
            <form method="POST" action="quiz.php">
                <input type="hidden" name="folder" value="<?= $m['folder'] ?>">
                <button type="submit" class="button"><?= htmlspecialchars($m['name']) ?></button>
            </form>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>