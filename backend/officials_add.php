<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. Sanitize Inputs
    $fullname   = trim($_POST['name']);
    $position   = trim($_POST['position']);
    $term_start = $_POST['term_start'];
    
    // Handle Term End: Convert empty string to NULL
    $term_end   = !empty($_POST['term_end']) ? $_POST['term_end'] : NULL;

    // Determine Status
    $status = 'Active';
    if ($term_end && $term_end < date('Y-m-d')) {
        $status = 'Archived';
    }

    // 2. Image Upload Handling
    $image_name = ""; 
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "../uploads/officials/";
        
        // Use 0755 for better security instead of 0777
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed)) {
            $image_name = 'off_' . time() . '_' . uniqid() . '.' . $ext;
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . $image_name)) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Failed to upload image.'];
            }
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Invalid file type. Only JPG, PNG, & GIF allowed.'];
            header("Location: ../pages/admin/admin_officials.php");
            exit();
        }
    }

    // 3. Database Insertion
    try {
        $stmt = $conn->prepare("INSERT INTO barangay_officials (full_name, position, term_start, term_end, status, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fullname, $position, $term_start, $term_end, $status, $image_name]);

        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Official added successfully!'];

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Database Error: ' . $e->getMessage()];
    }

    header("Location: ../pages/admin/admin_officials.php");
    exit();
}
?>