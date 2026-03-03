<?php
session_start();

function allowRoles($roles) {
    if (!isset($_SESSION['logged_in']) || 
        !in_array($_SESSION['role'], $roles)) {
        header("Location: ../index.php");
        exit;
    }
}