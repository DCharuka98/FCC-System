<?php
require_once "admin_guard.php";
require_once "../config/db.php";

$sql = "
SELECT p.player_id, p.full_name, p.joined_date, p.status, u.username
FROM players p
JOIN user_player up ON p.player_id = up.player_id
JOIN users u ON up.user_id = u.user_id
ORDER BY p.full_name
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Players | FCC</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include "partials/admin_nav.php"; ?>

<div class="page-container">
    <h2>MANAGE PLAYERS</h2>
    <div class="panel">
    <table class="players-table">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Username</th>
                <th>Joined</th>
                <th>Status</th>
                <th>Action</th>                
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['joined_date'] ?></td>
                <td class="<?= strtolower($row['status']) ?>">
                    <?= $row['status'] ?>
                </td>
                <td>
                    <a href="toggle_player.php?player_id=<?= $row['player_id'] ?>"
                        class="btn-edit">
                        <?= ($row['status'] === 'Active') ? 'Deactivate' : 'Activate' ?>
                    </a>
                </td>
                <td>
                    <a class="btn-edit"
                    href="edit_player.php?player_id=<?= $row['player_id'] ?>">
                    Edit
                    </a>
                </td>
                
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
</body>
</html>
