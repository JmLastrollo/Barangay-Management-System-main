<?php
session_start();
require_once 'db_connect.php';
require_once 'log_audit.php'; // Ensure this file is included

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    try {
        // --- STEP 1: FETCH NAME BEFORE DELETE ---
        $stmtGet = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = :id");
        $stmtGet->execute([':id' => $userId]);
        $staff = $stmtGet->fetch(PDO::FETCH_ASSOC);
        $staffName = $staff ? $staff['first_name'] . ' ' . $staff['last_name'] : 'Unknown Staff';

        // --- STEP 2: DELETE STAFF ---
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $userId]);

        // --- STEP 3: LOGGING ---
        if (isset($_SESSION['user_id'])) {
            // Detailed Log: "Deleted staff account: [Name]"
            $action = "Deleted staff account: " . $staffName;
            logActivity($conn, $_SESSION['user_id'], $action);
        }
        // ------------------------

        header("Location: ../pages/admin/staff_list.php?success=deleted");
        exit();

    } catch (PDOException $e) {
        header("Location: ../pages/admin/staff_list.php?error=delete_failed");
        exit();
    }
}
?>