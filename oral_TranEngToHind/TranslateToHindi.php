<?php
session_start();

// ✅ Correct file path
$lines = file("../AppFiles/Translations/EngToHinAnswers.txt", FILE_IGNORE_NEW_LINES);
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

shuffle($allQuestions);
$selectedSet = array_slice($allQuestions, 0, 10);

$_SESSION['translate_eng_questions'] = array_column($selectedSet, 'english');
$_SESSION['translate_eng_answers'] = array_column($selectedSet, 'answers');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Translate to Hindi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f8fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 25px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
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
            margin-bottom: 10px;
            font-weight: 500;
        }

        input[type="text"] {
            width: 100%;
            padding: 14px;
            font-size: 18px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
        }

        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        button {
            padding: 10px 20px;
            font-size: 15px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
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

        #keyboard {
            margin-top: 15px;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 8px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 5px;
        }

        #keyboard button {
            font-size: 20px;
            padding: 8px 12px;
            border-radius: 6px;
            background-color: #eee;
            border: 1px solid #ccc;
            cursor: pointer;
        }

        #keyboard button:hover {
            background-color: #ddd;
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

        function addChar(char) {
            event.preventDefault();
            const activeInput = document.querySelector('.slide.active input[type="text"]');
            if (activeInput) activeInput.value += char;
        }

        function deleteLast() {
            event.preventDefault();
            const activeInput = document.querySelector('.slide.active input[type="text"]');
            if (activeInput) activeInput.value = activeInput.value.slice(0, -1);
        }

        function clearInput() {
            event.preventDefault();
            const activeInput = document.querySelector('.slide.active input[type="text"]');
            if (activeInput) activeInput.value = '';
        }
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
                <input type="hidden" name="correct<?= $i ?>" value="<?= htmlspecialchars(json_encode($item['answers'])) ?>" />
            </div>
        <?php endforeach; ?>

        <div class="nav-buttons">
            <button type="button" id="prevBtn" onclick="prevSlide()">← Previous</button>
            <button type="button" id="nextBtn" onclick="nextSlide()">Next →</button>
            <button type="submit" id="submitBtn" style="display:none;">Submit</button>
        </div>
    </form>

    <div id="keyboard">
        <!-- Hindi characters (add more as needed) -->
        <button onclick="addChar('अ')">अ</button>
        <button onclick="addChar('आ')">आ</button>
        <button onclick="addChar('इ')">इ</button>
        <button onclick="addChar('ई')">ई</button>
        <button onclick="addChar('उ')">उ</button>
        <button onclick="addChar('ऊ')">ऊ</button>
        <button onclick="addChar('ए')">ए</button>
        <button onclick="addChar('ऐ')">ऐ</button>
        <button onclick="addChar('ओ')">ओ</button>
        <button onclick="addChar('औ')">औ</button>
        <button onclick="addChar('क')">क</button>
        <button onclick="addChar('ख')">ख</button>
        <button onclick="addChar('ग')">ग</button>
        <button onclick="addChar('घ')">घ</button>
        <button onclick="addChar('च')">च</button>
        <button onclick="addChar('छ')">छ</button>
        <button onclick="addChar('ज')">ज</button>
        <button onclick="addChar('झ')">झ</button>
        <button onclick="addChar('ट')">ट</button>
        <button onclick="addChar('ठ')">ठ</button>
        <button onclick="addChar('ड')">ड</button>
        <button onclick="addChar('ढ')">ढ</button>
        <button onclick="addChar('त')">त</button>
        <button onclick="addChar('थ')">थ</button>
        <button onclick="addChar('द')">द</button>
        <button onclick="addChar('ध')">ध</button>
        <button onclick="addChar('न')">न</button>
        <button onclick="addChar('प')">प</button>
        <button onclick="addChar('फ')">फ</button>
        <button onclick="addChar('ब')">ब</button>
        <button onclick="addChar('भ')">भ</button>
        <button onclick="addChar('म')">म</button>
        <button onclick="addChar('य')">य</button>
        <button onclick="addChar('र')">र</button>
        <button onclick="addChar('ल')">ल</button>
        <button onclick="addChar('व')">व</button>
        <button onclick="addChar('श')">श</button>
        <button onclick="addChar('ष')">ष</button>
        <button onclick="addChar('स')">स</button>
        <button onclick="addChar('ह')">ह</button>
        <button onclick="addChar('ा')">ा</button>
        <button onclick="addChar('ि')">ि</button>
        <button onclick="addChar('ी')">ी</button>
        <button onclick="addChar('ु')">ु</button>
        <button onclick="addChar('ू')">ू</button>
        <button onclick="addChar('े')">े</button>
        <button onclick="addChar('ै')">ै</button>
        <button onclick="addChar('ो')">ो</button>
        <button onclick="addChar('ौ')">ौ</button>
        <button onclick="addChar('ं')">ं</button>
        <button onclick="addChar('ः')">ः</button>
        <button onclick="addChar('्')">्</button>
        <button onclick="deleteLast()">⌫</button>
        <button onclick="clearInput()">Clear</button>
    </div>
</div>
</body>
</html>
