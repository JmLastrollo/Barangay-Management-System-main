<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    $_SESSION['toast'] = ['msg' => 'Unauthorized access', 'type' => 'error'];
    header("Location: ../../pages/staff/manage_issuances.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $issuance_id = $_POST['issuance_id'];
    $staff_id = $_SESSION['user_id'];
    
    try {
        // Update status to Payment Verified
        $sql = "UPDATE document_issuances 
                SET status = 'Payment Verified',
                    approved_at = NOW(),
                    processed_by = ?
                WHERE issuance_id = ? AND status = 'Pending'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$staff_id, $issuance_id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['toast'] = ['msg' => 'Payment verified successfully!', 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['msg' => 'Unable to verify payment', 'type' => 'error'];
        }
        
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
    
    header("Location: ../../pages/staff/manage_issuances.php");
    exit();
}
?>