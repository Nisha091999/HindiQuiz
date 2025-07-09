<?php
// 🛡️ Strong cache prevention
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

// ✅ Ensure UTF-8 output
header('Content-Type: text/html; charset=utf-8');

session_start();

// 🔒 Block access if quiz already completed
if (!empty($_SESSION['quiz_done'])) {
    header("Location: index.php");
    exit();
}

// 🔐 Enforce POST-only access from menu
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['folder']) ||
    !isset($_SESSION['allow_quiz']) ||
    $_SESSION['allow_quiz'] !== true
) {
    header("Location: index.php");
    exit();
}

unset($_SESSION['allow_quiz']); // One-time access only

$folder = $_POST['folder'];
$level = $_SESSION['level'];
$imgPath = "AppFiles/images/$level/$folder/";
$answerFile = $imgPath . "Answers.txt";

// 🔄 Load images and answers
$images = [];
$answersMap = [];
if (file_exists($answerFile)) {
    foreach (file($answerFile) as $line) {
        if (trim($line)) {
            // Split only on first comma to allow answers with commas
            $parts = explode(",", $line, 2);
            if(count($parts) === 2){
                list($img, $answers) = $parts;
                $images[] = trim($img);
                $answersMap[trim($img)] = array_map('trim', explode(",", $answers));
            }
        }
    }
}
shuffle($images);
$selectedImages = array_slice($images, 0, 10);

// 🧠 Store quiz session data
$_SESSION['quiz_images'] = $selectedImages;
$_SESSION['quiz_answers'] = $answersMap;
$_SESSION['quiz_folder'] = $folder;
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="utf-8" />
    <title>Quiz - <?= htmlspecialchars($folder) ?></title>
    <link rel="stylesheet" href="assets/style.css" />
    <style>
        /* --- General styles --- */
        html, body {
            margin: 0; padding: 0; height: 100%;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0f4f7, #e0ebf5);
            overflow: hidden; /* Prevent page scroll */
        }
        .container {
margin-top: 0;
            width: 70%;
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin: 10px auto;
            display: flex;
            flex-direction: column;
            height: 95vh;
        }
        /* Header */
.quiz-header-container {
   display: flex;
    align-items: center;
    justify-content: space-between; /* Spread children to edges */
    margin: 5px 0 10px 0;
    flex-wrap: nowrap;   /* Prevent wrapping */
    gap: 10px;           /* Optional spacing */
}
        .quiz-header-container h2 {
            margin: 0;
            font-size: 20px;
            color: #2c3e50;
        }
        .nav-next-button {
            padding: 8px 16px;
            font-size: 14px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(52, 152, 219, 0.6);
            transition: background-color 0.2s ease-in-out;
        }
        .nav-next-button:hover {
            background-color: #2980b9;
        }
        /* Slide container with scroll if needed */
        .slide-container {
            flex: 1 1 auto;
            overflow-y: auto;
            padding-right: 10px;
            margin-bottom: 10px;
        }
        .slide {
            display: none;
            text-align: center;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .slide.active {
            display: block;
        }
        /* Image */
        .quiz-image {
            width: 180px;
            height: 180px;
            margin: 2px 0;
        }
        /* Question heading */
        .slide h3 {
            margin: 5px 0;
            font-size: 14px;
            color: #444;
        }
        /* Navigation buttons */
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin: 10px 0 0 0;
        }
        .nav-buttons button, .submit-btn {
            padding: 6px 14px;
            font-size: 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-btn {
            background-color: #27ae60;
            border: none;
            color: white;
        }
        .submit-btn:hover {
            background-color: #1e8449;
        }
        /* Shared input */
        #sharedInput {
            width: 100%;
            height: 40px;
            font-size: 20px;
            padding: 5px 8px;
            box-sizing: border-box;
            margin: 10px 0 5px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            user-select: text;
        }
        /* Keyboard styles */




    /* ... existing styles ... */

    /* Keyboard styles */
    #keyboard {
        display: flex;
        gap: 125px;             /* reduced gap between columns by 50% */
        max-height: 50vh;
        overflow-x: auto;      /* horizontal scroll if needed */
        overflow-y: hidden;
        background: #f0f0f0;
        border-top: 2px solid #ccc;
        padding: 12px 15px;
        border-radius: 0 0 12px 12px;
        box-sizing: border-box;
        user-select: none;
        width: 100%;           /* 30% wider */
