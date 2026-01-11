<?php
require_once "admin_guard.php";
require_once "../config/db.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Player | FCC</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include "partials/admin_nav.php"; ?>

<div class="page-container">
    <div class="form-wrapper">
        <div class="form-panel-wrapper">
            <h2>ADD PLAYER</h2>
            <div class="panel form-panel">
            <form method="post">
                <label>Username</label>
                <input type="text" name="username" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <label>Full Name</label>
                <input type="text" name="full_name" required>

                <label>Joined Date</label>
                <input type="date" name="joined_date" required>

                <button type="submit">Add Player</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
