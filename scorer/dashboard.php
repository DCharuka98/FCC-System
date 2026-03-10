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
        display:grid;
        grid-template-columns:repeat(4, 1fr);
        gap:25px;
        width: 1300px;
        margin-top:40px;
        margin-left: -170px;
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