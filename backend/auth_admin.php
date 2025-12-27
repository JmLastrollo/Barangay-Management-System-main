<?php
session_start();

// If no session → redirect to admin login
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: /Barangay-Management-System/admin_login.php");
    exit;
}

// If wrong role → redirect to main index
if ($_SESSION['role'] !== 'Barangay Staff') {
    $_SESSION['toast'] = [
        "msg" => "You are not authorized to access that page.",
        "type" => "error"
    ];

    header("Location: /Barangay-Management-System/index.php");
    exit;
}
?>
