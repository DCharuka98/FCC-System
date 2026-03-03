<?php
require_once "../../role_guard.php";
allowRoles(['admin','scorer']);
require_once "../../config/db.php";

$match_id = (int)($_GET['match'] ?? 0);
$innings_id = (int)($_GET['innings'] ?? 0);
if ($match_id <= 0 || $innings_id <= 0) die("Invalid request");

$match = $conn->query("
SELECT 
i.batting_team_id,
i.bowling_team_id,
m.overs,
m.balls_per_over
FROM innings i
JOIN matches m ON m.match_id=i.match_id
WHERE i.innings_id=$innings_id
")->fetch_assoc();

$batting_team_id = $match['batting_team_id'];
$bowling_team_id = $match['bowling_team_id'];

$batPlayers = [];
$res = $conn->query("
    SELECT p.player_id, p.full_name
    FROM playing_day_team_players t
    JOIN players p ON p.player_id = t.player_id
    WHERE t.team_id = $batting_team_id
");
while ($r = $res->fetch_assoc()) $batPlayers[] = $r;

$bowlPlayers = [];
$res = $conn->query("
    SELECT p.player_id, p.full_name
    FROM playing_day_team_players t
    JOIN players p ON p.player_id = t.player_id
    WHERE t.team_id = $bowling_team_id
");
while ($r = $res->fetch_assoc()) $bowlPlayers[] = $r;

$state = $conn->query("
    SELECT COUNT(*) balls,
           SUM(runs + IFNULL(extra_runs,0)) runs,
           SUM(is_wicket) wickets
    FROM balls
    WHERE match_id = $match_id AND innings_id = $innings_id
")->fetch_assoc();

$inn = $conn->query("
SELECT innings_number, target
FROM innings
WHERE innings_id=$innings_id
")->fetch_assoc();

$innings_number = (int)$inn['innings_number'];
$target = (int)$inn['target'];
$isSecond = $innings_number === 2;

$outBatsmen = [];
$res = $conn->query("
    SELECT DISTINCT batsman_id
    FROM balls
    WHERE match_id = $match_id
      AND innings_id = $innings_id
      AND is_wicket = 1
");

while ($r = $res->fetch_assoc()) {
    $outBatsmen[] = (int)$r['batsman_id'];
}

$total_runs    = (int)($state['runs'] ?? 0);
$total_wickets = (int)($state['wickets'] ?? 0);
$total_balls   = (int)($state['balls'] ?? 0);
$extras        = 0;

$finalBatting = [];

$res = $conn->query("
    SELECT
        b.batsman_id,
        p.full_name,
        SUM(b.runs) AS runs,
        COUNT(
            CASE
                WHEN b.extra_type IS NULL
                     OR b.extra_type='NO_BALL'
                THEN 1
            END
        ) AS balls,
        SUM(CASE WHEN b.runs = 4 THEN 1 ELSE 0 END) AS fours,
        SUM(CASE WHEN b.runs = 6 THEN 1 ELSE 0 END) AS sixes
    FROM balls b
    JOIN players p ON p.player_id = b.batsman_id
    WHERE b.match_id = $match_id
      AND b.innings_id = $innings_id
    GROUP BY b.batsman_id
");

while ($row = $res->fetch_assoc()) {
    $finalBatting[] = $row;
}

$teamRow = $conn->query("
    SELECT t.team_name, t.captain_id, p.full_name AS captain_name
    FROM playing_day_teams t
    LEFT JOIN players p ON p.player_id = t.captain_id
    WHERE t.team_id = $batting_team_id
")->fetch_assoc();

$battingTeamName = $teamRow['team_name'] ?? 'Team';
$captainName = $teamRow['captain_name'] ?? '';

?>
<!DOCTYPE html>
<html>
<head>
<title>Live Scoring | FCC</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="icon" href="../../assets/images/Logo white.png">  
<style>

.scoring-grid {
    display: grid;
    grid-template-columns: 320px 1fr 320px;
    gap: 30px;
    align-items: start;
}

.panel {
    background: linear-gradient(180deg, #141414, #0e0e0e);
    padding: 24px;
    border-radius: 18px;
    box-shadow: 0 20px 40px rgba(0,0,0,.6);
}

.panel-title {
    font-size: 18px;
    font-weight: 700;
    color: #ff4d4d;
    margin-bottom: 18px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: .6px;
}

.setup-panel {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.setup-section {
    padding: 14px;
    border-radius: 14px;
    background: rgba(255,255,255,0.04);
}

.section-title {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: #fbbf24;
    margin-bottom: 12px;
}
.setup-panel.locked {
    opacity: 0.35;
    pointer-events: none;
}
.field label {
    font-size: 12px;
    color: #9ca3af;
    margin-bottom: 6px;
    display: block;
}

select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: none;
    background: #1b1b1b;
    color: #fff;
    font-size: 14px;
}

.start-btn {
    margin-top: auto;
    padding: 14px;
    font-size: 15px;
    font-weight: 700;
    border-radius: 14px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #000;
    cursor: pointer;
    transition: transform .15s ease, box-shadow .15s ease;
}

.start-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 25px rgba(34,197,94,.45);
}

.center-panel {
    text-align: center;
}

#score {
    font-size: 52px;
    font-weight: 800;
    margin: 10px 0;
}

#overs {
    font-size: 14px;
    color: #9ca3af;
}

.keypad {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 14px;
    margin-top: 22px;
}

.keypad button {
    padding: 18px 0;
    font-size: 18px;
    font-weight: 700;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    transition: transform .12s ease, box-shadow .12s ease;
}

.keypad button:hover {
    transform: translateY(-1px);
}

.runs button {
    background: #1f2933;
    color: #fff;
}

.extras button {
    background: #374151;
    color: #fbbf24;
}

.wicket-btn {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    grid-column: span 3;
}

.match-stats {
    font-size: 14px;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    border-radius: 10px;
    background: rgba(255,255,255,0.03);
    margin-bottom: 10px;
}

.stat-label {
    color: #9ca3af;
}

.stat-value {
    font-weight: 600;
}

.stat-value.bowler {
    color: #38bdf8;
}

.last-man-badge {
    margin-top: 16px;
    padding: 10px;
    text-align: center;
    border-radius: 12px;
    background: linear-gradient(135deg, #f59e0b, #f97316);
    font-size: 13px;
    font-weight: 700;
    color: #000;
}

.hidden {
    display: none;
}

.modal {    
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999;
    overflow-y: auto;
    padding: 20px;
}

.modal-box {
    background: #111;
    padding: 24px;
    border-radius: 16px;
    width: 560px; 
    max-width: 92vw;
    text-align: center;
    max-height: 90vh;
    overflow-y: auto;
}
.modal-box::-webkit-scrollbar {
    width: 6px;
}
.modal-box::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,.2);
    border-radius: 6px;
}

.modal h3 {
    margin-bottom: 16px;
    color: #ff4d4d;
}

.confirm-btn {
    margin-top: 16px;
    padding: 12px;
    border-radius: 12px;
    background: #22c55e;
    font-weight: 700;
}
.hidden {
    display: none !important;
}

.score-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 13px;
}

.score-table th {
    text-align: left;
    padding: 6px 6px;
    border-bottom: 1px solid rgba(255,255,255,0.15);
    color: #cbd5e1;
    font-weight: 600;
    font-size: 12px;
    letter-spacing: .4px;
}

.score-table td {
    padding: 6px 6px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.score-section h4 {
    margin-bottom: 8px;
    font-size: 14px;
    color: #f1f5f9;
    letter-spacing: .4px;
}

.score-table th:nth-child(n+2),
.score-table td:nth-child(n+2) {
    text-align: right;
}

.score-table th:first-child,
.score-table td:first-child {
    text-align: left;
}

.score-table td {
    padding: 8px 6px;
}

.score-table th:first-child,
.score-table td:first-child {
    width: 55%;
}
.score-section {
    margin-top: 20px;
    padding: 14px;
    border-radius: 12px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06); 
}

.innings-summary {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 12px;
    background: rgba(255,255,255,0.04);
    font-size: 14px;
    margin-bottom: 14px;
}

.innings-summary b {
    color: #22c55e;
}

#inningsTitle {
    text-transform: uppercase;
}

.innings-target {
    margin-top: 14px;
    padding: 12px;
    border-radius: 12px;
    background: rgba(34,197,94,.12);
    font-weight: 700;
}
</style>
</head>

<body class="admin-layout">
<?php include "../../partials/navbar.php"; ?> 
<main class="admin-content">
<div class="page-container">

<div class="scoring-grid">
<div class="panel setup-panel" id="setupPanel">
    <h3 class="panel-title">Match Setup</h3>

    <div class="setup-section">
        <h4 class="section-title">Batting</h4>

        <div class="field">
            <label>Striker</label>
            <select id="striker">
                <?php foreach($batPlayers as $p): ?>
                <option value="<?= $p['player_id'] ?>"><?= $p['full_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Non-Striker</label>
            <select id="nonStriker">
                <?php foreach($batPlayers as $p): ?>
                <option value="<?= $p['player_id'] ?>"><?= $p['full_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="setup-section">
        <h4 class="section-title">Bowling</h4>

        <div class="field">
            <label>Opening Bowler</label>
            <select id="bowler">
                <?php foreach($bowlPlayers as $p): ?>
                <option value="<?= $p['player_id'] ?>"><?= $p['full_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button class="start-btn" onclick="startScoring()">▶ Start Scoring</button>
</div>

<div class="panel center-panel">

    <h1 id="score">0-0</h1>
    <p class="muted">
        Overs: <span id="overs">0.0</span> / <?= $match['overs'] ?> |
        CRR: <span id="uiCRR">0.00</span>        
    </p>

    <p id="targetBox" style="color:#22c55e;font-weight:700;"></p>
    <div class="stat-row">
        <span class="stat-label">Extras</span>
        <span class="stat-value" id="uiExtras">0</span>
    </div>

    <div class="stat-row">
        <span class="stat-label">Partnership</span>
        <span class="stat-value" id="uiPartnership">0 (0)</span>
    </div>

<div class="keypad runs">
<button onclick="run(0)">0</button>
<button onclick="run(1)">1</button>
<button onclick="run(2)">2</button>
<button onclick="run(3)">3</button>
<button onclick="run(4)">4</button>
<button onclick="run(6)">6</button>
</div>

<div class="keypad extras">
<button onclick="askExtra('WIDE')">Wide</button>
<button onclick="askExtra('NO_BALL')">No Ball</button>
<button onclick="askExtra('BYE')">Bye</button>
</div>

<div id="extraRunModal" class="hidden modal">
    <div class="modal-box">
        <h3 id="extraTitle">Extra Runs</h3>

        <select id="extraRunSelect">
            <option value="1">1 Run</option>
            <option value="2">2 Runs</option>
            <option value="3">3 Runs</option>
            <option value="4">4 Runs</option>
            <option value="5">5 Runs</option>
            <option value="6">6 Runs</option>
        </select>

        <button class="confirm-btn" onclick="confirmExtra()">Confirm</button>
    </div>
</div>

<div class="keypad">
<button class="wicket-btn" onclick="wicket()">WICKET</button>
</div>
</div>

    <div id="selectionModal" class="hidden modal">
        <div class="modal-box">
            <h3 id="selectionTitle"></h3>
            <select id="selectionSelect"></select>
            <button class="confirm-btn" onclick="confirmSelection()">Confirm</button>
        </div>
    </div>

    <div id="dismissalBox" class="hidden modal">
    <div class="modal-box">
        <h3>Dismissal Type</h3>

        <select id="dismissalType">
            <option value="BOWLED">Bowled</option>
            <option value="CAUGHT">Caught</option>
            <option value="STUMPED">Stumped</option>
            <option value="RUN_OUT">Run Out</option>
        </select>

        <div id="assistPlayerBox" class="hidden">
            <label>Fielder / Keeper</label>
            <select id="assistPlayer"></select>
        </div>

        <button class="confirm-btn" onclick="confirmWicket()">Confirm Wicket</button>
    </div>
</div>

<div class="panel match-stats">
    <h3 class="panel-title">Match Stats</h3>

    <div id="liveBattingCard"></div>

    <hr style="opacity:.15;margin:12px 0;">

    <div id="liveBowlerCard"></div>
</div>
</div>
</div>
</main>

<script>
const batPlayers = <?= json_encode($batPlayers) ?>;
const bowlPlayers = <?= json_encode($bowlPlayers) ?>;
const BALLS_PER_OVER = <?= (int)$match['balls_per_over'] ?>;
const TOTAL_OVERS = <?= (int)$match['overs'] ?>;
const ALLOW_LAST_MAN = batPlayers.length < bowlPlayers.length;
const MAX_WICKETS = batPlayers.length - 1;
const dismissalTypeEl = document.getElementById("dismissalType");
const assistBox = document.getElementById("assistPlayerBox");
const assistPlayer = document.getElementById("assistPlayer");
const battingTeamName = <?= json_encode($battingTeamName) ?>;
const captainName = <?= json_encode($captainName) ?>;
const IS_SECOND = <?= $isSecond ? 'true':'false' ?>;
const TARGET = <?= $target ?: 0 ?>;

dismissalTypeEl.addEventListener("change", () => {
    const type = dismissalTypeEl.value;

    if (type === "CAUGHT" || type === "STUMPED" || type === "RUN_OUT") {
        assistBox.classList.remove("hidden");
        loadFielders();
    } else {
        assistBox.classList.add("hidden");
        assistPlayer.innerHTML = "";
    }
});

function loadFielders() {
    assistPlayer.innerHTML = "";
    bowlPlayers.forEach(p => {
        assistPlayer.innerHTML +=
          `<option value="${p.player_id}">${p.full_name}</option>`;
    });
}

if (IS_SECOND && TARGET) {
    document.getElementById("targetBox").innerText = "Target: " + TARGET;
}

let runs = <?= $total_runs ?>;
let wickets = <?= $total_wickets ?>;
let balls = <?= $total_balls ?>;
let scoringStarted = false;
let inningsOver = false;
let outBatsmen = <?= json_encode($outBatsmen) ?>;
let waitingForNextBatsman = false;
let waitingForNextBowler = false;
let lastBatsmanOnly = false;
let ballHistory = [];
let extras = <?= (int)$extras ?>;
let batsmanStats = {};
let bowlerStats = {};
let partnershipRuns = 0;
let partnershipBalls = 0;

const finalBatting = <?= json_encode($finalBatting) ?>;
function aliveBatsmenCount() {
    return batPlayers.filter(p => !outBatsmen.includes(p.player_id)).length;
}

function hasNextBatsman() {
    return aliveBatsmenCount() > 1;
}

function startScoring() {
    if (striker.value === nonStriker.value) {
        alert("Striker and Non-Striker cannot be same");
        return;
    }

    scoringStarted = true;

    initBatsman(striker.value);
    initBatsman(nonStriker.value);
    initBowler(bowler.value);

    document.querySelectorAll(".keypad button").forEach(b => b.disabled = false);
    document.getElementById("setupPanel").classList.add("locked");

    updateUI();
}

function updateUI() {

    score.innerText = `${runs}-${wickets}`;

    overs.innerText =
        `${Math.floor(balls / BALLS_PER_OVER)}.${balls % BALLS_PER_OVER}`;

    uiExtras.innerText = extras;
    uiCRR.innerText = currentRunRate();
    uiPartnership.innerText = partnershipStats();

    renderLiveBattingCard();
    renderLiveBowlerCard();
}

function ball() {
    balls++;

    if (balls >= TOTAL_OVERS * BALLS_PER_OVER) {
        endInnings("Overs Completed");
        return;
    }

    if (balls % BALLS_PER_OVER === 0) {
        waitingForNextBowler = true;
        document.querySelectorAll(".keypad button").forEach(b => b.disabled = true);
        showNextBowler();
        return;
    }

    updateUI();
}

function run(r) {
    if (inningsOver || !scoringStarted || waitingForNextBatsman || waitingForNextBowler) return;

    initBatsman(striker.value);
    initBowler(bowler.value);

    batsmanStats[striker.value].runs += r;
    batsmanStats[striker.value].balls += 1;

    if (r === 4) batsmanStats[striker.value].fours++;
    if (r === 6) batsmanStats[striker.value].sixes++;

    bowlerStats[bowler.value].balls++;
    bowlerStats[bowler.value].runs += r;

    partnershipRuns += r;
    partnershipBalls++;

    runs += r;

    saveBall({ runs: r });

    if (IS_SECOND && TARGET && runs >= TARGET) {
        updateUI();
        endInnings("Target chased");
        return;
    }

    if (r % 2 === 1 && !lastBatsmanOnly) {
        swapStrike();
    }

    ball();
}


function wide(extraRuns = 1) {
    if (inningsOver || !scoringStarted || waitingForNextBatsman || waitingForNextBowler) return;

    initBowler(bowler.value);
    
    runs += extraRuns;
    extras += extraRuns;

    bowlerStats[bowler.value].runs += extraRuns;

    saveBall({
        runs: 0,
        extraType: "WIDE",
        extraRuns: extraRuns
    });

    updateUI();
}

function noBall(batRuns = 0) {
    if (inningsOver || !scoringStarted || waitingForNextBatsman || waitingForNextBowler) return;

    initBatsman(striker.value);
    initBowler(bowler.value);

    runs += (1 + batRuns);
    extras += 1;

    batsmanStats[striker.value].runs += batRuns;
    if (batRuns > 0) batsmanStats[striker.value].balls += 1;

    bowlerStats[bowler.value].runs += (1 + batRuns);

    partnershipRuns += batRuns;

    saveBall({
        runs: batRuns,
        extraType: "NO_BALL",
        extraRuns: 1
    });

    if (batRuns % 2 === 1 && !lastBatsmanOnly) swapStrike();

    updateUI();
}

function bye(extraRuns = 1) {
    if (inningsOver || !scoringStarted || waitingForNextBatsman || waitingForNextBowler) return;

    initBowler(bowler.value);

    runs += extraRuns;
    extras += extraRuns;
    partnershipRuns += extraRuns;
    partnershipBalls += 1;

    bowlerStats[bowler.value].balls++;
    bowlerStats[bowler.value].runs += extraRuns;

    saveBall({
        runs: 0,
        extraType: "BYE",
        extraRuns: extraRuns
    });

    if (extraRuns % 2 === 1 && !lastBatsmanOnly) swapStrike();

    ball();
}
let pendingExtraType = null;

function askExtra(type) {
    if (waitingForNextBowler || waitingForNextBatsman) {
    alert("Complete over change before scoring extras");
    return;
}
    pendingExtraType = type;

    const title = document.getElementById("extraTitle");
    const select = document.getElementById("extraRunSelect");

    select.innerHTML = "";

    if (type === "WIDE") {
        title.innerText = "Total runs on this wide (including wide)";
        [1,2,3,4,5].forEach(r =>
            select.innerHTML += `<option value="${r}">${r} Run${r>1?'s':''}</option>`
        );
    }

    if (type === "NO_BALL") {
        title.innerText = "Batsman runs off the bat (excluding no-ball run)";
        [0,1,2,3,4,6].forEach(r =>
            select.innerHTML += `<option value="${r}">${r} Run${r!==1?'s':''}</option>`
        );
    }

    if (type === "BYE") {
        title.innerText = "Bye runs taken";
        [1,2,3,4].forEach(r =>
            select.innerHTML += `<option value="${r}">${r} Run${r>1?'s':''}</option>`
        );
    }

    document.getElementById("extraRunModal").classList.remove("hidden");
}

function confirmExtra() {
    if (inningsOver || !scoringStarted || waitingForNextBowler || waitingForNextBatsman) {
    alert("Select next bowler / batsman before continuing");
    return;
    }
    const val = parseInt(document.getElementById("extraRunSelect").value);
    document.getElementById("extraRunModal").classList.add("hidden");

    if (pendingExtraType === "WIDE") {       
        runs += val;
        extras += val;

        initBowler(bowler.value);         
        bowlerStats[bowler.value].runs += val;  

        saveBall({
            runs: 0,
            extraType: "WIDE",
            extraRuns: val
        });

        updateUI();
    }

    if (pendingExtraType === "NO_BALL") {
        runs += (1 + val);
        extras += 1;

        initBowler(bowler.value);             
        bowlerStats[bowler.value].runs += (1 + val);  

        saveBall({
            runs: val,
            extraType: "NO_BALL",
            extraRuns: 1
        });

        if (val % 2 === 1 && !lastBatsmanOnly) swapStrike();
        batsmanStats[striker.value].runs += val;
        if (val > 0) batsmanStats[striker.value].balls += 1;

        updateUI();

    }

    if (pendingExtraType === "BYE") {
        runs += val;
        extras += val;

        saveBall({
            runs: 0,
            extraType: "BYE",
            extraRuns: val
        });

        if (val % 2 === 1 && !lastBatsmanOnly) swapStrike();

        ball();
    }

    pendingExtraType = null;
}

function swapStrike() {
    if (lastBatsmanOnly) return;
    [striker.value, nonStriker.value] = [nonStriker.value, striker.value];
}

function initBatsman(playerId) {
    if (!batsmanStats[playerId]) {
        batsmanStats[playerId] = {
            runs: 0,
            balls: 0,
            fours: 0,
            sixes: 0
        };
    }
}

function initBowler(playerId) {
    if (!bowlerStats[playerId]) {
        bowlerStats[playerId] = {
            balls: 0,
            runs: 0,
            wickets: 0
        };
    }
}
function wicket() {
    if (inningsOver || !scoringStarted || waitingForNextBatsman || waitingForNextBowler) return;

    document.getElementById("dismissalBox").classList.remove("hidden");
    partnershipRuns = 0;
    partnershipBalls = 0;
}

function confirmWicket() {

    if (inningsOver) return;

    const dismissalType = dismissalTypeEl.value;
    const assistId = assistPlayer.value || null;

    document.getElementById("dismissalBox").classList.add("hidden");

    const outPlayer = striker.value;

    if (!outBatsmen.includes(outPlayer)) {
        outBatsmen.push(outPlayer);
    }

    wickets++;

    saveBall({
        runs: 0,
        isWicket: 1,
        dismissalType: dismissalType,
        dismissalBy: assistId
    });

    initBowler(bowler.value);
    bowlerStats[bowler.value].balls++;

    if (dismissalType !== "RUN_OUT") {
        bowlerStats[bowler.value].wickets++;
    }
    ball();

    const alive = aliveBatsmenCount();

    if (balls % BALLS_PER_OVER === 0 && balls < TOTAL_OVERS * BALLS_PER_OVER) {
        waitingForNextBowler = true;
        document.querySelectorAll(".keypad button").forEach(b => b.disabled = true);
    }

    if (alive === 0) {
        endInnings("All Out");
        return;
    }

    if (!ALLOW_LAST_MAN && alive === 1) {
        endInnings("All Out");
        return;
    }

    if (ALLOW_LAST_MAN && alive === 1) {
        lastBatsmanOnly = true;

        const remaining = batPlayers.find(
            p => !outBatsmen.includes(p.player_id)
        );

        striker.value = remaining.player_id;
        return;
    }

    waitingForNextBatsman = true;
    document.querySelectorAll(".keypad button").forEach(b => b.disabled = true);
    showNextBatsman();
}

function showNextBatsman() {
    selectionTitle.innerText = "Select Next Batsman";
    selectionSelect.innerHTML = "";

    const currentNonStriker = nonStriker.value;

    batPlayers.forEach(p => {
        if (
            !outBatsmen.includes(p.player_id) &&
            p.player_id !== currentNonStriker
        ) {
            selectionSelect.innerHTML +=
              `<option value="${p.player_id}">${p.full_name}</option>`;
        }
    });

    selectionModal.classList.remove("hidden");
    selectionType = "BATSMAN";
}

function showNextBowler() {
    selectionTitle.innerText = "Select Next Bowler";
    selectionSelect.innerHTML = "";

    bowlPlayers.forEach(p => {
        if (p.player_id !== bowler.value) {
            selectionSelect.innerHTML +=
              `<option value="${p.player_id}">${p.full_name}</option>`;
        }
    });

    selectionModal.classList.remove("hidden");
    selectionType = "BOWLER";
}

let selectionType = null;

function confirmSelection() {
    if (selectionType === "BATSMAN") {
        striker.value = selectionSelect.value;
        waitingForNextBatsman = false;
    }

    if (selectionType === "BOWLER") {
        bowler.value = selectionSelect.value;
        waitingForNextBowler = false;

        swapStrike();
    }

    selectionModal.classList.add("hidden");
    document.querySelectorAll(".keypad button").forEach(b => b.disabled = false);
    updateUI();
}

function saveBall(data) {

    const currentBall = balls + 1; 

    const overNo = Math.floor(balls / BALLS_PER_OVER) + 1;
    const ballNo = (balls % BALLS_PER_OVER) + 1;

    return fetch("save_ball.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            match_id: <?= $match_id ?>,
            innings_id: <?= $innings_id ?>,
            over_no: overNo,
            ball_no: ballNo,
            batsman_id: striker.value,
            bowler_id: bowler.value,
            runs: data.runs ?? 0,
            extra_type: data.extraType ?? null,
            extra_runs: data.extraRuns ?? 0,
            is_wicket: data.isWicket ?? 0,
            dismissal_type: data.dismissalType ?? null,
            dismissal_by_player: data.dismissalBy ?? null
        })
    });
}

function renderLiveBattingCard() {

    let html = "";

    const strikerPlayer = batPlayers.find(p => p.player_id == striker.value);
    const nonStrikerPlayer = batPlayers.find(p => p.player_id == nonStriker.value);

    [strikerPlayer, nonStrikerPlayer].forEach(p => {

        if (!p) return;

        const s = batsmanStats[p.player_id] || { runs: 0, balls: 0 };

        html += `
            <div class="stat-row">
                <span>${p.full_name} <b style="color:#22c55e">*</b></span>
                <span>${s.runs} (${s.balls})</span>
            </div>
        `;
    });

    document.getElementById("liveBattingCard").innerHTML = html;
}

function renderLiveBowlerCard() {

    const bowlerPlayer = bowlPlayers.find(p => p.player_id == bowler.value);

    if (!bowlerStats[bowler.value]) {
        document.getElementById("liveBowlerCard").innerHTML =
            `<div class='stat-row'>
                <span>${bowlerPlayer?.full_name || "Bowler"}</span>
                <span>0.0 - 0 - 0</span>
            </div>`;
        return;
    }

    const b = bowlerStats[bowler.value];

    const oversText =
        `${Math.floor(b.balls / BALLS_PER_OVER)}.${b.balls % BALLS_PER_OVER}`;

    document.getElementById("liveBowlerCard").innerHTML = `
        <div class="stat-row">
            <span>${bowlerPlayer?.full_name || "Bowler"}</span>
            <span>${oversText} - ${b.runs} - ${b.wickets}</span>
        </div>
    `;
}
function oversBowled() {
    return balls === 0 ? 0 : balls / BALLS_PER_OVER;
}

function currentRunRate() {
    if (balls === 0) return "0.00";

    return ((runs * BALLS_PER_OVER) / balls).toFixed(2);
}

function projectedScore() {
    return Math.round(currentRunRate() * TOTAL_OVERS);
}
function partnershipStats() {
    return `${partnershipRuns} (${partnershipBalls})`;
}

function endInnings(reason) {
    if (inningsOver) return;

    inningsOver = true;
    scoringStarted = false;

    const finalOvers =
        `${Math.floor(balls / BALLS_PER_OVER)}.${balls % BALLS_PER_OVER}`;

    document.getElementById("finalScore").innerText = `${runs}-${wickets}`;
    document.getElementById("finalOvers").innerText = finalOvers;
    document.getElementById("finalCRR").innerText = currentRunRate();

    document.getElementById("inningsTitle").innerText =
    `${IS_SECOND ? "2nd Innings" : "1st Innings"} – ${battingTeamName} (C: ${captainName})`;

    renderBattingScorecard();

    if (!IS_SECOND) {
        const target = runs + 1;
        document.getElementById("targetScore").innerText = target;
        document.querySelector(".innings-target").style.display = "block";
    } else {
        document.querySelector(".innings-target").style.display = "none";
    }

    document.getElementById("inningsEndModal").classList.remove("hidden");

    document.querySelectorAll(".keypad button, #setupPanel select, #setupPanel button")
    .forEach(e => e.disabled = true);

    fetch("end_innings.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            innings_id: <?= $innings_id ?>,
            total_runs: runs,
            wickets: wickets,
            balls: balls,
            overs: TOTAL_OVERS,
            target: (!IS_SECOND ? (runs + 1) : null),
            reason: reason
        })
    })
        .then(r => r.json())
    .then(res => {

        console.log("end innings response:", res);

        if (IS_SECOND) {
            window.location.href = "match_summary.php?match=<?= $match_id ?>";
        }
    })
    .catch(e => console.error(e));
}

