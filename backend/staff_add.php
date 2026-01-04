<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $status = 'Active';

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        header("Location: ../pages/admin/staff_list.php?error=empty");
        exit();
    }

    try {
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $checkStmt->execute([':email' => $email]);
        
        if ($checkStmt->fetchColumn() > 0) {
            header("Location: ../pages/admin/staff_list.php?error=exists");
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, email, password, role, status, created_at) 
                VALUES (:fname, :lname, :email, :pass, :role, :status, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':fname' => $firstName,
            ':lname' => $lastName,
            ':email' => $email,
            ':pass' => $hashedPassword,
            ':role' => $role,
            ':status' => $status
        ]);

        header("Location: ../pages/admin/staff_list.php?success=added");
        exit();

    } catch (PDOException $e) {
        header("Location: ../pages/admin/staff_list.php?error=failed");
        exit();
    }
}
?>