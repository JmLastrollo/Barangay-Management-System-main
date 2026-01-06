<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die('Unauthorized');
}

// Fetch all issuances
$sql = "SELECT 
            di.issuance_id,
            CONCAT(rp.first_name, ' ', rp.middle_name, ' ', rp.last_name) AS resident_name,
            di.document_type,
            di.payment_method,
            di.amount,
            di.status,
            di.requested_at,
            CONCAT(staff.first_name, ' ', staff.last_name) AS processed_by
        FROM document_issuances di
        INNER JOIN resident_profiles rp ON di.resident_id = rp.resident_id
        LEFT JOIN users staff ON di.processed_by = staff.user_id
        ORDER BY di.requested_at DESC";

$stmt = $conn->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count statistics
$counts = [
    'Pending' => 0,
    'Payment Verified' => 0,
    'Ready for Pickup' => 0,
    'Released' => 0,
    'Expired' => 0,
    'Rejected' => 0
];

$totalRevenue = 0;

foreach ($data as $row) {
    if (isset($counts[$row['status']])) {
        $counts[$row['status']]++;
    }
    if ($row['status'] == 'Released') {
        $totalRevenue += $row['amount'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issuances Report - Barangay Langkaan II</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 15px; }
        }
        
        body {
            font-family: Arial, sans-serif;
            background: white;
        }
        
        .report-header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .report-title {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-box {
            border: 2px solid #ddd;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        
        th {
            background-color: #4CAF50;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
        }
    </style>
</head>
<body>

<!-- Print Button -->
<div class="no-print print-btn">
    <button onclick="window.print()" class="btn btn-primary btn-lg">
        <i class="bi bi-printer-fill me-2"></i>Print Report
    </button>
    <a href="../../pages/admin/manage_issuances.php" class="btn btn-secondary btn-lg ms-2">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<!-- Report Header -->
<div class="report-header">
    <div style="font-size: 14px;">Republic of the Philippines</div>
    <div style="font-size: 14px;">Province of Pampanga</div>
    <div style="font-size: 14px;">Municipality of Dasmariñas</div>
    <div style="font-size: 18px; font-weight: bold; margin: 10px 0;">BARANGAY LANGKAAN II</div>
    <div class="report-title">DOCUMENT ISSUANCE REPORT</div>
    <div style="font-size: 12px; color: #666;">Generated on: <?= date('F d, Y h:i A') ?></div>
</div>

<!-- Statistics Summary -->
<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-label">Pending</div>
        <div class="stat-value" style="color: #ff9800;"><?= $counts['Pending'] ?></div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Payment Verified</div>
        <div class="stat-value" style="color: #2196F3;"><?= $counts['Payment Verified'] ?></div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Ready for Pickup</div>
        <div class="stat-value" style="color: #4CAF50;"><?= $counts['Ready for Pickup'] ?></div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Released</div>
        <div class="stat-value" style="color: #009688;"><?= $counts['Released'] ?></div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Expired</div>
        <div class="stat-value" style="color: #f44336;"><?= $counts['Expired'] ?></div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value" style="color: #673AB7;">₱<?= number_format($totalRevenue, 2) ?></div>
    </div>
</div>

<!-- Data Table -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Resident Name</th>
            <th>Document Type</th>
            <th>Payment</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Requested Date</th>
            <th>Processed By</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($data)): ?>
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px; color: #999;">
                    No records found
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= str_pad($row['issuance_id'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($row['resident_name']) ?></td>
                    <td><?= htmlspecialchars($row['document_type']) ?></td>
                    <td><?= htmlspecialchars($row['payment_method']) ?></td>
                    <td>₱<?= number_format($row['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= date('M d, Y', strtotime($row['requested_at'])) ?></td>
                    <td><?= $row['processed_by'] ? htmlspecialchars($row['processed_by']) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Footer -->
<div style="margin-top: 40px; text-align: center; font-size: 11px; color: #666;">
    <p>This is a system-generated report from Barangay Management System</p>
    <p>Barangay Langkaan II, Dasmariñas, Cavite
</div>

</body>
</html>