<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
$menus = [
    ["id" => 1, "name" => "Ka half words", "folder" => "KaImages"],
    ["id" => 2, "name" => "Ch Half words", "folder" => "ChaImages"],
    ["id" => 3, "name" => "Ta half words", "folder" => "TaImages"],
    ["id" => 4, "name" => "Tha half words", "folder" => "ThaImages"],
    ["id" => 5, "name" => "Pa Half words", "folder" => "PaImages"],
    ["id" => 6, "name" => "All Random words", "folder" => "None"]
];
?>
<!DOCTYPE html><html><head>
<title>Select Quiz</title>
<link rel="stylesheet" href="assets/style.css">
</head><body>
<div class="container">
<h2>Welcome, <?= htmlspecialchars($_SESSION['user']) ?></h2>
<p>Level: <?= htmlspecialchars($_SESSION['level']) ?></p>
<form method="GET" action="quiz.php">
    <div class="form-group">
        <label for="menu">Choose a category:</label>
        <select name="folder" id="menu" required>
            <?php foreach ($menus as $m): ?>
                <option value="<?= $m['folder'] ?>"><?= htmlspecialchars($m['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <input type="submit" value="Start Quiz">
</form>
</div>
</body></html>
