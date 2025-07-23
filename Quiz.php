<?php
// Strong cache prevention & UTF-8 header
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header('Content-Type: text/html; charset=utf-8');

session_start();

// Block if quiz done or invalid access
if (!empty($_SESSION['quiz_done']) || 
    (!isset($_POST['folder']) && !isset($_SESSION['quiz_images'])) || 
    (isset($_POST['folder']) && empty($_SESSION['allow_quiz']))) {
    header("Location: index.php");
    exit();
}

// Determine which question to show
$startIndex = isset($_POST['start_index']) ? (int)$_POST['start_index'] : 0;

if (isset($_POST['folder'])) {
    // First time loading quiz.php, initialize quiz
    unset($_SESSION['allow_quiz']);
    $folder = $_POST['folder'];
    $level = $_SESSION['level'];
    $imgPath = "AppFiles/images/$level/$folder/";
    $answerFile = $imgPath . "Answers.txt";

    // Load images and answers map
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

    // Save session data
    $_SESSION['quiz_images'] = $selectedImages;
    $_SESSION['quiz_answers'] = $answersMap;
    $_SESSION['quiz_folder'] = $folder;
} else {
    // Subsequent requests
    $selectedImages = $_SESSION['quiz_images'];
    $answersMap = $_SESSION['quiz_answers'];
    $folder = $_SESSION['quiz_folder'];
    $level = $_SESSION['level'];
    $imgPath = "AppFiles/images/$level/$folder/";
}

// Current question
$currentIndex = $startIndex;
$currentImage = $selectedImages[$currentIndex];
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="utf-8" />
<title>क्विज़ / Quiz - <?= htmlspecialchars($folder) ?></title>
<link rel="stylesheet" href="assets/style.css" />
<style>
html, body {
    margin:0; padding:0; height:100%; font-family:'Segoe UI',sans-serif;
    background: linear-gradient(135deg,#f0f4f7,#e0ebf5);
}
.container {
    margin:10px auto; width:70%; background:#fff; padding:15px 20px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1);
}
.quiz-header-container {
    display:flex; align-items:center; justify-content:space-between; margin:5px 0 10px;
}
.quiz-header-container h2 {
    margin:0; font-size:20px; color:#2c3e50;
}
.quiz-image {
    width:200px; height:200px; margin:10px auto; display:block;
}
#sharedInput {
    width:100%; height:40px; font-size:20px; padding:5px 8px; border-radius:6px; border:1px solid #ccc;
}
#keyboard {
    display:flex; gap:12px; background:#f0f0f0; border-top:2px solid #ccc; padding:12px 15px; border-radius:0 0 12px 12px;
    justify-content:center;
}
.keyboard-column {
    flex:1; display:flex; flex-direction:column; gap:6px;
    max-width: 160px;
}
#keyboard button {
    font-size:14px; padding:6px 10px; cursor:pointer; border-radius:6px; border:1px solid #bbb; background:#fff;
    transition: background-color 0.2s;
}
#keyboard button:hover {
    background:#d0e6fb; border-color:#3498db;
}
button.submit-btn {
    background:#27ae60; color:#fff; padding:10px 20px; border:none; border-radius:6px;
    font-size:16px; margin-top:10px; width:100%;
}
button.submit-btn:hover {
    background:#1e8449;
}
.keyboard-row {
    display:flex; gap:6px; flex-wrap:wrap; justify-content:center;
}
</style>
<script>
function addChar(c) {
    let input = document.getElementById("sharedInput");
    input.value += c;
}
function deleteLast() {
    let input = document.getElementById("sharedInput");
    input.value = input.value.slice(0, -1);
}
function clearInput() {
    document.getElementById("sharedInput").value = '';
}
</script>
</head>
<body>
<div class="container">
    <div class="quiz-header-container">
        <h2>प्रश्न <?= $currentIndex+1 ?> / <?= count($selectedImages) ?> : <?= htmlspecialchars($folder) ?></h2>
        <h2 style="font-size:16px; color:#666;">Question <?= $currentIndex+1 ?> / <?= count($selectedImages) ?></h2>
    </div>
    <form method="POST" action="answer_quiz.php">
        <img src="<?= $imgPath . $currentImage ?>" alt="" class="quiz-image" />
        <input type="hidden" name="current_index" value="<?= $currentIndex ?>" />
        <input type="hidden" name="img" value="<?= htmlspecialchars($currentImage) ?>" />
        <input type="hidden" name="folder" value="<?= htmlspecialchars($folder) ?>" />
        <input type="hidden" name="level" value="<?= htmlspecialchars($level) ?>" />
        <input 
            type="text" 
            name="user_answer" 
            id="sharedInput" 
            readonly 
            placeholder="अपना उत्तर यहाँ लिखें / Type your answer here" 
            aria-label="उत्तर / Answer input" 
        />
        <!-- Keyboard -->
        <div id="keyboard" aria-label="Hindi Input Keyboard">
            <?php
            $vowels = ['अ','आ','इ','ई','उ','ऊ','ए','ऐ','ओ','औ','अं','अः'];
            $matras = ['ा','ि','ी','ु','ू','े','ै','ो','ौ','ं','ः','्'];
            $consonantRows = [
                ['क','ख','ग','घ','ङ'],
                ['च','छ','ज','झ','ञ'],
                ['ट','ठ','ड','ढ','ण'],
                ['त','थ','द','ध','न'],
                ['प','फ','ब','भ','म'],
            ];
            $specialGroup = ['य','र','ल','व'];
            $remainingGroup = ['श','ष','स','ह','ळ','क्ष','ज्ञ'];

            $flatConsonants = [];
            foreach ($consonantRows as $row) {
                $flatConsonants = array_merge($flatConsonants, $row);
            }
            $allConsonants = array_merge($flatConsonants, $specialGroup, $remainingGroup);
            ?>
            <div class="keyboard-column" aria-label="स्वर एवं मात्राएँ / Vowels and Matras">
                <div class="keyboard-row" aria-label="स्वर / Vowels">
                    <?php foreach ($vowels as $v): ?>
                        <button type="button" onclick="addChar('<?= $v ?>')"><?= $v ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="keyboard-row" aria-label="मात्राएँ / Matras">
                    <?php foreach ($matras as $m): ?>
                        <button type="button" onclick="addChar('<?= $m ?>')"><?= $m ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="keyboard-column" aria-label="व्यंजन / Consonants">
                <div class="keyboard-row" aria-label="व्यंजन / Consonants">
                    <?php foreach ($allConsonants as $c): ?>
                        <button type="button" onclick="addChar('<?= $c ?>')"><?= $c ?></button>
                    <?php endforeach; ?>
                    <button type="button" onclick="deleteLast()" title="पिछला अक्षर हटाएँ / Delete last character">⌫</button>
                    <button type="button" onclick="clearInput()" title="साफ़ करें / Clear input">Clear</button>
                </div>
            </div>
        </div>
        <button type="submit" class="submit-btn" aria-label="उत्तर सबमिट करें / Submit answer">
            उत्तर सबमिट करें / Submit Answer
        </button>
    </form>
</div>
</body>
</html>
