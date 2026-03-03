<?php
require_once "../../role_guard.php";
allowRoles(['admin','scorer']);
require_once "../../config/db.php";

$playing_day_id = (int)($_GET['day'] ?? 0);
if ($playing_day_id <= 0) {
    die("Invalid playing day");
}

$result = $conn->query("
    SELECT player_id, full_name
    FROM players
    WHERE status = 'Active'
    ORDER BY full_name
");

$players = [];
while ($row = $result->fetch_assoc()) {
    $players[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Divide Teams | FCC</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="icon" href="../../assets/images/Logo white.png">
</head>

<body class="admin-layout">

<?php include "../../partials/navbar.php"; ?>

<main class="admin-content">
<div class="page-container">

<div class="form-card">
    <h2>Divide Teams</h2>

    <input
        type="text"
        id="playerSearch"
        placeholder="Type player name..."
        autocomplete="off"
    >

    <div id="searchResults"></div>

    <h3 style="margin-top:20px;">Selected Players</h3>

    <div id="selectedPlayers" class="selected-list"></div>

    <form method="POST" action="team_division.php">
        <input type="hidden" name="day" value="<?= $playing_day_id ?>">
        <div id="selectedInputs"></div>

        <button type="submit" class="btn-primary btn-full">
            Proceed to Team Division
        </button>
    </form>
</div>

</div>
</main>

<?php include "../partials/admin_footer.php"; ?>

<script>
const allPlayers = <?= json_encode($players) ?>;
const selectedPlayers = new Map();

const searchInput = document.getElementById("playerSearch");
const searchResults = document.getElementById("searchResults");
const selectedBox = document.getElementById("selectedPlayers");
const selectedInputs = document.getElementById("selectedInputs");

searchInput.addEventListener("input", () => {
    const q = searchInput.value.toLowerCase().trim();
    searchResults.innerHTML = "";

    if (q.length < 2) return;

    const matches = allPlayers.filter(p =>
        p.full_name.toLowerCase().includes(q) &&
        !selectedPlayers.has(p.player_id)
    );

    matches.forEach(p => {
        const row = document.createElement("div");
        row.className = "selected-player";
        row.innerHTML = `
            <span>${p.full_name}</span>
            <button type="button"
                class="btn-success"
                onclick="addPlayer(${p.player_id}, '${p.full_name}')">
                Add
            </button>
        `;
        searchResults.appendChild(row);
    });
});

function addPlayer(id, name) {
    selectedPlayers.set(id, name);
    renderSelected();
    searchResults.innerHTML = "";
    searchInput.value = "";
}

function removePlayer(id) {
    selectedPlayers.delete(id);
    renderSelected();
}

function renderSelected() {
    selectedBox.innerHTML = "";
    selectedInputs.innerHTML = "";

    selectedPlayers.forEach((name, id) => {
        const row = document.createElement("div");
        row.className = "selected-player";
        row.innerHTML = `
            <span>${name}</span>
            <button type="button"
                class="btn-remove"
                onclick="removePlayer(${id})">
                Remove
            </button>
        `;
        selectedBox.appendChild(row);

        const input = document.createElement("input");
        input.type = "hidden";
        input.name = "players[]";
        input.value = id;
        selectedInputs.appendChild(input);
    });
}
</script>

</body>
</html>
