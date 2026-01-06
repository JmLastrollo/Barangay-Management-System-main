<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// 1. FETCH MESSAGES
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['complaint_id'])) {
    $id = $_GET['complaint_id'];
    
    $stmt = $conn->prepare("SELECT * FROM complaint_conversations WHERE complaint_id = ? ORDER BY created_at ASC");
    $stmt->execute([$id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($messages);
    exit();
}

// 2. SEND MESSAGE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['complaint_id'];
    $message = trim($_POST['message']);
    
    if (empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty']);
        exit();
    }

    // Identify Sender
    if (isset($_SESSION['role']) && ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Staff')) {
        $role = $_SESSION['role'];
        // Get Admin Name (Optional, or just use 'Admin')
        $uid = $_SESSION['user_id'];
        $stmtUser = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
        $stmtUser->execute([$uid]);
        $u = $stmtUser->fetch();
        $name = $u['first_name'] . ' ' . $u['last_name'];
    } else {
        $role = 'Resident';
        // Get Resident Name
        $uid = $_SESSION['user_id'];
        $stmtRes = $conn->prepare("SELECT first_name, last_name FROM resident_profiles WHERE user_id = ?");
        $stmtRes->execute([$uid]);
        $r = $stmtRes->fetch();
        $name = $r['first_name'] . ' ' . $r['last_name'];
    }

    try {
        $stmt = $conn->prepare("INSERT INTO complaint_conversations (complaint_id, sender_role, sender_name, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $role, $name, $message]);
        
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}
?>