<?php
// Gagamitin natin ang db_connect.php (MySQL) sa halip na config.php
require_once "db_connect.php"; 

header("Content-Type: application/json");

try {
    // 1. Kumuha ng ACTIVE announcements, naka-sort ayon sa petsa
    $sql = "SELECT * FROM announcements 
            WHERE status = 'active' 
            ORDER BY date ASC, time ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $output = [];

    foreach ($events as $event) {
        $output[] = [
            "title"    => $event['title'],
            "details"  => $event['details'],
            "location" => $event['location'],
            "date"     => $event['date'], // Format: YYYY-MM-DD
            "time"     => $event['time'], // Format: HH:MM:SS
            // Ayusin ang image path
            "image"    => !empty($event['image']) ? "../uploads/announcements/" . $event['image'] : ""
        ];
    }

    echo json_encode($output);

} catch (PDOException $e) {
    // Kapag may error, mag-return ng empty array para di masira ang JS
    echo json_encode([]);
}
?>