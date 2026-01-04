<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['resident_id'])) {
    
    $resident_id = $_POST['resident_id'];
    $default_password = "12345678"; // Ito ang default password
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

    try {
        $conn->beginTransaction();

        // 1. Kunin muna ang user_id na connected sa resident_id
        $get_user = $conn->prepare("SELECT user_id FROM resident_profiles WHERE resident_id = :rid");
        $get_user->execute([':rid' => $resident_id]);
        $user_id = $get_user->fetchColumn();

        if ($user_id) {
            // 2. I-update ang password sa users table
            $update = $conn->prepare("UPDATE users SET password = :pass WHERE user_id = :uid");
            $update->execute([
                ':pass' => $hashed_password,
                ':uid' => $user_id
            ]);

            $conn->commit();
            
            // Redirect with success message
            header("Location: ../pages/admin/resident_list.php?success=reset");
            exit();
        } else {
            throw new Exception("User not found for this resident.");
        }

    } catch (Exception $e) {
        $conn->rollBack();
        // Pwede mong dagdagan ng error handling dito kung gusto mo
        header("Location: ../pages/admin/resident_list.php?error=failed");
        exit();
    }
} else {
    header("Location: ../pages/admin/resident_list.php");
    exit();
}
?>