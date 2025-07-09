<?php
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['level'])) {
    header("Location: index.php");
    exit();
}

$level = $_SESSION['level'];
$folder = isset($_POST['folder']) ? $_POST['folder'] : 'KaImages';
$imgPath = "AppFiles/images/$level/$folder/";
$answerFile = $imgPath . "Answers.txt";

$images = [];
$answersMap = [];

if (file_exists($answerFile)) {
    foreach (file($answerFile) as $line) {
        if (trim($line)) {
            $parts = explode(",", $line, 2);
            if (count($parts) === 2) {
                list($img, $answers) = $parts;
                $images[] = trim($img);
                $answersMap[trim($img)] = array_map('trim', explode(",", $answers));
            }
        }
    }
}

shuffle($images);
$selectedImages = array_slice($images, 0, 10);
$_SESSION['oral_quiz_images'] = $selectedImages;
$_SESSION['oral_quiz_answers'] = $answersMap;
$_SESSION['oral_quiz_folder'] = $folder;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Oral Half Letters Quiz</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f8;
            padding: 20px;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 30px;
            max-width: 850px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }
        .slide {
            display: none;
            text-align: center;
            margin-top: 30px;
        }
        .slide.active {
            display: block;
        }
        .slide img {
            border: 3px solid #ddd;
            border-radius: 10px;
            margin-bottom: 15px;
            width: 200px;
            height: 200px;
            object-fit: contain;
        }
        .voice-input input[type="text"] {
            width: 80%;
            padding: 10px;
            font-size: 16px;
            border: 2px solid #ccc;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .voice-input {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .btn-row {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        button {
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            min-width: 120px;
            transition: background 0.3s ease;
        }
        .speak-btn {
            background-color: #2980b9;
            color: white;
        }
        .speak-btn:hover {
            background-color: #1c5980;
        }
        .clear-btn {
            background-color: #e67e22;
            color: white;
        }
        .clear-btn:hover {
            background-color: #cf6a13;
        }
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .nav-buttons button {
            background-color: #3498db;
            color: white;
        }
        .nav-buttons button:hover {
            background-color: #2c80b4;
        }
        .submit-btn {
            background-color: #27ae60 !important;
        }
        .submit-btn:hover {
            background-color: #219150 !important;
        }
        .status {
            margin-top: 8px;
            font-weight: bold;
            color: #c0392b;
            min-height: 24px;
        }
        @media (max-width: 600px) {
            .voice-input input[type="text"] {
                width: 95%;
            }
            .nav-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Oral Quiz: <?= htmlspecialchars($folder) ?></h2>
    <form id="oralForm" method="post" action="oral_half_result.php">
        <?php foreach ($selectedImages as $index => $img): ?>
            <div class="slide" id="slide<?= $index ?>">
                <h3>Question <?= $index + 1 ?> of <?= count($selectedImages) ?></h3>
                <img src="<?= $imgPath . $img ?>" alt="<?= htmlspecialchars($img) ?>" />
                <div class="voice-input">
                    <input type="hidden" name="img<?= $index ?>" value="<?= htmlspecialchars($img) ?>">
                    <input type="text" name="response<?= $index ?>" id="response<?= $index ?>" placeholder="Your spoken answer will appear here" readonly>
                    <div class="btn-row">
                        <button type="button" class="speak-btn" onclick="startRecognition(<?= $index ?>)">🎤 Speak</button>
                        <button type="button" class="clear-btn" onclick="clearResponse(<?= $index ?>)">🗑️ Clear</button>
                    </div>
                    <div id="status<?= $index ?>" class="status"></div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="nav-buttons">
            <button type="button" onclick="prevSlide()">&larr; Previous</button>
            <button type="button" id="nextBtn" onclick="nextSlide()">Next &rarr;</button>
            <button type="submit" class="submit-btn" id="submitBtn" style="display: none;">Submit</button>
        </div>
    </form>
</div>

<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const totalSlides = slides.length;

    function showSlide(index) {
        slides.forEach(s => s.classList.remove('active'));
        slides[index].classList.add('active');

        document.getElementById('submitBtn').style.display = (index === totalSlides - 1) ? 'inline-block' : 'none';
        document.getElementById('nextBtn').style.display = (index === totalSlides - 1) ? 'none' : 'inline-block';
    }

    function nextSlide() {
        if (currentSlide < totalSlides - 1) {
            currentSlide++;
            showSlide(currentSlide);
        }
    }

    function prevSlide() {
        if (currentSlide > 0) {
            currentSlide--;
            showSlide(currentSlide);
        }
    }

    function clearResponse(index) {
        document.getElementById('response' + index).value = '';
        document.getElementById('status' + index).textContent = '';
    }

    function startRecognition(index) {
        const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.lang = 'hi-IN';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        const statusDiv = document.getElementById('status' + index);
        statusDiv.textContent = '🎙️ Listening... Please speak';

        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            document.getElementById('response' + index).value = transcript;
            statusDiv.textContent = '✅ Voice captured';
            setTimeout(() => statusDiv.textContent = '', 2000);
        };

        recognition.onerror = function(event) {
            statusDiv.textContent = '❌ Error: ' + event.error;
            setTimeout(() => statusDiv.textContent = '', 3000);
        };

        recognition.onend = function() {
            if (statusDiv.textContent === '🎙️ Listening... Please speak') {
                statusDiv.textContent = '⚠️ No speech detected';
                setTimeout(() => statusDiv.textContent = '', 2000);
            }
        };

        recognition.start();
    }

    document.addEventListener('DOMContentLoaded', () => showSlide(0));
</script>
</body>
</html>
