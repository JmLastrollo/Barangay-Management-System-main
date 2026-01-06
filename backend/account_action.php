<?php
session_start();
require_once 'db_connect.php';
require_once 'log_audit.php'; // 1. INCLUDE LOG AUDIT

// --- SECURITY CHECK ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../login.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    
    $userId = $_POST['user_id'];
    $action = $_POST['action'];

    try {
        // A. KUNIN ANG PANGALAN NG RESIDENT (Para sa Logs)
        // Check muna sa resident_profiles table
        $stmtGet = $conn->prepare("SELECT first_name, last_name FROM resident_profiles WHERE user_id = :id");
        $stmtGet->execute([':id' => $userId]);
        $resProfile = $stmtGet->fetch(PDO::FETCH_ASSOC);
        
        $residentName = $resProfile 
            ? $resProfile['first_name'] . ' ' . $resProfile['last_name'] 
            : 'Unknown Applicant';

        if ($action == 'approve') {
            // Update Users Table
            $stmt = $conn->prepare("UPDATE users SET status = 'Active' WHERE user_id = :id");
            $stmt->execute([':id' => $userId]);
            
            // Update Resident Profile
            $stmtProfile = $conn->prepare("UPDATE resident_profiles SET status = 'Active' WHERE user_id = :id");
            $stmtProfile->execute([':id' => $userId]);

            // LOGGING
            if (isset($_SESSION['user_id'])) {
                $logMessage = "Approved resident account: $residentName";
                logActivity($conn, $_SESSION['user_id'], $logMessage);
            }

            $_SESSION['toast'] = "Account successfully approved!";
            $_SESSION['toast_type'] = "success";

        } elseif ($action == 'decline') {
            // Update Users Table (Rejected)
            $stmt = $conn->prepare("UPDATE users SET status = 'Rejected' WHERE user_id = :id");
            $stmt->execute([':id' => $userId]);

            // Update Resident Profile (Rejected)
            $stmtProfile = $conn->prepare("UPDATE resident_profiles SET status = 'Rejected' WHERE user_id = :id");
            $stmtProfile->execute([':id' => $userId]);

            // LOGGING
            if (isset($_SESSION['user_id'])) {
                $logMessage = "Rejected resident account: $residentName";
                logActivity($conn, $_SESSION['user_id'], $logMessage);
            }

            $_SESSION['toast'] = "Account has been rejected.";
            $_SESSION['toast_type'] = "danger"; 
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