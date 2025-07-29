<?php
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['level'])) {
    header("Location: index.php");
    exit();
}

$level = $_SESSION['level'];
$folder = isset($_POST['folder']) ? $_POST['folder'] : 'KaImages';

if ($folder === 'None') {
    $allFolders = ['KaImages', 'ChaImages', 'TaImages', 'ThaImages', 'PaImages'];
    $folder = $allFolders[array_rand($allFolders)];
}

$serverImgPath = __DIR__ . "/../AppFiles/images/$level/$folder/";
$webImgPath    = "/HindiQuiz/AppFiles/images/$level/$folder/";
$answerFile    = $serverImgPath . "Answers.txt";

$images = [];
$answersMap = [];

if (file_exists($answerFile)) {
    foreach (file($answerFile) as $line) {
        $line = trim($line);
        if ($line !== '') {
            $parts = explode(",", $line, 2);
            if (count($parts) === 2) {
                $img = trim($parts[0]);
                $answers = array_map('trim', explode(",", $parts[1]));
                $images[] = $img;
                $answersMap[$img] = $answers;
            }
        }
    }
}

if (empty($images)) {
    echo "<h2 style='color:red;text-align:center;'>No images found for folder: " . htmlspecialchars($folder) . "</h2>";
    exit();
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
    <meta charset="UTF-8" />
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

        button:disabled {
        background-color: #cccccc !important;
        color: #666666 !important;
        cursor: not-allowed;
        opacity: 0.6;
    }
        .btn-row {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .btn-row.buttons-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 80%;
            max-width: 500px;
            margin-top: 20px;
            gap: 12px;
            position: relative;
        }

        .btn-row.buttons-nav .check-btn {
            margin-right: auto;
        }

        .btn-row.buttons-nav .next-btn {
            margin-left: auto;
        }


        button {
            padding: 12px 18px;
            font-size: 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            min-width: 100px;
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
        .check-btn {
            background-color: #f39c12;
            color: white;
        }
        .check-btn:hover {
            background-color: #d78c09;
        }
        .next-btn {
            background-color: #3498db;
            color: white;
        }
        .next-btn:hover {
            background-color: #2c80b4;
        }
        .status {
            margin-top: 10px;
            font-weight: bold;
            min-height: 30px;
            padding: 8px 10px;
            border-radius: 6px;
        }
        .nav-buttons {
            text-align: center;
            margin-top: 30px;
        }
        .submit-btn {
            background-color: #27ae60;
            color: white;
        }
        .submit-btn:hover {
            background-color: #219150;
        }
        @media (max-width: 600px) {
            .btn-row.buttons-nav {
                width: 100%;
                max-width: none;
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
                <img src="<?= $webImgPath . $img ?>" alt="<?= htmlspecialchars($img) ?>" />
                <div class="voice-input">
                    <input type="hidden" name="img<?= $index ?>" value="<?= htmlspecialchars($img) ?>">
                    <input type="text" name="response<?= $index ?>" id="response<?= $index ?>" placeholder="Your spoken answer will appear here" readonly>

                    <div class="btn-row">
                        <button type="button" class="speak-btn" onclick="startRecognition(<?= $index ?>)">🎤 Speak</button>
                        <button type="button" class="clear-btn" onclick="clearResponse(<?= $index ?>)">🗑️ Clear</button>
                    </div>

                    <div class="btn-row buttons-nav">
                        <button type="button" class="check-btn" id="checkBtn<?= $index ?>" onclick="checkAnswer(<?= $index ?>)">✔️ Check</button>
                        <button type="button" class="next-btn" id="nextBtn<?= $index ?>" onclick="nextSlide()" disabled>Next ➡️</button>
                    </div>



                    <div id="feedback<?= $index ?>" class="status"></div>
                    <input type="hidden" id="correct<?= $index ?>" value='<?= json_encode($answersMap[$img]) ?>'>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="nav-buttons">
            <button type="submit" class="submit-btn" id="submitBtn" style="display: none;" disabled>Submit Quiz</button>
        </div>
    </form>

    <audio id="correctSound" src="/HindiQuiz/AppFiles/sound/correct.mp3" ></audio>
    <audio id="wrongSound"   src="/HindiQuiz/AppFiles/sound/wrong.mp3" ></audio>

</div>

<script>
    const totalSlides = document.querySelectorAll('.slide').length;
    const recognitions = [];
    let currentSlide = 0;

    let correctSoundTimeout;

    document.addEventListener('DOMContentLoaded', () => {
        for (let i = 0; i < totalSlides; i++) {
            recognitions[i] = createRecognition(i);
        }
        showSlide(0);
    });

    function createRecognition(index) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            alert('Sorry, your browser does not support Speech Recognition API.');
            return null;
        }

        const recognition = new SpeechRecognition();
        recognition.lang = 'hi-IN';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        const statusDiv = document.getElementById('feedback' + index);

        recognition.onstart = () => {
            statusDiv.textContent = '🎙️ Listening... Please speak';
            statusDiv.style.background = 'none';
            statusDiv.style.color = '#000';
        };

        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            document.getElementById('response' + index).value = transcript;
            statusDiv.textContent = '✅ Voice captured';
        };

        recognition.onerror = (event) => {
            statusDiv.textContent = '❌ Error: ' + event.error;
            statusDiv.style.background = '#f8d7da';
            statusDiv.style.color = '#721c24';
        };

        recognition.onend = () => {
            if (statusDiv.textContent === '🎙️ Listening... Please speak') {
                statusDiv.textContent = '⚠️ No speech detected';
                statusDiv.style.background = '#f8d7da';
                statusDiv.style.color = '#721c24';
            }
        };

        return recognition;
    }

    function startRecognition(index) {
        if (!recognitions[index]) return;
        try {
            recognitions[index].start();
        } catch (e) {
            console.log('Recognition error:', e.message);
        }
    }

    function clearResponse(index) {
        document.getElementById('response' + index).value = '';
        const feedback = document.getElementById('feedback' + index);
        feedback.textContent = '';
        feedback.style.background = 'none';

        const checkBtn = document.getElementById('checkBtn' + index);
        const nextBtn = document.getElementById('nextBtn' + index);
        checkBtn.style.display = 'inline-block';
        nextBtn.disabled = true;
    }

    function showSlide(index) {
        const slides = document.querySelectorAll('.slide');
        slides.forEach((slide, i) => {
            slide.classList.remove('active');

            // Reset "Speak" and "Clear" buttons for each question
            const speakClearRow = slide.querySelector('.btn-row');
            if (speakClearRow) speakClearRow.style.display = 'flex';

            const checkBtn = document.getElementById('checkBtn' + i);
            if (checkBtn) {
                checkBtn.style.display = 'inline-block';
            }

            const nextBtn = document.getElementById('nextBtn' + i);
            if (nextBtn) {
                nextBtn.disabled = true;
                nextBtn.style.display = (i === slides.length - 1) ? 'none' : 'inline-block';
            }
        });

        slides[index].classList.add('active');

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.style.display = 'none';
        submitBtn.disabled = true;

        stopCorrectSound();
        currentSlide = index;
    }


    function nextSlide() {
        stopAllSounds(); // 🔇 Stop all sounds when moving to next question
        if (currentSlide < totalSlides - 1) {
            currentSlide++;
            showSlide(currentSlide);
        }
    }

    function checkAnswer(index) {
        const userInput = document.getElementById('response' + index).value.trim().toLowerCase();
        const correct = JSON.parse(document.getElementById('correct' + index).value);
        const feedback = document.getElementById('feedback' + index);
        const nextBtn = document.getElementById('nextBtn' + index);
        const checkBtn = document.getElementById('checkBtn' + index);

        let isCorrect = false;

        if (userInput !== "") {
            isCorrect = correct.some(ans => userInput === ans.trim().toLowerCase());
        }

        if (isCorrect) {
            feedback.textContent = "✅ सही उत्तर!";
            feedback.style.backgroundColor = "#d4edda";
            feedback.style.color = "#155724";
            playCorrectSoundOnceFor1Minute();
        } else {
            feedback.textContent = "❌ गलत! सही उत्तर: " + correct.join(", ");
            feedback.style.backgroundColor = "#f8d7da";
            feedback.style.color = "#721c24";
            document.getElementById("wrongSound").play();
        }

        nextBtn.disabled = false;
        if (index !== totalSlides - 1) {
            nextBtn.style.display = 'inline-block';
        }

        checkBtn.style.display = 'none';

        // Hide "Speak" and "Clear" buttons
        const speakClearRow = document.querySelector(`#slide${index} .btn-row`);
        if (speakClearRow) speakClearRow.style.display = 'none';

        // Show submit only on last slide after check
        if (index === totalSlides - 1) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.style.display = 'inline-block';
            submitBtn.disabled = false;
        }
    }



    // 🔊 Play correct sound for 1 minute only
    function playCorrectSoundOnceFor1Minute() {
        const correctSound = document.getElementById("correctSound");
        correctSound.currentTime = 0;
        correctSound.play();

        correctSoundTimeout = setTimeout(() => {
            correctSound.pause();
            correctSound.currentTime = 0;
        }, 60000); // stop after 1 minute
    }

    // 🔇 Stop correct sound if navigating
    function stopAllSounds() {
    clearTimeout(correctSoundTimeout);

    const correctSound = document.getElementById("correctSound");
    correctSound.pause();
    correctSound.currentTime = 0;

    const wrongSound = document.getElementById("wrongSound");
    wrongSound.pause();
    wrongSound.currentTime = 0;
}

</script>


</body>
</html>
