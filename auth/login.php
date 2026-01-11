<?php
session_start();
require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Invalid username or password";
    header("Location: ../index.php");
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Invalid username or password";
    header("Location: ../index.php");
    exit;
}

/* Check if Player is active */
if ($user['role'] === 'Player') {
    $check = $conn->prepare("
        SELECT p.status 
        FROM players p
        JOIN user_player up ON p.player_id = up.player_id
        WHERE up.user_id = ?
    ");
    $check->bind_param("i", $user['user_id']);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();

    if ($res['status'] !== 'Active') {
        $_SESSION['error'] = "Your account is inactive. Contact admin.";
        header("Location: ../index.php");
        exit;
    }
}

$role = strtolower($user['role']);


if ($role === 'player') {
    $check = $conn->prepare(
        "SELECT 1 FROM user_player WHERE user_id = ?"
    );
    $check->bind_param("i", $user['user_id']);
    $check->execute();

    if ($check->get_result()->num_rows !== 1) {
        $_SESSION['error'] = "Player profile not linked. Contact admin.";
        header("Location: ../index.php");
        exit;
    }
}

$_SESSION['logged_in'] = true;
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['username']  = $user['username'];
$_SESSION['role']      = $role;


switch ($role) {
    case 'admin':
        header("Location: ../admin/dashboard.php");
        break;

    case 'scorer':
        header("Location: ../scorer/dashboard.php");
        break;

    case 'player':
        header("Location: ../player/dashboard.php");
        break;

    default:
        $_SESSION['error'] = "Unauthorized role";
        header("Location: ../index.php");
}
exit;
