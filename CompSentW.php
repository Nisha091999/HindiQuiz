<?php
session_start();
$answerFile = "AppFiles/L2/Match/answers.txt";
$sentences = [];
$words = [];

if (file_exists($answerFile)) {
    foreach (file($answerFile) as $line) {
        if (trim($line)) {
            list($sentence, $word) = explode(",", trim($line), 2);
            $sentences[] = ['sentence' => $sentence, 'word' => $word];
            $words[] = $word;
        }
    }
}
shuffle($words);
$_SESSION['match_sentences'] = $sentences;
?>
<!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="UTF-8">
  <title>रिक्त स्थान भरें - Drag and Drop</title>
  <style>
    body { font-family: sans-serif; background: #f9f9f9; padding: 20px; }
    .container { display: flex; gap: 40px; }
    .sentences, .words {
        width: 45%; background: #fff; padding: 20px;
        border-radius: 8px; min-height: 200px;
    }
    .sentence-box {
        margin-bottom: 20px;
        padding: 10px;
        border-bottom: 1px solid #ccc;
        font-size: 18px;
    }
    .drop-zone {
        display: inline-block;
        min-width: 80px;
        padding: 4px 8px;
        margin: 0 4px;
        border: 2px dashed #888;
        background: #f0f0f0;
        border-radius: 4px;
    }
    .word {
        padding: 8px 12px;
        margin: 5px;
        background: #3498db;
        color: white;
        border-radius: 5px;
        cursor: move;
        display: inline-block;
    }
    .submit-btn {
        margin-top: 20px;
        padding: 10px 20px;
        background: green;
        color: white;
        font-size: 16px;
        border: none;
        border-radius: 5px;
    }
  </style>
</head>
<body>
<h2>रिक्त स्थान में सही शब्द भरें</h2>
<form method="POST" action="match_results.php">
  <div class="container">
    <div class="sentences" id="sentences">
      <?php foreach ($sentences as $i => $item): ?>
        <div class="sentence-box">
          <?php
          $parts = explode("_____", $item['sentence']);
          echo htmlspecialchars($parts[0]);
          ?>
          <span class="drop-zone"
                id="drop<?= $i ?>"
                ondragover="event.preventDefault();"
                ondrop="handleDrop(event, <?= $i ?>)">
            _____
          </span>
          <?php
          echo isset($parts[1]) ? htmlspecialchars($parts[1]) : '';
          ?>
          <input type="hidden" name="answer<?= $i ?>" id="answer<?= $i ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <div class="words" id="wordBank">
      <?php foreach ($words as $word): ?>
        <?php $id = 'w' . md5($word); ?>
        <div class="word"
             id="<?= $id ?>"
             draggable="true"
             data-word="<?= htmlspecialchars($word) ?>"
             ondragstart="event.dataTransfer.setData('text/plain', this.id)">
          <?= htmlspecialchars($word) ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <button class="submit-btn" type="submit">Submit</button>
</form>

<script>
function handleDrop(ev, index) {
  ev.preventDefault();
  const draggedId = ev.dataTransfer.getData("text");
  const dragged = document.getElementById(draggedId);
  const dropZone = document.getElementById('drop' + index);

  // If there's already a word, return it to the word bank
  if (dropZone.children.length > 0) {
    const existing = dropZone.querySelector('.word');
    if (existing) document.getElementById('wordBank').appendChild(existing);
    dropZone.innerHTML = '_____';
  }

  dropZone.innerHTML = '';
  dropZone.appendChild(dragged);
  document.getElementById("answer" + index).value = dragged.dataset.word;
}

// Drag back to word bank
const wordBank = document.getElementById("wordBank");
wordBank.addEventListener("drop", function (ev) {
  ev.preventDefault();
  const draggedId = ev.dataTransfer.getData("text");
  const dragged = document.getElementById(draggedId);

  for (let i = 0; i < <?= count($sentences) ?>; i++) {
    const dz = document.getElementById('drop' + i);
    if (dz.contains(dragged)) {
      dz.innerHTML = '_____';
      document.getElementById("answer" + i).value = '';
    }
  }

  wordBank.appendChild(dragged);
});

wordBank.addEventListener("dragover", function (ev) {
  ev.preventDefault();
});
</script>
</body>
</html>
