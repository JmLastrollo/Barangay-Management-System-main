<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['issuance_id'];
        
        // Delete related payments first (Foreign Key constraint)
        $stmt = $conn->prepare("DELETE FROM payments WHERE issuance_id = :id");
        $stmt->execute([':id' => $id]);

        // Delete issuance record
        $stmt = $conn->prepare("DELETE FROM issuance WHERE issuance_id = :id");
        $stmt->execute([':id' => $id]);
        
        // Redirect pabalik sa archive page
        header("Location: ../pages/admin/admin_issuance_archive.php");
        exit();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>