/*        margin-left: -15%; %/    /* center it visually */
    margin-left: calc(-5% + 50px); /* Adjust existing -15% margin and add 125px */
    }

.keyboard-column:first-child {
    margin-right: -50px;
}

    .keyboard-column {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        gap: 8px;             /* reduced vertical gap */
        overflow-y: auto;
        max-height: 50vh;
    }

    #keyboard button {
        font-size: 13.5px;     /* 25% smaller than 18px */
        padding: 6px 9px;
        cursor: pointer;
        border-radius: 6px;
        border: 1px solid #bbb;
        background: white;
        transition: background-color 0.2s;
        min-width: 30px;
        min-height: 30px;
        user-select: none;
    }

    #keyboard button:hover {
        background-color: #d0e6fb;
        border-color: #3498db;
    }

    .small-keys button {
        font-size: 12px;
        padding: 4px 6px;
        min-width: 26px;
        min-height: 26px;
    }

    .row-label {
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 4px;
        color: #444;
    }

    .row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: nowrap;
    }


    </style>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            let currentIndex = 0;
            const slides = document.querySelectorAll('.slide');
            const nextBtn = document.getElementById('globalNextBtn');
            const prevBtn = document.getElementById('globalPrevBtn');
            const submitBtn = document.getElementById('submitBtn');
            const sharedInput = document.getElementById('sharedInput');

function showSlide(index) {
    if (index < 0 || index >= slides.length) return;

    const currentAnswerInput = document.getElementById("answer" + currentIndex);
    if (currentAnswerInput) {
        currentAnswerInput.value = sharedInput.value;
    }

    currentIndex = index;

    slides.forEach(s => s.classList.remove('active'));
    slides[currentIndex].classList.add('active');

    const savedValue = document.getElementById("answer" + currentIndex)?.value || '';
    sharedInput.value = savedValue;
    sharedInput.focus();

    // Show/hide buttons appropriately
    nextBtn.style.display = (currentIndex === slides.length - 1) ? 'none' : 'inline-block';
    prevBtn.style.display = (currentIndex === 0) ? 'none' : 'inline-block';
submitBtn.style.display = (currentIndex === slides.length - 1) ? 'inline-block' : 'none';


//alert("Is last slide:"+ currentIndex + ":" + slides.length - 1);
//alert("submitBtn found:"+ submitBtn.style.display);



}


            nextBtn.addEventListener('click', () => {
                if (currentIndex < slides.length - 1) {
                    showSlide(currentIndex + 1);
                speakAnswer();
                }
            });

prevBtn.addEventListener('click', () => {
    if (currentIndex > 0) {
        showSlide(currentIndex - 1);
                speakAnswer();
    }
});


            // Expose globally for inline calls
            window.showSlide = showSlide;

            // Initialize first slide
            showSlide(0);
        });

        // Keyboard input handling
        function addChar(char) {
            const input = document.getElementById("sharedInput");
            input.value += char;
            speakChar(char);
            // Save on input
            const currentIndex = [...document.querySelectorAll('.slide')].findIndex(s => s.classList.contains('active'));
            const hiddenInput = document.getElementById("answer" + currentIndex);
            if (hiddenInput) hiddenInput.value = input.value;
        }


function speakAnswer() {
    const input = document.getElementById("sharedInput");
    const text = input.value.trim();
    if (text) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = "hi-IN";  // Hindi language code
        window.speechSynthesis.speak(utterance);
    }
}
        function speakChar(char) {
            const utterance = new SpeechSynthesisUtterance(char);
            utterance.lang = "hi-IN";
////            window.speechSynthesis.speak(utterance);
        }

        function deleteLast() {
            const input = document.getElementById("sharedInput");
            input.value = input.value.slice(0, -1);
            const currentIndex = [...document.querySelectorAll('.slide')].findIndex(s => s.classList.contains('active'));
            const hiddenInput = document.getElementById("answer" + currentIndex);
            if (hiddenInput) hiddenInput.value = input.value;
        }

        function clearInput() {
            const input = document.getElementById("sharedInput");
            input.value = '';
            const currentIndex = [...document.querySelectorAll('.slide')].findIndex(s => s.classList.contains('active'));
            const hiddenInput = document.getElementById("answer" + currentIndex);
            if (hiddenInput) hiddenInput.value = '';
        }
    </script>
