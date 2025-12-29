<?php
session_start();
require_once "db_connect.php";

if (isset($_POST["id"])) {
    $id = $_POST["id"];
    try {
        // Set status to 'archived' (lowercase based on DB)
        $sql = "UPDATE announcements SET status = 'archived' WHERE announcement_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $_SESSION['toast'] = ['msg' => 'Announcement archived!', 'type' => 'success'];
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
    // FIX: Redirect using ../ 
    header("Location: ../pages/admin/admin_announcement.php");
    exit();
}
?>