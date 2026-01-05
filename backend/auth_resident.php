<?php
// Check kung wala pang session bago mag-start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check kung naka-login
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: ../../login.php"); 
    exit;
}

// Check kung Resident ang role
if ($_SESSION['role'] !== 'Resident') {
    $_SESSION['toast'] = ["msg" => "You are not authorized to access that page.", "type" => "error"];
    header("Location: ../admin/admin_dashboard.php");
    exit;
}
?>