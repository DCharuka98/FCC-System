<?php
require_once "admin_guard.php";
require_once "../config/db.php";

$search = $_GET['search'] ?? '';

$fullRanking = $conn->query("
SELECT 
p.player_id,
p.full_name,
COALESCE(COUNT(pd.player_of_the_day)*5,0) total_pod
FROM players p
LEFT JOIN playing_days pd 
    ON pd.player_of_the_day = p.player_id
WHERE p.status = 'Active'
GROUP BY p.player_id, p.full_name
HAVING total_pod > 0
ORDER BY total_pod DESC
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
<title>Player of the Day Rankings</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="stylesheet" href="../assets/css/ranking.css">
<link rel="icon" href="../assets/images/Logo white.png">
</head>

<body class="admin-layout">
<?php include "partials/admin_nav.php"; ?>

<main class="admin-content">
<div class="page-container ranking-container">

<h2 style="margin-bottom:20px;">🏆 Player of the Day Rankings</h2>
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
        <input type="text" name="search"
               placeholder="Search player..."
               value="<?= htmlspecialchars($search) ?>">
        <?php if($search != ''): ?>
            <a href="ranking_pod_all.php" class="clear-inside">✕</a>
        <?php endif; ?>
    </div>
    <button type="submit" class="search-btn">Search</button>
</form>

<?php if(empty($rankings)) echo '<div class="no-result">No players found</div>'; ?>

<?php foreach($rankings as $r): 
$rank = $r['rank'];

$class = '';
if($rank == 1) $class = 'gold';
if($rank == 2) $class = 'silver';
if($rank == 3) $class = 'bronze';
?>

<div class="rank-card <?= $class ?>">
    <div class="rank-number">#<?= $rank ?></div>
    <div class="player-name"><?= htmlspecialchars($r['full_name']) ?></div>
    <div class="run-count"><?= $r['total_pod'] ?> Points</div>
</div>

<?php endforeach; ?>

</div>
</main>
<?php include "partials/admin_footer.php"; ?>
</body>
</html>