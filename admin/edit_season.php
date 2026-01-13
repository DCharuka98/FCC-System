<?php
require_once "admin_guard.php";
require_once "../config/db.php";

$season_id = (int) ($_GET['season_id'] ?? 0);

if ($season_id <= 0) {
    die("Invalid request");
}

/* Fetch season */
$stmt = $conn->prepare("
    SELECT season_name, start_date, end_date
    FROM seasons
    WHERE season_id = ?
");
$stmt->bind_param("i", $season_id);
$stmt->execute();
$season = $stmt->get_result()->fetch_assoc();

if (!$season) {
    die("Season not found");
}

/* Handle update */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = trim($_POST['season_name']);
    $start = $_POST['start_date'];
    $end   = $_POST['end_date'];

    if ($start >= $end) {
        $_SESSION['error'] = "End date must be after start date";
        header("Location: edit_season.php?season_id=$season_id");
        exit;
    }

    $update = $conn->prepare("
        UPDATE seasons 
        SET season_name = ?, start_date = ?, end_date = ?
        WHERE season_id = ?
    ");
    $update->bind_param("sssi", $name, $start, $end, $season_id);
    $update->execute();

    $_SESSION['success'] = "Season updated successfully";
    header("Location: seasons.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Season | FCC</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="../assets/images/Logo white.png">
</head>
<body>

<?php include "partials/admin_nav.php"; ?>

<div class="page-container">
    <div class="form-wrapper">
        <div class="panel form-panel">

            <h2 style="text-align:center;">Edit Season</h2>

            <form method="post">
                <label>Season Name</label>
                <input type="text" name="season_name"
                       value="<?= htmlspecialchars($season['season_name']) ?>"
                       required>

                <label>Start Date</label>
                <input type="date" name="start_date"
                       value="<?= $season['start_date'] ?>" required>

                <label>End Date</label>
                <input type="date" name="end_date"
                       value="<?= $season['end_date'] ?>" required>

                <button type="submit">Update Season</button>
            </form>

        </div>
    </div>
</div>

</body>
</html>
