<?php
require_once "admin_guard.php";
require_once "../config/db.php";

$search = $_GET['search'] ?? '';

$fullRanking = $conn->query("
SELECT 
p.player_id,
p.full_name,
COALESCE(SUM(s.runs_scored),0) total_runs
FROM players p
LEFT JOIN player_match_statistics s 
    ON s.player_id = p.player_id
GROUP BY p.player_id
ORDER BY total_runs DESC
");

$rankings = [];
$position = 1;

while($row = $fullRanking->fetch_assoc()){
    $row['rank'] = $position++;
    $rankings[] = $row;
}

if($search != ''){
    $searchLower = strtolower($search);
    $rankings = array_filter($rankings, function($player) use ($searchLower){
        return strpos(strtolower($player['full_name']), $searchLower) !== false;
    });
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Batting Rankings</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="stylesheet" href="../assets/css/ranking.css">
</head>

<body class="admin-layout">

<?php include "partials/admin_nav.php"; ?>

<main class="admin-content">
<div class="page-container ranking-container">

<h2 style="margin-bottom:20px;">🏏 Batting Rankings</h2>
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="ranking-mini-nav">
    <a href="ranking_batting_all.php" 
       class="<?= $currentPage == 'ranking_batting_all.php' ? 'active' : '' ?>">
        🏏 Batting
    </a>

    <a href="ranking_bowling_all.php" 
       class="<?= $currentPage == 'ranking_bowling_all.php' ? 'active' : '' ?>">
        🎯 Bowling
    </a>

    <a href="ranking_fielding_all.php" 
       class="<?= $currentPage == 'ranking_fielding_all.php' ? 'active' : '' ?>">
        🧤 Fielding
    </a>

    <a href="ranking_pod_all.php" 
       class="<?= $currentPage == 'ranking_pod_all.php' ? 'active' : '' ?>">
        🏆 Player of the Day
    </a>
</div>
<form method="GET" class="search-box">
    
    <div class="search-wrapper">
        <input type="text" 
               name="search" 
               placeholder="Search player..."
               value="<?= htmlspecialchars($search) ?>">

        <?php if($search != ''): ?>
            <a href="ranking_batting_all.php" class="clear-inside">✕</a>
        <?php endif; ?>
    </div>

    <button type="submit" class="search-btn">Search</button>

</form>

<?php 

if(empty($rankings)){
    echo '<div class="no-result">No players found</div>';
}

foreach($rankings as $r):

$rank = $r['rank'];

$class = '';
if($rank == 1) $class = 'gold';
if($rank == 2) $class = 'silver';
if($rank == 3) $class = 'bronze';
?>

<div class="rank-card <?= $class ?>">
    <div class="rank-number">#<?= $rank ?></div>
    <div class="player-name"><?= htmlspecialchars($r['full_name']) ?></div>
    <div class="run-count"><?= $r['total_runs'] ?> Runs</div>
</div>

<?php endforeach; ?>

</div>
</main>
<?php include "partials/admin_footer.php"; ?>
</body>
</html>