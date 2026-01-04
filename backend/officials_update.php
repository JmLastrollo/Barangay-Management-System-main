<?php
session_start();
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST["id"];
    
    // 1. Sanitize Inputs
    $name       = trim($_POST["name"]);
    $position   = $_POST["position"];
    $term_start = $_POST["term_start"];
    $term_end   = !empty($_POST['term_end']) ? $_POST['term_end'] : NULL;
    
    // 2. STATUS LOGIC
    // Kunin ang status mula sa form (Active or Resigned)
    $status = isset($_POST['status']) ? $_POST['status'] : 'Active';

    // SPECIAL RULE:
    // Kung ang pinili ay 'Active' PERO tapos na ang Term End, gawing 'Inactive'.
    // (Pero kung pinili ay 'Resigned', mananatiling 'Resigned' kahit expired na o hindi).
    if ($status == 'Active' && $term_end && $term_end < date('Y-m-d')) {
        $status = 'Inactive';
    }

    try {
        // 3. Prepare Update Query
        $sql = "UPDATE barangay_officials SET 
                full_name = :name, 
                position = :pos, 
                term_start = :t_start, 
                term_end = :t_end,
                status = :status"; // Isama ang status sa update

        $params = [
            ':name'    => $name,
            ':pos'     => $position,
            ':t_start' => $term_start,
            ':t_end'   => $term_end,
            ':status'  => $status,
            ':id'      => $id
        ];

        // 4. Handle Image Update (Optional)
        if (!empty($_FILES["photo"]["name"])) {
            $uploadDir = "../uploads/officials/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($ext, $allowed)) {
                $filename = 'off_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadDir . $filename)) {
                    $sql .= ", image = :img";
                    $params[':img'] = $filename;
                }
            }
        }

        $sql .= " WHERE official_id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $_SESSION['toast'] = ['msg' => 'Official details updated successfully!', 'type' => 'success'];

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }

    // Redirect based on status (Optional: para bumalik sa tamang page)
    if ($status == 'Active') {
        header("Location: ../pages/admin/admin_officials.php");
    } else {
        header("Location: ../pages/admin/admin_officials_archive.php");
    }
    exit();
}
?>