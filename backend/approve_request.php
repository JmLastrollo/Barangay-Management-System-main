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
        // For Cash payment, directly approve and mark as Payment Verified
        $sql = "UPDATE document_issuances 
                SET status = 'Payment Verified',
                    approved_at = NOW(),
                    processed_by = ?
                WHERE issuance_id = ? AND status = 'Pending' AND payment_method = 'Cash'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$staff_id, $issuance_id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['toast'] = ['msg' => 'Request approved successfully!', 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['msg' => 'Unable to approve request', 'type' => 'error'];
        }
        
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
    
    header("Location: ../../pages/staff/manage_issuances.php");
    exit();
}
?>