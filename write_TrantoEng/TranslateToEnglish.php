<?php
session_start();

$lines = file("../AppFiles/Translations/HinToEngAnswers.txt", FILE_IGNORE_NEW_LINES);
$allQuestions = [];

foreach ($lines as $line) {
    if (preg_match('/^(.*?)(?=\s*")\s*(.*)$/u', $line, $matches)) {
        $hindi = rtrim(trim($matches[1]), "।.?!॥,");
        $answerPart = trim($matches[2]);

        preg_match_all('/"([^"]+)"/u', $answerPart, $answerMatches);
        $answerList = $answerMatches[1];

        $allQuestions[] = ['hindi' => $hindi, 'answers' => $answerList];
    }
}

shuffle($allQuestions);
$selectedSet = array_slice($allQuestions, 0, 10);

$_SESSION['translate_questions'] = array_column($selectedSet, 'hindi');
$_SESSION['translate_answers'] = array_column($selectedSet, 'answers');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Translate to English</title>
<link rel="stylesheet" href="../assets/style.css">
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(to right, #f3f4f7, #e9f0fa);
        color: #333;
        margin: 0;
    }

    .container {
        max-width: 800px;
        margin: 40px auto;
        background: white;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #2c3e50;
    }

    .slide {
        display: none;
    }

    .slide.active {
        display: block;
    }

    .sentence-box {
        font-size: 20px;
        margin-bottom: 12px;
        font-weight: 600;
        color: #333;
        padding-left: 2px;
    }
    input[type="text"] {
        display: block;
        width: 96%;
        margin: 0 auto 16px auto;
        padding: 14px;
        font-size: 17px;
        border-radius: 8px;
        border: 1px solid #ccc;
        box-sizing: border-box;
        background-color: #fff;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
    }


    .feedback {
        font-weight: bold;
        margin-top: 10px;
        padding: 10px;
        border-radius: 8px;
    }

    .correct {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .wrong {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .nav-buttons {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }

    .nav-buttons #checkBtn {
        margin-right: auto;
    }

    .nav-buttons #nextBtn,
    .nav-buttons #submitBtn {
        margin-left: auto;
    }


    button {
        padding: 10px 20px;
        font-size: 15px;
        font-weight: 600;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: 0.3s ease;
    }

    #prevBtn {
        background-color: #95a5a6;
        color: white;
    }

    #nextBtn {
        background-color: #3498db;
        color: white;
    }

    #checkBtn {
        background-color: #f39c12;
        color: white;
    }

    #submitBtn {
        background-color: #27ae60;
        color: white;
        margin-left: auto;
    }

    button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    @media (max-width: 600px) {
        .container {
            padding: 20px;
        }

        .nav-buttons {
            flex-direction: column;
            gap: 10px;
        }

        button {
            width: 100%;
        }
    }
</style>

<script>
    let current = 0;
    let currentAudio = null;

    function showSlide(idx) {
        const slides = document.querySelectorAll('.slide');
        if (idx < 0 || idx >= slides.length) return;

        slides.forEach(s => s.classList.remove('active'));
        slides[idx].classList.add('active');
        current = idx;

        // Always disable next and show check initially
        document.getElementById('nextBtn').disabled = true;
        document.getElementById('checkBtn').style.display = 'inline-block';

        // ❌ REMOVE this from here:
        // Don't show submit yet — only after check is clicked on last question
        document.getElementById('submitBtn').style.display = 'none';

        // Show or hide Next button depending on whether it's the last question
        if (current === slides.length - 1) {
            document.getElementById('nextBtn').style.display = 'none';
        } else {
            document.getElementById('nextBtn').style.display = 'inline-block';
        }

        if (currentAudio) {
            currentAudio.pause();
            currentAudio.currentTime = 0;
        }
    }


    function checkAnswer(i) {
        const input = document.getElementById(`input${i}`).value.trim().toLowerCase();
        const correctAnswers = JSON.parse(document.getElementById(`correct${i}`).value.toLowerCase());
        const feedback = document.getElementById(`feedback${i}`);
        const checkBtn = document.getElementById('checkBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');

        if (currentAudio) {
            currentAudio.pause();
            currentAudio.currentTime = 0;
        }

        if (correctAnswers.includes(input)) {
            feedback.textContent = '✅ Correct!';
            feedback.className = 'feedback correct';
            currentAudio = new Audio('/HindiQuiz/AppFiles/sound/correct.mp3');
        } else {
            feedback.textContent = '❌ Wrong! Correct answers: ' + correctAnswers.join(' / ');
            feedback.className = 'feedback wrong';
            currentAudio = new Audio('/HindiQuiz/AppFiles/sound/wrong.mp3');
        }

        currentAudio.play();
        checkBtn.style.display = 'none';

        // If it's the last question
        if (i === document.querySelectorAll('.slide').length - 1) {
            submitBtn.style.display = 'inline-block';
        } else {
            nextBtn.disabled = false;
        }
    }


    function nextSlide() {
        showSlide(current + 1);
    }

    window.onload = () => {
        showSlide(0);
    };
</script>

</head>
<body>
    <div class="container">
        <h2>Translate the Hindi Sentence into English</h2>
        <form method="POST" action="submit_translation.php">
            <?php foreach ($selectedSet as $i => $item): ?>
                <div class="slide" id="slide<?= $i ?>">
                    <div class="sentence-box">
                        <strong>Sentence <?= $i + 1 ?>:</strong> <?= htmlspecialchars($item['hindi']) ?>
                    </div>
                    <input type="text" name="answer<?= $i ?>" id="input<?= $i ?>" placeholder="Type your English translation here..." />
                    <input type="hidden" name="question<?= $i ?>" value="<?= htmlspecialchars($item['hindi']) ?>" />
                    <input type="hidden" name="correct<?= $i ?>" id="correct<?= $i ?>" value='<?= json_encode($item['answers']) ?>' />
                    <div id="feedback<?= $i ?>" class="feedback"></div>
                </div>
            <?php endforeach; ?>

            <div class="nav-buttons">
                <button type="button" id="checkBtn" onclick="checkAnswer(current)">Check</button>
                <button type="button" id="nextBtn" onclick="nextSlide()" disabled>Next →</button>
                <button type="submit" id="submitBtn" style="display:none;">Submit</button>
            </div>
        </form>
    </div>
</body>
</html>
