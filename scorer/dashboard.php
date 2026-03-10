<?php
require_once "scorer_guard.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Scorer Dashboard | FCC</title>
    <link rel="icon" href="../assets/images/Logo white.png">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .cards{
        display:flex;
        flex-wrap:wrap;
        gap:30px;
        justify-content:center;
        margin-top:40px;
        max-width:900px;
        margin-inline:auto;
        }

        .cards .card{
        width:280px;
        }
    </style>
</head>

<body class="admin-layout">
<main class="admin-content">
<div class="dashboard-hero">

<div class="dashboard-header">
    <div class="dashboard-header-left">
        <img src="../assets/images/Logo white.png">
        <h2>FCC Scorer Panel</h2>
    </div>

    <a href="../auth/logout.php" class="logout-btn">Logout</a>
</div>

<h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?> 👋</h1>
<p class="subtitle">Manage match operations</p>

<div class="cards">

    <a href="../admin/add_player.php" class="card">
        <h3>➕ Add Player</h3>
        <p>Create new player profiles</p>
    </a>

    <a href="../admin/matches/start_day.php" class="card">
        <h3>🗓 Start Playing Day</h3>
        <p>Create and manage match day</p>
    </a>

    <a href="../admin/rankings.php" class="card">
        <h3>🏆 View Rankings</h3>
        <p>See batting, bowling and fielding stats</p>
    </a>

    <a href="../admin/previous_matches_month.php" class="card">
        <h3>📊 Previous Matches</h3>
        <p>View past match scorecards</p>
    </a>

    <a href="user_manual.php" class="card">
        <h3>📘 User Manual</h3>
        <p>View scorer system guide and usage instructions</p>
    </a>
</div>
</div>
</main>
<?php include "../admin/partials/admin_footer.php"; ?>
</body>
</html>