<?php
session_start();
$sentences = $_SESSION['match_sentences'] ?? [];
$results = [];

foreach ($sentences as $i => $item) {
    $expected = $item['word'];
    $user = $_POST["answer$i"] ?? '';
    $results[] = [
        'sentence' => $item['sentence'],
        'expected' => $expected,
        'user' => $user,
        'correct' => trim($expected) === trim($user)
    ];
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>Quiz Results</title>
    <style>
        body { font-family: sans-serif; background: #eef; padding: 20px; }
        .result { margin-bottom: 15px; padding: 10px; background: white; border-radius: 8px; }
        .correct { color: green; font-weight: bold; }
        .wrong { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h2>परिणाम</h2>
    <?php foreach ($results as $res): ?>
        <div class="result">
            <?= htmlspecialchars($res['sentence']) ?><br>
            आपने चुना: <strong><?= htmlspecialchars($res['user']) ?></strong><br>
            <?= $res['correct'] ? '<span class="correct">सही</span>' : '<span class="wrong">गलत (सही उत्तर: ' . htmlspecialchars($res['expected']) . ')</span>' ?>
        </div>
    <?php endforeach; ?>
</body>
</html>
