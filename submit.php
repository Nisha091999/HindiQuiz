<?php

$text = $_POST['hindiText'] ?? '';
$escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>Hindi Submission</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
        }
        #spokenText {
            font-size: 24px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <h2>You Submitted:</h2>
    <div id="spokenText"><?= $escaped ?></div>

    <button onclick="speak()">🔊 Read Aloud</button>

    <script>
        function speak() {
            const text = document.getElementById('spokenText').innerText;
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'hi-IN'; // Hindi
            window.speechSynthesis.speak(utterance);
        }

        // Auto-speak on page load
        window.onload = speak;
    </script>

</body>
</html>
submitphp.txt
Displaying submitphp.txt.