<?php
require_once "../role_guard.php";
allowRoles(['admin','scorer','player']);

require_once "../config/db.php";

$result = $conn->query("
SELECT DISTINCT 
DATE_FORMAT(pd.play_date,'%Y-%m') AS month_value,
DATE_FORMAT(pd.play_date,'%M %Y') AS month_label
FROM matches m
JOIN playing_days pd ON m.playing_day_id = pd.playing_day_id
ORDER BY pd.play_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Previous Matches | FCC</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="icon" href="../assets/images/Logo white.png">
</head>
<body class="admin-layout">
<?php include "../partials/navbar.php"; ?>
<main class="admin-content">
<div class="page-container">

<h2>SELECT MONTH</h2>

    <div class="cards">
        <?php while($row = $result->fetch_assoc()): ?>

            <a class="card" href="previous_playing_days.php?month=<?= $row['month_value'] ?>">

                <h3><?= strtoupper($row['month_label']) ?></h3>

                <p>View playing days</p>
            </a>

        <?php endwhile; ?>

    </div>
</div>
</main>

<?php include "partials/admin_footer.php"; ?>

</body>
</html>