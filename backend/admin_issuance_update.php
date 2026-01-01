<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tanggapin ang data galing sa form/ajax
    $id = $_POST['issuance_id']; 
    $status = $_POST['status'];

    // Update Query para sa bagong table structure
    $sql = "UPDATE issuance SET status = :status WHERE issuance_id = :id";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([':status' => $status, ':id' => $id])) {
        // Kung galing sa Archive button (Form submit), mag-redirect pabalik
        if ($status === 'Archived' && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
             header("Location: ../pages/admin/admin_issuance.php?archived=true");
             exit();
        }
        // Kung galing sa Edit Modal (Ajax), mag-return ng JSON
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>