<?php
session_start();
require_once 'db_connect.php';
require_once 'log_audit.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $complainant = $_POST['complainant'];
    $respondent = $_POST['respondent'];
    $type = $_POST['incident_type'];
    $date = $_POST['date'];
    $location = $_POST['incident_location'];
    $details = $_POST['details'];
    $recorded_by = $_SESSION['user_id'] ?? null;

    try {
        $stmt = $conn->prepare("INSERT INTO blotter_records (complainant, respondent, incident_type, incident_date, incident_location, narrative, status, status_archive, recorded_by) VALUES (?, ?, ?, ?, ?, ?, 'Pending', 'Active', ?)");
        $stmt->execute([$complainant, $respondent, $type, $date, $location, $details, $recorded_by]);

        if (isset($_SESSION['user_id'])) {
            logActivity($conn, $_SESSION['user_id'], "Added new blotter case: $type");
        }

        $_SESSION['toast'] = ['msg' => 'Blotter record added successfully!', 'type' => 'success'];
        header("Location: ../pages/admin/admin_rec_blotter.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
        header("Location: ../pages/admin/admin_rec_blotter.php");
    }
}
?>