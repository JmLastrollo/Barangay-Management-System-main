<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: ../../resident_login.php");
    exit;
}

if ($_SESSION['role'] !== 'Resident') {
    $_SESSION['toast'] = ["msg" => "You are not authorized to access that page.", "type" => "error"];
    header("Location: ../admin/admin_dashboard.php");
    exit;
}
?>
