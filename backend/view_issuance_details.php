<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    $issuance_id = $_GET['id'];
    
    $sql = "SELECT 
                di.*,
                CONCAT(rp.first_name, ' ', rp.middle_name, ' ', rp.last_name) AS resident_name,
                rp.contact_number,
                rp.address,
                rp.date_of_birth,
                u.email,
                CONCAT(staff.first_name, ' ', staff.last_name) AS processed_by_name
            FROM document_issuances di
            INNER JOIN resident_profiles rp ON di.resident_id = rp.resident_id
            INNER JOIN users u ON rp.user_id = u.user_id
            LEFT JOIN users staff ON di.processed_by = staff.user_id
            WHERE di.issuance_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$issuance_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Request not found']);
    }
}
?>