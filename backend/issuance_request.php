<?php
session_start();
require_once 'db_connect.php';

// Set Content Type to JSON
header('Content-Type: application/json');

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Resident') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Get Resident ID
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT resident_id FROM resident_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $resident = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resident) {
        echo json_encode(['status' => 'error', 'message' => 'Resident profile not found.']);
        exit();
    }
    
    $resident_id = $resident['resident_id'];
    
    // 2. Sanitize Inputs
    $doc_type = $_POST['document_type'] ?? '';
    $purpose = trim($_POST['purpose'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    
    // 3. Set Amount Based on Document Type (Updated: Removed Business Clearance)
    $amount = 0;
    switch($doc_type) {
        case 'Barangay Clearance':
            $amount = 50.00;
            break;
        case 'Certificate of Indigency':
            $amount = 0.00; // Free
            break;
        case 'Certificate of Residency':
            $amount = 50.00;
            break;
        default:
            // Kapag wala sa tatlo ang pinili, error na ito.
            echo json_encode(['status' => 'error', 'message' => 'Invalid document type selected.']);
            exit();
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
                if(!move_uploaded_file($_FILES['proof_of_payment']['tmp_name'], $uploadDir . $proof_filename)){
                    echo json_encode(['status' => 'error', 'message' => 'Failed to upload proof of payment.']);
                    exit();
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, PDF allowed.']);
                exit();
            }
        } else {
            // Kung Indigency (Free), hindi required ang payment proof kahit naka-select na "Online" (edge case handle)
            if ($amount > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Please upload proof of payment for online transactions.']);
                exit();
            }
        }
    }
    
    // 5. Insert Request
    try {
        $sql = "INSERT INTO document_issuances (resident_id, document_type, purpose, payment_method, amount, proof_of_payment, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$resident_id, $doc_type, $purpose, $payment_method, $amount, $proof_filename]);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Document request submitted successfully!'
        ]);
        exit();
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}
?>