<?php
require_once "admin_guard.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Manual | FCC</title>
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

<h1 class="manual-title">📘 FCC Admin User Manual</h1>

<div class="manual-card">
    <h3>👥 Manage Players</h3>
    <ul>
        <li>View all registered players.</li>
        <li>Activate or deactivate players.</li>
        <li>Edit player details.</li>
        <li>Reset player passwords.</li>
        <li>Inactive players will NOT appear in rankings.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>➕ Add New Player</h3>
    <ul>
        <li>Create new player profile with login access.</li>
        <li>Player becomes available for team selection.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>🗓 Manage Seasons</h3>
    <ul>
        <li>Create new cricket seasons.</li>
        <li>Only one season can be active at a time.</li>
        <li>All matches belong to the active season.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>🏏 Playing Day & Match Flow</h3>
    <ul>
        <li>Start Playing Day.</li>
        <li>Create Teams.</li>
        <li>Start Match.</li>
        <li>Score 1st Innings.</li>
        <li>Start 2nd Innings.</li>
        <li>View Match Summary.</li>
        <li>End Playing Day.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>🏆 Rankings</h3>
    <ul>
        <li>Batting Rankings – Based on total runs.</li>
        <li>Bowling Rankings – Based on total wickets.</li>
        <li>Fielding Rankings – Based on catches & runouts.</li>
        <li>Only ACTIVE players appear in rankings.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>⚙ System Rules</h3>
    <ul>
        <li>Match result auto-calculates after 2nd innings.</li>
        <li>Draw matches display as "Match was draw".</li>
        <li>Overs follow cricket format (e.g., 1.4 = 1 over 4 balls).</li>
        <li>Statistics update automatically after innings ends.</li>
    </ul>
</div>

</div>
</main>

<?php include "partials/admin_footer.php"; ?>

</body>
</html>