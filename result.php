<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

// Access validation
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_SESSION['user']) ||
    !isset($_SESSION['quiz_images']) ||
    !isset($_SESSION['quiz_answers']) ||
    empty($_POST["img0"])
) {
    header("Location: index.php");
    exit();
}

// Prevent repeat quiz attempt
$_SESSION['quiz_done'] = true;

$user = $_SESSION['user'];
$level = $_SESSION['level'];
$images = $_SESSION['quiz_images'];
$answers = $_SESSION['quiz_answers'];
$sessionId = session_id();
$time = date('d/m/Y H:i:s');

$total = count($images);
$totalSimilarity = 0;
$results = [];

for ($i = 0; $i < $total; $i++) {
    $img = $_POST["img$i"] ?? '';
    $response = trim($_POST["q$i"] ?? '');

    if (!isset($answers[$img])) continue;

    $correctAnswers = $answers[$img];
    $maxSimilarity = 0;
    $userAnswerLower = mb_strtolower($response, 'UTF-8');

    foreach ($correctAnswers as $correctAnswer) {
        similar_text($userAnswerLower, mb_strtolower($correctAnswer, 'UTF-8'), $percent);
        if ($percent > $maxSimilarity) {
            $maxSimilarity = $percent;
        }
    }

    $scoreForQuestion = round($maxSimilarity, 2);
    $totalSimilarity += $scoreForQuestion;

    $isCorrect = $scoreForQuestion >= 85; // ✅ 85% threshold
    $results[] = [
        'img' => $img,
        'response' => $response,
        'correct_answers' => implode(', ', $correctAnswers),
        'correct' => $isCorrect ? '✔️ सही / Correct' : '❌ गलत / Wrong',
        'score' => "$scoreForQuestion%"
    ];
}

$averageScore = $total > 0 ? round($totalSimilarity / $total, 2) : 0;

// Save average score summary to file
$line = "$level,$user,$time,$sessionId,$averageScore%\n";
file_put_contents("AppData/Scores.txt", $line, FILE_APPEND);

// Clear quiz session data
unset($_SESSION['quiz_images'], $_SESSION['quiz_answers'], $_SESSION['quiz_folder'], $_SESSION['allow_quiz']);
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="utf-8">
    <title>Quiz Results | क्विज़ परिणाम</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f2f2f2;
            padding: 30px;
        }
        .container {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .logout-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }
        .logout-btn {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #e74c3c;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        h2 {
            color: #2c3e50;
        }
    </style>
    <script>
        // Block back navigation
        if (performance.getEntriesByType("navigation")[0]?.type === "back_forward") {
            location.href = "index.php";
        }
        history.pushState(null, "", location.href);
        window.addEventListener("popstate", function () {
            history.pushState(null, "", location.href);
        });
    </script>
</head>
<body>
<div class="container">
    <div class="logout-wrapper">
        <form method="POST" action="logout.php">
            <button type="submit" class="logout-btn">Logout / लॉगआउट</button>
        </form>
    </div>

    <h2>Quiz Results for <?= htmlspecialchars($user) ?> | <span lang="hi">प्रश्नोत्तरी परिणाम</span></h2>
    <p><strong>Total Score / कुल स्कोर:</strong> <?= $averageScore ?>%</p>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Image / चित्र</th>
            <th>Your Answer / आपका उत्तर</th>
            <th>Correct Answers / सही उत्तर</th>
            <th>Result / परिणाम</th>
            <th>Score (%) / स्कोर (%)</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($results as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($r['img']) ?></td>
            <td><?= htmlspecialchars($r['response']) ?></td>
            <td><?= htmlspecialchars($r['correct_answers']) ?></td>
            <td><?= $r['correct'] ?></td>
            <td><?= $r['score'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
