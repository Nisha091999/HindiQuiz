<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>Hindi Input Comparison</title>
    <script src="https://www.google.com/jsapi"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            padding: 20px;
        }

        textarea {
            width: 100%;
            max-width: 500px;
            height: 120px;
            font-size: 18px;
            padding: 10px;
            margin-bottom: 20px;
            font-family: inherit;
        }

        input[type="submit"] {
            padding: 12px 24px;
            font-size: 18px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .result {
            margin-top: 20px;
            font-size: 22px;
            color: #333;
        }
    </style>

    <script type="text/javascript">
        google.load("elements", "1", {
            packages: "transliteration"
        });

        function onLoad() {
            var options = {
                sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
                destinationLanguage: [google.elements.transliteration.LanguageCode.HINDI],
                transliterationEnabled: true
            };

            var control = new google.elements.transliteration.TransliterationControl(options);
            control.makeTransliteratable(['hindiInput']);
        }

        google.setOnLoadCallback(onLoad);
    </script>
</head>
<body>

    <h2>प्रश्न: भारत की राजधानी क्या है?</h2>

    <form method="post">
        <textarea id="hindiInput" name="user_answer" placeholder="यहाँ उत्तर टाइप करें..."></textarea><br>
        <input type="submit" value="उत्तर भेजें">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Correct answer in Hindi
        $correctAnswer = "नई दिल्ली";  // You can change this to any correct answer

        // User's answer
        $userAnswer = isset($_POST['user_answer']) ? trim($_POST['user_answer']) : '';

        echo "<div class='result'>";
        if ($userAnswer === $correctAnswer) {
            echo "✅ सही उत्तर!";
        } else {
            echo "❌ गलत उत्तर। आपने लिखा: <strong>" . htmlspecialchars($userAnswer) . "</strong>";
        }
        echo "</div>";
    }
    ?>

</body>
</html>
