<?php
session_start();
require_once 'db_connect.php';
require_once 'log_audit.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complaint_id'])) {
    
    $id = $_POST['complaint_id'];

    if (isset($_POST['action']) && $_POST['action'] == 'archive') {
        try {
            $stmt = $conn->prepare("UPDATE complaints SET status = 'Archived' WHERE complaint_id = ?");
            $stmt->execute([$id]);
            
            if(isset($_SESSION['user_id'])) {
                logActivity($conn, $_SESSION['user_id'], "Archived Complaint #$id");
            }

            $_SESSION['toast'] = ['msg' => 'Complaint moved to archive.', 'type' => 'warning'];
            header("Location: ../pages/admin/admin_rec_complaints.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
            header("Location: ../pages/admin/admin_rec_complaints.php");
            exit();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subject']) && isset($_POST['email'])) {
    
    // Sanitize inputs
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email']);
    $subject  = trim($_POST['subject']);
    $message  = trim($_POST['message'] ?? '');

    if (empty($fullname) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['toast'] = ['msg' => 'Please fill in all fields.', 'type' => 'error'];
        header("Location: ../contact.php");
        exit();
    }

    try {
        $stmt = $conn->prepare("INSERT INTO messages (sender_name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fullname, $email, $subject, $message]);

        if(isset($_SESSION['user_id'])) {
            logActivity($conn, $_SESSION['user_id'], "Sent a contact message: $subject");
        }

        $_SESSION['toast'] = ['msg' => 'Message sent successfully! We will contact you soon.', 'type' => 'success'];
        header("Location: ../contact.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error sending message: ' . $e->getMessage(), 'type' => 'error'];
        header("Location: ../contact.php");
        exit();
    }
}
?>