<?php
session_start();
session_unset();
session_destroy();

// Redirect using full path relative to your project folder
header("Location: /HindiQuiz/index.php");
exit();
?>