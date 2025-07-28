<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header('Content-Type: text/html; charset=utf-8');

if (!empty($_SESSION['quiz_done']) || 
    (!isset($_POST['folder']) && !isset($_SESSION['quiz_images'])) || 
    (isset($_POST['folder']) && empty($_SESSION['allow_quiz']))) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['folder'])) {
    unset($_SESSION['allow_quiz']);
    $folder = $_POST['folder'];
    $level = $_SESSION['level'];
    $imgPath = "AppFiles/images/$level/$folder/";
    $answerFile = $imgPath . "Answers.txt";

    $images = $answersMap = [];
    if (file_exists($answerFile)) {
        foreach (file($answerFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $parts = explode(",", $line, 2);
            if (count($parts) === 2) {
                $img = trim($parts[0]);
                $answersMap[$img] = array_map('trim', explode(",", $parts[1]));
                $images[] = $img;
            }
        }
    }
    shuffle($images);
    $selectedImages = array_slice($images, 0, 10);

    $_SESSION['quiz_images'] = $selectedImages;
    $_SESSION['quiz_answers'] = $answersMap;
    $_SESSION['quiz_folder'] = $folder;
} else {
    $selectedImages = $_SESSION['quiz_images'];
    $answersMap     = $_SESSION['quiz_answers'];
    $folder         = $_SESSION['quiz_folder'];
    $level          = $_SESSION['level'];
    $imgPath        = "AppFiles/images/$level/$folder/";
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="UTF-8">
  <title>Duolingo-Style Hindi Quiz</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #eef2f5;
      padding: 20px;
      margin: 0;
    }

    .container {
      max-width: 960px;
      margin: 0 auto;
      background: #fff;
      padding: 20px 30px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      margin-top: 0;
    }

    .question-slide {
      display: none;
      animation: fadeIn 0.4s ease-in-out;
    }

    .question-slide.active {
      display: block;
    }

    .image-wrapper {
      text-align: center;
      margin-bottom: 20px;
    }
    .image-wrapper img {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }


    .feedback {
      display: none;
      padding: 10px;
      margin-top: 10px;
      border-radius: 6px;
      font-weight: bold;
    }

    .correct {
      background-color: #d4edda;
      color: #155724;
    }

    .wrong {
      background-color: #f8d7da;
      color: #721c24;
    }

    button {
      font-size: 18px;
      padding: 10px 20px;
      margin-top: 10px;
      border-radius: 8px;
      border: none;
      background: #007bff;
      color: white;
      cursor: pointer;
    }
    button:hover {
      background: #0056b3;
    }
    button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    input.sharedInput {
      width: 100%;
      height: 40px;
      font-size: 20px;
      padding: 5px 8px;
      margin: 10px 0 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
      user-select: text;
    }

    .keyboard-instruction {
      white-space: nowrap;
      margin: 6px 0;
      padding: 6px 10px;
      background-color: #eaf2ff;
      border-left: 4px solid #3498db;
      border-radius: 6px;
      font-size: 18px;
      color: #2c3e50;
      font-weight: bold;
    }

    kbd {
      background: #ccc;
      border-radius: 4px;
      padding: 2px 6px;
      font-weight: bold;
    }

    .hindi-text {
      font-family: 'Noto Sans Devanagari', sans-serif;
      margin-left: 10px;
    }

    .keyboard {
      max-width: 880px;
      margin: 10px auto 0;
      user-select: none;
    }

    .keyboard .row {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 6px;
      margin-bottom: 6px;
    }

    .keyboard .key {
      padding: 10px 16px;
      min-width: 40px;
      text-align: center;
      border: 1px solid #999;
      border-radius: 6px;
      background-color: #f0f0f0;
      font-size: 20px;
      cursor: pointer;
    }
    .keyboard .key:hover {
      background-color: #ddd;
    }
    .keyboard .key.special {
      background-color: #ccc;
      font-weight: bold;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateX(30px); }
      to   { opacity: 1; transform: translateX(0); }
    }
  </style>
