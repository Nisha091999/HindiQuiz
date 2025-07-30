<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$lines = file("../AppFiles/Translations/EngToHinAnswers.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$questions = [];

foreach ($lines as $line) {
    if (preg_match('/^(.*?)(?=\s*")\s*(.*)$/u', $line, $matches)) {
        $english = trim($matches[1], "।.?!॥,");
        preg_match_all('/"([^"]+)"/u', $matches[2], $answerMatches);
        $answers = $answerMatches[1];
        $questions[] = ['english' => $english, 'answers' => $answers];
    }
}

shuffle($questions);
$selected = array_slice($questions, 0, 10);
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
  .status-line {
  margin-top: 8px;
  text-align: center;
  }

  .status {
    font-weight: bold;
    color: #555;
    display: inline-block;
  }

  .slide { display: none; }
  .slide.active { display: block; }
  .sentence { font-size: 18px; margin-bottom: 10px; }
  input[type="text"] {
    width: 90%; padding: 12px; font-size: 16px;
    border-radius: 6px; border: 1px solid #ccc;
  }
  .controls, .actions { margin-top: 15px; text-align: center; }
  button {
    margin: 5px; padding: 10px 20px; font-size: 15px;
    border: none; border-radius: 6px; cursor: pointer;
  }
  .speak-btn { background-color: #3498db; color: white; }
  .clear-btn { background-color: #e67e22; color: white; }
  .actions {
    margin-top: 15px;
    display: flex;
    justify-content: space-between; /* places check on left, next on right */
    align-items: center;
  }

  .check-btn {
    background-color: #f39c12;
    color: white;
  }

  .next-btn {
    background-color: #3498db;
    color: white;
    margin-left: auto; /* pushes it to the right */
  }
  .submit-btn { background-color: #27ae60; color: white; }
  .feedback { margin-top: 10px; padding: 10px; border-radius: 6px; display: none; text-align: center;}
  .correct { background-color: #d4edda; color: #155724; }
  .wrong { background-color: #f8d7da; color: #721c24; }
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
        
        <div class="controls" id="btns<?= $i ?>">
          <button type="button" class="speak-btn" onclick="startRecognition(<?= $i ?>)">🎤 Speak</button>
          <button type="button" class="clear-btn" onclick="clearResponse(<?= $i ?>)">🗑️ Clear</button>
        </div>

        <div class="status-line">
          <span id="status<?= $i ?>" class="status"></span>
        </div>


        <div class="actions">
          <button type="button" class="check-btn" onclick="checkAnswer(<?= $i ?>)">Check</button>
          <?php if ($i < count($selected) - 1): ?>
            <button type="button" class="next-btn" id="next<?= $i ?>" onclick="nextSlide()" style="display:none;">Next →</button>
          <?php endif; ?>
        </div>

        <div class="feedback" id="feedback<?= $i ?>"></div>
      </div>
    <?php endforeach; ?>
    <div class="controls">
      <button type="submit" class="submit-btn" id="submitBtn" style="display:none;">Submit</button>
    </div>
  </form>
</div>

<audio id="correctSound" src="../AppFiles/sound/correct.mp3"></audio>
<audio id="wrongSound" src="../AppFiles/sound/wrong.mp3"></audio>

<script>
let current = 0;
const slides = document.querySelectorAll('.slide');
const correctSound = document.getElementById('correctSound');
const wrongSound = document.getElementById('wrongSound');
let soundTimeout = null; // Used to auto-stop sound after 1 min

function showSlide(i) {
  stopSound(); // stop any previous sound
  slides.forEach(s => s.classList.remove('active'));
  slides[i].classList.add('active');
}

function nextSlide() {
  if (current < slides.length - 1) {
    current++;
    showSlide(current);
  }
}

function stopSound() {
  correctSound.pause();
  correctSound.currentTime = 0;
  wrongSound.pause();
  wrongSound.currentTime = 0;
  if (soundTimeout) clearTimeout(soundTimeout);
}

function clearResponse(i) {
  document.getElementById('answer' + i).value = '';
  document.getElementById('feedback' + i).style.display = 'none';
}

function startRecognition(i) {
  const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
  recognition.lang = "hi-IN";
  recognition.interimResults = false;
  recognition.maxAlternatives = 1;

  const status = document.getElementById("status" + i);
  status.textContent = "🎙️ Listening...";

  recognition.onresult = function(event) {
    const transcript = event.results[0][0].transcript;
    document.getElementById("answer" + i).value = transcript;
    status.textContent = "✅ Captured";
  };

  recognition.onerror = function(event) {
    status.textContent = "❌ Error: " + event.error;
  };

  recognition.onend = function() {
    if (status.textContent === "🎙️ Listening...") {
      status.textContent = "⚠️ No speech detected";
    }
  };

  recognition.start();
}

function normalize(text) {
  return text.replace(/[।.]/g, '').trim();
}

function checkAnswer(i) {
  stopSound(); // Stop any currently playing sound

  const input = normalize(document.getElementById('answer' + i).value);
  const correct = JSON.parse(document.querySelector(`[name=correct${i}]`).value);
  const feedback = document.getElementById('feedback' + i);

  let matched = correct.some(ans => normalize(ans) === input);
  if (matched) {
    feedback.className = 'feedback correct';
    feedback.textContent = '✅ Correct!';
    correctSound.play();
    soundTimeout = setTimeout(stopSound, 60000); // Stop after 1 min
  } else {
    feedback.className = 'feedback wrong';
    feedback.innerHTML = '❌ Incorrect!<br><strong>Correct Answers:</strong><br>' + correct.join('<br>');
    wrongSound.play();
    soundTimeout = setTimeout(stopSound, 60000); // Stop after 1 min
  }

  feedback.style.display = 'block';
  document.getElementById('btns' + i).style.display = 'none';
  const nextBtn = document.getElementById('next' + i);
  if (nextBtn) nextBtn.style.display = 'inline-block';

  document.querySelector(`#slide${i} .check-btn`).style.display = 'none';

  if (i === slides.length - 1) {
    document.getElementById('submitBtn').style.display = 'inline-block';
  }
}

document.addEventListener('DOMContentLoaded', () => showSlide(current));
</script>

</body>
</html>
