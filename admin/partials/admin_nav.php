<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<div class="admin-navbar">
    <div class="nav-left">
        <img src="../assets/images/Logo white.png" alt="FCC">
        <span>FCC Admin</span>
    </div>

    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="players.php">Players</a>
        <a href="add_player.php">Add Player</a>
        <a href="seasons.php">Seasons</a>
    </div>

    <a href="../auth/logout.php" class="logout-btn">Logout</a>
</div>
