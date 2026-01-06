<?php
session_start();
require_once "db_connect.php"; // Gamitin ang MySQL Connection
require_once "log_audit.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Kunin ang data galing sa form
    $complainant = trim($_POST["complainant"]);
    $respondent = trim($_POST["respondent"]);
    $type = $_POST["incident_type"];
    $location = trim($_POST["incident_location"]);
    $date = $_POST["date"];
    $details = trim($_POST["details"]);
    $status = "Pending";
    $status_archive = "Active";

    try {
        // SQL Insert Command
        $sql = "INSERT INTO blotter_records (complainant, respondent, incident_type, incident_location, incident_date, narrative, status, status_archive) 
                VALUES (:comp, :resp, :type, :loc, :date, :narr, :stat, :arch)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':comp' => $complainant,
            ':resp' => $respondent,
            ':type' => $type,
            ':loc'  => $location,
            ':date' => $date,
            ':narr' => $details,
            ':stat' => $status,
            ':arch' => $status_archive
        ]);

        // Audit Log
        if (isset($_SESSION['user_id'])) {
            logActivity($conn, $_SESSION['user_id'], "Added new blotter case: $type ($complainant vs $respondent)");
        }

        // Set Toast Message
        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Blotter case recorded successfully!'];

    } catch (PDOException $e) {
        // Error Toast
        $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Failed to add record: ' . $e->getMessage()];
    }

    // Redirect pabalik
    header("Location: ../pages/admin/admin_rec_blotter.php");
    exit();
}
?>