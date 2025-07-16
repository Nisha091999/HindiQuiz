<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

// Load English sentences and Hindi answers
$lines = file("../AppFiles/Translations/EngToHinAnswers.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$questions = [];

foreach ($lines as $line) {
    if (preg_match('/^(.*?)(?=\s*")\s*(.*)$/u', $line, $matches)) {
        $english = trim($matches[1]);
        preg_match_all('/"([^"]+)"/u', $matches[2], $answerMatches);
        $answers = $answerMatches[1];
        $questions[] = ['english' => $english, 'answers' => $answers];
    }
}

shuffle($questions);
$selected = array_slice($questions, 0, 10);

// Store for result checking
$_SESSION['oral_translate_questions'] = array_column($selected, 'english');
$_SESSION['oral_translate_answers'] = array_column($selected, 'answers');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Oral Translate to Hindi</title>
  <style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f8;
        margin: 0;
        padding: 20px;
    }
    .container {
        max-width: 850px;
        margin: auto;
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
    }
    .slide {
        display: none;
    }
    .slide.active {
        display: block;
    }
    .sentence {
        font-size: 18px;
        margin-bottom: 20px;
    }
    input[type="text"] {
        width: 90%;
        padding: 12px;
        font-size: 16px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }
    .controls {
        margin-top: 10px;
        text-align: center;
    }
    button {
        margin: 5px;
        padding: 10px 20px;
        font-size: 15px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .speak-btn { background-color: #3498db; color: white; }
    .clear-btn { background-color: #e67e22; color: white; }
    .nav-btn { background-color: #95a5a6; color: white; }
    .submit-btn { background-color: #27ae60; color: white; }
    .status { font-weight: bold; margin-top: 8px; height: 24px; color: #c0392b; }
  </style>
</head>
<body>
<div class="container">
  <h2>Oral Translate to Hindi</h2>
  <form method="post" action="oral_submit_EngToHin.php" id="oralForm">
    <?php foreach ($selected as $i => $item): ?>
      <div class="slide" id="slide<?= $i ?>">
        <div class="sentence"><strong>Sentence <?= $i + 1 ?>:</strong> <?= htmlspecialchars($item['english']) ?></div>
        <input type="hidden" name="question<?= $i ?>" value="<?= htmlspecialchars($item['english']) ?>">
        <input type="hidden" name="correct<?= $i ?>" value='<?= json_encode($item["answers"]) ?>'>
        <input type="text" name="answer<?= $i ?>" id="answer<?= $i ?>" placeholder="Speak your Hindi translation here..." readonly>
        <div class="controls">
          <button type="button" class="speak-btn" onclick="startRecognition(<?= $i ?>)">🎤 Speak</button>
          <button type="button" class="clear-btn" onclick="clearResponse(<?= $i ?>)">🗑️ Clear</button>
          <div class="status" id="status<?= $i ?>"></div>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="controls">
      <button type="button" class="nav-btn" onclick="prevSlide()">← Previous</button>
      <button type="button" class="nav-btn" id="nextBtn" onclick="nextSlide()">Next →</button>
      <button type="submit" class="submit-btn" id="submitBtn" style="display:none;">Submit</button>
    </div>
  </form>
</div>

<script>
  let current = 0;
  const slides = document.querySelectorAll('.slide');

  function showSlide(index) {
      slides.forEach(s => s.classList.remove('active'));
      slides[index].classList.add('active');
      document.getElementById('nextBtn').style.display = index === slides.length - 1 ? 'none' : 'inline-block';
      document.getElementById('submitBtn').style.display = index === slides.length - 1 ? 'inline-block' : 'none';
  }

  function nextSlide() {
      if (current < slides.length - 1) current++;
      showSlide(current);
  }

  function prevSlide() {
      if (current > 0) current--;
      showSlide(current);
  }

  function clearResponse(i) {
    document.getElementById('answer' + i).value = '';
    document.getElementById('status' + i).textContent = '';
  }

  function startRecognition(i) {
    const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
    recognition.lang = 'hi-IN'; // Hindi recognition
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    const status = document.getElementById('status' + i);
    status.textContent = '🎙️ Listening... Please speak';

    recognition.onresult = function(e) {
        const transcript = e.results[0][0].transcript;
        document.getElementById('answer' + i).value = transcript;
        status.textContent = '✅ Voice captured';
        setTimeout(() => status.textContent = '', 2000);
    };

    recognition.onerror = function(e) {
        status.textContent = '❌ Error: ' + e.error;
        setTimeout(() => status.textContent = '', 3000);
    };

    recognition.onend = function() {
        if (status.textContent === '🎙️ Listening... Please speak') {
            status.textContent = '⚠️ No speech detected';
            setTimeout(() => status.textContent = '', 2000);
        }
    };

    recognition.start();
  }

  document.addEventListener('DOMContentLoaded', () => showSlide(0));
</script>
</body>
</html>
