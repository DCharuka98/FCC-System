<?php
require_once "../role_guard.php";
allowRoles(['scorer']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Scorer User Manual | FCC</title>
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

<h1 class="manual-title">📘 FCC Scorer User Manual</h1>

<div class="manual-card">
    <h3>➕ Add Player</h3>
    <ul>
        <li>Create new player accounts for the system.</li>
        <li>Enter username, password, full name and joined date.</li>
        <li>New players become available for match selection.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>🗓 Start Playing Day</h3>
    <ul>
        <li>Select playing date and venue.</li>
        <li>System automatically links active season.</li>
        <li>Redirects to team selection page.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>👥 Team Division</h3>
    <ul>
        <li>Select available active players.</li>
        <li>Divide players into two teams.</li>
        <li>Confirm teams before match creation.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>🏏 Match Scoring</h3>
    <ul>
        <li>Select striker, non-striker and bowler.</li>
        <li>Record runs, extras and wickets ball-by-ball.</li>
        <li>System auto-calculates total score and overs.</li>
        <li>Wickets exclude run-outs from bowler statistics.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>🔄 Innings Flow</h3>
    <ul>
        <li>End first innings to generate target.</li>
        <li>Start second innings automatically.</li>
        <li>System calculates match result at completion.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>📊 Match Summary</h3>
    <ul>
        <li>View top batsmen and bowlers.</li>
        <li>Overs displayed in cricket format (e.g., 3.2).</li>
        <li>Match result displayed clearly.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>🏁 End Playing Day</h3>
    <ul>
        <li>Finalize all completed matches.</li>
        <li>Generate playing day summary.</li>
        <li>Update rankings automatically.</li>
    </ul>
</div>

<div class="manual-card">
    <h3>🏆 Rankings</h3>
    <ul>
        <li>View Batting, Bowling and Fielding rankings.</li>
        <li>Only ACTIVE players appear in rankings.</li>
        <li>Statistics update automatically after innings completion.</li>
    </ul>
</div>

</div>
</main>

</body>
</html>