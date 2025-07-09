<?php
session_start();

// Protect route
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$filePath = "AppData/Translations/HindiSentences.txt";
if (!file_exists($filePath)) die("Hindi sentence file missing.");

$sentences = array_filter(array_map('trim', file($filePath)));
shuffle($sentences);
$selected = array_slice($sentences, 0, 10);
$_SESSION['trans_mode'] = 'H2E';
$_SESSION['trans_sentences'] = $selected;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Translate to English</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0f4f7, #e0ebf5);
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            background: #fff;
            margin: 20px auto;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .slide { display: none; }
        .slide.active { display: block; }
        .sentence-box { font-size: 18px; margin-bottom: 15px; }
        input[type="text"] {
            width: 100%; padding: 12px; font-size: 16px;
            border-radius: 6px; border: 1px solid #ccc;
        }
        .nav-buttons { display: flex; justify-content: space-between; margin-top: 20px; }
        button {
            padding: 10px 18px; font-size: 14px; border: none;
            border-radius: 6px; cursor: pointer;
        }
        .submit-btn {
            background: #27ae60; color: white;
        }
    </style>
    <script>
        let current = 0;
        function showSlide(idx) {
            const slides = document.querySelectorAll('.slide');
            if (idx < 0 || idx >= slides.length) return;
            slides.forEach(s => s.classList.remove('active'));
            slides[idx].classList.add('active');
            current = idx;
            document.getElementById('prevBtn').style.display = (current === 0) ? 'none' : 'inline-block';
            document.getElementById('nextBtn').style.display = (current === slides.length - 1) ? 'none' : 'inline-block';
            document.getElementById('submitBtn').style.display = (current === slides.length - 1) ? 'inline-block' : 'none';
        }
        function nextSlide() { showSlide(current + 1); }
        function prevSlide() { showSlide(current - 1); }
        window.onload = () => showSlide(0);
    </script>
</head>
<body>
<div class="container">
    <h2>Translate to English</h2>
    <form action="submit_translation.php" method="POST">
        <?php foreach ($selected as $i => $sentence): ?>
            <div class="slide" id="slide<?= $i ?>">
                <div class="sentence-box">
                    <strong>Sentence <?= $i+1 ?>:</strong> <?= htmlspecialchars($sentence) ?>
                </div>
                <input type="text" name="answer<?= $i ?>" placeholder="Type English translation here..." />
                <input type="hidden" name="question<?= $i ?>" value="<?= htmlspecialchars($sentence) ?>" />
            </div>
        <?php endforeach; ?>
        <div class="nav-buttons">
            <button type="button" id="prevBtn" onclick="prevSlide()">&larr; Previous</button>
            <button type="button" id="nextBtn" onclick="nextSlide()">Next &rarr;</button>
            <button type="submit" id="submitBtn" class="submit-btn" style="display:none">Submit</button>
        </div>
    </form>
</div>
</body>
</html>