</head>
<body>


<div class="container">
    <div class="quiz-header-container">
        <h2>Quiz: <?= htmlspecialchars($folder) ?></h2>
<div class="quiz-header-container">

<div class="quiz-header-container">
  <button type="button" id="globalPrevBtn" class="nav-next-button">&larr; Previous</button>
  
  <h2 style="margin: 0; flex-grow: 1; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
    Quiz: <?= htmlspecialchars($folder) ?>
  </h2>
  
  <button type="button" id="globalNextBtn" class="nav-next-button">Next &rarr;</button>
</div>

</div>
    </div>

    <form method="POST" action="result.php" id="quizForm" autocomplete="off">
 <div style="text-align: right; margin-top: 10px;">
  <button type="submit" id="submitBtn" class="submit-btn" style="display:none;">Submit</button>
</div>
        <div class="slide-container">
            <?php foreach ($selectedImages as $index => $img): ?>
                <div class="slide" id="slide<?= $index ?>">
                    <h3>Question <?= $index + 1 ?> of <?= count($selectedImages) ?> | प्रश्न <?= $index + 1 ?> / <?= count($selectedImages) ?></h3>
                    <img src="<?= $imgPath . $img ?>" alt="<?= htmlspecialchars($img) ?>" class="quiz-image" />
                    <input type="hidden" name="answer<?= $index ?>" id="answer<?= $index ?>" value="" />
                    <input type="hidden" name="img<?= $index ?>" value="<?= htmlspecialchars($img) ?>" />
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Shared Input Box -->
        <input type="text" id="sharedInput" readonly aria-label="Your answer in Hindi" />

        <!-- Keyboard -->
        <?php
        // You can move the following keyboard HTML into a separate file called 'keyboard-layout.php'
        // and then include it here with:
        // include 'keyboard-layout.php';
        ?>


