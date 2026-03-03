<?php
require_once "../../role_guard.php";
allowRoles(['admin','scorer']);
require_once "../../config/db.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['match_id'])) {
    echo json_encode(["success"=>false,"error"=>"Invalid request"]);
    exit;
}

$match_id = (int)$data['match_id'];

$exists = $conn->query("
SELECT innings_id FROM innings
WHERE match_id=$match_id AND innings_number=2
")->fetch_assoc();

if ($exists) {
    echo json_encode([
        "success"=>true,
        "innings_id"=>(int)$exists['innings_id']
    ]);
    exit;
}

$first = $conn->query("
SELECT batting_team_id, bowling_team_id, total_runs
FROM innings
WHERE match_id=$match_id AND innings_number=1
")->fetch_assoc();

if(!$first){
    echo json_encode(["success"=>false,"error"=>"First innings missing"]);
    exit;
}

$newBat  = (int)$first['bowling_team_id'];
$newBowl = (int)$first['batting_team_id'];
$target  = ((int)$first['total_runs']) + 1;

$conn->query("
INSERT INTO innings
(match_id, innings_number, batting_team_id, bowling_team_id, target)
VALUES ($match_id,2,$newBat,$newBowl,$target)
");

$new_id = $conn->insert_id;

echo json_encode([
    "success"=>true,
    "innings_id"=>$new_id
]);