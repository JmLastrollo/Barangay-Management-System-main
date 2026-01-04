<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    try {
        $stmt = $conn->prepare("SELECT image FROM announcements WHERE announcement_id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['image'])) {
            $imagePath = "../uploads/announcements/" . $row['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $delStmt = $conn->prepare("DELETE FROM announcements WHERE announcement_id = :id");
        $delStmt->execute([':id' => $id]);

        // Success redirect
        header("Location: ../pages/admin/admin_announcement_archive.php?success=deleted");
        exit();

    } catch (PDOException $e) {
        echo "Error deleting record: " . $e->getMessage();
    }
} else {
    header("Location: ../pages/admin/admin_announcement_archive.php");
    exit();
}
?>