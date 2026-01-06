<?php
session_start();
require_once 'db_connect.php';
require_once 'log_audit.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['transaction_type']; // Collection or Expense
    $desc = trim($_POST['description']);
    $amount = floatval($_POST['amount']);
    $date = $_POST['transaction_date'];
    $recorded_by = $_SESSION['user_id'];

    try {
        $sql = "INSERT INTO financial_records (transaction_type, description, amount, transaction_date, recorded_by) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$type, $desc, $amount, $date, $recorded_by]);

        // LOGGING
        if (isset($_SESSION['user_id'])) {
            $action = "Added Finance Record: $type - P" . number_format($amount, 2);
            logActivity($conn, $_SESSION['user_id'], $action);
        }

        $_SESSION['toast'] = ['msg' => 'Transaction record saved successfully!', 'type' => 'success'];

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }

    header("Location: ../pages/admin/finance_management.php");
    exit();
}
?>