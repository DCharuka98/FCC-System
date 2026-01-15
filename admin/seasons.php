<?php
require_once "admin_guard.php";
require_once "../config/db.php";

$result = $conn->query("
    SELECT 
        season_id,
        season_name,
        start_date,
        end_date,
        CASE 
            WHEN CURDATE() BETWEEN start_date AND end_date 
            THEN 'Active'
            ELSE 'Inactive'
        END AS status
    FROM seasons
    ORDER BY start_date DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Seasons | FCC</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="../assets/images/Logo white.png">
</head>
<body class="admin-layout">

<?php include "partials/admin_nav.php"; ?>
<main class="admin-content">
<div class="page-container">

    <h2>MANAGE SEASONS</h2>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="panel">
        <table class="players-table">
            <thead>
                <tr>
                    <th>Season</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['season_name']) ?></td>
                    <td><?= $row['start_date'] ?></td>
                    <td><?= $row['end_date'] ?></td>
                    <td class="<?= strtolower($row['status']) ?>">
                        <?= $row['status'] ?>
                    </td>
                    <td>
                        <a href="edit_season.php?season_id=<?= $row['season_id'] ?>"
                        class="btn btn-edit">
                            Edit
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <br>
    <a href="add_season.php" class="btn btn-warning">➕ Add New Season</a>

</div>
</main>
<?php include "partials/admin_footer.php"; ?>
</body>
</html>
