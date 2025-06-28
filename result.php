<?php
session_start();

// Set flag to block re-entering quiz.php
$_SESSION['quiz_done'] = true;

// Validate access
if (
    !isset($_SESSION['user']) || 
    !isset($_SESSION['quiz_images']) || 
    !isset($_SESSION['quiz_answers']) ||
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    empty($_POST["img0"])
) {
    header("Location: index.php");
    exit();
}

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
    
    // Avoid crashing on unexpected data
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

// Save score
$scoreLine = "$level,$user,$time,$sessionId,$score/$total\n";
file_put_contents("AppData/Scores.txt", $scoreLine, FILE_APPEND);
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
        // Disable back/forward navigation after result page
        window.history.pushState(null, document.title, window.location.href);
        window.history.pushState(null, document.title, window.location.href);
        window.history.back();

        window.onpopstate = function () {
            window.history.go(1);
        };
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
