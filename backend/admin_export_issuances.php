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
            di.purpose,
            di.payment_method,
            di.amount,
            di.status,
            di.requested_at,
            di.approved_at,
            di.released_at,
            CONCAT(staff.first_name, ' ', staff.last_name) AS processed_by
        FROM document_issuances di
        INNER JOIN resident_profiles rp ON di.resident_id = rp.resident_id
        LEFT JOIN users staff ON di.processed_by = staff.user_id
        ORDER BY di.requested_at DESC";

$stmt = $conn->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Issuances_Report_' . date('Y-m-d') . '.xls"');

echo "<table border='1'>";
echo "<thead>";
echo "<tr style='background-color: #4CAF50; color: white; font-weight: bold;'>";
echo "<th>ID</th>";
echo "<th>Resident Name</th>";
echo "<th>Document Type</th>";
echo "<th>Purpose</th>";
echo "<th>Payment Method</th>";
echo "<th>Amount</th>";
echo "<th>Status</th>";
echo "<th>Requested Date</th>";
echo "<th>Approved Date</th>";
echo "<th>Released Date</th>";
echo "<th>Processed By</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

foreach ($data as $row) {
    echo "<tr>";
    echo "<td>" . str_pad($row['issuance_id'], 4, '0', STR_PAD_LEFT) . "</td>";
    echo "<td>" . htmlspecialchars($row['resident_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['document_type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['purpose']) . "</td>";
    echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
    echo "<td>₱" . number_format($row['amount'], 2) . "</td>";
    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
    echo "<td>" . ($row['requested_at'] ? date('M d, Y h:i A', strtotime($row['requested_at'])) : '—') . "</td>";
    echo "<td>" . ($row['approved_at'] ? date('M d, Y h:i A', strtotime($row['approved_at'])) : '—') . "</td>";
    echo "<td>" . ($row['released_at'] ? date('M d, Y h:i A', strtotime($row['released_at'])) : '—') . "</td>";
    echo "<td>" . ($row['processed_by'] ? htmlspecialchars($row['processed_by']) : '—') . "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
exit();
?>