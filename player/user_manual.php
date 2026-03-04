<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'player') {
    header("Location: ../index.php");
    exit();
}

require_once "../config/db.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Player User Manual | FCC</title>
<link rel="icon" href="../assets/images/Logo white.png">
<link rel="stylesheet" href="../assets/css/admin.css">
<style>
    .manual-container{
    max-width:1000px;
    margin:40px auto;
    padding:20px;
    }

    .manual-title{
    font-size:28px;
    font-weight:900;
    margin-bottom:25px;
    }

    .manual-card{
    background:#111827;
    padding:22px;
    border-radius:16px;
    margin-bottom:20px;
    border-left:5px solid #38bdf8;
    transition:.3s;
    }

    .manual-card:hover{
    transform:translateY(-4px);
    box-shadow:0 12px 30px rgba(0,0,0,.5);
    }

    .manual-card h3{
    margin-bottom:10px;
    font-size:18px;
    color:#38bdf8;
    }

    .manual-card ul{
    margin-left:18px;
    line-height:1.8;
    font-size:14px;
    }

    .manual-card li{
    margin-bottom:6px;
    }

</style>
</head>

<body class="admin-layout">

<?php include "../partials/navbar.php"; ?>
<main class="admin-content">
<div class="manual-container">
<h1 class="manual-title">📘 FCC Player User Manual</h1>
<div class="manual-card">
<h3>🏠 Player Dashboard</h3>
    <ul>
    <li>Displays your player profile information.</li>
    <li>Shows career statistics including matches, runs, wickets and catches.</li>
    <li>Shows your ranking positions in batting, bowling and fielding.</li>
    <li>Displays Player of the Day ranking points.</li>
    </ul>
</div>

<div class="manual-card">
<h3>👤 Player Profile</h3>
    <ul>
    <li>Shows your registered player details.</li>
    <li>Displays full name, username, join date and account status.</li>
    <li>Profile details can be updated using the Edit Profile option.</li>
    </ul>
</div>

<div class="manual-card">
<h3>📊 Career Statistics</h3>
    <ul>
    <li>Total Matches Played.</li>
    <li>Total Runs Scored.</li>
    <li>Total Wickets Taken.</li>
    <li>Total Catches Taken.</li>
    <li>Statistics automatically update after each match.</li>
    </ul>
</div>

<div class="manual-card">
<h3>🏆 Rankings</h3>
    <ul>
    <li>Batting Ranking – Based on total runs scored.</li>
    <li>Bowling Ranking – Based on total wickets taken.</li>
    <li>Fielding Ranking – Based on catches and runouts.</li>
    <li>Player of the Day Ranking – Based on match awards.</li>
    <li>Click "View Rankings" to see the full leaderboard.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>⚙ Account Settings</h3>
    <ul>
    <li>Edit Profile – Update your name and username.</li>
    <li>Change Password – Update your account password.</li>
    <li>Use strong passwords to keep your account secure.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>ℹ System Notes</h3>
        <ul>
        <li>Player statistics update automatically after matches.</li>
        <li>Rankings are calculated based on recorded match statistics.</li>
        <li>Inactive players will not appear in rankings.</li>
        <li>Only authorized players can access the player dashboard.</li>
        </ul>
    </div>
</div>
</main>
</body>
</html>