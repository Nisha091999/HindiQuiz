<?php
session_start();

$api_url = "http://127.0.0.1:5000/hindi_similarity";
$total = count($_SESSION['translate_eng_questions'] ?? []);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid access");
}

// Helper: normalize sentence
function normalize($text) {
    $text = mb_strtolower($text);
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text); // Remove punctuation
    $text = preg_replace('/\s+/', ' ', $text); // Remove extra spaces
    return trim($text);
}

$results = [];

for ($i = 0; $i < $total; $i++) {
    $english_sentence = $_POST["question$i"] ?? '';
    $user_answer = trim($_POST["answer$i"] ?? '');
    $correct_answers_json = $_POST["correct$i"] ?? '[]';
    $correct_answers = json_decode($correct_answers_json, true) ?: [];

    if ($user_answer === '') {
        $results[] = [
            'english' => $english_sentence,
            'user' => '(No answer given)',
            'correct' => $correct_answers,
            'score' => 0,
            'pass' => false,
            'message' => 'No answer provided'
        ];
        continue;
    }

    // Send to Python Flask API
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
            'correct' => $correct_answers,
            'score' => 0,
            'pass' => false,
            'message' => "Error contacting similarity API: $curl_err"
        ];
        continue;
    }

    $data = json_decode($response, true);

    if (!$data || !isset($data['grammar'], $data['semantic_similarity'], $data['paraphrasing'])) {
        $results[] = [
            'english' => $english_sentence,
            'user' => $user_answer,
            'correct' => $correct_answers,
            'score' => 0,
            'pass' => false,
            'message' => 'Invalid response from similarity API'
        ];
        continue;
    }

    // Calculate AI-based score
    $score = (
        0.3 * floatval($data['grammar']) +
        0.5 * floatval($data['semantic_similarity']) +
        0.2 * floatval($data['paraphrasing'])
    ) * 100;

    $pass = $score >= 60;

    // Fallback: if user's translation matches any correct answer (token-based)
    $userNorm = normalize($user_answer);
    foreach ($correct_answers as $correct) {
        if (normalize($correct) === $userNorm) {
            $pass = true;
            $score = max($score, 95); // boost score
            break;
        }
    }

    $results[] = [
        'english' => $english_sentence,
        'user' => $user_answer,
        'correct' => $correct_answers,
        'score' => round($score, 2),
        'pass' => $pass,
        'message' => 'Evaluated'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Translation Results - English to Hindi</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f8fa; margin: 20px; color: #333; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px 15px; border: 1px solid #ddd; vertical-align: top; }
        th { background-color: #3498db; color: white; text-align: left; }
        tr.pass { background-color: #d4edda; }
        tr.fail { background-color: #f8d7da; }
        .score { font-weight: bold; font-size: 1.1em; }
        .correct-answers { color: #555; font-style: italic; }
    </style>
</head>
<body>
<div class="container">
    <h1>English to Hindi Translation Results</h1>
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>English Sentence</th>
            <th>Your Hindi Translation</th>
            <th>Correct Hindi Answers</th>
            <th>Score (%)</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($results as $i => $res): ?>
            <tr class="<?= $res['pass'] ? 'pass' : 'fail' ?>">
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($res['english']) ?></td>
                <td><?= htmlspecialchars($res['user']) ?></td>
                <td class="correct-answers">
                    <?php foreach ($res['correct'] as $ans) echo htmlspecialchars($ans) . "<br>"; ?>
                </td>
                <td class="score"><?= $res['score'] ?></td>
                <td><?= $res['pass'] ? '✔ Pass' : '✘ Fail' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
