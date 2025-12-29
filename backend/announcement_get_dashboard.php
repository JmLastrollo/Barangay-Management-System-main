<?php
require_once "db_connect.php"; 

header("Content-Type: application/json");

try {
    // Select active announcements, oldest to newest (for upcoming timeline)
    $sql = "SELECT * FROM announcements 
            WHERE status = 'active' 
            ORDER BY date ASC, time ASC 
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($result);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>