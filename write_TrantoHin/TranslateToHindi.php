<?php
session_start();

$lines = @file("C:/xampp/htdocs/HindiQuiz/AppFiles/Translations/EngToHinAnswers.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (!$lines) {
    die("Could not read questions file.");
}

$questions = [];
foreach ($lines as $line) {
    $parts = explode(',', $line, 2);
    if (count($parts) < 2) continue;
    $english = trim($parts[0]);
    $answers = array_map('trim', explode(',', $parts[1]));
    $answers = array_map(function($a) {
        return trim($a, ' "');
    }, $answers);
    $questions[] = ['english' => $english, 'answers' => $answers];
}

shuffle($questions);
$selectedSet = array_slice($questions, 0, 10);
$_SESSION['translate_eng_questions'] = array_column($selectedSet, 'english');
$_SESSION['translate_eng_answers'] = array_column($selectedSet, 'answers');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Translate English to Hindi</title>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: #f0f2f5;
        margin: 0;
        padding: 30px;
    }
    .container {
        max-width: 950px;
        margin: auto;
        background: #ffffff;
        padding: 30px;
        border-radius: 14px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }
    h2 {
        text-align: center;
        margin-bottom: 25px;
    }
    .slide { display: none; }
    .slide.active { display: block; }
    .sentence-box {
        font-size: 18px;
        margin-bottom: 14px;
        font-weight: 600;
    }
    input[type="text"] {
        width: 100%;
        padding: 14px;
        font-size: 17px;
        border-radius: 10px;
        border: 1px solid #ccc;
        margin-bottom: 10px;
        background-color: #fcfcfc;
    }
    .feedback {
        margin-top: 10px;
        font-weight: bold;
        font-size: 16px;
        padding: 10px;
        border-radius: 8px;
    }
    .feedback.correct {
        background-color: #d4edda;
        border: 1px solid #28a745;
        color: #155724;
    }
    .feedback.wrong {
        background-color: #f8d7da;
        border: 1px solid #dc3545;
        color: #721c24;
    }
    .nav-buttons {
        display: flex;
        justify-content: flex-end;
        margin-top: 25px;
        gap: 12px;
    }
    button {
        padding: 12px 20px;
        font-size: 16px;
        border: none;
        border-radius: 8px;
        background-color: #007bff;
        color: white;
        cursor: pointer;
        transition: background 0.3s;
    }
    button:hover {
        background-color: #0056b3;
    }
    button:disabled {
        background-color: #cccccc;
        cursor: not-allowed;
    }
</style>
<script>
let current = 0;
let currentAudio = null; // ✅ To track currently playing audio

function showSlide(idx) {
    const slides = document.querySelectorAll('.slide');
    if (idx < 0 || idx >= slides.length) return;

    slides.forEach(s => s.classList.remove('active'));
    slides[idx].classList.add('active');
    current = idx;

    document.getElementById('nextBtn').disabled = true;
    document.getElementById('nextBtn').style.display = current === slides.length - 1 ? 'none' : 'inline-block';
    document.getElementById('checkBtn').disabled = false;
    document.getElementById('checkBtn').style.display = 'inline-block';
    document.getElementById('submitBtn').style.display = current === slides.length - 1 ? 'inline-block' : 'none';
}

function checkAnswer(i) {
    const input = document.getElementById('input' + i);
    const correctAnswers = JSON.parse(document.getElementById('correct' + i).value);
    const feedback = document.getElementById('feedback' + i);
    const userAns = input.value.trim();
    let correct = false;

    for (const ans of correctAnswers) {
        if (userAns === ans.trim()) {
            correct = true;
            break;
        }
    }

    if (currentAudio) {
        currentAudio.pause(); // ✅ Stop previous sound
        currentAudio.currentTime = 0;
    }

    if (correct) {
        feedback.textContent = '✅ Correct!';
        feedback.className = 'feedback correct';
        currentAudio = new Audio('/HindiQuiz/AppFiles/sound/correct.mp3');
        currentAudio.play();
    } else {
        feedback.textContent = '❌ Wrong! Correct answers: ' + correctAnswers.join(' / ');
        feedback.className = 'feedback wrong';
        currentAudio = new Audio('/HindiQuiz/AppFiles/sound/wrong.mp3');
        currentAudio.play();
    }


    document.getElementById('checkBtn').disabled = true;
    document.getElementById('checkBtn').style.display = 'none';
    document.getElementById('nextBtn').disabled = false;
}

function nextSlide() {
    if (currentAudio) {
        currentAudio.pause(); // ✅ Stop any sound on "Next"
        currentAudio.currentTime = 0;
    }

    showSlide(current + 1);

    document.getElementById('checkBtn').disabled = false;
    document.getElementById('checkBtn').style.display = 'inline-block';
    document.getElementById('nextBtn').disabled = true;
}


function addChar(char) {
    const input = document.querySelector('.slide.active input.hindiInput');
    if (input) input.value += char;
}
function clearInput() {
    const input = document.querySelector('.slide.active input.hindiInput');
    if (input) input.value = '';
}
function deleteLastChar() {
    const input = document.querySelector('.slide.active input.hindiInput');
    if (input) input.value = input.value.slice(0, -1);
}

window.onload = () => showSlide(0);
</script>
</head>
<body>
<div class="container">
    <h2>Translate English Sentences to Hindi</h2>
    <form method="POST" action="submit_EngToHin.php">
        <?php foreach ($selectedSet as $i => $item): ?>
            <div class="slide" id="slide<?= $i ?>">
                <div class="sentence-box">
                    <strong>Sentence <?= $i + 1 ?>:</strong> <?= htmlspecialchars($item['english']) ?>
                </div>
                <input type="text" name="answer<?= $i ?>" id="input<?= $i ?>" class="hindiInput" placeholder="Type Hindi translation...">
                <input type="hidden" name="question<?= $i ?>" value="<?= htmlspecialchars($item['english']) ?>">
                <input type="hidden" id="correct<?= $i ?>" value='<?= json_encode($item['answers']) ?>'>
                <div class="feedback" id="feedback<?= $i ?>"></div>
            </div>
        <?php endforeach; ?>

        <!-- ✅ Include Hindi Keyboard -->
        <?php include '../hindi_keyboard.php'; renderHindiKeyboard(); ?>

        <div class="nav-buttons">
            <button type="button" id="checkBtn" onclick="checkAnswer(current)">Check</button>
            <button type="button" id="nextBtn" onclick="nextSlide()" disabled>Next →</button>
            <button type="submit" id="submitBtn" style="display:none;">Submit</button>
        </div>
    </form>
</div>
</body>
</html>
