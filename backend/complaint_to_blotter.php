<?php
session_start();
require_once 'db_connect.php';
require_once 'log_audit.php'; // Para sa history logs

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complaint_id'])) {
    $complaint_id = $_POST['complaint_id'];

    try {
        // 1. Kunin ang details ng Complaint
        $stmtGet = $conn->prepare("SELECT * FROM complaints WHERE complaint_id = ?");
        $stmtGet->execute([$complaint_id]);
        $comp = $stmtGet->fetch(PDO::FETCH_ASSOC);

        if ($comp) {
            // 2. I-insert ang data sa Blotter Records (Kopyahin ang data)
            $sqlInsert = "INSERT INTO blotter_records 
                (complainant, respondent, incident_type, incident_date, incident_location, narrative, status, status_archive) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pending', 'Active')";
            
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->execute([
                $comp['complainant_name'],
                $comp['respondent_name'],
                $comp['complaint_type'],
                $comp['incident_date'],
                $comp['incident_place'],
                $comp['details']
            ]);

            // 3. I-update ang status ng Complaint para alam na na-process na
            $stmtUpdate = $conn->prepare("UPDATE complaints SET status = 'Processed' WHERE complaint_id = ?");
            $stmtUpdate->execute([$complaint_id]);

            // 4. Mag-log sa History
            if (isset($_SESSION['user_id'])) {
                $action = "Converted Complaint #" . $complaint_id . " to Official Blotter Record";
                logActivity($conn, $_SESSION['user_id'], $action);
            }

            // Success Redirect
            $_SESSION['toast'] = ['msg' => 'Complaint successfully filed to Blotter!', 'type' => 'success'];
            header("Location: ../pages/admin/admin_rec_complaints.php");
            exit();
        } else {
            throw new Exception("Complaint record not found.");
        }

    } catch (Exception $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
        header("Location: ../pages/admin/admin_rec_complaints.php");
        exit();
    }
} else {
    header("Location: ../pages/admin/admin_rec_complaints.php");
    exit();
}
?>