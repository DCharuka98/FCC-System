<?php
require_once "../admin_guard.php";
require_once "../../config/db.php";

$playing_day_id = (int)($_POST['day'] ?? $_GET['day'] ?? 0);
$players = $_POST['players'] ?? [];

if ($playing_day_id <= 0 || empty($players)) {
    die("Invalid access");
}

/* Fetch selected players */
$ids = implode(",", array_map("intval", $players));
$result = $conn->query("
    SELECT player_id, full_name
    FROM players
    WHERE player_id IN ($ids)
    ORDER BY full_name
");

$selectedPlayers = [];
while ($row = $result->fetch_assoc()) {
    $selectedPlayers[] = $row;
}

/*  SAVE  */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_teams'])) {

    $teamA = $_POST['teamA'] ?? [];
    $teamB = $_POST['teamB'] ?? [];
    $captainA = $_POST['captainA'] ?? null;
    $captainB = $_POST['captainB'] ?? null;

    /* VALIDATION */
    if (empty($teamA) || empty($teamB)) {
        die("Both teams must have players");
    }

    if (!$captainA || !$captainB) {
        die("Both teams must have captains");
    }

    if (!in_array($captainA, $teamA) || !in_array($captainB, $teamB)) {
        die("Captain must belong to their team");
    }

    /* STEP 1: delete team players first */
    $conn->query("
        DELETE FROM playing_day_team_players
        WHERE team_id IN (
            SELECT team_id FROM playing_day_teams
            WHERE playing_day_id = $playing_day_id
        )
    ");

    /* STEP 2: delete teams */
    $conn->query("
        DELETE FROM playing_day_teams
        WHERE playing_day_id = $playing_day_id
    ");

    /* ===== INSERT TEAM A ===== */
    $stmt = $conn->prepare("
        INSERT INTO playing_day_teams
        (playing_day_id, team_name, captain_id)
        VALUES (?, 'A', ?)
    ");
    $stmt->bind_param("ii", $playing_day_id, $captainA);
    $stmt->execute();
    $teamA_id = $stmt->insert_id;

    /* TEAM A PLAYERS */
    $stmtPlayer = $conn->prepare("
        INSERT INTO playing_day_team_players
        (team_id, player_id)
        VALUES (?, ?)
    ");
    foreach ($teamA as $pid) {
        $stmtPlayer->bind_param("ii", $teamA_id, $pid);
        $stmtPlayer->execute();
    }

    /* ===== INSERT TEAM B ===== */
    $stmt = $conn->prepare("
        INSERT INTO playing_day_teams
        (playing_day_id, team_name, captain_id)
        VALUES (?, 'B', ?)
    ");
    $stmt->bind_param("ii", $playing_day_id, $captainB);
    $stmt->execute();
    $teamB_id = $stmt->insert_id;

    /* TEAM B PLAYERS */
    foreach ($teamB as $pid) {
        $stmtPlayer->bind_param("ii", $teamB_id, $pid);
        $stmtPlayer->execute();
    }

    header("Location: create_match.php?day=$playing_day_id");
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Team Division | FCC</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body class="admin-layout">
<?php include "../partials/admin_nav.php"; ?>

<main class="admin-content">
<div class="page-container">

<form method="POST" class="form-card" style="max-width:900px;">
    <h2>Team Division</h2>

    <input type="hidden" name="day" value="<?= $playing_day_id ?>">

    <?php foreach ($players as $pid): ?>
        <input type="hidden" name="players[]" value="<?= $pid ?>">
    <?php endforeach; ?>

    <div class="team-division">

        <!-- AVAILABLE -->
        <div class="team-panel">
            <h3>Available Players</h3>
            <div id="available">
                <?php foreach ($selectedPlayers as $p): ?>
                    <div class="player-row" data-id="<?= $p['player_id'] ?>">
                        <span class="player-name"><?= htmlspecialchars($p['full_name']) ?></span>
                        <div class="player-actions">
                            <button type="button" onclick="addToTeam('A', <?= $p['player_id'] ?>)">A</button>
                            <button type="button" onclick="addToTeam('B', <?= $p['player_id'] ?>)">B</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- TEAM A -->
        <div class="team-panel">
            <h3>Team A</h3>
            <div id="teamA" class="team-list"></div>

            <label>Captain</label>
            <select name="captainA" id="captainA" required>
                <option value="">Select Captain</option>
            </select>
        </div>

        <!-- TEAM B -->
        <div class="team-panel">
            <h3>Team B</h3>
            <div id="teamB" class="team-list"></div>

            <label>Captain</label>
            <select name="captainB" id="captainB" required>
                <option value="">Select Captain</option>
            </select>
        </div>
    </div>

    <button type="submit" name="save_teams" class="btn-primary btn-full">
        Save Teams & Continue
    </button>
</form>

</div>
</main>

<?php include "../partials/admin_footer.php"; ?>

<script>
const availableBox = document.getElementById("available");
const teamABox = document.getElementById("teamA");
const teamBBox = document.getElementById("teamB");

const captainA = document.getElementById("captainA");
const captainB = document.getElementById("captainB");

/* ===== ADD TO TEAM ===== */
function addToTeam(team, playerId) {
    const row = document.querySelector(`.player-row[data-id='${playerId}']`);
    if (!row) return;

    const name = row.querySelector(".player-name").innerText;
    row.remove();

    const targetBox = team === 'A' ? teamABox : teamBBox;
    const captainSelect = team === 'A' ? captainA : captainB;

    const div = document.createElement("div");
    div.className = "player-row";
    div.dataset.id = playerId;

    div.innerHTML = `
        <span class="player-name">${name}</span>
        <div class="player-actions">
            <button type="button" onclick="moveBack(${playerId})">↩</button>
            <button type="button" onclick="switchTeam('${team === 'A' ? 'B' : 'A'}', ${playerId})">
                ${team === 'A' ? 'B' : 'A'}
            </button>
        </div>
        <input type="hidden" name="team${team}[]" value="${playerId}">
    `;

    targetBox.appendChild(div);

    addCaptainOption(captainSelect, playerId, name);
}

function moveBack(playerId) {
    const row = document.querySelector(`.player-row[data-id='${playerId}']`);
    if (!row) return;

    const name = row.querySelector(".player-name").innerText;
    row.remove();

    removeCaptainOption(captainA, playerId);
    removeCaptainOption(captainB, playerId);

    const div = document.createElement("div");
    div.className = "player-row";
    div.dataset.id = playerId;

    div.innerHTML = `
        <span class="player-name">${name}</span>
        <div class="player-actions">
            <button type="button" onclick="addToTeam('A', ${playerId})">A</button>
            <button type="button" onclick="addToTeam('B', ${playerId})">B</button>
        </div>
    `;

    availableBox.appendChild(div);
}

function switchTeam(targetTeam, playerId) {
    moveBack(playerId);
    addToTeam(targetTeam, playerId);
}

function addCaptainOption(select, id, name) {
    if ([...select.options].some(o => o.value == id)) return;

    const opt = document.createElement("option");
    opt.value = id;
    opt.textContent = name;
    select.appendChild(opt);
}

function removeCaptainOption(select, id) {
    [...select.options].forEach(o => {
        if (o.value == id) o.remove();
    });

    if (select.value == id) {
        select.value = "";
    }
}
</script>


</body>
</html>
