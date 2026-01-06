<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'Staff') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$id = $_POST['issuance_id'];
$status = $_POST['status'];
$remarks = $_POST['remarks'] ?? null;
$staff_id = $_SESSION['user_id'];

try {
    $sql = "UPDATE document_issuances SET status = :status, admin_remarks = :remarks, processed_by = :staff";
    
    // Set Timestamps based on status
    if ($status === 'Ready for Pickup') {
        $sql .= ", approved_at = NOW()";
    } elseif ($status === 'Released') {
        $sql .= ", released_at = NOW()";
    }

    $sql .= " WHERE issuance_id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':status' => $status,
        ':remarks' => $remarks,
        ':staff' => $staff_id,
        ':id' => $id
    ]);

    echo json_encode(['status' => 'success']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>