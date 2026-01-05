<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT 
                i.issuance_id, 
                i.document_type, 
                i.purpose, 
                i.date_requested, 
                i.status, 
                i.reference_no,
                r.first_name, 
                r.middle_name,
                r.last_name,
                r.purok
            FROM issuance i
            JOIN resident_profiles r ON i.resident_id = r.resident_id
            WHERE i.status != 'Archived' 
            ORDER BY i.date_requested DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>