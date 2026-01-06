<?php
session_start();
require_once 'db_connect.php';
require_once 'log_audit.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complaint_id'])) {
    $id = $_POST['complaint_id'];

    try {
        // 1. Get Complaint Data
        $stmt = $conn->prepare("SELECT * FROM complaints WHERE complaint_id = ?");
        $stmt->execute([$id]);
        $comp = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($comp) {
            // 2. Insert to Blotter
            $sqlInsert = "INSERT INTO blotter_records (complainant, respondent, incident_type, incident_date, incident_location, narrative, status, status_archive) VALUES (?, ?, ?, ?, ?, ?, 'Pending', 'Active')";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->execute([
                $comp['complainant_name'],
                $comp['respondent_name'],
                $comp['complaint_type'],
                $comp['incident_date'],
                $comp['incident_place'],
                $comp['details']
            ]);

            // 3. Update Complaint Status
            $stmtUpdate = $conn->prepare("UPDATE complaints SET status = 'Processed' WHERE complaint_id = ?");
            $stmtUpdate->execute([$id]);

            if (isset($_SESSION['user_id'])) {
                logActivity($conn, $_SESSION['user_id'], "Filed Complaint #$id to Official Blotter");
            }

            $_SESSION['toast'] = ['msg' => 'Complaint successfully filed as Blotter Case!', 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['msg' => 'Complaint record not found.', 'type' => 'error'];
        }
    } catch (Exception $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
    header("Location: ../pages/admin/admin_rec_complaints.php");
    exit();
}
?>