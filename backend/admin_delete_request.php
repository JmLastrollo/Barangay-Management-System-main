<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['toast'] = ['msg' => 'Unauthorized access', 'type' => 'error'];
    header("Location: ../../pages/admin/manage_issuances.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $issuance_id = $_POST['issuance_id'];
    
    try {
        // Only allow deletion of Expired or Rejected requests
        $sql = "DELETE FROM document_issuances 
                WHERE issuance_id = ? AND status IN ('Expired', 'Rejected')";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$issuance_id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['toast'] = ['msg' => 'Request deleted successfully', 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['msg' => 'Unable to delete request', 'type' => 'error'];
        }
        
    } catch (PDOException $e) {
            } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
    
    header("Location: ../../pages/admin/manage_issuances.php");
    exit();
}
?>