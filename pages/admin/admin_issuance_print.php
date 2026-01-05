<?php

require_once '../../backend/db_connect.php';
require_once '../../backend/fpdf186/fpdf.php'; 

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Error: Missing ID.");
}

try {
    
    $sql = "SELECT i.*, 
                   rp.first_name, rp.middle_name, rp.last_name, rp.address, rp.civil_status, rp.gender, rp.age 
            FROM issuance i
            LEFT JOIN resident_profiles rp ON i.resident_id = rp.resident_id
            WHERE i.issuance_id = :id";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        die("Error: Record not found. Please check if the issuance ID exists.");
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}


$middleInitial = !empty($data['middle_name']) ? strtoupper($data['middle_name'][0]) . '.' : '';

$residentName = strtoupper($data['first_name'] . ' ' . $middleInitial . ' ' . $data['last_name']);
$address = $data['address'] ?? 'Brgy. Langkaan II, Dasmariñas City';
$purpose = $data['purpose'] ?? '';
$age = $data['age'] ?? '';
$civilStatus = $data['civil_status'] ?? 'Single';
$gender = $data['gender'] ?? ''; 
$dateIssued = date('jS \d\a\y \o\f F Y', strtotime($data['request_date']));
$officialSignatory = "HON. FERNANDO B. LAUDATO JR."; 
$docType = strtolower(trim($data['document_type']));


if (strpos($docType, 'clearance') !== false) {

    if (file_exists('pdf_files/pdf_clearance.php')) {
        require_once 'pdf_files/pdf_clearance.php';
    } else {
        require_once 'pdf_files/pdf_generic.php';
    }
} 
elseif (strpos($docType, 'indigency') !== false) {
    if (file_exists('pdf_files/pdf_indigency.php')) {
        require_once 'pdf_files/pdf_indigency.php';
    } else {
        require_once 'pdf_files/pdf_generic.php';
    }
} 
elseif (strpos($docType, 'residency') !== false) {
    if (file_exists('pdf_files/pdf_residency.php')) {
        require_once 'pdf_files/pdf_residency.php';
    } else {
        require_once 'pdf_files/pdf_generic.php';
    }
} 
else {
    // Default / Generic Template
    require_once 'pdf_files/pdf_generic.php';
}
?>