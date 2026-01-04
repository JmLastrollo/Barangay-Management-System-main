<?php
session_start();
require_once 'db_connect.php';
require_once 'log_audit.php';

// 1. Log Activity (Kung nakalogin pa)
if (isset($_SESSION['user_id'])) {
    try {
        logActivity($conn, $_SESSION['user_id'], "Logged out from the system");
    } catch (Exception $e) {
        // Ignore errors
    }
}

// 2. Clear all session variables & Destroy
session_unset();
session_destroy();

// 3. START A NEW SESSION (Para lang sa Toast Message)
session_start();
$_SESSION['toast'] = "You have successfully logged out.";
$_SESSION['toast_type'] = "success";

// 4. Redirect sa Login
header("Location: ../login.php");
exit();
?>