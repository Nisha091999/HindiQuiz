<?php
session_start();

// Prevent direct access without POST and valid session
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

// Set flag to prevent re-entering quiz.php
$_SESSION['quiz_done'] = true;

$user = $_SESSION['user'];
$level = $_SESSION['level'];
$images = $_SESSION['quiz_images'];
$answers = $_SESSION['quiz_answers'];
$sessionId = session_id();
$time = date('d/m/Y H:i:s');
$score = 0;
$total = count($images);

$results = [];
for ($i = 0; $i < $total; $i++) {
    $img = $_POST["img$i"] ?? '';
    $response = trim($_POST["q$i"] ?? '');

    // Skip invalid input
    if (!isset($answers[$img])) continue;

    $correct = in_array(strtolower($response), array_map('strtolower', $answers[$img]));
    if ($correct) $score++;

    $results[] = [
        'img' => $img,
        'response' => $response,
        'correct_answers' => implode(', ', $answers[$img]),
        'correct' => $correct ? 'Y' : 'N'
    ];
}

// Save score to file
$scoreLine = "$level,$user,$time,$sessionId,$score/$total\n";
file_put_contents("AppData/Scores.txt", $scoreLine, FILE_APPEND);

// Clear quiz session data so it can't be reused
unset($_SESSION['quiz_images'], $_SESSION['quiz_answers'], $_SESSION['quiz_folder'], $_SESSION['allow_quiz']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quiz Results</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f2f2f2;
            padding: 30px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: relative;
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
            transition: background-color 0.3s;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        @media (max-width: 600px) {
            .logout-btn {
                font-size: 14px;
                padding: 8px 14px;
            }
            .logout-wrapper {
                justify-content: center;
            }
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
        // Prevent browser back or forward navigation to quiz
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
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <h2>Quiz Results for <?= htmlspecialchars($user) ?></h2>
    <p><strong>Score:</strong> <?= $score ?>/<?= $total ?></p>
    <table>
        <tr>
            <th>Q#</th>
            <th>Image</th>
            <th>Your Answer</th>
            <th>Correct Answers</th>
            <th>Result</th>
        </tr>
        <?php foreach ($results as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($r['img']) ?></td>
            <td><?= htmlspecialchars($r['response']) ?></td>
            <td><?= htmlspecialchars($r['correct_answers']) ?></td>
            <td><?= $r['correct'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