function renderBattingScorecard() {

    let html = "";

    Object.keys(batsmanStats).forEach(id => {

        const p = batPlayers.find(x => x.player_id == id);
        const b = batsmanStats[id];

        const notOut =
            !outBatsmen.includes(parseInt(id)) ? " *" : "";

        html += `
            <tr>
                <td>${p?.full_name || "Batsman"}${notOut}</td>
                <td>${b.runs}</td>
                <td>${b.balls}</td>
                <td>${b.fours}</td>
                <td>${b.sixes}</td>
            </tr>
        `;
    });

    document.getElementById("battingScorecard").innerHTML = html;

    renderBowlingScorecard();
}

function renderBowlingScorecard() {

    let html = "";

    Object.keys(bowlerStats).forEach(id => {

        const p = bowlPlayers.find(x => x.player_id == id);
        const b = bowlerStats[id];

        const oversText =
            `${Math.floor(b.balls / BALLS_PER_OVER)}.${b.balls % BALLS_PER_OVER}`;

        html += `
            <tr>
                <td>${p?.full_name || "Bowler"}</td>
                <td>${oversText}</td>
                <td>${b.runs}</td>
                <td>${b.wickets}</td>
            </tr>
        `;
    });

    document.getElementById("bowlingScorecard").innerHTML = html;
}

