<?php
require_once "../admin_guard.php";
require_once "../../config/db.php";

/* Get active seasons */
$seasons = $conn->query("
    SELECT season_id, season_name
    FROM seasons
    WHERE status = 'Active'
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $date  = $_POST['playing_date'];
    $venue = $_POST['venue'];

    $season = $conn->query("
        SELECT season_id
        FROM seasons
        WHERE status = 'Active'
        LIMIT 1
    ")->fetch_assoc();

    if (!$season) {
        die("No active season found");
    }

    $season_id = $season['season_id'];

    $stmt = $conn->prepare("
        INSERT INTO playing_days (play_date, venue, season_id, status)
        VALUES (?, ?, ?, 'Active')
    ");
    $stmt->bind_param("ssi", $date, $venue, $season_id);
    $stmt->execute();

    $playing_day_id = $stmt->insert_id;

    header("Location: player_select.php?day=$playing_day_id");
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
<div class="page-container">

    <div class="form-card">
    <h2>Start Playing Day</h2>

    <form method="POST">
        <label>Playing Date</label>
        <input type="date" name="playing_date" required>

        <label>Venue</label>
        <input type="text" name="venue" required>

        <button type="submit">Start Playing Day</button>
    </form>
</div>


</div>

</main>

<?php include "../partials/admin_footer.php"; ?>
</body>
</html>
