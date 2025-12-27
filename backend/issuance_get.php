<?php
require_once 'auth_resident.php';
require_once 'config.php';

header('Content-Type: application/json');

$residentEmail = $_SESSION['email'] ?? '';
if (!$residentEmail) {
    echo json_encode([]);
    exit;
}

$cursor = $issuanceCollection->find(
    ['resident_email' => $residentEmail],
    ['sort' => ['request_date' => -1]]
);

$data = [];
foreach ($cursor as $r) {
    $data[] = [
        '_id' => (string)$r['_id'],
        'document_type' => $r['document_type'] ?? '',

        // Indigency
        'purpose' => $r['purpose'] ?? '',
        'certificate_for' => $r['certificate_for'] ?? '',
        'certificate_other_relationship' => $r['certificate_other_relationship'] ?? '',
        'certificate_for_fullname' => $r['certificate_for_fullname'] ?? '',

        // Business clearance
        'business_name' => $r['business_name'] ?? '',
        'business_location' => $r['business_location'] ?? '',

        // Shared
        'reason' => $r['reason'] ?? '',
        'request_date' => isset($r['request_date']) ? date('Y-m-d', strtotime($r['request_date'])) : '',
        'request_time' => isset($r['request_time']) ? date('H:i', strtotime($r['request_time'])) : '',
        'status' => $r['status'] ?? 'Pending'
    ];
}
 
echo json_encode($data);
