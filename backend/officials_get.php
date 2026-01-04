<?php
require_once "db_connect.php";

header("Content-Type: application/json");

// Check kung may specific ID na hinihingi (pang-Edit modal)
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $conn->prepare("SELECT * FROM barangay_officials WHERE official_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $official = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($official);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Kung walang ID, kunin lahat ng ACTIVE (pang-display sa table kung naka-AJAX ka)
try {
    $stmt = $conn->prepare("SELECT * FROM barangay_officials WHERE status = 'Active'");
    $stmt->execute();
    $officials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($officials);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>