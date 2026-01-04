<?php
require_once 'db_connect.php';

// Fetch ALL active announcements
$sql = "SELECT * FROM announcements WHERE status = 'active' ORDER BY date DESC, time DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
?>