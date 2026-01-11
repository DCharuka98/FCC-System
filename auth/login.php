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

/* Plain text check (OK for now – will hash later) */
if ($password !== $user['password']) {
    $_SESSION['error'] = "Invalid username or password";
    header("Location: ../index.php");
    exit;
}

/* Normalize role */
$role = strtolower($user['role']);

/* Player must be linked */
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

/* Login success */
$_SESSION['logged_in'] = true;
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['username']  = $user['username'];
$_SESSION['role']      = $role;

/* Redirect by role */
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
