<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-navbar">
    <div class="nav-left">
        <img src="/fcc-system/assets/images/Logo white.png" alt="FCC Logo">
        <span>FCC Admin</span>
    </div>

    <div class="nav-links">
        <a href="/fcc-system/admin/dashboard.php"
           class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
            Dashboard
        </a>

        <a href="/fcc-system/admin/players.php"
           class="<?= $currentPage == 'players.php' ? 'active' : '' ?>">
            Players
        </a>

        <a href="/fcc-system/admin/add_player.php"
           class="<?= $currentPage == 'add_player.php' ? 'active' : '' ?>">
            Add Player
        </a>

        <a href="/fcc-system/admin/seasons.php"
           class="<?= $currentPage == 'seasons.php' ? 'active' : '' ?>">
            Seasons
        </a>

        <a href="/fcc-system/admin/matches/start_day.php"
           class="<?= $currentPage == 'start_day.php' ? 'active' : '' ?>">
            Match Scoring
        </a>

        <a href="/fcc-system/admin/rankings.php"
           class="<?= strpos($currentPage, 'ranking') !== false || $currentPage == 'rankings.php' ? 'active' : '' ?>">
            Rankings
        </a>
    </div>

    <a href="/fcc-system/auth/logout.php" class="logout-btn">Logout</a>
</div>