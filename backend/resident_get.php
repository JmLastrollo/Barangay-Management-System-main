<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    
    $sql = "SELECT * FROM resident_profiles 
            WHERE status != 'Pending' 
            AND user_id IS NOT NULL 
            ORDER BY last_name ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>