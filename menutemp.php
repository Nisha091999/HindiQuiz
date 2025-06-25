<!DOCTYPE html>
<html>
<head>
    <title>PHP Button Grid</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
            font-family: Arial, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .wrapper {
            width: 60%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            width: 100%;
        }

        button {
            width: 100%;
            padding: 30px 0;
            font-size: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        .message {
            margin-top: 30px;
            font-size: 22px;
            color: #333;
        }
    </style>
</head>
<body>

    <div class="wrapper">
        <form method="post" class="container">
            <?php
            for ($i = 1; $i <= 6; $i++) {
                echo "<button type='submit' name='button' value='Button$i'>Button$i</button>";
            }
            ?>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['button'])) {
            $clicked = htmlspecialchars($_POST['button']);
            echo "<div class='message'>You clicked $clicked</div>";
        }
        ?>
    </div>

</body>
</html>
