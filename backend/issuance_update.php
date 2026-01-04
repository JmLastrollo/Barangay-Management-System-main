<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['issuance_id']; 
    $status = $_POST['status'];

    // Default Query
    $sql = "UPDATE issuance SET status = :status WHERE issuance_id = :id";
    $params = [':status' => $status, ':id' => $id];

    if ($status == 'Ready for Pickup' && $payment_status == 'Paid') {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+2 days'));
        $sql = "UPDATE issuance SET status = :status, print_token = :token, print_expiry = :expiry, download_count = 0 WHERE issuance_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':status' => $status, ':token' => $token, ':expiry' => $expiry, ':id' => $id]);
    } else {
        $sql = "UPDATE issuance SET status = :status WHERE issuance_id = :id";
    }

    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute($params)) {
        if ($status === 'Archived' && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
             header("Location: ../pages/admin/admin_issuance.php?archived=true");
             exit();
        }
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>