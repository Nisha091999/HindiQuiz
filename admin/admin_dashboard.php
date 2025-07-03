<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$scoreData = file_exists("AppData/Scores.txt") ? file("AppData/Scores.txt") : [];
$loginData = file_exists("AppData/userlogfile.txt") ? file("AppData/userlogfile.txt") : [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 20px;
        }
        .logout-btn {
            float: right;
            padding: 8px 15px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        h2 {
            margin-top: 50px;
            color: #2c3e50;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #3498db;
            color: white;
            cursor: pointer;
        }
    </style>
    <script>
        function filterTable(inputId, tableId) {
            const filter = document.getElementById(inputId).value.toLowerCase();
            const rows = document.querySelectorAll(`#${tableId} tbody tr`);
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
            });
        }

        function sortTable(tableId, colIndex) {
            const table = document.getElementById(tableId);
            const rows = Array.from(table.rows).slice(1);
            const asc = table.getAttribute('data-sort') !== 'asc';
            rows.sort((a, b) => a.cells[colIndex].innerText.localeCompare(b.cells[colIndex].innerText));
            if (!asc) rows.reverse();
            rows.forEach(r => table.appendChild(r));
            table.setAttribute('data-sort', asc ? 'asc' : 'desc');
        }
    </script>
</head>
<body>
    <a href="admin_users.php" style="
        display: inline-block;
        padding: 12px 24px;
        background-color: #9b59b6;
        color: white;
        font-weight: bold;
        text-decoration: none;
        border-radius: 10px;
        margin-top: 20px;
        transition: background 0.3s ease;">
        👤 Manage Quiz Users
    </a>


    <form action="admin_logout.php" method="POST">
        <button class="logout-btn">Logout</button>
    </form>

    <h2>📈 Scoreboard</h2>
    <input type="text" id="scoreSearch" placeholder="Search scores..." onkeyup="filterTable('scoreSearch','scoreTable')">
    <table id="scoreTable" data-sort="desc">
        <thead>
            <tr>
                <th onclick="sortTable('scoreTable', 0)">Level</th>
                <th onclick="sortTable('scoreTable', 1)">User</th>
                <th onclick="sortTable('scoreTable', 2)">Time</th>
                <th onclick="sortTable('scoreTable', 4)">Score</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($scoreData as $line): ?>
                <?php $parts = explode(",", trim($line)); ?>
                <tr>
                    <td><?= htmlspecialchars($parts[0] ?? '') ?></td>
                    <td><?= htmlspecialchars($parts[1] ?? '') ?></td>
                    <td><?= htmlspecialchars($parts[2] ?? '') ?></td>
                    <td><?= htmlspecialchars($parts[4] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>🧾 Login Log</h2>
    <input type="text" id="logSearch" placeholder="Search login logs..." onkeyup="filterTable('logSearch','logTable')">
    <table id="logTable">
        <thead>
            <tr>
                <th>Level</th>
                <th>User</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loginData as $line): ?>
                <?php
                    $line = trim($line);
                    if (preg_match('/^(\S+)\s+(\S+)\s+(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2})/', $line, $matches)) {
                        $level = $matches[1];
                        $user = $matches[2];
                        $datetime = $matches[3];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($level) ?></td>
                        <td><?= htmlspecialchars($user) ?></td>
                        <td><?= htmlspecialchars($datetime) ?></td>
                    </tr>
                <?php } ?>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
