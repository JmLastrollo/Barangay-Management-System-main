<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $position = $_POST['position'];
    $term_start = !empty($_POST['term_start']) ? $_POST['term_start'] : NULL;
    $term_end = !empty($_POST['term_end']) ? $_POST['term_end'] : NULL;

    // --- LOGIC: AUTO-DETECT STATUS ---
    // Default is Active
    $status = 'Active';
    
    // If term_end is provided AND it is a past date, set to Archived
    if (!empty($term_end) && $term_end < date('Y-m-d')) {
        $status = 'Archived';
    }

    // Image Upload
    $image = "";
    if (isset($_FILES['photo']['name']) && $_FILES['photo']['name'] != "") {
        $targetDir = "../uploads/officials/";
        $image = time() . "_" . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $targetDir . $image);
    }

    try {
        $sql = "INSERT INTO barangay_officials 
                (full_name, position, image, status, term_start, term_end) 
                VALUES (:name, :pos, :img, :status, :t_start, :t_end)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':pos' => $position,
            ':img' => $image,
            ':status' => $status,
            ':t_start' => $term_start,
            ':t_end' => $term_end
        ]);

        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Official added successfully!'];
        
        // Redirect based on the calculated status
        if($status == 'Active'){
            header("Location: ../pages/admin/admin_officials.php");
        } else {
            header("Location: ../pages/admin/admin_officials_archive.php");
        }

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Error: ' . $e->getMessage()];
        header("Location: ../pages/admin/admin_officials.php");
    }
}
?>