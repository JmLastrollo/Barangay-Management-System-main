<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['issuance_id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$id || !$status) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        exit;
    }

    try {
        // Logic: Kapag "Received" na, lagyan ng Date Released
        if ($status === 'Received') {
            $sql = "UPDATE issuance SET status = :status, date_released = NOW() WHERE issuance_id = :id";
        } else {
            // Normal status update (Pending, Ready for Pickup, Rejected, Archived)
            $sql = "UPDATE issuance SET status = :status WHERE issuance_id = :id";
        }

        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([':status' => $status, ':id' => $id]);

        if ($result) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update database.']);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>