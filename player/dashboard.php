<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'player') {
    header("Location: ../index.php");
    exit();
}

require_once "../config/db.php";

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
SELECT p.player_id, p.full_name, p.joined_date, p.status, u.username
FROM players p
JOIN user_player up ON p.player_id = up.player_id
JOIN users u ON u.user_id = up.user_id
WHERE up.user_id = ?
");

$stmt->bind_param("i",$user_id);
$stmt->execute();
$player = $stmt->get_result()->fetch_assoc();

$player_id = $player['player_id'];

$stats = $conn->query("
SELECT 
COUNT(DISTINCT match_id) matches_played,
SUM(runs_scored) total_runs,
SUM(wickets_taken) total_wickets,
SUM(catches + runouts) total_field_points
FROM player_match_statistics
WHERE player_id = $player_id
");

$career = $stats->fetch_assoc();

$matches_played = $career['matches_played'] ?? 0;
$total_runs = $career['total_runs'] ?? 0;
$total_wickets = $career['total_wickets'] ?? 0;
$total_field_points = $career['total_field_points'] ?? 0;

$bat_rank = 0;
$bat_runs = 0;

$bat = $conn->query("
SELECT player_id, SUM(runs_scored) runs
FROM player_match_statistics
GROUP BY player_id
ORDER BY runs DESC
");

$pos = 1;
while($r = $bat->fetch_assoc()){
    if($r['player_id'] == $player_id){
        $bat_rank = $pos;
        $bat_runs = $r['runs'];
        break;
    }
    $pos++;
}

$bowl_rank = 0;
$bowl_wk = 0;

$bowl = $conn->query("
SELECT player_id, SUM(wickets_taken) wk
FROM player_match_statistics
GROUP BY player_id
ORDER BY wk DESC
");

$pos = 1;
while($r = $bowl->fetch_assoc()){
    if($r['player_id'] == $player_id){
        $bowl_rank = $pos;
        $bowl_wk = $r['wk'];
        break;
    }
    $pos++;
}

$field_rank = 0;
$field_points = 0;

$field = $conn->query("
SELECT player_id, SUM(catches + runouts) field_points
FROM player_match_statistics
GROUP BY player_id
ORDER BY field_points DESC
");

$pos = 1;
while($r = $field->fetch_assoc()){
    if($r['player_id'] == $player_id){
        $field_rank = $pos;
        $field_points = $r['field_points'];
        break;
    }
    $pos++;
}

$pod_rank = 0;
$pod_points = 0;

$pod = $conn->query("
SELECT player_of_the_day AS player_id, COUNT(*)*5 pod_points
FROM playing_days
WHERE player_of_the_day IS NOT NULL
GROUP BY player_of_the_day
ORDER BY pod_points DESC
");

$pos = 1;
while($r = $pod->fetch_assoc()){
    if($r['player_id'] == $player_id){
        $pod_rank = $pos;
        $pod_points = $r['pod_points'];
        break;
    }
    $pos++;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Player Dashboard | FCC</title>

<link rel="icon" href="../assets/images/Logo white.png">
<link rel="stylesheet" href="../assets/css/admin.css">

<style>
    .player-container{
    max-width:1100px;
    margin:40px auto;
    padding:20px;
    }

    .dashboard-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
    }

    .dashboard-header-left{
    display:flex;
    align-items:center;
    gap:10px;
    }

    .dashboard-header-left img{
    width:32px;
    }

    .player-info-card{
    background:#111827;
    border-radius:16px;
    padding:20px;
    margin-top:25px;
    border-left:5px solid #38bdf8;
    }

    .player-info-card h3{
    margin-bottom:15px;
    color:#38bdf8;
    }

    .player-info-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-top:10px;
    }

    .player-info-grid span{
    font-size:12px;
    color:#94a3b8;
    }

    .player-info-grid p{
    font-size:16px;
    font-weight:600;
    margin-top:4px;
    }

    .career-stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-top:25px;
    }

    .career-card{
    background:#0f172a;
    padding:18px;
    border-radius:14px;
    text-align:center;
    border:1px solid #1f2937;
    }

    .career-card h3{
    color:#38bdf8;
    margin-bottom:10px;
    font-size:14px;
    }

    .career-card p{
    font-size:28px;
    font-weight:900;
    }

    .rank-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:25px;
    margin-top:30px;
    }

    .rank-card{
    background:#111827;
    padding:18px;
    border-radius:16px;
    text-align:center;
    border-left:5px solid #38bdf8;
    }

    .rank-card h3{
    color:#38bdf8;
    margin-bottom:10px;
    }

    .rank-card p{
    font-size:30px;
    font-weight:900;
    }

    .rank-card span{
    font-size:13px;
    color:#94a3b8;
    }

    .player-actions{
    display:flex;
    justify-content:center;
    gap:20px;
    margin-top:40px;
    flex-wrap:wrap;
    }

    .action-btn{
    background:#2563eb;
    color:white;
    padding:12px 22px;
    border-radius:10px;
    text-decoration:none;
    font-weight:700;
    transition:.3s;
    }

    .action-btn:hover{
    background:#1d4ed8;
    transform:translateY(-2px);
    }
</style>
</head>
<body class="admin-layout">
<main class="admin-content">
<div class="player-container">
<div class="dashboard-header">
<div class="dashboard-header-left">
<img src="../assets/images/Logo white.png">
<h2>FCC Player Panel</h2>
</div>
<a href="../auth/logout.php" class="logout-btn">Logout</a>
</div>

<h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?> 👋</h1>
<p class="subtitle">View your rankings and performance</p>

<div class="player-info-card">
<h3>👤 Player Profile</h3>
    <div class="player-info-grid">
        <div>
        <span>👤 Name</span>
        <p><?= htmlspecialchars($player['full_name']) ?></p>
        </div>

        <div>
        <span>🔐 Username</span>
        <p><?= htmlspecialchars($player['username']) ?></p>
        </div>

        <div>
        <span>📅 Joined</span>
        <p><?= $player['joined_date'] ?></p>
        </div>

        <div>
        <span>✅ Status</span>
        <p><?= $player['status'] ?></p>
        </div>
    </div>


    <div class="career-stats">
        <div class="career-card">
        <h3>🏏 Matches</h3>
        <p><?= $matches_played ?></p>
        </div>

        <div class="career-card">
        <h3>🔥 Runs</h3>
        <p><?= $total_runs ?></p>
        </div>

        <div class="career-card">
        <h3>🎯 Wickets</h3>
        <p><?= $total_wickets ?></p>
        </div>

        <div class="career-card">
        <h3>🧤 Fielding Points</h3>
        <p><?= $total_field_points ?></p>
        </div>
        </div>
    </div>

    <div class="rank-grid">
        <div class="rank-card">
        <h3>🏏 Batting Rank</h3>
        <p>#<?= $bat_rank ?></p>
        <span><?= $bat_runs ?> Runs</span>
        </div>

        <div class="rank-card">
        <h3>🎯 Bowling Rank</h3>
        <p>#<?= $bowl_rank ?></p>
        <span><?= $bowl_wk ?> Wickets</span>
        </div>

        <div class="rank-card">
        <h3>🧤 Fielding Rank</h3>
        <p>#<?= $field_rank ?></p>
        <span><?= $field_points ?> Points</span>
        </div>

        <div class="rank-card">
        <h3>🏆 Player of the Day</h3>
        <p>#<?= $pod_rank ?></p>
        <span><?= $pod_points ?> Points</span>
        </div>
    </div>
    <div class="player-actions">
        <a href="../admin/rankings.php" class="action-btn">
        🏆 View Rankings
        </a>

        <a href="../admin/previous_matches_month.php"class="action-btn">
        📊 Previous Matches
        </a>

        <a href="change_password.php" class="action-btn">
        🔑 Change Password
        </a>

        <a href="edit_profile.php" class="action-btn">
        ✏️ Edit Profile
        </a>

        <a href="user_manual.php" class="action-btn">
        ❓ Player Guide
        </a>
    </div>
</div>

</main>
<?php include "../admin/partials/admin_footer.php"; ?>
</body>
</html>