<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
$menus = [];
foreach (file("AppFiles/Menus.txt") as $line) {
    if (trim($line) && strpos($line, 'MenuId') === false) {
        $parts = preg_split("/\s{2,}/", trim($line));
        if (count($parts) >= 5 && $parts[1] === $_SESSION['level']) {
            $menus[] = [
                'id' => $parts[0],
                'name' => $parts[3],
                'folder' => $parts[4]
            ];
        }
    }
}
?>
<!DOCTYPE html><html><head>
<title>Select Quiz</title>
<link rel="stylesheet" href="assets/style.css">
</head><body>
<div class="container">
<h2>Select a Quiz</h2>
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