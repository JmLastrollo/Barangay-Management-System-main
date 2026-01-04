<?php
require_once 'db_connect.php'; // Siguraduhin na MySQL ang gamit
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['issuance_id'];
        $status = $_POST['status'];
        
        $sql = "UPDATE issuance SET status = :status";
        
        // Kapag "Received" na, lagyan ng Date Released
        if ($status === 'Received') {
            $sql .= ", date_released = NOW()";
        }
        
        // Kapag "Archived", ilipat lang ang status (nasa logic na ito)
        
        $sql .= " WHERE issuance_id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([':status' => $status, ':id' => $id]);
        
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>