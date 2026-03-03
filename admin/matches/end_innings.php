<?php
require_once "../../role_guard.php";
allowRoles(['admin','scorer']);
require_once "../../config/db.php";

header("Content-Type: application/json");

$conn->begin_transaction();

try {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['innings_id'])) {
        throw new Exception("Invalid request");
    }

    $innings_id = (int)$data['innings_id'];
    $target     = $data['target'] ?? null;
    $reason     = $data['reason'] ?? null; 


    $row = $conn->query("
        SELECT match_id
        FROM innings
        WHERE innings_id = $innings_id
        LIMIT 1
    ")->fetch_assoc();

    if (!$row) {
        throw new Exception("Innings not found");
    }

    $match_id = (int)$row['match_id'];


    $tot = $conn->query("
        SELECT
            COUNT(*) AS balls,
            SUM(runs + IFNULL(extra_runs,0)) AS runs,
            SUM(is_wicket) AS wickets
        FROM balls
        WHERE innings_id = $innings_id
    ")->fetch_assoc();

    $total_balls = (int)$tot['balls'];

    if ($total_balls === 0) {
        throw new Exception("No balls recorded for this innings");
    }

    $total_runs    = (int)$tot['runs'];
    $total_wickets = (int)$tot['wickets'];

    $matchInfo = $conn->query("
        SELECT m.balls_per_over
        FROM innings i
        JOIN matches m ON m.match_id = i.match_id
        WHERE i.innings_id = $innings_id
    ")->fetch_assoc();

    $balls_per_over = (int)$matchInfo['balls_per_over'];

    $completed_overs = floor($total_balls / $balls_per_over);
    $remaining_balls = $total_balls % $balls_per_over;

    $overs_completed = $completed_overs . "." . $remaining_balls;

    $target_sql = $target ? (int)$target : "NULL";

   
    $ok = $conn->query("
        UPDATE innings
        SET
            total_runs = $total_runs,
            wickets = $total_wickets,
            overs_completed = $overs_completed,
            target = $target_sql
        WHERE innings_id = $innings_id
    ");

    if (!$ok) {
        throw new Exception($conn->error);
    }

$inn = $conn->query("
SELECT innings_number, match_id
FROM innings WHERE innings_id=$innings_id
")->fetch_assoc();

if ((int)$inn['innings_number'] === 2) {

    $first = $conn->query("
        SELECT total_runs, batting_team_id
        FROM innings
        WHERE match_id={$inn['match_id']} AND innings_number=1
    ")->fetch_assoc();

    $second = $conn->query("
        SELECT total_runs, batting_team_id
        FROM innings
        WHERE innings_id=$innings_id
    ")->fetch_assoc();

    $winner = ($second['total_runs'] >= $first['total_runs']+1)
        ? $second['batting_team_id']
        : $first['batting_team_id'];

    $conn->query("
        UPDATE matches
        SET status='COMPLETED',
            winner_team_id=$winner
        WHERE match_id={$inn['match_id']}
    ");
}

$innInfo = $conn->query("
SELECT innings_number, match_id
FROM innings
WHERE innings_id=$innings_id
")->fetch_assoc();

if ((int)$innInfo['innings_number'] === 1 && $target_sql !== "NULL") {

    $conn->query("
    UPDATE matches
    SET target=$target_sql
    WHERE match_id={$innInfo['match_id']}
    ");
}
$bat = $conn->query("
SELECT
    batsman_id AS player_id,
    SUM(runs) AS runs_scored
FROM balls
WHERE innings_id = $innings_id
GROUP BY batsman_id
");

while ($r = $bat->fetch_assoc()) {

    $player_id = (int)$r['player_id'];
    $runs_scored = (int)$r['runs_scored'];

    $conn->query("
    INSERT INTO player_match_statistics
    (player_id, match_id, runs_scored, wickets_taken, catches, runouts, stumpings)
    VALUES ($player_id, $match_id, $runs_scored, 0, 0, 0, 0)
    ON DUPLICATE KEY UPDATE
    runs_scored = runs_scored + $runs_scored
    ");
}


$bowl = $conn->query("
SELECT
    bowler_id AS player_id,
    SUM(
        CASE 
            WHEN is_wicket=1 
            AND dismissal_type!='RUN_OUT'
            THEN 1 
            ELSE 0 
        END
    ) AS wickets_taken
FROM balls
WHERE innings_id = $innings_id
GROUP BY bowler_id
");

while ($r = $bowl->fetch_assoc()) {

    $player_id = (int)$r['player_id'];
    $wk = (int)$r['wickets_taken'];

    $conn->query("
    INSERT INTO player_match_statistics
    (player_id, match_id, runs_scored, wickets_taken, catches, runouts, stumpings)
    VALUES ($player_id, $match_id, 0, $wk, 0, 0, 0)
    ON DUPLICATE KEY UPDATE
    wickets_taken = wickets_taken + $wk
    ");
}

$field = $conn->query("
SELECT
    dismissal_by_player AS player_id,
    SUM(dismissal_type='CAUGHT') AS catches,
    SUM(dismissal_type='RUN_OUT') AS runouts,
    SUM(dismissal_type='STUMPED') AS stumpings
FROM balls
WHERE innings_id=$innings_id
AND dismissal_by_player IS NOT NULL
GROUP BY dismissal_by_player
");

while($r=$field->fetch_assoc()){

    $player=(int)$r['player_id'];
    $c=(int)$r['catches'];
    $ro=(int)$r['runouts'];
    $st=(int)$r['stumpings'];

    $conn->query("
    INSERT INTO player_match_statistics
    (player_id, match_id, runs_scored, wickets_taken, catches, runouts, stumpings)
    VALUES ($player,$match_id,0,0,$c,$ro,$st)
    ON DUPLICATE KEY UPDATE
    catches=catches+$c,
    runouts=runouts+$ro,
    stumpings=stumpings+$st
    ");
}
    $conn->commit();

    echo json_encode([
        "success" => true,
        "runs" => $total_runs,
        "balls" => $total_balls
    ]);
    exit;

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
    exit;
}