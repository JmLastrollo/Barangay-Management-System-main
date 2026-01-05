<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'Staff') {
    
    if ($_SESSION['role'] === 'Admin') {
        header("Location: ../admin/admin_dashboard.php");
        exit();
    }
    
    if ($_SESSION['role'] === 'Resident') {
        header("Location: ../resident/resident_dashboard.php");
        exit();
    }

    header("Location: ../../backend/logout.php");
    exit();
}
?>