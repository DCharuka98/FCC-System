<?php
require_once "../role_guard.php";
allowRoles(['admin','scorer','player']);
require_once "../config/db.php";

function getTop10($conn,$sql){
    $res=$conn->query($sql);
    $rows=[];
    while($r=$res->fetch_assoc()) $rows[]=$r;
    return $rows;
}

$batting = getTop10($conn,"
SELECT p.full_name,SUM(s.runs_scored) total
FROM player_match_statistics s
JOIN players p ON p.player_id=s.player_id
GROUP BY p.player_id
ORDER BY total DESC
LIMIT 10
");

$bowling = getTop10($conn,"
SELECT p.full_name,SUM(s.wickets_taken) total
FROM player_match_statistics s
JOIN players p ON p.player_id=s.player_id
GROUP BY p.player_id
ORDER BY total DESC
LIMIT 10
");

$fielding = getTop10($conn,"
SELECT p.full_name,
SUM((s.catches*8)+(s.stumpings*9)+(s.runouts*10)) total
FROM player_match_statistics s
JOIN players p ON p.player_id=s.player_id
GROUP BY p.player_id
ORDER BY total DESC
LIMIT 10
");

$pod = getTop10($conn,"
SELECT p.full_name,
COUNT(pd.player_of_the_day)*5 total
FROM playing_days pd
JOIN players p ON p.player_id=pd.player_of_the_day
WHERE pd.player_of_the_day IS NOT NULL
GROUP BY pd.player_of_the_day
ORDER BY total DESC
LIMIT 10
");
?>
<!DOCTYPE html>
<html>
<head>
    
<title>Player Rankings | FCC</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="icon" href="../assets/images/Logo white.png">
<style>
    .rank-grid{
    display:grid;
    gap:30px;
    grid-template-columns:repeat(auto-fit,minmax(420px,1fr));
    margin-top:20px;
    }

    .rank-card{
    background:linear-gradient(180deg,#141414,#0e0e0e);
    border-radius:22px;
    padding:24px;
    box-shadow:0 25px 50px rgba(0,0,0,.6);
    position:relative;
    overflow:hidden;
    }

    .rank-title{
    font-weight:700;
    margin-bottom:16px;
    font-size:18px;
    letter-spacing:.4px;
    }

    .leader{
    text-align:center;
    margin-bottom:18px;
    padding:18px;
    border-radius:16px;
    background:linear-gradient(135deg,#064e3b,#022c22);
    }

    .leader-name{
    font-size:20px;
    font-weight:700;
    }

    .leader-score{
    font-size:15px;
    opacity:.85;
    margin-top:4px;
    }

    .rank-list{
    font-size:14px;
    margin-top:8px;
    }

    .rank-row{
    display:flex;
    justify-content:space-between;
    padding:9px 0;
    border-bottom:1px solid rgba(255,255,255,.06);
    }

    .rank-row:last-child{
    border-bottom:none;
    }

    .pos{
    font-weight:700;
    width:26px;
    display:inline-block;
    }

    .gold{color:#fbbf24;}
    .silver{color:#cbd5e1;}
    .bronze{color:#f97316;}

    .view-btn{
    margin-top:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    padding:14px 18px;
    border-radius:14px;
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    font-weight:700;
    font-size:14px;
    text-decoration:none;
    color:white;
    transition:all .3s ease;
    position:relative;
    overflow:hidden;
    box-shadow:0 8px 20px rgba(37,99,235,.4);
    }

    .view-btn:hover{
    transform:translateY(-3px);
    box-shadow:0 15px 30px rgba(37,99,235,.6);
    }

    .view-btn::after{
    content:"→";
    transition:.3s;
    }

    .view-btn:hover::after{
    transform:translateX(5px);
    }
</style>
</head>

<body class="admin-layout">

<?php include "../partials/navbar.php"; ?>

<main class="admin-content">
<div class="page-container">

<h2>🏆 Player Rankings</h2>

<div class="rank-grid">

<?php
function renderCard($title,$data,$view){

$top=$data[0] ?? null;
?>
<div class="rank-card">

<div class="rank-title"><?= $title ?></div>

<?php if($top): ?>
<div class="leader">
<div class="leader-name">🥇 <?= htmlspecialchars($top['full_name']) ?></div>
<div class="leader-score"><?= $top['total'] ?></div>
</div>
<?php endif; ?>

<div class="rank-list">
<?php foreach(array_slice($data,1) as $i=>$r): 
$pos=$i+2;
$cls=$pos==2?'silver':($pos==3?'bronze':'');
?>
<div class="rank-row">
<span>
<span class="pos <?= $cls ?>"><?= $pos ?></span>
<?= htmlspecialchars($r['full_name']) ?>
</span>
<b><?= $r['total'] ?></b>
</div>
<?php endforeach; ?>
</div>

<a class="view-btn" href="<?= $view ?>">View Full Ranking</a>

</div>
<?php } ?>

<?php
renderCard("🏏 Batting",$batting,"ranking_batting_all.php");
renderCard("🎯 Bowling",$bowling,"ranking_bowling_all.php");
renderCard("🧤 Fielding",$fielding,"ranking_fielding_all.php");
renderCard("⭐ Player of the Day",$pod,"ranking_pod_all.php");
?>

</div>
</div>
</main>
<?php include "partials/admin_footer.php"; ?>
</body>
</html>