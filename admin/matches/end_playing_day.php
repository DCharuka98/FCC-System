<?php
require_once "../admin_guard.php";
require_once "../../config/db.php";

$playing_day_id = (int)($_GET['day'] ?? 0);

if ($playing_day_id <= 0) {
    die("Invalid playing day");
}

$conn->begin_transaction();

try {

$conn->query("
UPDATE playing_days
SET status='COMPLETED'
WHERE playing_day_id=$playing_day_id
");


$seasonRow = $conn->query("
SELECT season_id
FROM playing_days
WHERE playing_day_id=$playing_day_id
")->fetch_assoc();

$season_id = (int)$seasonRow['season_id'];

$pod = $conn->query("
SELECT pms.player_id,
SUM(
    pms.runs_scored +
    (pms.wickets_taken*10) +
    (pms.catches*8) +
    (pms.stumpings*9) +
    (pms.runouts*10)
) AS impact
FROM player_match_statistics pms
JOIN matches m ON m.match_id=pms.match_id
WHERE m.playing_day_id=$playing_day_id
GROUP BY pms.player_id
ORDER BY impact DESC
LIMIT 1
")->fetch_assoc();

if($pod){
    $player=(int)$pod['player_id'];

    $conn->query("
    UPDATE playing_days
    SET player_of_the_day=$player
    WHERE playing_day_id=$playing_day_id
    ");
}

$bat=$conn->query("
SELECT pms.player_id, SUM(pms.runs_scored) runs
FROM player_match_statistics pms
JOIN matches m ON m.match_id=pms.match_id
WHERE m.playing_day_id=$playing_day_id
GROUP BY pms.player_id
");

while($r=$bat->fetch_assoc()){
    $p=(int)$r['player_id'];
    $runs=(int)$r['runs'];

    $conn->query("
    INSERT INTO rankings(player_id,ranking_type,total_points,season_id)
    VALUES($p,'Batting',$runs,$season_id)
    ON DUPLICATE KEY UPDATE total_points=total_points+$runs
    ");
}

$bowl=$conn->query("
SELECT pms.player_id, SUM(pms.wickets_taken) wk
FROM player_match_statistics pms
JOIN matches m ON m.match_id=pms.match_id
WHERE m.playing_day_id=$playing_day_id
GROUP BY pms.player_id
");

while($r=$bowl->fetch_assoc()){
    $p=(int)$r['player_id'];
    $wk=(int)$r['wk'];

    $conn->query("
    INSERT INTO rankings(player_id,ranking_type,total_points,season_id)
    VALUES($p,'Bowling',$wk,$season_id)
    ON DUPLICATE KEY UPDATE total_points=total_points+$wk
    ");
}

while($r=$bowl->fetch_assoc()){
    $p=(int)$r['player_id'];
    $wk=(int)$r['wk'];

    $conn->query("
    INSERT INTO rankings(player_id,ranking_type,total_points,season_id)
    VALUES($p,'Bowling',$wk,$season_id)
    ON DUPLICATE KEY UPDATE total_points=total_points+$wk
    ");
}

$field=$conn->query("
SELECT pms.player_id,
SUM(pms.catches*8 + pms.stumpings*9 + pms.runouts*10) pts
FROM player_match_statistics pms
JOIN matches m ON m.match_id=pms.match_id
WHERE m.playing_day_id=$playing_day_id
GROUP BY pms.player_id
");

while($r=$field->fetch_assoc()){
    $p=(int)$r['player_id'];
    $pts=(int)$r['pts'];

    $conn->query("
    INSERT INTO rankings(player_id,ranking_type,total_points,season_id)
    VALUES($p,'Fielding',$pts,$season_id)
    ON DUPLICATE KEY UPDATE total_points=total_points+$pts
    ");
}

$conn->commit();

header("Location: playing_day_summary.php?day=".$playing_day_id);
exit;

}catch(Exception $e){
    $conn->rollback();
    die($e->getMessage());
}