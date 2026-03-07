<?php
require_once "admin_guard.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <title>Admin Dashboard | FCC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/images/Logo white.png">
    <link rel="stylesheet" href="../assets/css/admin.css">
    
</head>
<body class="admin-layout">
    <main class="admin-content">
    <div class="dashboard-hero">    
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <img src="../assets/images/Logo white.png" alt="FCC Logo">
                <h2>FCC Admin Dashboard</h2>
            </div>

            <a href="../auth/logout.php" class="logout-btn">Logout</a>
        </div>

        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?> 👋</h1>
        <p class="subtitle">
            Manage players and system settings of the FCC scoring platform
        </p>

        <div class="cards">

            <a href="players.php" class="card">
                <h3>👥 Manage Players</h3>
                <p>View, activate, deactivate and manage player profiles</p>
            </a>

            <a href="add_player.php" class="card">
                <h3>➕ Add New Player</h3>
                <p>Create a new player with login access</p>
            </a>

            <a href="seasons.php" class="card">
                <h3>🗓 Manage Seasons</h3>
                <p>Create and activate cricket seasons</p>
            </a>

            <a href="matches/start_day.php" class="card">
                <h3>🗓 Start Playing Day</h3>
                <p>Create a new playing day for matches</p>
            </a>

            <a href="rankings.php" class="card">
                <h3>🏆 View Rankings</h3>
                <p>See batting, bowling and fielding rankings</p>
            </a>
            <a href="user_manual.php" class="card">
                <h3>📘 User Manual</h3>
                <p>View admin system guide and usage instructions</p>
            </a>
        </div>
    </div>
    </main>
    <?php include "partials/admin_footer.php"; ?>
</body>
</html>
