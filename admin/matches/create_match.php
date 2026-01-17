
<?php
require_once "../admin_guard.php";
require_once "../../config/db.php";

/* get active season */
$seasonRes = $conn->query("SELECT season_id FROM seasons WHERE status='Active' LIMIT 1");
$seasonRow = $seasonRes->fetch_assoc();
$active_season_id = $seasonRow['season_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $date   = $_POST['playing_date'];
    $venue  = $_POST['venue'];

    if (!$active_season_id) {
        die("No active season found");
    }

    $stmt = $conn->prepare("
        INSERT INTO playing_days (playing_date, venue, status, season_id)
        VALUES (?, ?, 'Active', ?)
    ");
    $stmt->bind_param("ssi", $date, $venue, $active_season_id);
    $stmt->execute();

    $playing_day_id = $stmt->insert_id;

    header("Location: create_match.php?day=$playing_day_id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Start Playing Day | FCC</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="icon" href="../../assets/images/Logo white.png">    
</head>
<body class="admin-layout">

<?php include "../partials/admin_nav.php"; ?>

<main class="admin-content">
<div class="form-card">
    <h2>Create Match</h2>

    <form method="POST">
        <label>Team A Captain</label>
        <select name="team_a_captain" required></select>

        <label>Team B Captain</label>
        <select name="team_b_captain" required></select>

        <label>Overs per Innings</label>
        <input type="number" name="overs" min="1" required>

        <label>Balls per Over</label>
        <select name="balls_per_over">
            <option value="6">6 Balls</option>
            <option value="4">4 Balls</option>
        </select>

        <label>Batting First</label>
        <select name="bat_first">
            <option value="A">Team A</option>
            <option value="B">Team B</option>
        </select>

        <button type="submit">Start Match</button>
    </form>
</div>
</main>

<?php include "../partials/admin_footer.php"; ?>
</body>
</html>

