<?php
require_once "admin_guard.php";
require_once "../config/db.php";

if (!isset($_GET['player_id'])) {
    die("Player ID missing");
}

$player_id = (int) $_GET['player_id'];

$stmt = $conn->prepare(
    "SELECT status FROM players WHERE player_id = ?"
);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Player not found");
}

$row = $result->fetch_assoc();
$current_status = $row['status'];

$new_status = ($current_status === 'Active') ? 'Inactive' : 'Active';

$update = $conn->prepare(
    "UPDATE players SET status = ? WHERE player_id = ?"
);
$update->bind_param("si", $new_status, $player_id);
$update->execute();

header("Location: players.php");
exit;
