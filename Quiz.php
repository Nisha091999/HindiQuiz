<?php
// 🛡️ Strong cache prevention
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

session_start();

// 🔒 Block access if quiz already completed
if (!empty($_SESSION['quiz_done'])) {
    header("Location: index.php");
    exit();
}

// 🔐 Enforce POST-only access from menu
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['folder']) ||
    !isset($_SESSION['allow_quiz']) ||
    $_SESSION['allow_quiz'] !== true
) {
    header("Location: index.php");
    exit();
}

unset($_SESSION['allow_quiz']); // One-time access only

$folder = $_POST['folder'];
$level = $_SESSION['level'];
$imgPath = "AppFiles/images/$level/$folder/";
$answerFile = $imgPath . "Answers.txt";

// 🔄 Load images and answers
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

// 🧠 Store quiz session data
$_SESSION['quiz_images'] = $selectedImages;
$_SESSION['quiz_answers'] = $answersMap;
$_SESSION['quiz_folder'] = $folder;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quiz - <?= htmlspecialchars($folder) ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0f4f7, #e0ebf5);
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
        }
        .container {
            width: 70%;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .slide-container {
            position: relative;
            width: 100%;
            overflow: hidden;
        }
        .slide {
            display: none;
            text-align: center;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
        .slide.active {
            display: block;
        }
        .quiz-image {
            width: 250px;
            height: 250px;
            object-fit: contain;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .quiz-input {
            width: 100%;
            max-width: 400px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .nav-buttons button, .submit-btn {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .nav-buttons button:hover, .submit-btn:hover {
            background-color: #2980b9;
        }
    </style>
    <script>
        // 🛡 Handle browser back-forward navigation issue
        if (performance.getEntriesByType("navigation")[0]?.type === "back_forward") {
            location.href = "index.php";
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Quiz: <?= htmlspecialchars($folder) ?></h2>
    <form method="POST" action="result.php" id="quizForm">
        <div class="slide-container">
            <?php foreach ($selectedImages as $index => $img): ?>
                <div class="slide<?= $index === 0 ? ' active' : '' ?>" id="slide<?= $index ?>">
                    <h3>Question <?= $index + 1 ?> of <?= count($selectedImages) ?></h3>
                    <img src="<?= $imgPath . $img ?>" alt="<?= $img ?>" class="quiz-image"><br>
                    <input type="text" name="q<?= $index ?>" class="quiz-input" placeholder="Enter your answer...">
                    <input type="hidden" name="img<?= $index ?>" value="<?= $img ?>">
                    <div class="nav-buttons">
                        <?php if ($index > 0): ?>
                            <button type="button" onclick="showSlide(<?= $index - 1 ?>)">&larr; Prev</button>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                        <?php if ($index < count($selectedImages) - 1): ?>
                            <button type="button" onclick="showSlide(<?= $index + 1 ?>)">Next &rarr;</button>
                        <?php else: ?>
                            <button type="submit" class="submit-btn">Submit Quiz</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
</div>
<script>
function showSlide(index) {
    const slides = document.querySelectorAll('.slide');
    if (index < 0 || index >= slides.length) return;
    slides.forEach(s => s.classList.remove('active'));
    slides[index].classList.add('active');
}
</script>
</body>
</html>
