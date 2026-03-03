<?php
require_once "../admin_guard.php";
require_once "../../config/db.php";

$match_id = (int)($_GET['match'] ?? 0);
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

while($r = $res->fetch_assoc()){
    $innings[] = $r;
}

$team1 = $innings[0]['team_name'] ?? 'Team 1';
$team2 = $innings[1]['team_name'] ?? 'Team 2';

$winnerText = "Match Result Pending";

if(count($innings) === 2){

    $first  = $innings[0];
    $second = $innings[1];

if($second['total_runs'] > $first['total_runs']){

    $teamCount = $conn->query("
        SELECT COUNT(*) c 
        FROM playing_day_team_players
        WHERE team_id = {$second['batting_team_id']}
    ")->fetch_assoc()['c'];

    $maxWickets = $teamCount - 1;

    $wicketsLost = (int)$second['wickets'];

    $marginWk = $maxWickets - $wicketsLost;
    
    if($marginWk <= 0){
        $winnerText = $second['team_name'] . " won by 01 wicket";
    } else {

        $formattedWk = str_pad($marginWk, 2, "0", STR_PAD_LEFT);

        if($marginWk == 1){
            $winnerText = $second['team_name'] . " won by $formattedWk wicket";
        } else {
            $winnerText = $second['team_name'] . " won by $formattedWk wickets";
        }
    }

} elseif($second['total_runs'] < $first['total_runs']) {

        $marginRuns = $first['total_runs'] - $second['total_runs'];
        $winnerText = $first['team_name'] . " won by " . $marginRuns . " runs";

    } else {

        $winnerText = "Match Tied";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Match Summary</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="icon" href="../assets/images/Logo white.png">

<style>
*{
margin:0;
padding:0;
box-sizing:border-box;
}

html, body{
height:100%;
overflow:hidden;
font-family:'Segoe UI',sans-serif;
background:linear-gradient(135deg,#0f172a,#1e293b);
text-transform:uppercase;
color:white;
}

.page-container{
height:100vh;
display:flex;
flex-direction:column;
align-items:center;
justify-content:center;   
gap:20px;                
padding:20px 30px;
}

.main-title{
font-size:24px;
font-weight:900;
letter-spacing:2px;
text-align:center;
}

.summary{
display:grid;
grid-template-columns:1fr 1fr;
gap:25px;
width:100%;
max-width:1100px;
}

.card{
background:#111827;
padding:15px;
border-radius:14px;
box-shadow:0 15px 30px rgba(0,0,0,.5);
}

.card h2{
text-align:center;
font-size:14px;
margin-bottom:10px;
color:#38bdf8;
}

table{
width:100%;
border-collapse:collapse;
margin-bottom:14px;
}

th{
font-size:11px;
padding:8px 6px;   /* more vertical spacing */
color:#94a3b8;
border-bottom:1px solid rgba(255,255,255,.2);
text-align:left;
}

td{
font-size:11px;
font-weight:700;
padding:8px 6px;
border-bottom:1px solid rgba(255,255,255,.08);
}

tr:hover{
background:rgba(255,255,255,.05);
}
.empty-row td{
color: rgba(255,255,255,0.25);
}

.overs{
background:#1f2937;
padding:8px;
border-radius:8px;
text-align:center;
font-size:11px;
font-weight:600;
margin:12px 0;
}

.result-bar{
padding:12px 30px;
font-size:14px;
font-weight:900;
width: 500px;
border-radius:25px;
background:linear-gradient(90deg,#2563eb,#7c3aed);
color:white;
text-align:center;
}

.actions{
display:flex;
gap:15px;
}

.action-btn{
padding:7px 18px;
border-radius:8px;
font-weight:700;
font-size:11px;
text-decoration:none;
color:white;
transition:.3s;
}

.end-btn{
background:#ef4444;
}

.next-btn{
background:#2563eb;
}

.action-btn:hover{
opacity:.85;
transform:translateY(-2px);
}
.day-btn{
background:#f59e0b; 
}

.day-btn:hover{
background:#d97706;
}
</style>
</head>

<body class="admin-layout">
<main class="admin-content">
<div class="page-container">

<div class="header">
<h1 class="main-title">
<?= strtoupper(htmlspecialchars($team1)) ?> VS <?= strtoupper(htmlspecialchars($team2)) ?>
</h1>
</div>

<div class="summary">

<?php foreach($innings as $inn): ?>

<div class="card">

<h2>
<?= strtoupper(htmlspecialchars($inn['team_name'])) ?> -
<?= $inn['total_runs'] ?>/<?= $inn['wickets'] ?>
</h2>

<table>
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
LIMIT 4
");

$count = 0;

while($r = $bat->fetch_assoc()):
$count++;
?>
<tr>
<td><?= strtoupper(htmlspecialchars($r['full_name'])) ?></td>
<td><?= $r['runs'] ?></td>
<td><?= $r['balls'] ?></td>
</tr>
<?php endwhile; ?>

<?php
for($i = $count; $i < 4; $i++):
?>
<tr class="empty-row">
<td>&nbsp;</td>
<td>-</td>
<td>-</td>
</tr>
<?php endfor; ?>
</table>

<div class="overs">
OVERS BOWLED: <?= $inn['overs_completed'] ?>
</div>

<table>
<tr>
<th>Bowler</th>
<th>W</th>
<th>R</th>
<th>O</th>
</tr>

<?php
$bowl = $conn->query("
SELECT 
    p.full_name,
    SUM(CASE 
        WHEN b.is_wicket=1 
        AND b.dismissal_type!='RUN_OUT' 
        THEN 1 ELSE 0 END) wickets,
    SUM(b.runs + IFNULL(b.extra_runs,0)) runs,
    COUNT(b.ball_id) balls
FROM balls b
JOIN players p ON p.player_id=b.bowler_id
WHERE b.innings_id={$inn['innings_id']}
GROUP BY b.bowler_id
ORDER BY wickets DESC
LIMIT 4
");

$count = 0;

while($r = $bowl->fetch_assoc()):
$count++;
?>
<tr>
<td><?= strtoupper(htmlspecialchars($r['full_name'])) ?></td>
<td><?= $r['wickets'] ?></td>
<td><?= $r['runs'] ?></td>
<?php
$balls = (int)$r['balls'];
$balls_per_over = 6;

$completed = floor($balls / $balls_per_over);
$remaining = $balls % $balls_per_over;

$oversText = $completed . "." . $remaining;
?>

<td><?= $oversText ?></td>
</tr>
<?php endwhile; ?>

<?php
for($i = $count; $i < 4; $i++):
?>
<tr class="empty-row">
<td>&nbsp;</td>
<td>-</td>
<td>-</td>
<td>-</td>
</tr>
<?php endfor; ?>
</table>

</div>

<?php endforeach; ?>

</div> 


<div class="result-bar">
<?= strtoupper(htmlspecialchars($winnerText)) ?>
</div>

<div class="actions">

<a href="create_match.php?day=<?= $playing_day_id ?>" 
   class="action-btn next-btn">
   START NEXT MATCH
</a>

<a href="end_playing_day.php?day=<?= $playing_day_id ?>" 
   class="action-btn day-btn">
   END PLAYING DAY
</a>

</div>
</div>

</div>
</main>
</body>