<?php
session_start();
$_SESSION['quiz_done'] = true;

if (
    !isset($_SESSION['user']) || 
    !isset($_SESSION['oral_quiz_images']) || 
    !isset($_SESSION['oral_quiz_answers']) ||
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    empty($_POST["img0"])
) {
    header("Location: index.php");
    exit();
}

$user      = $_SESSION['user'];
$level     = $_SESSION['level'];
$images    = $_SESSION['oral_quiz_images'];
$answers   = $_SESSION['oral_quiz_answers'];
$folder    = $_SESSION['oral_quiz_folder'];
$sessionId = session_id();
$time      = date('d/m/Y H:i:s');

function calculateMatchPercentage($userAnswer, $correctAnswer) {
    if (!$userAnswer || !$correctAnswer) return 0;

    $userChars = preg_split('//u', (string)$userAnswer, 0, PREG_SPLIT_NO_EMPTY);
    $correctChars = preg_split('//u', (string)$correctAnswer, 0, PREG_SPLIT_NO_EMPTY);
    $matchCount = 0;

    foreach ($userChars as $i => $char) {
        if (isset($correctChars[$i]) && $char === $correctChars[$i]) {
            $matchCount++;
        }
    }

    $maxLength = max(count($userChars), count($correctChars));
    return $maxLength ? round(($matchCount / $maxLength) * 100, 2) : 0;
}

$results = [];
$totalPoints = 0;
$totalPossible = count($images);

foreach ($images as $i => $img) {
    $response = trim($_POST["response$i"] ?? '');
    if (!isset($answers[$img])) continue;

    $bestMatch = 0;
    foreach ($answers[$img] as $correctAns) {
        $match = calculateMatchPercentage(mb_strtolower($response), mb_strtolower($correctAns));
        $bestMatch = max($bestMatch, $match);
    }

    if ($bestMatch >= 99.9) {
        $points = 1.0;
    } elseif ($bestMatch >= 85) {
        $points = 0.75;
    } elseif ($bestMatch >= 60) {
        $points = 0.5;
    } else {
        $points = 0;
    }

    $totalPoints += $points;

    $results[] = [
        'img'             => $img,
        'response'        => $response,
        'correct_answers' => implode(', ', $answers[$img]),
        'percent'         => $bestMatch,
        'points'          => $points,
        'correct'         => $points > 0 ? 'Y' : 'N'
    ];
}

$finalPercentage = round(($totalPoints / $totalPossible) * 100, 2);
$scoreLine = "$level,$user,$time,$sessionId,$totalPoints/$totalPossible,$finalPercentage% (Oral Mode)\n";
file_put_contents("../AppData/Scores.txt", $scoreLine, FILE_APPEND);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Oral Quiz Results</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f2f2f2; padding: 30px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .logout-wrapper { display: flex; justify-content: flex-end; margin-bottom: 15px; }
        .logout-btn { padding: 10px 20px; font-size: 16px; background-color: #e74c3c; border: none; border-radius: 6px; color: white; cursor: pointer; }
        .logout-btn:hover { background-color: #c0392b; }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: center; }
        th { background-color: #3498db; color: white; }
        .speaker-button { background-color: #2ecc71; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; }
        .speaker-button:hover { background-color: #27ae60; }
    </style>
    <script>
        function extractLastWord(text) {
            const words = text.trim().split(/\s+/);
            return words.length > 0 ? words[words.length - 1] : '';
        }

        let hindiFemaleVoice = null;

        function loadVoices() {
            const allVoices = speechSynthesis.getVoices();
            hindiFemaleVoice = allVoices.find(v => v.lang === 'hi-IN' && /female/i.test(v.name)) ||
                               allVoices.find(v => v.lang === 'hi-IN');
        }

        function speakResult(index) {
            const data = window.quizResults?.[index];
            if (!data) return;

            const userWord = extractLastWord(data.userAnswer);
            const correctWord = extractLastWord(data.correctAnswer);

            const userSaid = userWord.trim() ? `Your answer: ${userWord}` : `You did not respond.`;
            const correctSaid = `Correct answer: ${correctWord}`;

            const utterUser = new SpeechSynthesisUtterance(userSaid);
            const utterCorrect = new SpeechSynthesisUtterance(correctSaid);

            utterUser.lang = utterCorrect.lang = "hi-IN";

            if (hindiFemaleVoice) {
                utterUser.voice = hindiFemaleVoice;
                utterCorrect.voice = hindiFemaleVoice;
            }

            speechSynthesis.cancel();
            speechSynthesis.speak(utterUser);

            utterUser.onend = () => {
                setTimeout(() => {
                    speechSynthesis.speak(utterCorrect);
                }, 500);
            };
        }

        window.speechSynthesis.onvoiceschanged = loadVoices;
        window.addEventListener('load', loadVoices);
    </script>
</head>
<body>
    <div class="container">
        <div class="logout-wrapper">
            <form method="POST" action="/HindiQuiz/logout.php">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>

        <h2>Oral Quiz Results for <?= htmlspecialchars($user) ?></h2>
        <p><strong>Score:</strong> <?= $totalPoints ?>/<?= $totalPossible ?> (<?= $finalPercentage ?>%)</p>
        <p><strong>Use 🔊icon on each row to hear your response and the correct answer</strong></p>

        <table>
            <thead>
                <tr>
                    <th>Q#</th>
                    <th>Image</th>
                    <th>Your Answer</th>
                    <th>Correct Answers</th>
                    <th>Match %</th>
                    <th>Points</th>
                    <th>✔/✘</th>
                    <th>Listen 🔊</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($results as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <img src="<?= htmlspecialchars("../AppFiles/images/$level/$folder/" . $r['img']) ?>" alt="Q<?= $i + 1 ?>" width="75" height="75" style="object-fit: cover; border: 1px solid #ccc;" />
                    </td>
                    <td><?= htmlspecialchars($r['response']) ?></td>
                    <td><?= htmlspecialchars($r['correct_answers']) ?></td>
                    <td><?= $r['percent'] ?>%</td>
                    <td><?= $r['points'] ?></td>
                    <td><?= $r['correct'] ?></td>
                    <td>
                        <button class="speaker-button" onclick="speakResult(<?= $i ?>)">🔊</button>
                        <script>
                            window.quizResults = window.quizResults || [];
                            window.quizResults[<?= $i ?>] = {
                                userAnswer: <?= json_encode($r['response']) ?>,
                                correctAnswer: <?= json_encode($r['correct_answers']) ?>
                            };
                        </script>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>