<?php
require_once "../role_guard.php";
allowRoles(['admin','scorer','player']);

require_once "../config/db.php";

$month = $_GET['month'];

$stmt = $conn->prepare("
SELECT DISTINCT 
pd.playing_day_id,
pd.play_date
FROM matches m
JOIN playing_days pd ON m.playing_day_id = pd.playing_day_id
WHERE DATE_FORMAT(pd.play_date,'%Y-%m')=?
ORDER BY pd.play_date DESC
");

$stmt->bind_param("s",$month);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Playing Days | FCC</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="icon" href="../assets/images/Logo white.png">
</head>

<body class="admin-layout">
<style>
    .mini-nav{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:20px;
    font-size:13px;
    }

    .mini-nav a{
    color:#38bdf8;
    text-decoration:none;
    font-weight:600;
    }

    .mini-nav a:hover{
    text-decoration:underline;
    }

    .mini-nav span{
    color:#94a3b8;
    }

    .mini-nav .active{
    color:white;
    font-weight:700;
    }
</style>
<?php include "../partials/navbar.php"; ?>

<main class="admin-content">

<div class="page-container">
<h2>SELECT PLAYING DAY</h2>
<div class="mini-nav">
    <a href="previous_matches_month.php">Months</a>
    <span>›</span>
    <span class="active">Playing Days</span>
</div>
<div class="cards">

<?php while($row = $result->fetch_assoc()): ?>
<a class="card"
href="previous_matches.php?playing_day_id=<?= $row['playing_day_id'] ?>&month=<?= $month ?>">

<h3><?= $row['play_date'] ?></h3>

<p>View matches</p>

</a>    

</a>

<?php endwhile; ?>

</div>

</div>

</main>

<?php include "partials/admin_footer.php"; ?>

</body>
</html>