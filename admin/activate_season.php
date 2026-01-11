<?php
require_once "admin_guard.php";
require_once "../config/db.php";

$season_id = (int) ($_GET['season_id'] ?? 0);

if ($season_id <= 0) {
    $_SESSION['error'] = "Invalid season";
    header("Location: seasons.php");
    exit;
}

$conn->begin_transaction();

try {
    // Deactivate all seasons
    $conn->query("UPDATE seasons SET status = 'Inactive'");

    // Activate selected season
    $stmt = $conn->prepare(
        "UPDATE seasons SET status = 'Active' WHERE season_id = ?"
    );
    $stmt->bind_param("i", $season_id);
    $stmt->execute();

    $conn->commit();
    $_SESSION['success'] = "Season activated successfully";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Failed to activate season";
}

header("Location: seasons.php");
exit;
