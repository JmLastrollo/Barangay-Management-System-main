<?php
session_start();
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["id"])) {
    $id = $_POST["id"];
    
    try {
        // 1. Kunin muna ang image filename bago i-delete ang record
        //    (Para mabura rin natin ang file sa folder)
        $stmt_img = $conn->prepare("SELECT image FROM barangay_officials WHERE official_id = :id LIMIT 1");
        $stmt_img->execute([':id' => $id]);
        $official = $stmt_img->fetch(PDO::FETCH_ASSOC);

        // 2. I-delete na ang record sa database
        $stmt = $conn->prepare("DELETE FROM barangay_officials WHERE official_id = :id");
        $stmt->execute([':id' => $id]);
        
        // 3. Kung successful ang delete sa DB, burahin din ang actual image file
        if ($official && !empty($official['image'])) {
            $filePath = "../uploads/officials/" . $official['image'];
            if (file_exists($filePath)) {
                unlink($filePath); // Ito ang command na nagbubura ng file
            }
        }
        
        $_SESSION['toast'] = ['msg' => 'Official record permanently deleted.', 'type' => 'success'];

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error deleting record: ' . $e->getMessage(), 'type' => 'error'];
    }
    
    // Ibalik sa Archive Page
    header("Location: ../pages/admin/admin_officials_archive.php");
    exit();
}
?>