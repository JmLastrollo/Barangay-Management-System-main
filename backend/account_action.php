<?php
session_start();
require_once 'db_connect.php';

// --- 1. SECURITY CHECK (NEW) ---
// Bawal i-access ang file na ito kung hindi naka-login o hindi Admin/Staff
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../login.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    
    $userId = $_POST['user_id'];
    $action = $_POST['action'];

    try {
        if ($action == 'approve') {
            // A. Update Users Table
            $stmt = $conn->prepare("UPDATE users SET status = 'Active' WHERE user_id = :id");
            $stmt->execute([':id' => $userId]);
            
            // B. Update Resident Profile
            $stmtProfile = $conn->prepare("UPDATE resident_profiles SET status = 'Active' WHERE user_id = :id");
            $stmtProfile->execute([':id' => $userId]);

            $_SESSION['toast'] = "Account successfully approved!";
            $_SESSION['toast_type'] = "success";

        } elseif ($action == 'decline') {
            // A. Update Users Table (Rejected)
            $stmt = $conn->prepare("UPDATE users SET status = 'Rejected' WHERE user_id = :id");
            $stmt->execute([':id' => $userId]);

            // B. Update Resident Profile (Rejected) - ADDED THIS FOR CONSISTENCY
            $stmtProfile = $conn->prepare("UPDATE resident_profiles SET status = 'Rejected' WHERE user_id = :id");
            $stmtProfile->execute([':id' => $userId]);

            $_SESSION['toast'] = "Account has been rejected.";
            $_SESSION['toast_type'] = "danger"; // Ginawang 'danger' para automatic red sa Bootstrap
        }

    } catch (PDOException $e) {
        $_SESSION['toast'] = "Error: " . $e->getMessage();
        $_SESSION['toast_type'] = "danger";
    }
}

// Redirect pabalik sa approval page
header("Location: ../pages/admin/account_approval.php");
exit();
?>