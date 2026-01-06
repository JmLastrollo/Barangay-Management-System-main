<?php
session_start();
require_once '../../backend/auth_resident.php';
require_once '../../backend/db_connect.php';

// Get Request ID
if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$issuance_id = $_GET['id'];

// Get Resident ID
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT resident_id FROM resident_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$resident = $stmt->fetch(PDO::FETCH_ASSOC);
$resident_id = $resident['resident_id'];

// Fetch Issuance Record
$stmt = $conn->prepare("SELECT * FROM document_issuances WHERE issuance_id = ? AND resident_id = ?");
$stmt->execute([$issuance_id, $resident_id]);
$issuance = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$issuance) {
    die("Document not found or access denied.");
}

// Check Status
if ($issuance['status'] !== 'Ready for Pickup') {
    die("Document is not yet ready for printing.");
}

// Check if Already Printed
if ($issuance['is_printed'] == 1) {
    die("This document has already been printed. You can only print once.");
}

// Check Expiration (2 days from approval)
$now = new DateTime();
$expires = new DateTime($issuance['expires_at']);

if ($now > $expires) {
    // Update Status to Expired
    $stmt = $conn->prepare("UPDATE document_issuances SET status = 'Expired' WHERE issuance_id = ?");
    $stmt->execute([$issuance_id]);
    die("This document has expired. Please request a new one.");
}

// Get Resident Full Details
$stmt = $conn->prepare("SELECT * FROM resident_profiles WHERE resident_id = ?");
$stmt->execute([$resident_id]);
$residentData = $stmt->fetch(PDO::FETCH_ASSOC);

// Mark as Printed
$stmt = $conn->prepare("UPDATE document_issuances SET is_printed = 1, printed_at = NOW() WHERE issuance_id = ?");
$stmt->execute([$issuance_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Document - <?= $issuance['document_type'] ?></title>
    <style>
        @media print {
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header img {
            width: 80px;
            height: 80px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 24px;
        }
        .header p {
            margin: 2px 0;
            font-size: 14px;
        }
        .title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin: 30px 0;
            text-decoration: underline;
        }
        .content {
            line-height: 2;
            text-align: justify;
            font-size: 16px;
        }
        .signature {
            margin-top: 60px;
            text-align: right;
        }
        .signature div {
            display: inline-block;
            text-align: center;
        }
        .signature .line {
            border-top: 2px solid #000;
            width: 250px;
            margin-top: 40px;
        }
    </style>
</head>
<body>

<!-- Print Button (Hidden on Print) -->
<div class="no-print" style="text-align: center; margin-bottom: 20px;">
    <button onclick="window.print()" style="padding: 10px 30px; font-size: 16px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
        <i class="bi bi-printer"></i> Print Document
    </button>
    <p style="color: red; font-weight: bold;">⚠️ You can only print this document ONCE!</p>
</div>

<!-- Document Header -->
<div class="header">
    <img src="../../assets/img/dasma logo-modified.png" alt="Logo">
    <h2>BARANGAY LANGKAAN II</h2>
    <p>Dasmariñas City, Cavite</p>
    <p>Tel: (046) XXX-XXXX</p>
</div>

<!-- Document Title -->
<div class="title">
    <?= strtoupper($issuance['document_type']) ?>
</div>

<!-- Document Content -->
<div class="content">
    <?php if ($issuance['document_type'] == 'Barangay Clearance'): ?>
        <p>TO WHOM IT MAY CONCERN:</p>
        <p style="text-indent: 50px;">
            This is to certify that <strong><?= strtoupper($residentData['first_name'] . ' ' . $residentData['last_name']) ?></strong>, 
            of legal age, Filipino, and a resident of <strong><?= $residentData['address'] ?>, Barangay Langkaan II, Dasmariñas City, Cavite</strong>, 
            is personally known to me to be of good moral character and a law-abiding citizen of this barangay.
        </p>
        <p style="text-indent: 50px;">
            This certification is issued upon the request of the above-named person for <strong><?= strtoupper($issuance['purpose']) ?></strong>.
        </p>
        <p style="text-indent: 50px;">
            Issued this <strong><?= date('jS') ?></strong> day of <strong><?= date('F Y') ?></strong> at Barangay Langkaan II, Dasmariñas City, Cavite.
        </p>
    
    <?php elseif ($issuance['document_type'] == 'Certificate of Indigency'): ?>
        <p>TO WHOM IT MAY CONCERN:</p>
        <p style="text-indent: 50px;">
            This is to certify that <strong><?= strtoupper($residentData['first_name'] . ' ' . $residentData['last_name']) ?></strong>, 
            of legal age, Filipino, and a resident of <strong><?= $residentData['address'] ?>, Barangay Langkaan II, Dasmariñas City, Cav            of legal age, Filipino, and a resident of <strong><?= $residentData['address'] ?>, Barangay Langkaan II, Dasmariñas City, Cavite</strong>, 
            belongs to an indigent family in this barangay.
        </p>
        <p style="text-indent: 50px;">
            This certification is issued upon the request of the above-named person for <strong><?= strtoupper($issuance['purpose']) ?></strong>.
        </p>
        <p style="text-indent: 50px;">
            Issued this <strong><?= date('jS') ?></strong> day of <strong><?= date('F Y') ?></strong> at Barangay Langkaan II, Dasmariñas City, Cavite.
        </p>
    
    <?php elseif ($issuance['document_type'] == 'Certificate of Residency'): ?>
        <p>TO WHOM IT MAY CONCERN:</p>
        <p style="text-indent: 50px;">
            This is to certify that <strong><?= strtoupper($residentData['first_name'] . ' ' . $residentData['last_name']) ?></strong>, 
            of legal age, Filipino, is a bonafide resident of <strong><?= $residentData['address'] ?>, Barangay Langkaan II, Dasmariñas City, Cavite</strong>, 
            since <strong><?= date('Y', strtotime($residentData['created_at'])) ?></strong>.
        </p>
        <p style="text-indent: 50px;">
            This certification is issued upon the request of the above-named person for <strong><?= strtoupper($issuance['purpose']) ?></strong>.
        </p>
        <p style="text-indent: 50px;">
            Issued this <strong><?= date('jS') ?></strong> day of <strong><?= date('F Y') ?></strong> at Barangay Langkaan II, Dasmariñas City, Cavite.
        </p>
    <?php endif; ?>
</div>

<!-- Signature Section -->
<div class="signature">
    <div>
        <div class="line"></div>
        <p style="margin: 5px 0; font-weight: bold;">HON. JUAN DELA CRUZ</p>
        <p style="margin: 0; font-size: 14px;">Barangay Captain</p>
    </div>
</div>

<!-- Document Control Number -->
<div style="margin-top: 50px; font-size: 12px; color: #666;">
    <p><strong>Control No:</strong> <?= str_pad($issuance['issuance_id'], 6, '0', STR_PAD_LEFT) ?></p>
    <p><strong>Date Issued:</strong> <?= date('F d, Y', strtotime($issuance['approved_at'])) ?></p>
    <p><strong>Valid Until:</strong> <?= date('F d, Y', strtotime($issuance['expires_at'])) ?></p>
</div>

<script>
// Auto-print on load (optional)
// window.onload = function() { window.print(); }
</script>

</body>
</html>