<?php
session_start();
$_SESSION['quiz_done'] = true;

// Redirect if accessed incorrectly
if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'] ?? 'Guest';
$time = date('d/m/Y H:i:s');

function normalizeText($text) {
    $text = mb_strtolower($text);
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

function calculateMatchPercentage($userAnswer, $correctAnswer) {
    $userTokens = explode(' ', normalizeText($userAnswer));
    $correctTokens = explode(' ', normalizeText($correctAnswer));

    $matchCount = 0;
    foreach ($userTokens as $token) {
        if (in_array($token, $correctTokens)) {
            $matchCount++;
        }
    }

    $maxTokens = max(count($userTokens), count($correctTokens));
    return $maxTokens ? round(($matchCount / $maxTokens) * 100, 2) : 0;
}

$lines = file("../AppFiles/Translations/EngToHinAnswers.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$answerMap = [];
foreach ($lines as $line) {
    $parts = explode(',', $line);
    $english = trim($parts[0]);
    $answers = array_map(function($a) {
        return trim(trim($a), '"');
    }, array_slice($parts, 1));
    $answerMap[$english] = $answers;
}

$results = [];
$totalPoints = 0;
$totalQuestions = 0;

foreach ($_POST as $key => $value) {
    if (strpos($key, 'answer') === 0) {
        $index = substr($key, 6);
        $response = trim($value);
        $question = $_POST["question$index"] ?? '';
        $correctAnswers = $answerMap[$question] ?? [];

        $bestMatch = 0;
        foreach ($correctAnswers as $correctAns) {
            $match = calculateMatchPercentage($response, $correctAns);
            $bestMatch = max($bestMatch, $match);
        }

        $points = 0;
        if ($bestMatch >= 99.9) $points = 1.0;
        elseif ($bestMatch >= 85) $points = 0.75;
        elseif ($bestMatch >= 60) $points = 0.5;

        $totalPoints += $points;
        $totalQuestions++;

        $results[] = [
            'question' => $question,
            'response' => $response,
            'correct_answers' => implode(', ', $correctAnswers),
            'percent' => $bestMatch,
            'points' => $points,
            'correct' => $points > 0 ? '✔' : '✘'
        ];
    }
}

$finalPercentage = $totalQuestions ? round(($totalPoints / $totalQuestions) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Translation Results</title>
  <link rel="stylesheet" href="../assets/style.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f6f8;
      padding: 30px;
    }
    .container {
      max-width: 950px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 14px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 25px;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 10px;
      font-size: 15px;
      text-align: center;
    }
    th {
      background-color: #2c3e50;
      color: white;
    }
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    .speaker-button {
      background-color: #27ae60;
      color: white;
      border: none;
      padding: 6px 10px;
      font-size: 14px;
      border-radius: 6px;
      cursor: pointer;
    }
    .speaker-button:hover {
      background-color: #1e874b;
    }
    .score {
      font-size: 18px;
      margin-bottom: 10px;
    }
    .logout-btn {
      background-color: #e74c3c;
      color: white;
      border: none;
      padding: 8px 14px;
      font-size: 14px;
      border-radius: 6px;
      cursor: pointer;
    }
    .logout-btn:hover {
      background-color: #c0392b;
    }
    .top-right {
      text-align: right;
      margin-bottom: 10px;
    }
  </style>
  <script>
    function loadVoices() {
      const allVoices = speechSynthesis.getVoices();
      window.hindiVoice = allVoices.find(v => v.lang === 'hi-IN');
      window.engVoice = allVoices.find(v => v.lang === 'en-US');
    }

    function speak(index) {
      const data = window.quizResults[index];

      const hindiText = new SpeechSynthesisUtterance(`अंग्रेज़ी वाक्य: ${data.hindi}`);
      hindiText.voice = window.engVoice;

      const userText = data.user.trim() === "" ? "Your translation is empty" : `Your translation: ${data.user}`;
      const correctText = `Correct answer: ${data.correct}`;

      const user = new SpeechSynthesisUtterance(userText);
      const correct = new SpeechSynthesisUtterance(correctText);

      user.voice = window.hindiVoice;
      correct.voice = window.hindiVoice;

      speechSynthesis.cancel();
      speechSynthesis.speak(hindiText);
      hindiText.onend = () => {
        setTimeout(() => {
          speechSynthesis.speak(user);
          user.onend = () => setTimeout(() => speechSynthesis.speak(correct), 400);
        }, 400);
      };
    }

    window.speechSynthesis.onvoiceschanged = loadVoices;
    window.addEventListener('load', loadVoices);
  </script>
</head>
<body>
<div class="container">
  <div class="top-right">
    <form action="../logout.php" method="post">
      <button class="logout-btn" type="submit">Logout</button>
    </form>
  </div>

  <h2>English to Hindi Translation Results - <?= htmlspecialchars($user) ?></h2>
  <p class="score"><strong>Score:</strong> <?= $totalPoints ?>/<?= $totalQuestions ?> (<?= $finalPercentage ?>%)</p>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>English Sentence</th>
        <th>Your Hindi Translation</th>
        <th>Expected Answers</th>
        <th>Match %</th>
        <th>Points</th>
        <th>✔/✘</th>
        <th>🔊</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($results as $i => $r): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= htmlspecialchars($r['question']) ?></td>
        <td><?= htmlspecialchars($r['response']) ?></td>
        <td><?= htmlspecialchars($r['correct_answers']) ?></td>
        <td><?= $r['percent'] ?>%</td>
        <td><?= $r['points'] ?></td>
        <td><?= $r['correct'] ?></td>
        <td>
          <button class="speaker-button" onclick="speak(<?= $i ?>)">🔊</button>
          <script>
            window.quizResults = window.quizResults || [];
            window.quizResults[<?= $i ?>] = {
              hindi: <?= json_encode($r['question']) ?>,
              user: <?= json_encode($r['response']) ?>,
              correct: <?= json_encode($r['correct_answers']) ?>
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
