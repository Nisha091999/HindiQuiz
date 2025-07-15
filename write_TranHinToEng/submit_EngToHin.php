<?php
session_start();

$api_url = "http://127.0.0.1:5000/hindi_similarity";

$total = count($_SESSION['translate_eng_questions'] ?? []);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $total === 0) {
    header("Location: ../index.php");
    exit();
}

$results = [];
$totalScore = 0;

for ($i = 0; $i < $total; $i++) {
    $english_sentence = $_POST["question$i"] ?? '';
    $user_answer = trim($_POST["answer$i"] ?? '');

    if ($user_answer === '') {
        $results[] = [
            'english' => $english_sentence,
            'user' => '(No answer given)',
            'expected' => ['No expected answers'],
            'score' => 0,
            'pass' => false
        ];
        continue;
    }

    $post_data = [
        'english_sentence' => $english_sentence,
        'user_answer' => $user_answer
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $curl_err = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        $results[] = [
            'english' => $english_sentence,
            'user' => $user_answer,
            'expected' => ['Error contacting AI backend'],
            'score' => 0,
            'pass' => false
        ];
        continue;
    }

    $data = json_decode($response, true);

    if (!$data || !isset($data['final_score'], $data['expected_translations'])) {
        $results[] = [
            'english' => $english_sentence,
            'user' => $user_answer,
            'expected' => ['Invalid response from AI backend'],
            'score' => 0,
            'pass' => false
        ];
        continue;
    }

    $score = floatval($data['final_score']);
    $expected_answers = $data['expected_translations'];

    $pass = $score >= 60;
    $totalScore += $score;

    $results[] = [
        'english' => $english_sentence,
        'user' => $user_answer,
        'expected' => $expected_answers,
        'score' => round($score, 2),
        'pass' => $pass
    ];
}

$averageScore = $total ? round($totalScore / $total, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>English to Hindi Translation Results</title>
<style>
  body { font-family: Arial, sans-serif; background: #f5f7fa; padding: 25px; }
  .container { max-width: 900px; margin: auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
  h2 { text-align: center; color: #333; }
  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  th, td { border: 1px solid #ccc; padding: 12px; text-align: left; vertical-align: top; }
  th { background: #2c3e50; color: #fff; }
  tr.pass { background: #d4edda; }
  tr.fail { background: #f8d7da; }
  .score { font-weight: bold; font-size: 1.1em; }
  .expected-answers { font-style: italic; color: #555; }
  .speaker-button {
      background-color: #27ae60; color: white; border: none; padding: 6px 10px;
      font-size: 14px; border-radius: 6px; cursor: pointer;
  }
  .speaker-button:hover { background-color: #1e874b; }
</style>
<script>
  // Setup voices for Hindi and English
  let hindiVoice = null, engVoice = null;
  function loadVoices() {
    const voices = speechSynthesis.getVoices();
    hindiVoice = voices.find(v => v.lang.startsWith('hi')) || null;
    engVoice = voices.find(v => v.lang.startsWith('en')) || null;
  }
  speechSynthesis.onvoiceschanged = loadVoices;
  window.onload = loadVoices;

  // Speak Hindi sentence, user answer, and expected answers
  function speak(index) {
    const data = window.quizResults[index];
    if (!hindiVoice || !engVoice) {
      alert("Speech synthesis voices not loaded yet.");
      return;
    }

    const hindiText = new SpeechSynthesisUtterance(`English sentence: ${data.english}`);
    hindiText.voice = engVoice;

    const userText = new SpeechSynthesisUtterance(`Your translation: ${data.user}`);
    userText.voice = hindiVoice;

    const expectedText = new SpeechSynthesisUtterance(`Expected answers: ${data.expected.join(', ')}`);
    expectedText.voice = hindiVoice;

    speechSynthesis.cancel();
    speechSynthesis.speak(hindiText);
    hindiText.onend = () => {
      setTimeout(() => {
        speechSynthesis.speak(userText);
        userText.onend = () => setTimeout(() => speechSynthesis.speak(expectedText), 400);
      }, 400);
    };
  }
</script>
</head>
<body>
<div class="container">
  <h2>English to Hindi Translation Results</h2>
  <p class="score">Average Score: <?= $averageScore ?>%</p>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>English Sentence</th>
        <th>Your Hindi Translation</th>
        <th>Expected Answers (AI)</th>
        <th>Score (%)</th>
        <th>Status</th>
        <th>🔊</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($results as $i => $r): ?>
      <tr class="<?= $r['pass'] ? 'pass' : 'fail' ?>">
        <td><?= $i + 1 ?></td>
        <td><?= htmlspecialchars($r['english']) ?></td>
        <td><?= htmlspecialchars($r['user']) ?></td>
        <td class="expected-answers">
          <?php foreach ($r['expected'] as $ans): ?>
            <?= htmlspecialchars($ans) ?><br>
          <?php endforeach; ?>
        </td>
        <td><?= $r['score'] ?></td>
        <td><?= $r['pass'] ? '✔ Pass' : '✘ Fail' ?></td>
        <td>
          <button class="speaker-button" onclick="speak(<?= $i ?>)">🔊</button>
          <script>
            window.quizResults = window.quizResults || [];
            window.quizResults[<?= $i ?>] = {
              english: <?= json_encode($r['english']) ?>,
              user: <?= json_encode($r['user']) ?>,
              expected: <?= json_encode($r['expected']) ?>
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
