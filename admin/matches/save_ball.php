<?php
require_once "../../config/db.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON"]);
    exit;
}

$extra_type = $data['extra_type'] ?? null;
$dismissal_type = $data['dismissal_type'] ?? null;

$stmt = $conn->prepare("
    INSERT INTO balls
    (match_id, innings_id, over_no, ball_no,
     batsman_id, bowler_id,
     runs, extra_type, extra_runs,
     is_wicket, dismissal_type, dismissal_by_player)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iiiiiiisiisi",
    $data['match_id'],
    $data['innings_id'],
    $data['over_no'],
    $data['ball_no'],
    $data['batsman_id'],
    $data['bowler_id'],
    $data['runs'],
    $extra_type,
    $data['extra_runs'],
    $data['is_wicket'],
    $dismissal_type,
    $data['dismissal_by_player']
);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["sql_error" => $stmt->error]);
    exit;
}

echo json_encode(["status" => "OK"]);
