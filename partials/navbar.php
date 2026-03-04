<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
?>

<div class="admin-navbar">
    <div class="nav-left">
        <img src="/fcc-system/assets/images/Logo white.png" alt="FCC Logo">
        <span>FCC System</span>
    </div>

    <div class="nav-links">

        <?php if ($role === 'admin'): ?>
            <a href="/fcc-system/admin/dashboard.php"
               class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>

        <?php elseif ($role === 'scorer'): ?>
            <a href="/fcc-system/scorer/dashboard.php"
               class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>

        <?php elseif ($role === 'player'): ?>
            <a href="/fcc-system/player/dashboard.php"
               class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
        <?php endif; ?>


        <?php if ($role === 'admin'): ?>

            <a href="/fcc-system/admin/players.php"
               class="<?= $currentPage == 'players.php' ? 'active' : '' ?>">Players</a>

            <a href="/fcc-system/admin/seasons.php"
               class="<?= $currentPage == 'seasons.php' ? 'active' : '' ?>">Seasons</a>

        <?php endif; ?>


        <?php if (in_array($role, ['admin','scorer'])): ?>

            <a href="/fcc-system/admin/add_player.php"
               class="<?= $currentPage == 'add_player.php' ? 'active' : '' ?>">Add Player</a>

            <a href="/fcc-system/admin/matches/start_day.php"
               class="<?= $currentPage == 'start_day.php' ? 'active' : '' ?>">Match Scoring</a>

        <?php endif; ?>


        <?php if (in_array($role, ['admin','scorer','player'])): ?>

            <a href="/fcc-system/admin/rankings.php"
               class="<?= strpos($currentPage,'ranking') !== false ? 'active' : '' ?>">Rankings</a>

        <?php endif; ?>


        <?php if ($role === 'player'): ?>

            <a href="/fcc-system/player/change_password.php"
               class="<?= $currentPage == 'change_password.php' ? 'active' : '' ?>">
               Change Password
            </a>

            <a href="/fcc-system/player/edit_profile.php"
               class="<?= $currentPage == 'edit_profile.php' ? 'active' : '' ?>">
               Edit Profile
            </a>

        <?php endif; ?>

    </div>

    <a href="/fcc-system/auth/logout.php" class="logout-btn">Logout</a>
</div>