<!-- Keyboard -->
<div id="keyboard" role="application" aria-label="Hindi input keyboard">

  <!-- Vowels + Matras column (unchanged) -->
  <div class="keyboard-column" aria-label="Vowels and Matras">
    <div class="row">
      <div class="row-label">स्वर</div>
      <button type="button" onclick="addChar('अ')">अ</button>
      <button type="button" onclick="addChar('आ')">आ</button>
      <button type="button" onclick="addChar('इ')">इ</button>
      <button type="button" onclick="addChar('ई')">ई</button>
      <button type="button" onclick="addChar('उ')">उ</button>
      <button type="button" onclick="addChar('ऊ')">ऊ</button>
      <button type="button" onclick="addChar('ए')">ए</button>
      <button type="button" onclick="addChar('ऐ')">ऐ</button>
      <button type="button" onclick="addChar('ओ')">ओ</button>
      <button type="button" onclick="addChar('औ')">औ</button>
      <button type="button" onclick="addChar('अं')">अं</button>
      <button type="button" onclick="addChar('अः')">अः</button>
    </div>
    <div class="row small-keys">
      <div class="row-label">मात्राएँ</div>
      <button type="button" onclick="addChar('ा')">ा</button>
      <button type="button" onclick="addChar('ि')">ि</button>
      <button type="button" onclick="addChar('ी')">ी</button>
      <button type="button" onclick="addChar('ु')">ु</button>
      <button type="button" onclick="addChar('ू')">ू</button>
      <button type="button" onclick="addChar('े')">े</button>
      <button type="button" onclick="addChar('ै')">ै</button>
      <button type="button" onclick="addChar('ो')">ो</button>
      <button type="button" onclick="addChar('ौ')">ौ</button>
      <button type="button" onclick="addChar('ं')">ं</button>
      <button type="button" onclick="addChar('ः')">ः</button>
      <button type="button" onclick="addChar('्')">्</button> <!-- Halant -->
    </div>
  </div>

  <!-- Consonant columns -->
  <div class="keyboard-column consonants" aria-label="Consonants">

    <!-- Row 1: Ka series -->
    <div class="row" aria-label="व्यंजन 1: Ka श्रृंखला">
      <div class="row-label">व्यंजन 1 (क श्रृंखला)</div>
      <button type="button" onclick="addChar('क')">क</button>
      <button type="button" onclick="addChar('ख')">ख</button>
      <button type="button" onclick="addChar('ग')">ग</button>
      <button type="button" onclick="addChar('घ')">घ</button>
      <button type="button" onclick="addChar('ङ')">ङ</button>
    </div>

    <!-- Row 2: Cha series -->
    <div class="row" aria-label="व्यंजन 2: Cha श्रृंखला">
      <div class="row-label">व्यंजन 2 (च श्रृंखला)</div>
      <button type="button" onclick="addChar('च')">च</button>
      <button type="button" onclick="addChar('छ')">छ</button>
      <button type="button" onclick="addChar('ज')">ज</button>
      <button type="button" onclick="addChar('झ')">झ</button>
      <button type="button" onclick="addChar('ञ')">ञ</button>
    </div>

    <!-- Row 3: Ta series -->
    <div class="row" aria-label="व्यंजन 3: Ta श्रृंखला">
      <div class="row-label">व्यंजन 3 (ट श्रृंखला)</div>
      <button type="button" onclick="addChar('ट')">ट</button>
      <button type="button" onclick="addChar('ठ')">ठ</button>
      <button type="button" onclick="addChar('ड')">ड</button>
      <button type="button" onclick="addChar('ढ')">ढ</button>
      <button type="button" onclick="addChar('ण')">ण</button>
    </div>

    <!-- Row 4: Tha series -->
    <div class="row" aria-label="व्यंजन 4: Tha श्रृंखला">
      <div class="row-label">व्यंजन 4 (त श्रृंखला)</div>
      <button type="button" onclick="addChar('त')">त</button>
      <button type="button" onclick="addChar('थ')">थ</button>
      <button type="button" onclick="addChar('द')">द</button>
      <button type="button" onclick="addChar('ध')">ध</button>
      <button type="button" onclick="addChar('न')">न</button>
    </div>

    <!-- Row 5: Pa series -->
    <div class="row" aria-label="व्यंजन 5: Pa श्रृंखला">
      <div class="row-label">व्यंजन 5 (प श्रृंखला)</div>
      <button type="button" onclick="addChar('प')">प</button>
      <button type="button" onclick="addChar('फ')">फ</button>
      <button type="button" onclick="addChar('ब')">ब</button>
      <button type="button" onclick="addChar('भ')">भ</button>
      <button type="button" onclick="addChar('म')">म</button>
    </div>

    <!-- Vishesh group -->
    <div class="row" aria-label="विशेष वर्ण समूह">
      <div class="row-label">विशेष वर्ण</div>
      <button type="button" onclick="addChar('य')">य</button>
      <button type="button" onclick="addChar('र')">र</button>
      <button type="button" onclick="addChar('ल')">ल</button>
      <button type="button" onclick="addChar('व')">व</button>
    </div>

    <!-- Shesh group -->
    <div class="row" aria-label="शेष वर्ण समूह">
      <div class="row-label">शेष वर्ण</div>
      <button type="button" onclick="addChar('श')">श</button>
      <button type="button" onclick="addChar('ष')">ष</button>
      <button type="button" onclick="addChar('स')">स</button>
      <button type="button" onclick="addChar('ह')">ह</button>
      <button type="button" onclick="addChar('ळ')">ळ</button>
      <button type="button" onclick="addChar('क्ष')">क्ष</button>
      <button type="button" onclick="addChar('ज्ञ')">ज्ञ</button>

      <!-- Delete and Clear aligned here -->
              <button type="button" onclick="deleteLast()" title="Delete last character">⌫</button>
              <button type="button" onclick="clearInput()" title="Clear input">Clear</button>
    </div>

  </div>
</div>

       </div>


    </form>
</div>
</body>
</html>
