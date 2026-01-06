<?php
session_start();
require_once '../../backend/db_connect.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    die("Access Denied");
}

$issuance_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. Kunin ang detalye ng request at payment method
$sql = "SELECT i.*, p.payment_method 
        FROM issuance i 
        LEFT JOIN payments p ON i.issuance_id = p.issuance_id
        JOIN resident_profiles r ON i.resident_id = r.resident_id
        WHERE i.issuance_id = :id AND r.user_id = :uid";

$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $issuance_id, ':uid' => $user_id]);
$req = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$req) {
    die("Request not found or access denied.");
}

// 2. SETTINGS
$limit_hours = 48; // Valid for 48 hours
$max_print = 1;    // 1 attempt only

// 3. VALIDATION RULES

// Rule A: Status must be Ready for Pickup
if ($req['status'] !== 'Ready for Pickup') {
    echo "<script>alert('Document is not yet ready for printing.'); window.close();</script>";
    exit;
}

// Rule B: Cash Payments cannot print (Must pick up)
// Note: Kung Free (0.00), pwede i-print kahit Cash/Walk-in ang tag, depende sa policy nyo. 
// Dito, strictly Online Payment lang ang pwede mag-print.
if ($req['payment_method'] === 'Cash' && $req['price'] > 0) {
    echo "<script>alert('For Cash payments, please pick up your document at the Barangay Hall.'); window.close();</script>";
    exit;
}

// Rule C: Check Print Attempts
if ($req['print_attempts'] >= $max_print) {
    echo "<script>alert('You have already printed/downloaded this document. Please visit the Barangay Hall for another copy.'); window.close();</script>";
    exit;
}

// Rule D: Check Expiration (48 Hours)
if ($req['approved_date']) {
    $approved_time = new DateTime($req['approved_date']);
    $current_time = new DateTime();
    $interval = $current_time->diff($approved_time);
    $hours_passed = ($interval->days * 24) + $interval->h;

    if ($hours_passed > $limit_hours) {
        echo "<script>alert('The download link has expired (48 hours passed). Please visit the Barangay Hall.'); window.close();</script>";
        exit;
    }
} else {
    // Kung walang approved_date, baka luma na record or error
    echo "<script>alert('Error: Approval date not found.'); window.close();</script>";
    exit;
}

// 4. SUCCESS: Update Print Count & Redirect to PDF
$update = $conn->prepare("UPDATE issuance SET print_attempts = print_attempts + 1 WHERE issuance_id = :id");
$update->execute([':id' => $issuance_id]);

// Redirect sa tamang PDF file base sa Document Type
$pdf_url = '';
switch ($req['document_type']) {
    case 'Barangay Clearance':
        $pdf_url = '../admin/pdf_files/pdf_clearance.php';
        break;
    case 'Certificate of Residency':
        $pdf_url = '../admin/pdf_files/pdf_residency.php';
        break;
    case 'Certificate of Indigency':
        $pdf_url = '../admin/pdf_files/pdf_indigency.php';
        break;
    default:
        die("Unknown document type.");
}

header("Location: " . $pdf_url . "?id=" . $issuance_id);
exit();
?>