<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'scorer') {
    header("Location: ../index.php");
    exit;
}