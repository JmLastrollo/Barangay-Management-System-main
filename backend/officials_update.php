<?php
session_start();
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST["id"];
    
    // Check if simple Restore/Archive action (via query params or simple form) or Full Edit
    // Here we assume Full Edit based on your form
    $name       = trim($_POST["name"]);
    $position   = $_POST["position"];
    $term_start = $_POST["term_start"];
    $term_end   = !empty($_POST['term_end']) ? $_POST['term_end'] : NULL;
    
    // Auto-update status based on Term End
    $status = 'Active';
    if ($term_end && $term_end < date('Y-m-d')) {
        $status = 'Archived';
    }

    try {
        // Prepare base SQL
        $sql = "UPDATE barangay_officials SET 
                full_name = :name, 
                position = :pos, 
                term_start = :t_start, 
                term_end = :t_end,
                status = :status";

        $params = [
            ':name'    => $name,
            ':pos'     => $position,
            ':t_start' => $term_start,
            ':t_end'   => $term_end,
            ':status'  => $status,
            ':id'      => $id
        ];

        // Handle Image Update
        if (!empty($_FILES["photo"]["name"])) {
            $uploadDir = "../uploads/officials/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
            $filename = 'off_' . time() . '_' . uniqid() . '.' . $ext;

            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadDir . $filename)) {
                $sql .= ", image = :img";
                $params[':img'] = $filename;
            }
        }

        $sql .= " WHERE official_id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $_SESSION['toast'] = ['msg' => 'Official details updated successfully!', 'type' => 'success'];

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }

    header("Location: ../pages/admin/admin_officials.php");
    exit();
}
?>