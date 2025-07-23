<?php 
// Prevent cache & force UTF-8
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header('Content-Type: text/html; charset=utf-8');

session_start();

// Validate required session + POST
if (
    empty($_SESSION['quiz_images']) ||
    empty($_SESSION['quiz_answers']) ||
    !isset($_POST['current_index']) ||
    !isset($_POST['folder']) ||
    !isset($_POST['level']) ||
    !isset($_POST['img']) ||
    !isset($_POST['user_answer'])
) {
    header("Location: index.php");
    exit();
}

// Load data
$quizImages   = $_SESSION['quiz_images'];
$quizAnswers  = $_SESSION['quiz_answers'];
$folder       = $_POST['folder'];
$_SESSION['quiz_folder'] = $folder; // store folder for result.php
$level        = $_POST['level'];
$imgPath      = "AppFiles/images/$level/$folder/";
$currentIndex = (int) $_POST['current_index'];
$totalQuestions = count($quizImages);

// Validate index
if ($currentIndex < 0 || $currentIndex >= $totalQuestions) {
    header("Location: index.php");
    exit();
}

$imgName     = trim($_POST['img']);
$userAnswer  = trim($_POST['user_answer']);

// Store user's answer in session
if (!isset($_SESSION['quiz_user_answers'])) {
    $_SESSION['quiz_user_answers'] = [];
}
$_SESSION['quiz_user_answers'][$currentIndex] = $userAnswer;

// Get correct answers
$correctAnswers = $quizAnswers[$imgName] ?? [];

// Function to calculate match percentage & score
function getMatchScore($userAns, $correctArr) {
    $userAns = mb_strtolower(trim($userAns));
    $bestScore = 0;
    foreach ($correctArr as $correct) {
        similar_text($userAns, mb_strtolower(trim($correct)), $percent);
        if ($percent > $bestScore) {
            $bestScore = $percent;
        }
    }
    // Return fractional score based on match
    if ($bestScore >= 99) {       
        return 1.0;
    } elseif ($bestScore >= 85) { 
        return 0.75;
    } elseif ($bestScore >= 60) { 
        return 0.5;
    }
    return 0;
}

// Calculate score for this answer
$answerScore = getMatchScore($userAnswer, $correctAnswers);
$correct = $answerScore == 1.0; 

// Update score in session
if (!isset($_SESSION['quiz_score'])) {
    $_SESSION['quiz_score'] = 0;
}
$_SESSION['quiz_score'] += $answerScore;

$score = $_SESSION['quiz_score'];

