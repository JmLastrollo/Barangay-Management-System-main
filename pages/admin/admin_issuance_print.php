<?php
// backend/admin_issuance_print.php
require_once '../../backend/auth_admin.php';
require_once '../../backend/config.php';
require_once '../../backend/fpdf186/fpdf.php'; // adjust path if different

// Basic validation
$id = $_GET['id'] ?? null;
if (!$id) {
    die("Missing id.");
}

// fetch issuance
try {
    $request = $issuanceCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
} catch (Exception $e) {
    die("Invalid id.");
}
if (!$request) {
    die("Request not found.");
}

// fetch resident by foreign key resident_id (string)
$resident = null;
if (!empty($request['resident_id'])) {
    try {
        $resident = $residentsCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($request['resident_id'])]);
    } catch (Exception $e) {
        // maybe resident_id is stored as string of ObjectId already; attempt find by string _id
        $resident = $residentsCollection->findOne(['_id' => $request['resident_id']]);
    }
}

// prepare some derived fields safely
$requestArr = (array)$request;
$residentArr = $resident ? (array)$resident : [];

$documentType = trim($requestArr['document_type'] ?? '');
$documentTypeLower = mb_strtolower($documentType);

// choose template
switch ($documentTypeLower) {
    case 'barangay clearance':
    case 'barangay clearance':
        require_once __DIR__ . '/pdf_files/pdf_clearance.php';
        exit;
    case 'certificate of indigency':
    case 'certificate of indigency':
        require_once __DIR__ . '/pdf_files/pdf_indigency.php';
        exit;
    case 'certificate of residency':
        require_once __DIR__ . '/pdf_files/pdf_residency.php';
        exit;
    case 'barangay business clearance':
    case 'barangay business certification':
    case 'barangay business certification':
        require_once __DIR__ . '/pdf_files/pdf_business.php';
        exit;
    default:
        // fallback: basic generic PDF (simple)
        require_once __DIR__ . '/pdf_files/pdf_generic.php';
        exit;
}
