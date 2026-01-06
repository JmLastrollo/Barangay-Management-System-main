<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$action = $_POST['action'] ?? '';

try {
    if ($action === 'update_status') {
        $id = $_POST['id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE health_appointments SET status = ? WHERE appointment_id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['success' => true]);
    }
    elseif ($action === 'add_record') {
        // Form submit, not AJAX
        $name = $_POST['patient_name'];
        $age = $_POST['age'];
        $date = $_POST['date_visit'];
        $concern = $_POST['concern'];
        $dx = $_POST['diagnosis'];
        $staff = "Staff ID: " . $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO health_records (resident_name, age, concern, diagnosis, date_visit, attended_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $age, $concern, $dx, $date, $staff]);
        
        header("Location: ../pages/staff/staff_health.php");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>