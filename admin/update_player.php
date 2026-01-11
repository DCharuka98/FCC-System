<?php
require_once "admin_guard.php";
require_once "../config/db.php";

$player_id  = (int) $_POST['player_id'];
$username   = trim($_POST['username']);
$full_name  = trim($_POST['full_name']);
$joined     = $_POST['joined_date'];

if ($player_id <= 0) {
    die("Invalid request");
}

try {
    $conn->begin_transaction();


    $stmt1 = $conn->prepare(
        "UPDATE players SET full_name = ?, joined_date = ?
         WHERE player_id = ?"
    );
    $stmt1->bind_param("ssi", $full_name, $joined, $player_id);
    $stmt1->execute();
 
    $stmt2 = $conn->prepare(
        "UPDATE users u
         JOIN user_player up ON u.user_id = up.user_id
         SET u.username = ?
         WHERE up.player_id = ?"
    );
    $stmt2->bind_param("si", $username, $player_id);
    $stmt2->execute();

    $conn->commit();

    header("Location: players.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Update failed");
}
