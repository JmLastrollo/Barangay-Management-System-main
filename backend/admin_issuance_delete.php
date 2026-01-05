<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['issuance_id'] ?? null;

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Missing issuance_id']);
        exit;
    }

    try {
        $sql = "DELETE FROM issuance WHERE issuance_id = :id";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([':id' => $id]);

        if($result){
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>