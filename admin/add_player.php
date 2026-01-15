<?php
require_once "admin_guard.php";
require_once "../config/db.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username   = trim($_POST['username']);
    $password   = $_POST['password'];
    $full_name  = trim($_POST['full_name']);
    $joined     = $_POST['joined_date'];
    $status     = "Active";

    if ($username === "" || $password === "" || $full_name === "" || $joined === "") {
        $error = "All fields are required";
    } else {

        // 🔐 Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Start transaction
            $conn->begin_transaction();

            // 1️⃣ Insert into users table
            $stmt1 = $conn->prepare(
                "INSERT INTO users (username, password, role)
                 VALUES (?, ?, 'Player')"
            );
            $stmt1->bind_param("ss", $username, $hashedPassword);
            $stmt1->execute();

            $user_id = $conn->insert_id;

            // 2️⃣ Insert into players table
            $stmt2 = $conn->prepare(
                "INSERT INTO players (full_name, joined_date, status)
                 VALUES (?, ?, ?)"
            );
            $stmt2->bind_param("sss", $full_name, $joined, $status);
            $stmt2->execute();

            $player_id = $conn->insert_id;

            // 3️⃣ Link user ↔ player
            $stmt3 = $conn->prepare(
                "INSERT INTO user_player (user_id, player_id)
                 VALUES (?, ?)"
            );
            $stmt3->bind_param("ii", $user_id, $player_id);
            $stmt3->execute();

            // Commit
            $conn->commit();

            // Redirect to manage players
            header("Location: players.php");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error adding player. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Player | FCC</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="../assets/images/Logo white.png">
</head>
<body>

<?php include "partials/admin_nav.php"; ?>
<main class="admin-content">
<div class="page-container">

    <div class="form-wrapper">

        <div class="panel form-panel">

            <h2 style="text-align:center;">Add Player</h2>

            <?php if ($error): ?>
                <p style="color:#ff6b6b; text-align:center;"><?= $error ?></p>
            <?php endif; ?>

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
</main>               
<?php include "partials/admin_footer.php"; ?>
</body>
</html>
