<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

try {
    // --- 1. ADD WALK-IN REQUEST (WITH AUTO REGISTER) ---
    if ($action === 'add_walkin') {
        
        $fname = trim($_POST['first_name']);
        $lname = trim($_POST['last_name']);
        
        // A. CHECK IF RESIDENT EXISTS
        $stmtCheck = $conn->prepare("SELECT resident_id FROM resident_profiles WHERE first_name = :f AND last_name = :l LIMIT 1");
        $stmtCheck->execute([':f' => $fname, ':l' => $lname]);
        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Use existing ID
            $resident_id = $existing['resident_id'];
        } else {
            // B. AUTO-REGISTER RESIDENT
            $stmtReg = $conn->prepare("INSERT INTO resident_profiles (first_name, last_name, status) VALUES (:f, :l, 'Active')");
            $stmtReg->execute([':f' => $fname, ':l' => $lname]);
            $resident_id = $conn->lastInsertId();
        }

        // C. INSERT ISSUANCE REQUEST
        $doc_type = $_POST['document_type'];
        $purpose  = trim($_POST['purpose']);
        $amount   = floatval($_POST['amount']);
        $staff_id = $_SESSION['user_id'];
        $ctrl     = "REQ-" . date("Ymd") . "-" . strtoupper(substr(md5(uniqid()), 0, 4));

        $sql = "INSERT INTO document_issuances 
                (resident_id, request_control_no, document_type, purpose, amount, status, payment_method, requested_at, processed_by) 
                VALUES (:rid, :ctrl, :dtype, :purp, :amt, 'Pending', 'Cash', NOW(), :staff)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':rid' => $resident_id,
            ':ctrl' => $ctrl,
            ':dtype' => $doc_type,
            ':purp' => $purpose,
            ':amt' => $amount,
            ':staff' => $staff_id
        ]);

        echo json_encode(['status' => 'success']);
    }

    // --- 2. UPDATE STATUS ---
    elseif ($action === 'update_status') {
        $id = $_POST['issuance_id'];
        $status = $_POST['status'];
        $remarks = $_POST['remarks'] ?? null;
        $staff_id = $_SESSION['user_id'];

        $sql = "UPDATE document_issuances SET status = :status, processed_by = :staff";
        
        if ($status === 'Ready for Pickup') {
            $sql .= ", approved_at = NOW()";
        } elseif ($status === 'Released') {
            $sql .= ", released_at = NOW()";
        } elseif ($status === 'Rejected') {
            $sql .= ", admin_remarks = :remarks";
        }

        $sql .= " WHERE issuance_id = :id";

        $stmt = $conn->prepare($sql);
        $params = [':status' => $status, ':staff' => $staff_id, ':id' => $id];
        
        if ($status === 'Rejected') {
            $params[':remarks'] = $remarks;
        }

        $stmt->execute($params);
        echo json_encode(['status' => 'success']);
    }
    
    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>