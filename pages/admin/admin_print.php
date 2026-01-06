<?php
session_start();
require_once '../../backend/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Access Denied");
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid Request ID");

// Fetch Data
$sql = "SELECT i.*, r.first_name, r.middle_name, r.last_name, r.address, r.civil_status, r.age 
        FROM document_issuances i 
        JOIN resident_profiles r ON i.resident_id = r.resident_id 
        WHERE i.issuance_id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("Record not found.");

// Auto-Update to Ready if Printing
if (in_array($data['status'], ['Pending', 'Payment Verified'])) {
    $upStmt = $conn->prepare("UPDATE document_issuances SET status = 'Ready for Pickup', approved_at = NOW(), processed_by = :uid WHERE issuance_id = :id");
    $upStmt->execute([':uid' => $_SESSION['user_id'], ':id' => $id]);
}

// Formatting
$fullname = strtoupper($data['first_name'] . ' ' . $data['middle_name'] . ' ' . $data['last_name']);
$address = strtoupper($data['address']);
$type = strtoupper($data['document_type']);
$purpose = strtoupper($data['purpose']);
$date = date('jS \d\a\y \o\f F Y');
$controlNo = $data['request_control_no'] ?? 'REQ-' . str_pad($data['issuance_id'], 5, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print - <?= $controlNo ?></title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #ccc; padding: 20px; font-family: 'Times New Roman', serif; }
        .paper { background: white; width: 8.5in; height: 11in; margin: 0 auto; padding: 1in; position: relative; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
        @media print { body { background: white; padding: 0; } .paper { box-shadow: none; width: 100%; height: 100%; margin: 0; } .no-print { display: none !important; } }
        .header { text-align: center; margin-bottom: 40px; line-height: 1.2; }
        .logo { width: 80px; height: 80px; position: absolute; top: 40px; left: 60px; }
        .title { text-align: center; font-weight: bold; font-size: 24px; text-decoration: underline; margin: 40px 0; }
        .content { font-size: 14px; line-height: 1.8; text-align: justify; }
        .indent { text-indent: 50px; }
        .signature { margin-top: 80px; text-align: right; }
        .sign-line { border-top: 1px solid black; width: 200px; display: inline-block; text-align: center; padding-top: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="text-center mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary fw-bold px-4">Print Document</button>
        <button onclick="window.close()" class="btn btn-secondary px-4">Close</button>
    </div>
    <div class="paper">
        <img src="../../assets/img/Langkaan 2 Logo-modified.png" class="logo" alt="Logo">
        <div class="header">
            <small>Republic of the Philippines</small><br>
            <span>Province of Cavite</span><br>
            <span>City of Dasmariñas</span><br>
            <span style="font-weight: bold; font-size: 18px;">BARANGAY LANGKAAN II</span><br>
            <small style="font-style: italic;">OFFICE OF THE PUNONG BARANGAY</small>
        </div>
        <div class="title"><?= $type ?></div>
        <div class="content">
            <p><strong>TO WHOM IT MAY CONCERN:</strong></p>
            <p class="indent">This is to certify that <strong><?= $fullname ?></strong>, of legal age, Filipino, and a resident of <strong><?= $address ?></strong>, is a person of good moral character.</p>
            <p class="indent">This certification is being issued upon the request of the above-named person for <strong><?= $purpose ?></strong>.</p>
            <p class="indent">Issued this <strong><?= $date ?></strong> at Barangay Langkaan II, Dasmariñas City, Cavite.</p>
        </div>
        <div class="signature">
            <div class="sign-line">HON. FERNANDO LAUDATO JR.</div><br><small style="margin-right: 40px;">Punong Barangay</small>
        </div>
        <div style="position: absolute; bottom: 50px; left: 50px; font-size: 10px; color: gray;">
            Control No: <?= $controlNo ?><br>Printed by: Admin
        </div>
    </div>
</body>
</html>