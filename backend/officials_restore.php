<?php
session_start();
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["id"])) {
    $id = $_POST["id"];
    
    try {
        // Ibalik sa Active status
        $stmt = $conn->prepare("UPDATE barangay_officials SET status = 'Active' WHERE official_id = :id");
        $stmt->execute([':id' => $id]);
        
        $_SESSION['toast'] = ['msg' => 'Official restored successfully!', 'type' => 'success'];
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error restoring official: ' . $e->getMessage(), 'type' => 'error'];
    }
    
    header("Location: ../pages/admin/admin_officials_archive.php");
    exit();
}
?>