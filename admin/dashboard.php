<?php
session_start();

/* 🔐 Protect page */
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | FCC</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/images/Logo white.png">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        body {
            margin: 0;
            background: #0f2027;
            color: #fff;
        }

        /* HEADER */
        .header {
            background: #121212;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 5px 20px rgba(0,0,0,0.6);
        }

        .header img {
            width: 45px;
        }

        .header h2 {
            margin: 0;
            font-size: 22px;
        }

        .logout-btn {
            background: #dc3545;
            border: none;
            padding: 8px 15px;
            color: #fff;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #b02a37;
        }

        /* MAIN */
        .container {
            padding: 30px;
        }

        .welcome {
            margin-bottom: 25px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: #1a1a1a;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            margin-top: 0;
            color: #0d6efd;
        }

        .card p {
            color: #cccccc;
        }
    </style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <div style="display:flex; align-items:center; gap:10px;">
        <img src="../assets/images/Logo white.png">
        <h2>FCC Admin Panel</h2>
    </div>

    <a href="../auth/logout.php" class="logout-btn">Logout</a>
</div>

<!-- MAIN -->
<div class="container">

    <div class="welcome">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h1>
        <p>Manage FCC Automated Scoring & Ranking System</p>
    </div>

    <div class="cards">

        <div class="card">
            <h3>👥 Manage Users</h3>
            <p>Add admins, scorers, and link players</p>
        </div>

        <div class="card">
            <h3>🏏 Players</h3>
            <p>View and manage registered players</p>
        </div>

        <div class="card">
            <h3>📅 Matches</h3>
            <p>Create and manage match days</p>
        </div>

        <div class="card">
            <h3>🏆 Rankings</h3>
            <p>View and recalculate player rankings</p>
        </div>

    </div>

</div>

</body>
</html>
