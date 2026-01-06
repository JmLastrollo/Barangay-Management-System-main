<?php
session_start();
require_once 'db_connect.php';
require_once 'log_audit.php';

// --- 1. APPROVE ---
if (isset($_POST['action']) && $_POST['action'] == 'approve') {
    $id = $_POST['appointment_id'];
    try {
        $stmt = $conn->prepare("UPDATE health_appointments SET status = 'Approved' WHERE appointment_id = ?");
        $stmt->execute([$id]);
        $_SESSION['toast'] = ['msg' => 'Appointment approved successfully!', 'type' => 'success'];
        
        if(isset($_SESSION['user_id'])) {
            logActivity($conn, $_SESSION['user_id'], "Approved Health Appointment #$id");
        }
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
    header("Location: ../pages/admin/patient_records.php");
    exit();
}

// --- 2. CANCEL ---
if (isset($_POST['action']) && $_POST['action'] == 'cancel') {
    $id = $_POST['appointment_id'];
    try {
        $stmt = $conn->prepare("UPDATE health_appointments SET status = 'Cancelled' WHERE appointment_id = ?");
        $stmt->execute([$id]);
        $_SESSION['toast'] = ['msg' => 'Appointment cancelled.', 'type' => 'warning'];
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
    header("Location: ../pages/admin/patient_records.php");
    exit();
}

// --- 3. RESCHEDULE (NEW FEATURE) ---
if (isset($_POST['action']) && $_POST['action'] == 'reschedule') {
    $id = $_POST['appointment_id'];
    $new_date = $_POST['new_date'];
    $new_time = $_POST['new_time'];
    $reason = $_POST['resched_reason']; // Optional: Reason for rescheduling

    try {
        $stmt = $conn->prepare("UPDATE health_appointments SET appointment_date = ?, appointment_time = ?, status = 'Approved' WHERE appointment_id = ?");
        $stmt->execute([$new_date, $new_time, $id]);

        $_SESSION['toast'] = ['msg' => 'Appointment rescheduled successfully!', 'type' => 'success'];

        if(isset($_SESSION['user_id'])) {
            logActivity($conn, $_SESSION['user_id'], "Rescheduled Appointment #$id to $new_date ($new_time). Reason: $reason");
        }

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
    header("Location: ../pages/admin/patient_records.php");
    exit();
}

// --- 4. COMPLETE CONSULTATION ---
if (isset($_POST['action']) && $_POST['action'] == 'complete') {
    $appt_id = $_POST['appointment_id'];
    $resident_id = $_POST['resident_id'];
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];
    $attended_by = "Admin/Staff";

    try {
        $stmtRes = $conn->prepare("SELECT first_name, last_name, birthdate FROM resident_profiles WHERE resident_id = ?");
        $stmtRes->execute([$resident_id]);
        $res = $stmtRes->fetch(PDO::FETCH_ASSOC);
        
        if ($res) {
            $fullname = $res['first_name'] . " " . $res['last_name'];
            $birthDate = new DateTime($res['birthdate']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;

            $sqlInsert = "INSERT INTO health_records (resident_name, age, concern, diagnosis, treatment, date_visit, attended_by) 
                          VALUES (?, ?, ?, ?, ?, NOW(), ?)";
            
            $stmtAppt = $conn->prepare("SELECT reason FROM health_appointments WHERE appointment_id = ?");
            $stmtAppt->execute([$appt_id]);
            $apptData = $stmtAppt->fetch();
            $concern = $apptData['reason'] ?? 'General Checkup';

            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->execute([$fullname, $age, $concern, $diagnosis, $treatment, $attended_by]);

            $stmtUpdate = $conn->prepare("UPDATE health_appointments SET status = 'Completed' WHERE appointment_id = ?");
            $stmtUpdate->execute([$appt_id]);

            $_SESSION['toast'] = ['msg' => 'Consultation completed!', 'type' => 'success'];
            
            if(isset($_SESSION['user_id'])) {
                logActivity($conn, $_SESSION['user_id'], "Completed consultation for $fullname");
            }
        }
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
    header("Location: ../pages/admin/patient_records.php");
    exit();
}
?>