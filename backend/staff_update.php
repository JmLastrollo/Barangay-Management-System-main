<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['user_id'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    try {
        $sql = "UPDATE users SET role = :role, status = :status WHERE user_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':role' => $role,
            ':status' => $status,
            ':id' => $userId
        ]);

        header("Location: ../pages/admin/staff_list.php?success=updated");
        exit();

    } catch (PDOException $e) {
        header("Location: ../pages/admin/staff_list.php?error=update_failed");
        exit();
    }
}
?>