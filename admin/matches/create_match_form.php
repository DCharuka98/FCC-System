<?php
require_once "../admin_guard.php";
require_once "../../config/db.php";

$playing_day_id = (int)($_GET['day'] ?? 0);
if ($playing_day_id <= 0) {
    die("Invalid playing day");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Match</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body class="admin-layout">

<?php include "../partials/admin_nav.php"; ?>

<main class="admin-content">
<div class="page-container">

<form method="POST" action="save_match.php" class="form-card">
    <h2>Create Match</h2>

    <input type="hidden" name="day" value="<?= $playing_day_id ?>">

    <label>Overs</label>
    <input type="number" name="overs" min="1" required>

    <label>Balls per Over</label>
    <input type="number" name="balls_per_over" min="1" required>

    <label>Bat First</label>
    <select name="bat_first" required>
        <option value="A">Team A</option>
        <option value="B">Team B</option>
    </select>

    <button type="submit" class="btn-primary">Start Match</button>
</form>

</div>
</main>

</body>
</html>
