<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['issuance_id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$id || !$status) {
        echo json_encode(['status' => 'error', 'message' => 'Missing fields.']);
        exit;
    }

    try {
        if ($status === 'Received') {
            $sql = "UPDATE issuance SET status = :status, date_released = NOW() WHERE issuance_id = :id";
        } else {
            $sql = "UPDATE issuance SET status = :status WHERE issuance_id = :id";
        }

        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([':status' => $status, ':id' => $id]);

        if ($result) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}
echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
?>