<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'Admin') {
    if ($_SESSION['role'] === 'Staff') {
        header("Location: ../staff/staff_dashboard.php");
        exit();
    }
    
    if ($_SESSION['role'] === 'Resident') {
        header("Location: ../resident/resident_dashboard.php");
        exit();
    }

    $_SESSION['toast'] = ["msg" => "Access Denied: Admins Only.", "type" => "error"];
    header("Location: ../../index.php");
    exit();
}
?>