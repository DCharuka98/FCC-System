<?php
require_once "../role_guard.php";
allowRoles(['admin','scorer','player']);

require_once "../config/db.php";

$match_id = (int)($_GET['match_id'] ?? 0);
if ($match_id <= 0) die("Invalid match");

$matchRow = $conn->query("
SELECT playing_day_id 
FROM matches 
WHERE match_id = $match_id
")->fetch_assoc();

$playing_day_id = (int)($matchRow['playing_day_id'] ?? 0);

$innings = [];

$res = $conn->query("
SELECT 
i.*,
CONCAT('Team ', SUBSTRING_INDEX(p.full_name,' ',1)) AS team_name
FROM innings i
LEFT JOIN playing_day_teams t ON t.team_id = i.batting_team_id
LEFT JOIN players p ON p.player_id = t.captain_id
WHERE i.match_id = $match_id
ORDER BY innings_number
");

while ($r = $res->fetch_assoc()) {
    $innings[] = $r;
}

$team1 = $innings[0]['team_name'] ?? 'Team 1';
$team2 = $innings[1]['team_name'] ?? 'Team 2';

$winnerText = "Match Result Pending";

if (count($innings) === 2) {

    $first  = $innings[0];
    $second = $innings[1];

    if ($second['total_runs'] > $first['total_runs']) {

        $teamCount = $conn->query("
        SELECT COUNT(*) c 
        FROM playing_day_team_players
        WHERE team_id = {$second['batting_team_id']}
        ")->fetch_assoc()['c'];

        $maxWickets = $teamCount - 1;

        $marginWk = $maxWickets - (int)$second['wickets'];

        $winnerText = $second['team_name'] . " won by " . max($marginWk,1) . " wicket" . ($marginWk>1?'s':'');

    } elseif ($second['total_runs'] < $first['total_runs']) {

        $marginRuns = $first['total_runs'] - $second['total_runs'];
        $winnerText = $first['team_name'] . " won by $marginRuns runs";

    } else {
        $winnerText = "Match Tied";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Match Scorecard | FCC</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="icon" href="../assets/images/Logo white.png">

<style>
    .scorecards-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:25px;
    margin-top:25px;
    }

    .score-card{
    background:#111827;
    padding:18px;
    border-radius:14px;
    box-shadow:0 15px 30px rgba(0,0,0,.45);
    }

    .score-card h3{
    color:#38bdf8;
    margin-bottom:10px;
    }

    .score{
    font-size:20px;
    font-weight:800;
    margin-bottom:10px;
    }

    .score-table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:10px;
    }

    .score-table th{
    font-size:11px;
    color:#94a3b8;
    border-bottom:1px solid rgba(255,255,255,.2);
    padding:6px;
    text-align:left;
    }

    .score-table td{
    font-size:12px;
    padding:6px;
    border-bottom:1px solid rgba(255,255,255,.08);
    }

    .timeline{
    margin-top:10px;
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    }

    .ball{
    padding:5px 8px;
    border-radius:6px;
    font-size:11px;
    font-weight:700;
    color:white;
    }

    .ball.run{background:#2563eb;}
    .ball.wicket{background:#ef4444;}
    .ball.dot{background:#475569;}

    .result-banner{
    margin-top:25px;
    text-align:center;
    font-size:18px;
    font-weight:800;
    padding:10px 25px;
    border-radius:25px;
    background:linear-gradient(90deg,#2563eb,#7c3aed);
    display:inline-block;
    }

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
</style>

</head>

<body class="admin-layout">

<?php include "../partials/navbar.php"; ?>

<main class="admin-content">
<div class="page-container">
    <div class="mini-nav">
        <a href="previous_matches_month.php">Months</a>
        <span>›</span>
        <a href="previous_playing_days.php?month=<?= $month ?? '' ?>">Playing Days</a>
        <span>›</span>
        <a href="previous_matches.php?playing_day_id=<?= $playing_day_id ?? '' ?>">Matches</a>
        <span>›</span>
        <span class="active">Scorecard</span>
    </div>
<h2 style="text-align:center;">
<?= strtoupper($team1) ?> VS <?= strtoupper($team2) ?>
</h2>

<div class="scorecards-grid">

<?php foreach ($innings as $inn): ?>

<div class="score-card">
    <h3><?= strtoupper($inn['team_name']) ?></h3>
    <div class="score">
    <?= $inn['total_runs'] ?>/<?= $inn['wickets'] ?> (<?= $inn['overs_completed'] ?>)
    </div>
    <h4>Batting</h4>
    <table class="score-table">
    <tr>
    <th>Batsman</th>
    <th>R</th>
    <th>B</th>
</tr>

<?php
    $bat = $conn->query("
    SELECT p.full_name,
    SUM(b.runs) runs,
    COUNT(b.ball_id) balls
    FROM balls b
    JOIN players p ON p.player_id=b.batsman_id
    WHERE b.innings_id={$inn['innings_id']}
    GROUP BY b.batsman_id
    ORDER BY runs DESC
    ");
?>

<?php while($r=$bat->fetch_assoc()): ?>
<tr>
<td><?= strtoupper($r['full_name']) ?></td>
<td><?= $r['runs'] ?></td>
<td><?= $r['balls'] ?></td>
</tr>
<?php endwhile; ?>

</table>
<h4>Bowling</h4>
    <table class="score-table">
        <tr>
        <th>Bowler</th>
        <th>W</th>
        <th>R</th>
        <th>O</th>
</tr>

<?php
$bowl = $conn->query("
SELECT p.full_name,
SUM(CASE WHEN b.is_wicket=1 AND b.dismissal_type!='RUN_OUT' THEN 1 ELSE 0 END) wickets,
SUM(b.runs + IFNULL(b.extra_runs,0)) runs,
COUNT(b.ball_id) balls
FROM balls b
JOIN players p ON p.player_id=b.bowler_id
WHERE b.innings_id={$inn['innings_id']}
GROUP BY b.bowler_id
ORDER BY wickets DESC
");
?>

<?php while($r=$bowl->fetch_assoc()):
$balls=(int)$r['balls'];
$overs=floor($balls/6).".".($balls%6);
?>

<tr>
    <td><?= strtoupper($r['full_name']) ?></td>
    <td><?= $r['wickets'] ?></td>
    <td><?= $r['runs'] ?></td>
    <td><?= $overs ?></td>
</tr>
<?php endwhile; ?>
</table>

<h4>Timeline</h4>

<div class="timeline">
<?php
    $timeline = $conn->query("
    SELECT over_no,ball_no,runs,is_wicket,extra_runs
    FROM balls
    WHERE innings_id={$inn['innings_id']}
    ORDER BY over_no,ball_no
    ");

    while($b=$timeline->fetch_assoc()):

    $ballText = ($b['over_no'] - 1) . "." . $b['ball_no'];

    if($b['is_wicket']==1){
    $event="W"; 
    $class="wicket";
    }elseif($b['runs']==0){
    $event="0"; 
    $class="dot";
    }else{
    $event=$b['runs']; 
    $class="run";
    }

    if($b['extra_runs']>0) $event.="+".$b['extra_runs'];
?>

<span class="ball <?= $class ?>">
<?= $ballText ?> <?= $event ?>
</span>
    <?php endwhile; ?>
    </div>
    </div>
    <?php endforeach; ?>
</div>

<div style="text-align:center;">
    <div class="result-banner">
    <?= strtoupper($winnerText) ?>
    </div>
    </div>
    </div>
    </main>
    <?php include "partials/admin_footer.php"; ?>
</body>
</html>