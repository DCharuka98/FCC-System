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
<body>
    <div class="dashboard-hero">    
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <img src="../assets/images/Logo white.png" alt="FCC Logo">
                <h2>FCC Admin Dashboard</h2>
            </div>

            <a href="../auth/logout.php" class="logout-btn">Logout</a>
        </div>

        <!-- WELCOME -->
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?> 👋</h1>
        <p class="subtitle">
            Manage players and system settings of the FCC scoring platform
        </p>

        <!-- CARDS -->
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

        </div>
    </div>
</body>
</html>
