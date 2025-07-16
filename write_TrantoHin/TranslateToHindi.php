<?php
session_start();

// Load English sentences only (remove Python/AI placeholder stuff)
$lines = @file("../AppFiles/Translations/EngToHinAnswers.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (!$lines) {
    die("Could not read questions file.");
}

$questions = [];
foreach ($lines as $line) {
    $parts = explode(',', $line);
    $english = trim($parts[0]);  // Only take the part before the first comma
    $questions[] = ['english' => $english, 'answers' => ["(manual input)"]];
}


shuffle($questions);
$selectedSet = array_slice($questions, 0, 10);

// Store for result page
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
            background: #f4f6f8;
            padding: 30px;
        }
        .container {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }
        .slide { display: none; }
        .slide.active { display: block; }
        .sentence-box { font-size: 18px; margin-bottom: 12px; font-weight: 500; }
        .nav-buttons { display: flex; justify-content: space-between; margin-top: 20px; }
        button {
            padding: 10px 18px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            background-color: #2980b9;
            color: white;
            cursor: pointer;
        }
        input[type="text"] {
            width: 100%; padding: 12px; font-size: 16px; border-radius: 8px; border: 1px solid #ccc;
        }
        #keyboard {
            margin-top: 20px;
            background: #f0f0f0;
            padding: 10px;
            border-radius: 10px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 6px;
        }
        #keyboard button {
            font-size: 16px;
            padding: 6px 10px;
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

        function addChar(char, id) {
            const input = document.getElementById(id);
            input.value += char;
        }
        function nextSlide() { showSlide(current + 1); }
        function prevSlide() { showSlide(current - 1); }
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
                <input type="text" name="answer<?= $i ?>" id="input<?= $i ?>" placeholder="Type Hindi translation...">
                <input type="hidden" name="question<?= $i ?>" value="<?= htmlspecialchars($item['english']) ?>">
                <input type="hidden" name="correct<?= $i ?>" value='<?= json_encode($item['answers']) ?>'>

                <div id="keyboard">
                    <?php
                    $chars = ['अ','आ','इ','ई','उ','ऊ','ए','ऐ','ओ','औ','अं','अः','क','ख','ग','घ','च','छ','ज','झ','ट','ठ','ड','ढ','ण','त','थ','द','ध','न','प','फ','ब','भ','म','य','र','ल','व','श','ष','स','ह','ा','ि','ी','ु','ू','े','ै','ो','ौ','ं','ः','्'];
                    foreach ($chars as $char) {
                        echo "<button onclick=\"event.preventDefault();addChar('$char','input$i')\">$char</button>";
                    }
                    ?>
                    <button onclick="event.preventDefault();document.getElementById('input<?= $i ?>').value = ''">Clear</button>
                    <button onclick="event.preventDefault();let val = document.getElementById('input<?= $i ?>').value; document.getElementById('input<?= $i ?>').value = val.slice(0, -1);">⌫</button>
                </div>
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
