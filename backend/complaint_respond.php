<?php
session_start();
require_once 'db_connect.php';
require_once 'log_audit.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complaint_id'], $_POST['feedback'])) {
    $id = $_POST['complaint_id'];
    $feedback = trim($_POST['feedback']);
    $status = $_POST['status']; // e.g., 'Active', 'Settled'

    try {
        // Update query
        $stmt = $conn->prepare("UPDATE complaints SET admin_feedback = ?, status = ? WHERE complaint_id = ?");
        $stmt->execute([$feedback, $status, $id]);

        // Log Activity
        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['role']; // Admin or Staff
            $action = "$role responded to Complaint #$id";
            logActivity($conn, $_SESSION['user_id'], $action);
        }

        $_SESSION['toast'] = ['msg' => 'Response sent successfully!', 'type' => 'success'];
        
        // Redirect back (Check kung Admin or Staff)
        if ($_SESSION['role'] == 'Staff') {
            header("Location: ../pages/staff/staff_complaints.php"); // Adjust kung iba filename mo
        } else {
            header("Location: ../pages/admin/admin_rec_complaints.php");
        }
        exit();

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
        header("Location: ../pages/admin/admin_rec_complaints.php");
        exit();
    }
}
?>