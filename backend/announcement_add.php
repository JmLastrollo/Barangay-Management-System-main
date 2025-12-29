<?php
session_start();
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $details = $_POST["details"];
    $location = $_POST["location"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $filename = "";

    if (!empty($_FILES["photo"]["name"])) {
        $uploadDir = "../uploads/announcements/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filename = time() . "_" . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadDir . $filename);
    }

    try {
        // Status lowercase 'active'
        $sql = "INSERT INTO announcements (title, details, location, date, time, image, status) 
                VALUES (:title, :details, :location, :date, :time, :image, 'active')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title, ':details' => $details, ':location' => $location,
            ':date' => $date, ':time' => $time, ':image' => $filename
        ]);
        $_SESSION['toast'] = ['msg' => 'Announcement added!', 'type' => 'success'];
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
    // FIX: Redirect using ../ 
    // TAMA - Ibabalik sa admin_announcement.php
header("Location: ../pages/admin/admin_announcement.php?success=added");
exit();
}
?>