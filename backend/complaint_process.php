<?php
session_start();
require_once 'db_connect.php';
require_once 'log_audit.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complaint_id'])) {
    
    $id = $_POST['complaint_id'];

    if (isset($_POST['action']) && $_POST['action'] == 'archive') {
        try {
            $stmt = $conn->prepare("UPDATE complaints SET status = 'Archived' WHERE complaint_id = ?");
            $stmt->execute([$id]);
            
            if(isset($_SESSION['user_id'])) {
                logActivity($conn, $_SESSION['user_id'], "Archived Complaint #$id");
            }

            // TOAST
            $_SESSION['toast'] = ['msg' => 'Complaint moved to archive.', 'type' => 'warning'];
            header("Location: ../pages/admin/admin_rec_complaints.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
            header("Location: ../pages/admin/admin_rec_complaints.php");
            exit();
        }
    }
}
?>