</head>
<body>
  <div class="container">
    <form id="quizForm" method="POST" action="result.php" style="display:none;"></form>
    <h2 id="questionCounter">Question 1 / <?= count($selectedImages) ?></h2>
    <div id="quizWrapper">
      <?php foreach ($selectedImages as $i => $img): ?>
        <div class="question-slide<?= $i===0 ? ' active' : '' ?>" data-index="<?= $i ?>">
          <div class="image-wrapper">
            <img src="<?= $imgPath . $img ?>" alt="Q<?= $i+1 ?>" width="300">
          </div>
          <input type="text" class="sharedInput" readonly placeholder="अपना उत्तर यहाँ लिखें">
          <input type="hidden" class="correctAnswers" value='<?= json_encode($answersMap[$img]) ?>'>
          <div class="feedback"></div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="keyboard-instruction">
      Tip: Press <kbd>Shift</kbd> for more letters —
      <span class="hindi-text">टिप: अधिक अक्षरों के लिए <kbd>Shift</kbd> दबाएँ।</span>
    </div>
    <div class="keyboard" id="keyboard" aria-label="Hindi InScript Keyboard"></div>

    <button id="checkBtn" onclick="checkAnswer()">Check</button>
    <button id="nextBtn" onclick="nextQuestion()" disabled>Next</button>
  </div>

  <audio id="correctSound" src="/HindiQuiz/AppFiles/sound/correct.mp3"></audio>
  <audio id="wrongSound"   src="/HindiQuiz/AppFiles/sound/wrong.mp3"></audio>

  <script>
    let currentQuestion = 0;
    const totalQuestions = <?= count($selectedImages) ?>;

    function checkAnswer() {
      const input    = document.querySelectorAll('.sharedInput')[currentQuestion].value.trim();
      const correct  = JSON.parse(document.querySelectorAll('.correctAnswers')[currentQuestion].value);
      const feedback = document.querySelectorAll('.feedback')[currentQuestion];
      let isRight = false;
      if (input !== "") {
        isRight = correct.some(a => input.toLowerCase() === a.toLowerCase());
      }

      if (isRight) {
        feedback.textContent = "✅ सही उत्तर!";
        feedback.className   = "feedback correct";
        correctSound.play();
      } else {
        feedback.textContent = "❌ गलत! सही उत्तर: " + correct.join(", ");
        feedback.className   = "feedback wrong";
        wrongSound.play();
      }

      feedback.style.display    = "block";
      checkBtn.disabled         = true;
      nextBtn.disabled          = false;
    }

    let answers = [];

    function nextQuestion() {
      // Stop previous audio if playing
      correctSound.pause();
      correctSound.currentTime = 0;
      wrongSound.pause();
      wrongSound.currentTime = 0;

      const currentSlide = document.querySelectorAll('.question-slide')[currentQuestion];
      const inputValue = document.querySelectorAll('.sharedInput')[currentQuestion].value.trim();
      answers.push(inputValue);

      currentSlide.classList.remove('active');
      currentQuestion++;

      if (currentQuestion >= totalQuestions) {
        // Submit answers to result.php
        const form = document.getElementById('quizForm');
        answers.forEach((ans, i) => {
          const hiddenInput = document.createElement('input');
          hiddenInput.type = 'hidden';
          hiddenInput.name = `answer_${i}`;
          hiddenInput.value = ans;
          form.appendChild(hiddenInput);
        });
        form.submit();
        return;
      }

      document.querySelectorAll('.question-slide')[currentQuestion].classList.add('active');
      document.getElementById("questionCounter").textContent =
        `Question ${currentQuestion+1} / ${totalQuestions}`;
      checkBtn.disabled = false;
      nextBtn.disabled  = true;
    }


    // --- Keyboard Logic ---
    let shift = false;

const layout = [
  // Row 1: Numbers & Symbols
  [
    { normal: 'ऍ', shift: 'ऑ' }, { normal: '1', shift: 'ऒ' }, { normal: '2', shift: 'ऍ' },
    { normal: '3', shift: 'आ' }, { normal: '4', shift: 'ई' }, { normal: '5', shift: 'ऊ' },
    { normal: '6', shift: 'भ' }, { normal: '7', shift: 'ङ' }, { normal: '8', shift: 'घ' },
    { normal: '9', shift: 'ध' }, { normal: '0', shift: 'झ' }, { normal: '-', shift: 'ञ' },
    { normal: 'ृ', shift: 'ऋ' }
  ],
  // Row 2: Vowels & Mid consonants
  [
    { normal: 'ौ', shift: 'औ' }, { normal: 'ै', shift: 'ऐ' }, { normal: 'ा', shift: 'आ' },
    { normal: 'ी', shift: 'ई' }, { normal: 'ू', shift: 'ऊ' }, { normal: 'ब', shift: 'भ' },
    { normal: 'ह', shift: 'ङ' }, { normal: 'ग', shift: 'घ' }, { normal: 'द', shift: 'ध' },
    { normal: 'ज', shift: 'झ' }, { normal: 'ड', shift: 'ञ' }
  ],
  // Row 3: Halant + consonants
  [
    { normal: 'ो', shift: 'ओ' }, { normal: 'े', shift: 'ए' }, { normal: '्', shift: 'अ' },
    { normal: 'ि', shift: 'इ' }, { normal: 'ु', shift: 'उ' }, { normal: 'प', shift: 'फ' },
    { normal: 'र', shift: 'ऱ' }, { normal: 'क', shift: 'ख' }, { normal: 'त', shift: 'थ' },
    { normal: 'च', shift: 'छ' }, { normal: 'ट', shift: 'ठ' }
  ],
  // Row 4: Misc consonants + Special keys
  [
    { normal: 'Shift', special: true }, { normal: 'ं', shift: 'ँ' }, { normal: 'म', shift: 'ण' },
    { normal: 'न', shift: 'ऩ' }, { normal: 'व', shift: 'ऴ' }, { normal: 'ल', shift: 'ळ' },
    { normal: 'स', shift: 'श' }, { normal: 'य', shift: 'य़' }, { normal: '⌫', special: true },
    { normal: '⨉', special: true }
  ],
  // Row 5: Spacebar
  [
    { normal: 'Space', special: true }
  ]
];


    function renderKeyboard() {
      const kb = document.getElementById('keyboard');
      kb.innerHTML = '';
      layout.forEach(row => {
        const rowDiv = document.createElement('div');
        rowDiv.className = 'row';
        row.forEach(key => {
          const k = document.createElement('div');
          k.className = 'key' + (key.special?' special':'');
          k.textContent = key.special ? key.normal : (shift?key.shift:key.normal);
          k.onclick = () => handleKey(key);
          rowDiv.appendChild(k);
        });
        kb.appendChild(rowDiv);
      });
    }

    function handleKey(key) {
      const inputEl = document.querySelectorAll('.sharedInput')[currentQuestion];
      if (key.special) {
        if (key.normal === 'Shift') {
          shift = !shift;
          renderKeyboard();
        } else if (key.normal === '⌫')     inputEl.value = inputEl.value.slice(0,-1);
        else if (key.normal === '⨉')        inputEl.value = '';
        else if (key.normal === 'Space')    inputEl.value += ' ';
      } else {
        inputEl.value += (shift? key.shift : key.normal);
      }
    }

    renderKeyboard();
  </script>
</body>
</html>
