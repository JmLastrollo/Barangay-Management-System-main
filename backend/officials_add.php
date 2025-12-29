<?php
session_start();
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST["name"];
    $position = $_POST["position"];
    $filename = "";

    $uploadDir = "../uploads/officials/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (!empty($_FILES["photo"]["name"])) {
        $extension = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
        $filename = time() . "_" . uniqid() . "." . $extension;
        move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadDir . $filename);
    }

    try {
        // FIX: Insert with 'Active' status (Capital A)
        $sql = "INSERT INTO barangay_officials (full_name, position, image, status) VALUES (:name, :pos, :img, 'Active')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':name' => $name, ':pos' => $position, ':img' => $filename]);
        $_SESSION['toast'] = ['msg' => 'Official added successfully!', 'type' => 'success'];
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error adding official: ' . $e->getMessage(), 'type' => 'error'];
    }

    // FIX: Redirect path ../ lang
    header("Location: ../pages/admin/admin_officials.php");
    exit;
}
?>