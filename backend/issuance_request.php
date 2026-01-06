<?php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Resident') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Get Resident ID
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT resident_id FROM resident_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $resident = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resident) {
        $_SESSION['toast'] = ['msg' => 'Resident profile not found.', 'type' => 'error'];
        header("Location: ../pages/resident/request_document.php");
        exit();
    }
    
    $resident_id = $resident['resident_id'];
    
    // 2. Sanitize Inputs
    $doc_type = $_POST['document_type'];
    $purpose = trim($_POST['purpose']);
    $payment_method = $_POST['payment_method'];
    
    // 3. Set Amount Based on Document Type
    $amount = 0;
    switch($doc_type) {
        case 'Barangay Clearance':
            $amount = 50.00;
            break;
        case 'Certificate of Indigency':
            $amount = 0.00; // Free
            break;
        case 'Certificate of Residency':
            $amount = 30.00;
            break;
    }
    
    // 4. Handle Online Payment Proof Upload
    $proof_filename = NULL;
    
    if ($payment_method == 'Online') {
        if (!empty($_FILES['proof_of_payment']['name'])) {
            $uploadDir = "../uploads/payment_proofs/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = strtolower(pathinfo($_FILES['proof_of_payment']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (in_array($ext, $allowed)) {
                $proof_filename = 'proof_' . time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['proof_of_payment']['tmp_name'], $uploadDir . $proof_filename);
            } else {
                $_SESSION['toast'] = ['msg' => 'Invalid file type. Only JPG, PNG, PDF allowed.', 'type' => 'error'];
                header("Location: ../pages/resident/request_document.php");
                exit();
            }
        } else {
            $_SESSION['toast'] = ['msg' => 'Please upload proof of payment for online transactions.', 'type' => 'error'];
            header("Location: ../pages/resident/request_document.php");
            exit();
        }
    }
    
    // 5. Insert Request
    try {
        $sql = "INSERT INTO document_issuances (resident_id, document_type, purpose, payment_method, amount, proof_of_payment, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$resident_id, $doc_type, $purpose, $payment_method, $amount, $proof_filename]);
        
        $_SESSION['toast'] = ['msg' => 'Document request submitted successfully!', 'type' => 'success'];
        header("Location: ../pages/resident/my_requests.php");
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
        header("Location: ../pages/resident/request_document.php");
        exit();
    }
}
?>