<?php
require_once "admin_guard.php";
require_once "../config/db.php";

$player_id = (int) ($_GET['player_id'] ?? 0);

if ($player_id <= 0) {
    die("Invalid player");
}

$sql = "
SELECT 
    p.player_id,
    p.full_name,
    p.joined_date,
    u.username
FROM players p
JOIN user_player up ON p.player_id = up.player_id
JOIN users u ON up.user_id = u.user_id
WHERE p.player_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Player not found");
}

$player = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Player | FCC</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="../assets/images/Logo white.png">
</head>
<body>

<?php include "partials/admin_nav.php"; ?>

<div class="page-container">

    <h2 style="text-align:center;">Edit Player</h2>

    <div class="form-wrapper">
        <div class="panel form-panel">

            <form method="post" action="update_player.php">
                <input type="hidden" name="player_id" value="<?= $player['player_id'] ?>">

                <label>Username</label>
                <input type="text" name="username"
                       value="<?= htmlspecialchars($player['username']) ?>" required>

                <label>Full Name</label>
                <input type="text" name="full_name"
                       value="<?= htmlspecialchars($player['full_name']) ?>" required>

                <label>Joined Date</label>
                <input type="date" name="joined_date"
                       value="<?= $player['joined_date'] ?>" required>

                <button type="submit">Update Player</button>
            </form>

        </div>
    </div>

</div>

</body>
</html>
