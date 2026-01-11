<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>
<h1>Admin Dashboard</h1>
<a href="../auth/logout.php">Logout</a>
