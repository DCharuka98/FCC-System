<?php
require_once "admin_guard.php";
require_once "../config/db.php";

/* =====================
   HANDLE POST (UPDATE PASSWORD)
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = (int) ($_POST['user_id'] ?? 0);
    $new     = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($user_id <= 0) {
        $_SESSION['error'] = "Invalid request";
        header("Location: players.php");
        exit;
    }

    if ($new !== $confirm) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: reset_password.php?user_id=$user_id");
        exit;
    }

    if (strlen($new) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters";
        header("Location: reset_password.php?user_id=$user_id");
        exit;
    }

    // Hash password
    $hashed = password_hash($new, PASSWORD_DEFAULT);

    // Update DB
    $stmt = $conn->prepare(
        "UPDATE users SET password = ? WHERE user_id = ?"
    );
    $stmt->bind_param("si", $hashed, $user_id);
    $stmt->execute();

    $_SESSION['success'] = "Password updated successfully";
    header("Location: players.php");
    exit;
}

/* =====================
   HANDLE GET (SHOW FORM)
===================== */
$user_id = (int) ($_GET['user_id'] ?? 0);

if ($user_id <= 0) {
    $_SESSION['error'] = "Invalid request";
    header("Location: players.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | FCC</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include "partials/admin_nav.php"; ?>

<div class="page-container">

    <!-- ALERTS -->
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="form-wrapper">
        <div class="panel form-panel">

            <h2 style="text-align:center;">Reset Password</h2>

            <form method="post">
                <input type="hidden" name="user_id" value="<?= $user_id ?>">

                <label>New Password</label>
                <input type="password" name="new_password" required>

                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>

                <button type="submit">Update Password</button>
            </form>

        </div>
    </div>

</div>

</body>
</html>
