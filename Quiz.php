<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_GET['folder'])) {
    header("Location: index.php");
    exit();
}

$folder = $_GET['folder'];
$level = $_SESSION['level'];
$imgPath = "AppFiles/images/$level/$folder/";
$answerFile = $imgPath . "Answers.txt";

$images = [];
$answersMap = [];
if (file_exists($answerFile)) {
    foreach (file($answerFile) as $line) {
        if (trim($line)) {
            list($img, $answers) = explode(",", $line, 2);
            $images[] = $img;
            $answersMap[$img] = array_map('trim', explode(",", $answers));
        }
    }
}
shuffle($images);
$selectedImages = array_slice($images, 0, 10);
$_SESSION['quiz_images'] = $selectedImages;
$_SESSION['quiz_answers'] = $answersMap;
$_SESSION['quiz_folder'] = $folder;
?>
<!DOCTYPE html><html><head>
<title>Quiz - <?= htmlspecialchars($folder) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head><body>
<div class="container">
<h2>Quiz: <?= htmlspecialchars($folder) ?></h2>
<form method="POST" action="result.php">
<?php foreach ($selectedImages as $index => $img): ?>
    <div class="form-group">
        <label>Q<?= $index + 1 ?>:</label><br>
        <img src="<?= $imgPath . $img ?>" alt="<?= $img ?>" width="100"><br>
        <input type="text" name="q<?= $index ?>" required>
        <input type="hidden" name="img<?= $index ?>" value="<?= $img ?>">
    </div>
<?php endforeach; ?>
    <input type="submit" value="Submit Quiz">
</form>
</div>
</body></html>
