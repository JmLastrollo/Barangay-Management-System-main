<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'] ?? $_POST['announcement_id'];
        
        $stmt = $conn->prepare("DELETE FROM announcements WHERE announcement_id = :id");
        $stmt->execute([':id' => $id]);
        
        // FIX: Scoped Session Key
        $_SESSION['toast_announcement'] = ['msg' => 'Announcement deleted permanently.', 'type' => 'success']; // Using 'success' for green toast, or 'error'/custom class for red

        if ($_SESSION['role'] === 'Staff') {
            header("Location: ../pages/staff/staff_announcement_archive.php");
        } else {
            header("Location: ../pages/admin/admin_announcement_archive.php");
        }
        exit();

    } catch (PDOException $e) {
        $_SESSION['toast_announcement'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
        header("Location: ../pages/staff/staff_announcement.php");
    }
}
?>