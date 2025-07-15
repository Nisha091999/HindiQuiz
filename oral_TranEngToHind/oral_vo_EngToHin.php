<?php
session_start();

// Load and parse the file (move up one directory to access AppFiles)
$lines = file("../AppFiles/Translations/EngToHinAnswers.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (!$lines) {
    die("❌ Failed to load translation file.");
}

$allQuestions = [];

foreach ($lines as $line) {
    if (preg_match('/^(.*?)(?=\s*")\s*(.*)$/u', $line, $matches)) {
        $english = trim($matches[1]);
        $answerPart = trim($matches[2]);

        preg_match_all('/"([^"]+)"/u', $answerPart, $answerMatches);
        $answerList = $answerMatches[1];

        $allQuestions[] = ['english' => $english, 'answers' => $answerList];
    }
}

// Shuffle and pick 10
shuffle($allQuestions);
$selectedSet = array_slice($allQuestions, 0, 10);

// Store for result comparison
$_SESSION['translate_eng_questions'] = array_column($selectedSet, 'english');
$_SESSION['translate_eng_answers'] = array_column($selectedSet, 'answers');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Translate English to Hindi</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: linear-gradient(to right, #f3f4f7, #e9f0fa);
            color: #333;
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
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
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

        #submitBtn {
            background-color: #27ae60;
            color: white;
            margin-left: auto;
        }

        button:hover {
            opacity: 0.9;
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
        function showSlide(idx) {
            const slides = document.querySelectorAll('.slide');
            if (idx < 0 || idx >= slides.length) return;
            slides.forEach(s => s.classList.remove('active'));
            slides[idx].classList.add('active');
            current = idx;

            document.getElementById('prevBtn').style.display = current === 0 ? 'none' : 'inline-block';
            document.getElementById('nextBtn').style.display = current === slides.length - 1 ? 'none' : 'inline-block';
            document.getElementById('submitBtn').style.display = current === slides.length - 1 ? 'inline-block' : 'none';
        }

        function nextSlide() { showSlide(current + 1); }
        function prevSlide() { showSlide(current - 1); }

        window.onload = () => showSlide(0);
    </script>
</head>
<body>
    <div class="container">
        <h2>Translate the English Sentence into Hindi</h2>
        <form method="POST" action="submit_EngToHin.php">
            <?php foreach ($selectedSet as $i => $item): ?>
                <div class="slide" id="slide<?= $i ?>">
                    <div class="sentence-box">
                        <strong>Sentence <?= $i + 1 ?>:</strong> <?= htmlspecialchars($item['english']) ?>
                    </div>
                    <input type="text" name="answer<?= $i ?>" placeholder="Type your Hindi translation here..." />
                    <input type="hidden" name="question<?= $i ?>" value="<?= htmlspecialchars($item['english']) ?>" />
                    <input type="hidden" name="correct<?= $i ?>" value='<?= json_encode($item['answers']) ?>' />
                </div>
            <?php endforeach; ?>

            <div class="nav-buttons">
                <button type="button" id="prevBtn" onclick="prevSlide()">← Previous</button>
                <button type="button" id="nextBtn" onclick="nextSlide()">Next →</button>
                <button type="submit" id="submitBtn" style="display:none;">Submit</button>
            </div>
        </form>
    </div>
</body>
</html>