// Prepare for next step
$nextIndex = $currentIndex + 1;
$quizDone  = $nextIndex >= $totalQuestions;
if ($quizDone) {
    $_SESSION['quiz_done'] = true;
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="utf-8">
<title>उत्तर / Answer - प्रश्न / Question <?= $currentIndex + 1 ?></title>
<style>
/* Same CSS as before */
body {font-family:'Segoe UI',sans-serif; background:#f8f9fa; margin:0; padding:20px;}
.container {background:#fff; max-width:700px; margin:20px auto; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1);}
h2 {text-align:center; color:#2c3e50;}
.quiz-image {display:block; margin:15px auto; max-width:200px; border:1px solid #ccc; padding:5px; border-radius:6px; background:#fdfdfd;}
.answer-text {font-size:18px; margin:10px 0;}
.feedback {font-size:20px; font-weight:bold; padding:12px; margin-top:15px; border-radius:8px;}
.correct {color:#2c7a2c; background:#e0f3e0; border:2px solid #2c7a2c;}
.wrong {color:#a94442; background:#fbeaea; border:2px solid #a94442;}
.button-next {background-color: #007bff; color: white; border: none; padding: 14px 24px; font-size: 16px; border-radius: 8px; cursor: pointer; width: 100%; margin-top:20px;}
.button-next:hover { background-color: #0056b3; }

/* Progress bar */
.progress-container {
  width: 100%;
  background-color: #ddd;
  border-radius: 10px;
  margin-bottom: 20px;
  height: 20px;
  overflow: hidden;
}
.progress-bar {
  height: 100%;
  background-color: #007bff;
  width: <?= (($currentIndex+1) / $totalQuestions) * 100 ?>%;
  transition: width 0.4s ease;
  border-radius: 10px;
  text-align: center;
  color: white;
  font-weight: bold;
  line-height: 20px;
  font-size: 14px;
}

/* Score box */
.score-box {
  text-align: center;
  font-size: 18px;
  margin-bottom: 20px;
  font-weight: bold;
  color: #2c3e50;
}
</style>
</head>
<body>

<div class="container">
    <h2>प्रश्न <?= $currentIndex + 1 ?> का उत्तर / Answer of Question <?= $currentIndex + 1 ?></h2>

    <div class="progress-container">
        <div class="progress-bar">
            <?= ($currentIndex + 1) ?>/<?= $totalQuestions ?>
        </div>
    </div>

    <div class="score-box">
        🏆 कुल स्कोर / Total Score: <?= round($score,2) ?> / <?= $totalQuestions ?> 
        <br>(इस प्रश्न पर प्राप्त अंक: <?= $answerScore ?>)
    </div>

    <img src="<?= htmlspecialchars($imgPath . $imgName) ?>" alt="Image" class="quiz-image" />

    <div class="answer-text">
      <strong>✅ आपका उत्तर:</strong> <?= htmlspecialchars($userAnswer ?: "(कोई उत्तर नहीं)") ?>
    </div>
    <div class="answer-text">
      <strong>📘 सही उत्तर:</strong> <?= htmlspecialchars(implode(", ", $correctAnswers)) ?>
    </div>

    <div class="feedback <?= $correct ? 'correct' : 'wrong' ?>">
      <?= $correct ? "✅ पूर्ण रूप से सही!" : ($answerScore > 0 ? "ℹ️ आंशिक रूप से सही" : "❌ गलत उत्तर") ?>
    </div>

    <!-- Button: changes target depending on last question -->
    <form method="POST" action="<?= $quizDone ? 'result.php' : 'quiz.php' ?>">
        <?php if (!$quizDone): ?>
            <input type="hidden" name="start_index" value="<?= $nextIndex ?>">
            <input type="hidden" name="allow_quiz" value="true">
        <?php endif; ?>
        <button class="button-next" type="submit">
            <?= $quizDone ? '🎉 क्विज़ समाप्त करें / Finish Quiz' : '➡ अगला प्रश्न / Next Question' ?>
        </button>
    </form>
</div>

<!-- Multi-tone Sound -->
<script>
const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
function playBeep(freq, duration, volume = 2.5, delay = 0) {
    const oscillator = audioCtx.createOscillator();
    const gainNode = audioCtx.createGain();
    oscillator.type = 'sawtooth';
    oscillator.frequency.value = freq;
    gainNode.gain.setValueAtTime(volume, audioCtx.currentTime + delay);
    gainNode.gain.exponentialRampToValueAtTime(0.05, audioCtx.currentTime + delay + duration / 1000);
    oscillator.connect(gainNode); gainNode.connect(audioCtx.destination);
    oscillator.start(audioCtx.currentTime + delay);
    oscillator.stop(audioCtx.currentTime + delay + duration / 1000);
}
function playCorrectSound() {
    playBeep(880, 500, 2.5, 0);
    playBeep(1320, 500, 2.5, 0.55);
    playBeep(1760, 500, 2.5, 1.1);
}
function playWrongSound() { playBeep(200, 900, 2.5, 0); }
function tryPlaySound() {
    if (audioCtx.state === 'suspended') {
        audioCtx.resume().then(playSoundBasedOnAnswer).catch(() => {});
    } else {
        playSoundBasedOnAnswer();
    }
}
function playSoundBasedOnAnswer() {
    const score = <?= json_encode($answerScore) ?>;
    if (score >= 1) { playCorrectSound(); } else { playWrongSound(); }
}
window.onload = tryPlaySound;
</script>

</body>
</html>
