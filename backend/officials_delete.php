<?php
session_start();
require_once "db_connect.php";

if (isset($_POST["id"])) {
    $id = $_POST["id"];
    
    try {
        // FIX 1: Gamitin ang 'official_id' (base sa DB mo) at 'Archived' (Capital A)
        $sql = "UPDATE barangay_officials SET status = 'Archived' WHERE official_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $_SESSION['toast'] = ['msg' => 'Official archived successfully!', 'type' => 'success'];
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error archiving official: ' . $e->getMessage(), 'type' => 'error'];
    }
    
    // FIX 2: IMPORTANT - ../ lang dapat, hindi ../../
    header("Location: ../pages/admin/admin_officials.php");
    exit();
}
?>