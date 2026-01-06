<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: ../../login.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $desc = $_POST['description'];
    $amt  = $_POST['amount'];
    $staff = $_SESSION['user_id'];

    $sql = "INSERT INTO financial_records (transaction_type, description, amount, transaction_date, recorded_by) 
            VALUES (:type, :desc, :amt, NOW(), :staff)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':type'=>$type, ':desc'=>$desc, ':amt'=>$amt, ':staff'=>$staff]);

    header("Location: ../pages/staff/staff_finance.php");
    exit();
}
?>