<?php
session_start();
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["id"])) {
    $id = $_POST["id"];
    
    try {
        // UPDATED: Set status to 'Inactive' instead of 'Archived'
        $stmt = $conn->prepare("UPDATE barangay_officials SET status = 'Inactive' WHERE official_id = :id");
        $stmt->execute([':id' => $id]);
        
        $_SESSION['toast'] = ['msg' => 'Official moved to Inactive list.', 'type' => 'success'];
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
    
    header("Location: ../pages/admin/admin_officials.php");
    exit();
}
?>