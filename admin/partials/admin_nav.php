<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<div class="admin-navbar">
    <div class="nav-left">
        <img src="/fcc-system/assets/images/Logo white.png" alt="FCC Logo">
        <span>FCC Admin</span>
    </div>

    <div class="nav-links">
        <a href="/fcc-system/admin/dashboard.php">Dashboard</a>
        <a href="/fcc-system/admin/players.php">Players</a>
        <a href="/fcc-system/admin/add_player.php">Add Player</a>
        <a href="/fcc-system/admin/seasons.php">Seasons</a>
        <a href="/fcc-system/admin/matches/start_day.php">Match Scoring</a>
    </div>

    <a href="../auth/logout.php" class="logout-btn">Logout</a>
</div>
