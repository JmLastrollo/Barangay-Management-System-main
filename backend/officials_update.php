<?php
session_start();
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST["id"];
    $name = $_POST["name"] ?? null; 
    $position = $_POST["position"] ?? null;
    $status = $_POST["status"] ?? 'Active'; // Default Active

    try {
        // Kung RESTORE (walang name/position na pinasa)
        if ($name === null) {
            $sql = "UPDATE barangay_officials SET status = :status WHERE official_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':status' => $status, ':id' => $id]);
        } 
        // Kung EDIT (may name/position)
        else {
            $sql = "UPDATE barangay_officials SET full_name = :name, position = :pos, status = :status";
            $params = [':name' => $name, ':pos' => $position, ':status' => $status, ':id' => $id];

            // Image Upload
            if (!empty($_FILES["photo"]["name"])) {
                $uploadDir = "../uploads/officials/";
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $extension = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
                $filename = time() . "_" . uniqid() . "." . $extension;
                
                if(move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadDir . $filename)){
                    $sql .= ", image = :img";
                    $params[':img'] = $filename;
                }
            }

            // FIX: Use official_id
            $sql .= " WHERE official_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }

        $_SESSION['toast'] = ['msg' => 'Action successful!', 'type' => 'success'];

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }

    // FIX: Redirect path ../ lang
    if ($status === 'Archived' || $status === 'archived') {
        header("Location: ../pages/admin/admin_officials_archive.php");
    } else {
        header("Location: ../pages/admin/admin_officials.php");
    }
    exit;
}
?>