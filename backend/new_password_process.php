<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['reset_email'])) {
    $email = $_SESSION['reset_email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Check if Passwords Match
    if ($new_password !== $confirm_password) {
        header("Location: ../new_password.php?error=mismatch");
        exit();
    }

    // 2. Server-Side Strong Password Validation
    // Min 8 chars, 1 Upper, 1 Lower, 1 Number, 1 Special Char
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/';
    
    if (!preg_match($pattern, $new_password)) {
        header("Location: ../new_password.php?error=weak");
        exit();
    }

    // 3. Hash and Update
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("UPDATE users SET password = :pass WHERE email = :email");
        $stmt->execute([
            ':pass' => $hashed_password,
            ':email' => $email
        ]);

        // Clear session
        unset($_SESSION['reset_email']);
        
        // Success Toast
        $_SESSION['toast'] = [
            'msg' => 'Password Reset Successful! Please login.',
            'type' => 'success'
        ];

        header("Location: ../login.php");
        exit();

    } catch (PDOException $e) {
        header("Location: ../new_password.php?error=db");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>