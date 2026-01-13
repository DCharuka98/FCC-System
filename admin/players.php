<?php
require_once "admin_guard.php";
require_once "../config/db.php";
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$sql = "
SELECT 
    p.player_id,
    p.full_name,
    p.joined_date,
    p.status,
    u.username,
    u.user_id
FROM players p
JOIN user_player up ON p.player_id = up.player_id
JOIN users u ON up.user_id = u.user_id
WHERE 1
";

$params = [];
$types  = "";

if ($search !== '') {
    $sql .= " AND (p.full_name LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if ($statusFilter === 'Active' || $statusFilter === 'Inactive') {
    $sql .= " AND p.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$sql .= " ORDER BY p.full_name";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();


$countResult = $conn->query("
    SELECT 
        COUNT(*) AS total,
        SUM(status = 'Active') AS active,
        SUM(status = 'Inactive') AS inactive
    FROM players
");

$counts = $countResult->fetch_assoc();
?>
<!DOCTYPE html>
<html>
    
<head>
    <title>Manage Players | FCC</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="../assets/images/Logo white.png">
        
    
</head>
<body>

<?php include "partials/admin_nav.php"; ?>

<div class="page-container">
    <h2>MANAGE PLAYERS</h2>

    <form method="get" class="filter-bar">

        <div class="filter-group">
            <input type="text"
                name="search"
                placeholder="🔍 Search name or username"
                value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="filter-group">
            <select name="status">
                <option value="">All Status</option>
                <option value="Active" <?= ($statusFilter === 'Active') ? 'selected' : '' ?>>
                    Active
                </option>
                <option value="Inactive" <?= ($statusFilter === 'Inactive') ? 'selected' : '' ?>>
                    Inactive
                </option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="players.php" class="btn btn-ghost">Reset</a>
        </div>

    </form>

    <div class="stats-grid">

        <div class="stat-card">
            <h3>Total Players</h3>
            <p><?= $counts['total'] ?></p>
        </div>

        <div class="stat-card stat-active">
            <h3>Active Players</h3>
            <p><?= $counts['active'] ?></p>
        </div>

        <div class="stat-card stat-inactive">
            <h3>Inactive Players</h3>
            <p><?= $counts['inactive'] ?></p>
        </div>

    </div>
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
                    <!-- Activate / Deactivate -->
                    <a href="toggle_player.php?player_id=<?= $row['player_id'] ?>"
                    class="btn <?= ($row['status'] === 'Active') ? 'btn-danger' : 'btn-success' ?>">
                        <?= ($row['status'] === 'Active') ? 'Deactivate' : 'Activate' ?>
                    </a>

                    &nbsp;|&nbsp;

                    <!-- Edit -->
                    <a href="edit_player.php?player_id=<?= $row['player_id'] ?>"
                    class="btn btn-edit">
                        Edit
                    </a>

                    &nbsp;|&nbsp;

                    <!-- Reset Password -->
                    <a href="reset_password.php?user_id=<?= $row['user_id'] ?>"
                    class="btn btn-warning">
                        Reset Password
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
