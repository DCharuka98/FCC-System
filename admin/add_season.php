<?php
require_once "admin_guard.php";
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = trim($_POST['season_name']);
    $start = $_POST['start_date'];
    $end   = $_POST['end_date'];

    if ($start >= $end) {
        $_SESSION['error'] = "End date must be after start date";
        header("Location: add_season.php");
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO seasons (season_name, start_date, end_date)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("sss", $name, $start, $end);
    $stmt->execute();

    $_SESSION['success'] = "Season created successfully";
    header("Location: seasons.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Season | FCC</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include "partials/admin_nav.php"; ?>

<div class="page-container">
    <div class="form-wrapper">
        <div class="panel form-panel-wrapper">

            <h2>Add Season</h2>

            <form method="post">
                <label>Season Name</label>
                <input type="text" name="season_name" placeholder="2025–2026" required>

                <label>Start Date</label>
                <input type="date" name="start_date" required>

                <label>End Date</label>
                <input type="date" name="end_date" required>

                <button type="submit">Create Season</button>
            </form>

        </div>
    </div>
</div>

</body>
</html>
