<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    try {
        // Check if email exists in users table
        $stmt = $conn->prepare("SELECT user_id, email FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Email found! Save to session to allow password reset
            $_SESSION['reset_email'] = $user['email'];
            
            // Redirect to Create New Password Page
            header("Location: ../new_password.php");
            exit();
        } else {
            // Email not found
            header("Location: ../forgot_password.php?error=notfound");
            exit();
        }

    } catch (PDOException $e) {
        header("Location: ../forgot_password.php?error=db");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>