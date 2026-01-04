<?php
session_start();
require_once '../../backend/db_connect.php';

if (!isset($_GET['id'])) { die("Request ID not found."); }

$id = $_GET['id'];

// Fetch Record
$sql = "SELECT i.*, 
               CONCAT(rp.first_name, ' ', rp.last_name) as full_name,
               rp.address, rp.civil_status, rp.citizenship
        FROM issuance i
        LEFT JOIN resident_profiles rp ON i.resident_id = rp.resident_id
        WHERE i.issuance_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) { die("Record not found."); }

// Format Title based on Document Type
$docTitle = strtoupper($data['document_type']);
$dateIssued = date('jS \d\a\y \o\f F Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print - <?= $docTitle ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; margin: 40px; color: #000; }
        .header { text-align: center; line-height: 1.2; margin-bottom: 40px; position: relative; }
        .header img { position: absolute; width: 100px; }
        .header .logo-left { left: 20px; top: 0; }
        .header .logo-right { right: 20px; top: 0; }
        .header h4, .header h3 { margin: 0; font-weight: bold; }
        .header h4 { font-size: 14px; }
        .header h3 { font-size: 16px; margin-top: 5px; }
        
        .title { text-align: center; font-size: 24px; font-weight: bold; text-decoration: underline; margin-bottom: 40px; text-transform: uppercase; }
        
        .content { font-size: 16px; line-height: 1.8; text-align: justify; padding: 0 50px; }
        .indent { text-indent: 40px; }
        .bold { font-weight: bold; }
        
        .footer { margin-top: 80px; padding: 0 50px; display: flex; justify-content: flex-end; }
        .signature-box { text-align: center; width: 250px; }
        .signature-line { border-top: 1px solid #000; margin-top: 40px; }
        
        /* Print Settings */
        @media print {
            @page { margin: 0.5in; }
            .no-print { display: none; }
        }
        
        .btn-print {
            position: fixed; top: 20px; right: 20px; 
            padding: 10px 20px; background: #0d6efd; color: white; 
            border: none; border-radius: 5px; cursor: pointer; font-family: sans-serif;
        }
    </style>
</head>
<body>

    <button class="btn-print no-print" onclick="window.print()">Print Document</button>

    <div class="header">
        <img src="../../assets/img/Langkaan 2 Logo-modified.png" class="logo-left">
        <h4>REPUBLIC OF THE PHILIPPINES</h4>
        <h4>PROVINCE OF CAVITE</h4>
        <h4>CITY OF DASMARIÑAS</h4>
        <h3>BARANGAY LANGKAAN II</h3>
    </div>

    <div class="title">
        <?= $docTitle ?>
    </div>

    <div class="content">
        <p class="bold">TO WHOM IT MAY CONCERN:</p>

        <p class="indent">
            THIS IS TO CERTIFY that <span class="bold"><?= strtoupper($data['full_name']) ?></span>, 
            <?= strtolower($data['civil_status'] ?? 'single') ?>, <?= strtolower($data['citizenship'] ?? 'Filipino') ?> citizen, 
            is a bonafide resident of <?= $data['address'] ?? 'Barangay Langkaan II, Dasmariñas City, Cavite' ?>.
        </p>

        <?php if ($data['document_type'] == 'Certificate of Indigency'): ?>
            <p class="indent">
                This is to certify further that the above-named person belongs to the indigent family in this barangay and is seeking assistance for: <span class="bold"><?= strtoupper($data['purpose']) ?></span>.
            </p>
        <?php elseif ($data['document_type'] == 'Barangay Business Clearance'): ?>
            <p class="indent">
                This clearance is hereby granted for the business <span class="bold"><?= strtoupper($data['business_name']) ?></span> located at <?= $data['business_location'] ?>.
            </p>
        <?php else: ?>
            <p class="indent">
                This certification is being issued upon the request of the interested party for: <span class="bold"><?= strtoupper($data['purpose']) ?></span>.
            </p>
        <?php endif; ?>

        <p class="indent">
            Issued this <span class="bold"><?= $dateIssued ?></span> at Barangay Langkaan II, City of Dasmariñas, Cavite.
        </p>
    </div>

    <div class="footer">
        <div class="signature-box">
            <span class="bold">HON. David John Paulo C. Laudato</span><br>
            Punong Barangay
            <div class="signature-line"></div>
        </div>
    </div>

</body>
</html>