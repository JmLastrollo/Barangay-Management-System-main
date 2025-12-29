<?php
// Ensure this points to your correct database connection file
require_once "db_connect.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $details = $_POST["details"];
    $location = $_POST["location"];
    $date = $_POST["date"];
    $time = $_POST["time"];

    $filename = "";

    // Handle File Upload
    if (!empty($_FILES["photo"]["name"])) {
        $filename = time() . "_" . basename($_FILES["photo"]["name"]);
        // Make sure this folder exists
        $target = "../uploads/announcements/" . $filename; 
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target);
    }

    try {
        // Insert query matching your NEW database structure
        $sql = "INSERT INTO announcements (title, details, location, date, time, image, status) 
                VALUES (:title, :details, :location, :date, :time, :image, :status)";
                
        $stmt = $conn->prepare($sql);
        
        $stmt->execute([
            ':title'    => $title,
            ':details'  => $details,
            ':location' => $location,
            ':date'     => $date,
            ':time'     => $time,
            ':image'    => $filename,
            ':status'   => 'active'
        ]);

        header("Location: ../pages/admin/admin_announcement.php?success=added");
        exit;

    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>