<?php
session_start();
require_once "db_connect.php";

// Security Check
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $details = $_POST["details"];
    $location = $_POST["location"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $filename = "";

    // Image Upload Logic
    if (!empty($_FILES["photo"]["name"])) {
        $uploadDir = "../uploads/announcements/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filename = time() . "_" . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadDir . $filename);
    }

    try {
        $sql = "INSERT INTO announcements (title, details, location, date, time, image, status) 
                VALUES (:title, :details, :location, :date, :time, :image, 'active')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $title, ':details' => $details, ':location' => $location,
            ':date' => $date, ':time' => $time, ':image' => $filename
        ]);
        
        // FIX: Using SCOPED session key
        $_SESSION['toast_announcement'] = ['msg' => 'Announcement added successfully!', 'type' => 'success'];
        
    } catch (PDOException $e) {
        $_SESSION['toast_announcement'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
    
    // Redirect
    if ($_SESSION['role'] === 'Staff') {
        header("Location: ../pages/staff/staff_announcement.php");
    } else {
        header("Location: ../pages/admin/admin_announcement.php");
    }
    exit();
}
?>