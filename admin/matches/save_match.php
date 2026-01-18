<?php
require_once "../admin_guard.php";
require_once "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$playing_day_id = (int)($_POST['day'] ?? 0);
$overs          = (int)($_POST['overs'] ?? 0);
$balls          = (int)($_POST['balls_per_over'] ?? 0);
$bat_first      = $_POST['bat_first'] ?? '';

if (
    $playing_day_id <= 0 ||
    $overs <= 0 ||
    $balls <= 0 ||
    !in_array($bat_first, ['A','B'])
) {
    die("Invalid match data");
}

$day = $conn->query("
    SELECT venue, season_id
    FROM playing_days
    WHERE playing_day_id = $playing_day_id
")->fetch_assoc();

$teams = [];
$res = $conn->query("
    SELECT team_id, team_name
    FROM playing_day_teams
    WHERE playing_day_id = $playing_day_id
");
while ($r = $res->fetch_assoc()) {
    $teams[$r['team_name']] = $r['team_id'];
}

$batting_team_id = $teams[$bat_first];
$bowling_team_id = ($bat_first === 'A') ? $teams['B'] : $teams['A'];


$stmt = $conn->prepare("
    INSERT INTO matches
    (playing_day_id, venue, season_id, overs, balls_per_over,
     batting_team_id, bowling_team_id, innings, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, '1st', 'Created')
");

$stmt->bind_param(
    "isiiiii",
    $playing_day_id,
    $day['venue'],
    $day['season_id'],
    $overs,
    $balls,
    $batting_team_id,
    $bowling_team_id
);
$stmt->execute();

$match_id = $stmt->insert_id;

$stmt2 = $conn->prepare("
    INSERT INTO innings
    (match_id, innings_number, batting_team_id, bowling_team_id,
     total_runs, wickets, overs_completed, target)
    VALUES (?, 1, ?, ?, 0, 0, 0, NULL)
");

$stmt2->bind_param(
    "iii",
    $match_id,
    $batting_team_id,
    $bowling_team_id
);  

$stmt2->execute();
$innings_id = $stmt2->insert_id;

header("Location: match_scoring.php?match=$match_id&innings=$innings_id");
exit;
