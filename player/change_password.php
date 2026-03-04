<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'player') {
    header("Location: ../index.php");
    exit();
}

require_once "../config/db.php";

$user_id = $_SESSION['user_id'];
$message = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id=?");
    $stmt->bind_param("i",$user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if(!password_verify($current,$result['password'])){
        $message = "Current password incorrect";
    }
    elseif($new !== $confirm){
        $message = "New passwords do not match";
    }
    else{

        $newHash = password_hash($new,PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $stmt->bind_param("si",$newHash,$user_id);
        $stmt->execute();

        $message = "Password updated successfully";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Change Password | FCC</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="icon" href="../assets/images/Logo white.png">
</head>

<body class="admin-layout">
<?php include "../partials/navbar.php"; ?>
<main class="admin-content">
<div class="form-card" style="max-width:450px;margin:60px auto;">
    <h2>🔑 Change Password</h2>
    <?php if($message): ?>
    <p style="color:#38bdf8"><?= $message ?></p>
    <?php endif; ?>

<form method="POST">
    <label>Current Password</label>
    <input type="password" name="current_password" required>

    <label>New Password</label>
    <input type="password" name="new_password" required>

    <label>Confirm New Password</label>
    <input type="password" name="confirm_password" required>

    <button type="submit">Update Password</button>
</form>
</div>
</main>
</body>
</html>