function closeInningsModal() {
    document.getElementById("inningsEndModal").classList.add("hidden");
}

function startSecondInnings() {

    fetch("start_second_innings.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            match_id: <?= $match_id ?>,
            first_innings_id: <?= $innings_id ?>
        })
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) {
            alert("Failed to start 2nd innings");
            return;
        }

        window.location.href =
            `match_scoring.php?match=<?= $match_id ?>&innings=${res.innings_id}`;
    });
}

updateUI();
</script>
<div id="inningsEndModal" class="hidden modal">
    <div class="modal-box">
        <h3 id="inningsTitle"></h3>

       <div class="innings-summary">
            <span>Score: <b id="finalScore"></b></span>
            <span>Overs: <b id="finalOvers"></b></span>
            <span>RR: <b id="finalCRR"></b></span>
        </div>

        <div class="score-section">
            <h4>Batting</h4>

            <table class="score-table">
            <thead>
            <tr>
            <th>Batsman</th>
            <th>R</th>
            <th>B</th>
            <th>4s</th>
            <th>6s</th>
            </tr>
            </thead>
            <tbody id="battingScorecard"></tbody>
            </table>
            </div>

            <div class="score-section">
                <h4>Bowling</h4>

                <table class="score-table">
                <thead>
                <tr>
                <th>Bowler</th>
                <th>O</th>
                <th>R</th>
                <th>W</th>
                </tr>
                </thead>
                <tbody id="bowlingScorecard"></tbody>
                </table>
                </div>
        
        <div class="innings-target">
            Target: <b id="targetScore"></b>
        </div>

        <?php if(!$isSecond): ?>
            <button id="startSecondBtn" class="confirm-btn" onclick="startSecondInnings()">
                Start 2nd Innings
            </button>
            <?php else: ?>
            <button class="confirm-btn" onclick="window.location='match_summary.php?match=<?= $match_id ?>'">
                View Match Summary
            </button>
        <?php endif; ?>
    </div>
</div>
</body>
</html>