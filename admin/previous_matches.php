<?php
require_once "../role_guard.php";
allowRoles(['admin','scorer','player']);

require_once "../config/db.php";

$playing_day_id = $_GET['playing_day_id'];
$month = $_GET['month'] ?? '';

$matchNo = 1;
$stmt = $conn->prepare("
SELECT 
m.match_id,

CONCAT('Team ', SUBSTRING_INDEX(p1.full_name,' ',1)) AS batting_team,
CONCAT('Team ', SUBSTRING_INDEX(p2.full_name,' ',1)) AS bowling_team

FROM matches m

JOIN playing_day_teams t1 ON m.batting_team_id = t1.team_id
JOIN playing_day_teams t2 ON m.bowling_team_id = t2.team_id

JOIN players p1 ON t1.captain_id = p1.player_id
JOIN players p2 ON t2.captain_id = p2.player_id

WHERE m.playing_day_id = ?
");

$stmt->bind_param("i",$playing_day_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Matches | FCC</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="icon" href="../assets/images/Logo white.png">
</head>

<body class="admin-layout">
<style>
    .mini-nav{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:20px;
    font-size:13px;
    }

    .mini-nav a{
    color:#38bdf8;
    text-decoration:none;
    font-weight:600;
    }

    .mini-nav a:hover{
    text-decoration:underline;
    }

    .mini-nav span{
    color:#94a3b8;
    }

    .mini-nav .active{
    color:white;
    font-weight:700;
    }

    .match-number{
    font-size:12px;
    background:#2563eb;
    padding:4px 10px;
    border-radius:20px;
    display:inline-block;
    margin-bottom:8px;
    font-weight:700;
    }
</style>
<?php include "../partials/navbar.php"; ?>
<main class="admin-content">
    <div class="page-container">
        <div class="mini-nav">
            <a href="previous_matches_month.php">Months</a>
            <span>›</span>
            <a href="previous_playing_days.php?month=<?= $month ?>">Playing Days</a>
            <span>›</span>
        <span class="active">Matches</span>
     </div>

    <h2>SELECT MATCH</h2>
<div class="cards">
    <?php $matchNo = 1; ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <a class="card"
            href="scorecard.php?match_id=<?= $row['match_id'] ?>&month=<?= $month ?>&playing_day_id=<?= $playing_day_id ?>">
                <div class="match-number">
                MATCH <?= str_pad($matchNo,2,'0',STR_PAD_LEFT) ?>
                </div>
                    <h3>
                    <?= strtoupper($row['batting_team']) ?>
                    VS
                    <?= strtoupper($row['bowling_team']) ?>
                    </h3>
                    <p>View scorecard</p>
            </a>
            <?php $matchNo++; ?>
    <?php endwhile; ?>
</div>
</div>
</main>
    <?php include "partials/admin_footer.php"; ?>
</body>
</html>