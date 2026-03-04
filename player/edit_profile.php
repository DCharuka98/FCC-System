<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'player') {
    header("Location: ../index.php");
    exit();
}

require_once "../config/db.php";

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
SELECT u.username, p.full_name
FROM users u
JOIN user_player up ON u.user_id = up.user_id
JOIN players p ON p.player_id = up.player_id
WHERE u.user_id=?
");

$stmt->bind_param("i",$user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

$message="";

if($_SERVER['REQUEST_METHOD']=="POST"){

$username = trim($_POST['username']);
$name = trim($_POST['full_name']);

$conn->begin_transaction();

try{

$stmt1 = $conn->prepare("UPDATE users SET username=? WHERE user_id=?");
$stmt1->bind_param("si",$username,$user_id);
$stmt1->execute();

$stmt2 = $conn->prepare("
UPDATE players p
JOIN user_player up ON p.player_id=up.player_id
SET p.full_name=?
WHERE up.user_id=?
");

$stmt2->bind_param("si",$name,$user_id);
$stmt2->execute();

$conn->commit();

$_SESSION['username']=$username;

$message="Profile updated successfully";

}
catch(Exception $e){

$conn->rollback();
$message="Error updating profile";

}
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Profile | FCC</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="icon" href="../assets/images/Logo white.png">
</head>

<body class="admin-layout">
<?php include "../partials/navbar.php"; ?>
<main class="admin-content">

<div class="form-card" style="max-width:450px;margin:60px auto;">

<h2>✏️ Edit Profile</h2>

<?php if($message): ?>
<p style="color:#38bdf8"><?= $message ?></p>
<?php endif; ?>

    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="full_name"
        value="<?= htmlspecialchars($data['full_name']) ?>" required>

        <label>Username</label>
        <input type="text" name="username"
        value="<?= htmlspecialchars($data['username']) ?>" required>

        <button type="submit">Update Profile</button>
    </form>
</div>
</main>
</body>
</html>