<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hindi Knowledge Test Menu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h2 {
            margin-bottom: 20px;
        }

        form {
            width: 60vw;
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        fieldset {
            border: 1px solid #aaa;
            padding: 20px;
            margin-bottom: 20px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            grid-auto-rows: 100px;
            gap: 20px;
        }

        .menu-grid input[type="submit"] {
            width: 100%;
            height: 100%;
            font-size: 18px;
            font-weight: bold;
            background-color: #f7f7f7;
            border: 2px solid #ccc;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .menu-grid input[type="submit"]:hover {
            background-color: #cce5ff;
            border-color: #3399ff;
        }

        @media (max-width: 600px) {
            form {
                width: 90vw;
                min-height: 70vh;
            }
        }
    </style>

    <script>
        let clickedButton = null;

        function buttonClicked(btn) {
            clickedButton = btn;
        }

        function onFormSubmit(event) {
            const form = event.target;
            const mode = form.elements['mode'].value;

            if (!clickedButton) {
                alert("Please select an activity.");
                event.preventDefault();
                return false;
            }

            const activity = clickedButton.value;

            const oralMap = {
                "Half Letters": "oral_half_menu.php",
                "Complete the Sentence": "oral_complete_sentence.php",
                "Translate to English": "oral_translate_to_english.php",
                "Translate to Hindi": "oral_translate_to_hindi.php"
            };

            const writtenMap = {
                "Half Letters": "HalfLetterWMenu.php",
                "Complete the Sentence": "CompSentW.php",
                "Translate to English": "TranslateToEnglish.php",
                "Translate to Hindi": "TranslateToHindi.php"
            };

            if (mode === 'oral') {
                if (oralMap[activity]) {
                    form.action = oralMap[activity];
                } else {
                    alert("Activity not available in Oral mode.");
                    event.preventDefault();
                    return false;
                }
            } else {
                if (writtenMap[activity]) {
                    form.action = writtenMap[activity];
                } else {
                    alert("Activity not available in Written mode.");
                    event.preventDefault();
                    return false;
                }
            }

            return true;
        }
    </script>
</head>
<body>
    <h2>Hindi Knowledge Test</h2>

    <form method="post" onsubmit="return onFormSubmit(event);">
        <fieldset>
            <legend>Select Mode:</legend>
            <label>
                <input type="radio" name="mode" value="oral" required> Oral
            </label>
            <label style="margin-left: 20px;">
                <input type="radio" name="mode" value="written"> Written
            </label>
        </fieldset>

        <fieldset>
            <legend>Choose a Hindi Learning Activity:</legend>
            <div class="menu-grid">
                <input type="submit" value="Half Letters" onclick="buttonClicked(this);">
                <input type="submit" value="Complete the Sentence" onclick="buttonClicked(this);">
                <input type="submit" value="Translate to English" onclick="buttonClicked(this);">
                <input type="submit" value="Translate to Hindi" onclick="buttonClicked(this);">
            </div>
        </fieldset>
    </form>
</body>
</html>
