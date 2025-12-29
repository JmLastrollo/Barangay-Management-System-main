<?php
require_once "db_connect.php"; 

header("Content-Type: application/json");

try {
    // Select all non-archived announcements, newest first
    $sql = "SELECT * FROM announcements 
            WHERE status != 'archived' 
            ORDER BY date DESC, time DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($result);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>