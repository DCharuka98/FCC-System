<?php
require_once "../../role_guard.php";
allowRoles(['admin','scorer']);
require_once "../../config/db.php";

$playing_day_id = (int)($_GET['day'] ?? 0);
if ($playing_day_id <= 0) die("Invalid playing day");

$day = $conn->query("
SELECT pd.*, p.full_name AS player_name
FROM playing_days pd
LEFT JOIN players p ON p.player_id = pd.player_of_the_day
WHERE pd.playing_day_id=$playing_day_id
")->fetch_assoc();


$wins = [];

$res = $conn->query("
SELECT 
CONCAT('Team ', SUBSTRING_INDEX(p.full_name,' ',1)) AS captain_name,
COUNT(*) wins
FROM matches m
JOIN playing_day_teams t ON t.team_id = m.winner_team_id
JOIN players p ON p.player_id = t.captain_id
WHERE m.playing_day_id=$playing_day_id
AND m.status='COMPLETED'
GROUP BY m.winner_team_id
");

while($r=$res->fetch_assoc()){
    $wins[]=$r;
}

$matches=[];

$res=$conn->query("
SELECT 
m.match_id,
m.winner_team_id,

i1.total_runs AS first_runs,
i2.total_runs AS second_runs,
i2.wickets AS second_wickets,

CONCAT('Team ', SUBSTRING_INDEX(pa.full_name,' ',1)) AS teamA,
CONCAT('Team ', SUBSTRING_INDEX(pb.full_name,' ',1)) AS teamB,
CONCAT('Team ', SUBSTRING_INDEX(pw.full_name,' ',1)) AS winner,

i2.batting_team_id AS second_batting_team_id

FROM matches m
LEFT JOIN innings i1 ON i1.match_id=m.match_id AND i1.innings_number=1
LEFT JOIN innings i2 ON i2.match_id=m.match_id AND i2.innings_number=2
LEFT JOIN playing_day_teams ta ON ta.team_id=i1.batting_team_id
LEFT JOIN playing_day_teams tb ON tb.team_id=i2.batting_team_id
LEFT JOIN playing_day_teams tw ON tw.team_id=m.winner_team_id
LEFT JOIN players pa ON pa.player_id=ta.captain_id
LEFT JOIN players pb ON pb.player_id=tb.captain_id
LEFT JOIN players pw ON pw.player_id=tw.captain_id

WHERE m.playing_day_id=$playing_day_id
AND m.status='COMPLETED'
ORDER BY m.match_id
");

while($r=$res->fetch_assoc()) $matches[]=$r;
?>
<!DOCTYPE html>
<html>
<head>
<title>Playing Day Summary</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="icon" href="../assets/images/Logo white.png">
<body class="admin-layout">
<style>
    *{
    margin:0;
    padding:0;
    box-sizing:border-box;
    }

    body{
    font-family:'Segoe UI',sans-serif;
    background:linear-gradient(135deg,#0f172a,#1e293b);
    color:white;
    }

    .admin-content{
    padding-top:90px;
    }

    .page-container{
    max-width:1100px;
    margin:30px auto;
    padding:20px;
    }

    .page-title{
    font-size:24px;
    font-weight:900;
    margin-bottom:25px;
    letter-spacing:1px;
    }

    .player-card{
    background:linear-gradient(90deg,#2563eb,#7c3aed);
    padding:18px 25px;
    border-radius:14px;
    font-size:16px;
    font-weight:700;
    text-align:center;
    box-shadow:0 10px 25px rgba(0,0,0,.4);
    margin-bottom:30px;
    }
    .section-title{
    font-size:22px;
    font-weight:900;
    margin-bottom:20px;
    }

    .stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:15px;
    margin-bottom:30px;
    }

    .stat-box{
    background:#1f2937;
    padding:18px;
    border-radius:14px;
    text-align:center;
    transition:.3s;
    border:1px solid rgba(255,255,255,.05);
    }

    .stat-box:hover{
    transform:translateY(-3px);
    box-shadow:0 8px 20px rgba(0,0,0,.4);
    }

    .stat-box h4{
    font-size:12px;
    color:#94a3b8;
    margin-bottom:6px;
    }

    .stat-box p{
    font-size:20px;
    font-weight:800;
    }

    .stat-box:hover{
    transform:translateY(-4px);
    box-shadow:0 10px 25px rgba(0,0,0,.4);
    }

    .stat-box h4{
    font-size:14px;
    color:#94a3b8;
    margin-bottom:10px;
    }

    .stat-box p{
    font-size:28px;
    font-weight:900;
    }

    .results-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px 30px;
    }

    .result-item{
    background:linear-gradient(135deg,#0f172a,#1f2937);
    padding:22px 25px;
    border-radius:18px;
    display:flex;
    flex-direction:column;
    gap:8px;
    border-left:5px solid #38bdf8;
    transition:.3s;
    }

    .result-item:hover{
    transform:translateY(-4px);
    box-shadow:0 15px 35px rgba(0,0,0,.5);
    }

    .match-number{
    font-size:13px;
    color:#94a3b8;
    font-weight:600;
    letter-spacing:1px;
    }

    .result-text{
    font-size:17px;
    font-weight:800;
    }

    .winner-name{
    color:#38bdf8;
    }

    .margin-text{
    color:white;
    font-weight:700;
    }

    .close-match{
    border-left:5px solid #f59e0b;
    }

    @media(max-width:768px){
    .results-grid{
    grid-template-columns:1fr;
    }
    }
</style>
</head>
<body>

<?php include "../../partials/navbar.php"; ?>

<main class="admin-content">
<div class="page-container">

<h1 class="page-title">Playing Day Summary</h1>

<div class="player-card">
🏆 Player of the Day<br><br>
<?= htmlspecialchars($day['player_name'] ?? 'Not decided') ?>
</div>

<h2 class="section-title">Team Wins</h2>

<?php if(!$wins): ?>
<div>No completed matches</div>
<?php else: ?>
<div class="stats-grid">
<?php foreach($wins as $w): ?>
<div class="stat-box">
<h4><?= htmlspecialchars($w['captain_name']) ?></h4>
<p><?= $w['wins'] ?></p>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<h2 class="section-title">Match Results</h2>

<div class="results-grid">

<?php 
$count = 1;

foreach($matches as $m):

if($m['first_runs'] == $m['second_runs']){

    $isDraw = true;
    $resultText = "Match was draw";
    $highlightClass = " close-match";

} else {

    $isDraw = false;

    if($m['winner_team_id'] == $m['second_batting_team_id']){

        $teamCount = 3; 
        $maxWk = $teamCount - 1;
        $wkRemaining = $maxWk - $m['second_wickets'];

        $margin = ($wkRemaining <= 0)
            ? "won by last wicket"
            : "won by $wkRemaining wickets";

    } else {

        $runMargin = $m['first_runs'] - $m['second_runs'];
        $margin = "won by $runMargin runs";
    }

    $highlightClass = "";
    if(str_contains($margin, "last wicket") || 
       str_contains($margin, "1 wicket") || 
       str_contains($margin, "1 runs")){
        $highlightClass = " close-match";
    }
}

$highlightClass = "";
if(str_contains($margin, "last wicket") || 
   str_contains($margin, "1 wicket") || 
   str_contains($margin, "1 runs")){
    $highlightClass = " close-match";
}
?>

<div class="result-item<?= $highlightClass ?>">
    <div class="match-number">
        Match <?= str_pad($count,2,'0',STR_PAD_LEFT) ?>
    </div>

    <div class="result-text">
    <?php if($isDraw): ?>
        <span class="margin-text">MATCH WAS DRAW</span>
    <?php else: ?>
        <span class="winner-name">
            <?= htmlspecialchars($m['winner']) ?>
        </span>
        <span class="margin-text">
            <?= $margin ?>
        </span>
    <?php endif; ?>
    </div>
</div>

<?php 
$count++;
endforeach; 
?>

</div>
</div>
</main>
<?php include "../partials/admin_footer.php"; ?>
</body>
</html>