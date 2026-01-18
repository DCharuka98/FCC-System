<?php
require_once "../admin_guard.php";
require_once "../../config/db.php";

$match_id = (int)($_GET['match'] ?? 0);
if ($match_id <= 0) die("Invalid match");

$innRes = $conn->query("
    SELECT innings_id, batting_team, bowling_team
    FROM innings
    WHERE match_id = $match_id
    ORDER BY innings_id DESC
    LIMIT 1
");
$innings = $innRes->fetch_assoc();

$batPlayers = $conn->query("
    SELECT p.player_id, p.full_name
    FROM playing_day_team_players t
    JOIN players p ON p.player_id = t.player_id
    WHERE t.team_id = {$innings['batting_team_id']}
");

$bowlPlayers = $conn->query("
    SELECT p.player_id, p.full_name
    FROM playing_day_team_players t
    JOIN players p ON p.player_id = t.player_id
    WHERE t.team_id = {$innings['bowling_team_id']}
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scoring Setup | FCC</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body class="admin-layout">

<?php include "../partials/admin_nav.php"; ?>

<main class="admin-content">
<div class="page-container">

<form method="POST" action="match_scoring.php" class="form-card">

    <h2>Scoring Setup</h2>

    <input type="hidden" name="match_id" value="<?= $match_id ?>">
    <input type="hidden" name="innings_id" value="<?= $innings['innings_id'] ?>">

    <label>Striker</label>
    <select name="striker" required>
        <?php while ($p = $batPlayers->fetch_assoc()): ?>
            <option value="<?= $p['player_id'] ?>">
                <?= htmlspecialchars($p['full_name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Non-Striker</label>
    <select name="non_striker" required>
        <?php
        $batPlayers->data_seek(0);
        while ($p = $batPlayers->fetch_assoc()):
        ?>
            <option value="<?= $p['player_id'] ?>">
                <?= htmlspecialchars($p['full_name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Opening Bowler</label>
    <select name="bowler" required>
        <?php while ($p = $bowlPlayers->fetch_assoc()): ?>
            <option value="<?= $p['player_id'] ?>">
                <?= htmlspecialchars($p['full_name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit" class="btn-primary btn-full">
        Start Scoring
    </button>

</form>

</div>
</main>

<?php include "../partials/admin_footer.php"; ?>
</body>
</html>
