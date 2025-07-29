<?php 
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$lines = file("../AppFiles/Translations/HinToEngAnswers.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$questions = [];

foreach ($lines as $line) {
    if (preg_match('/^(.*?)(?=\s*")\s*(.*)$/u', $line, $matches)) {
        $hindi = trim($matches[1]);
        preg_match_all('/"([^"]+)"/u', $matches[2], $answerMatches);
        $answers = array_map('trim', $answerMatches[1]);
        $questions[] = ['hindi' => $hindi, 'answers' => $answers];
    }
}

shuffle($questions);
$selected = array_slice($questions, 0, 10);

$_SESSION['oral_translate_questions'] = array_column($selected, 'hindi');
$_SESSION['oral_translate_answers'] = array_column($selected, 'answers');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Oral Translate to English</title>
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
    .nav-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .nav-btn { background-color: #95a5a6; color: white; }
    .check-btn { background-color: #f39c12; color: white; }
    .submit-btn { background-color: #27ae60; color: white; }

    .status {
        font-weight: bold;
        margin-top: 8px;
        height: 24px;
    }
    .correct {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 10px;
        margin-top: 15px;
        border-radius: 5px;
    }
    .wrong {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        padding: 10px;
        margin-top: 15px;
        border-radius: 5px;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>Oral Translate to English</h2>
  <form method="post" action="oral_submit_TranToEng.php">
    <?php foreach ($selected as $i => $item): ?>
      <div class="slide" id="slide<?= $i ?>">
        <div class="sentence"><strong>Sentence <?= $i + 1 ?>:</strong> <?= htmlspecialchars($item['hindi']) ?></div>
        <input type="text" name="answer<?= $i ?>" id="answer<?= $i ?>" placeholder="Speak your English translation here..." readonly onkeydown="return false;">
        <input type="hidden" name="question<?= $i ?>" value="<?= htmlspecialchars($item['hindi']) ?>">

        <div class="controls">
          <!-- Centered Speak & Clear -->
          <div style="text-align: center; margin-bottom: 10px;">
            <button type="button" class="speak-btn" id="speakBtn<?= $i ?>" onclick="startRecognition(<?= $i ?>)">🎤 Speak</button>
            <button type="button" class="clear-btn" id="clearBtn<?= $i ?>" onclick="clearResponse(<?= $i ?>)">🗑️ Clear</button>
          </div>

          <!-- Left-aligned Check & Right-aligned Next -->
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="flex: 1; text-align: left;">
              <button type="button" class="check-btn" id="checkBtn<?= $i ?>" onclick="checkAnswer(<?= $i ?>)">✔️ Check</button>
            </div>
            <div style="flex: 1; text-align: right;">
              <?php if ($i < count($selected) - 1): ?>
                <button type="button" class="nav-btn" id="nextBtn<?= $i ?>" onclick="nextSlide()" disabled style="background-color: #3498db; color: white;">Next →</button>
              <?php else: ?>
                <button type="submit" class="submit-btn" id="submitBtn<?= $i ?>" style="display: none;">Submit</button>
              <?php endif; ?>
            </div>
          </div>


          <div class="status" id="status<?= $i ?>"></div>
          <div id="feedback<?= $i ?>"></div>
        </div>


        <input type="hidden" id="correct<?= $i ?>" value='<?= json_encode($item["answers"]) ?>'>
      </div>
    <?php endforeach; ?>

    

  </form>
</div>

<audio id="correctSound" src="/HindiQuiz/AppFiles/sound/correct.mp3" ></audio>
    <audio id="wrongSound"   src="/HindiQuiz/AppFiles/sound/wrong.mp3" ></audio>

<script>
  let current = 0;
  const slides = document.querySelectorAll('.slide');
  const nextBtn = document.getElementById("nextBtn");

  function showSlide(index) {
    slides.forEach((s, i) => {
      s.classList.remove("active");
      if (i === index) {
        s.classList.add("active");
        document.getElementById(`checkBtn${i}`).style.display = "inline-block";
        document.getElementById(`status${i}`).textContent = "";
        document.getElementById(`feedback${i}`).innerHTML = "";
        nextBtn.disabled = true;
      }
    });

    nextBtn.style.display = index === slides.length - 1 ? 'none' : 'inline-block';
    document.getElementById("submitBtn").style.display = index === slides.length - 1 ? "inline-block" : "none";
  }

  function nextSlide() {
    // Stop any currently playing audio
    const correctSound = document.getElementById("correctSound");
    const wrongSound = document.getElementById("wrongSound");

    correctSound.pause();
    correctSound.currentTime = 0;

    wrongSound.pause();
    wrongSound.currentTime = 0;

    current++;
    showSlide(current);
  }


  function prevSlide() {
    if (current > 0) current--;
    showSlide(current);
  }

  function clearResponse(i) {
    document.getElementById("answer" + i).value = "";
    document.getElementById("status" + i).textContent = "";
    document.getElementById("feedback" + i).innerHTML = "";
  }

  function checkAnswer(i) {
    const userInput = document.getElementById("answer" + i).value.trim().toLowerCase();
    const correctAnswers = JSON.parse(document.getElementById("correct" + i).value).map(a => a.toLowerCase());
    const feedback = document.getElementById("feedback" + i);
    const correctSound = document.getElementById("correctSound");
    const wrongSound = document.getElementById("wrongSound");

    document.getElementById("checkBtn" + i).style.display = "none";
    document.getElementById("speakBtn" + i).style.display = "none";
    document.getElementById("clearBtn" + i).style.display = "none";

    const nextBtnDynamic = document.getElementById("nextBtn" + i);
    const submitBtn = document.getElementById("submitBtn" + i);

    if (correctAnswers.includes(userInput)) {
        feedback.innerHTML = '<div class="correct">✅ Correct!</div>';
        correctSound.currentTime = 0;
        correctSound.play();
    } else {
        feedback.innerHTML = '<div class="wrong">❌ Incorrect.<br><strong>Correct Answers:</strong><br>' + correctAnswers.join("<br>") + "</div>";
        wrongSound.currentTime = 0;
        wrongSound.play();
    }

    if (nextBtnDynamic) nextBtnDynamic.disabled = false;
    if (submitBtn) submitBtn.style.display = "inline-block";
  }


function loopSoundForOneMinute(audioElement) {
  const playStart = Date.now();
  audioElement.loop = true;
  audioElement.currentTime = 0;
  audioElement.play();

  setTimeout(() => {
    audioElement.pause();
    audioElement.currentTime = 0;
    audioElement.loop = false;
  }, 60000); // 1 minute
}

  function startRecognition(i) {
    const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
    recognition.lang = "en-US";
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

  document.addEventListener("DOMContentLoaded", () => {
    showSlide(0);
  });
</script>
</body>
</html>
