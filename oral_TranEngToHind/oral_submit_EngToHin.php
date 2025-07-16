<?php
session_start();
$_SESSION['quiz_done'] = true;

if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'] ?? 'Guest';
$questions = $_SESSION['oral_translate_questions'] ?? [];
$answers = $_SESSION['oral_translate_answers'] ?? [];

function normalizeText($text) {
    $text = mb_strtolower($text);
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

function calculateFinalMatch($userAnswer, $correctAnswer) {
    $userTokens = explode(' ', normalizeText($userAnswer));
    $correctTokens = explode(' ', normalizeText($correctAnswer));

    $tokenMatchCount = 0;
    foreach ($userTokens as $token) {
        if (in_array($token, $correctTokens)) {
            $tokenMatchCount++;
        }
    }
    $tokenMatch = count($correctTokens) > 0 ? $tokenMatchCount / count($correctTokens) : 0;

    $orderedMatchCount = 0;
    $minLen = min(count($userTokens), count($correctTokens));
    for ($i = 0; $i < $minLen; $i++) {
        if ($userTokens[$i] === $correctTokens[$i]) {
            $orderedMatchCount++;
        }
    }
    $orderMatch = $minLen > 0 ? $orderedMatchCount / $minLen : 0;

    return round((0.7 * $tokenMatch + 0.3 * $orderMatch) * 100, 2);
}

$results = [];
$totalPoints = 0;
$totalQuestions = count($questions);

for ($i = 0; $i < $totalQuestions; $i++) {
    $question = $questions[$i];
    $correctSet = $answers[$i];
    $userAnswer = $_POST["answer$i"] ?? '';

    $bestMatch = 0;
    foreach ($correctSet as $correct) {
        $score = calculateFinalMatch($userAnswer, $correct);
        $bestMatch = max($bestMatch, $score);
    }

    $points = 0;
    if ($bestMatch >= 99.9) $points = 1.0;
    elseif ($bestMatch >= 85) $points = 0.75;
    elseif ($bestMatch >= 60) $points = 0.5;

    $totalPoints += $points;

    $results[] = [
        'question' => $question,
        'response' => $userAnswer,
        'correct_answers' => implode(', ', $correctSet),
        'percent' => $bestMatch,
        'points' => $points,
        'correct' => $points > 0 ? '✔' : '✘'
    ];
}

$finalPercentage = $totalQuestions ? round(($totalPoints / $totalQuestions) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Oral Translation Result - English to Hindi</title>
  <style>
    body {
      font-family: Arial, sans-serif;
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
    .top-right {
      text-align: right;
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
    h2 {
      text-align: center;
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
    .score {
      font-size: 18px;
      margin-bottom: 10px;
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
  </style>
  <script>
    function loadVoices() {
      const allVoices = speechSynthesis.getVoices();
      window.hindiVoice = allVoices.find(v => v.lang === 'hi-IN');
      window.engVoice = allVoices.find(v => v.lang === 'en-US');
    }

    function speak(index) {
      const data = window.quizResults[index];
      const english = new SpeechSynthesisUtterance("English sentence: " + data.english);
      const user = new SpeechSynthesisUtterance("Your answer: " + data.user);
      const correct = new SpeechSynthesisUtterance("Correct answer: " + data.correct);

      english.voice = window.engVoice;
      user.voice = window.hindiVoice;
      correct.voice = window.hindiVoice;

      speechSynthesis.cancel();
      speechSynthesis.speak(english);
      english.onend = () => {
        setTimeout(() => speechSynthesis.speak(user), 400);
        user.onend = () => setTimeout(() => speechSynthesis.speak(correct), 400);
      };
    }

    window.speechSynthesis.onvoiceschanged = loadVoices;
    window.addEventListener('load', loadVoices);
  </script>
</head>
<body>
<div class="container">
  <div class="top-right">
    <form action="../index.php" method="post">
      <button class="logout-btn" type="submit">Logout</button>
    </form>
  </div>

  <h2>Oral Translate to Hindi - <?= htmlspecialchars($user) ?></h2>
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
              english: <?= json_encode($r['question']) ?>,
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
