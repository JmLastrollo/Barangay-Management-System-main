<?php
require_once 'auth_admin.php';
require_once 'config.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? '';

if (!$id) {
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

try {
    $doc = $issuanceCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);

    if (!$doc) {
        echo json_encode(['error' => 'Not found']);
        exit;
    }

    echo json_encode([
        'id' => (string)$doc['_id'],
        'resident_name' => $doc['resident_name'] ?? '',
        'resident_email' => $doc['resident_email'] ?? '',
        'document_type' => $doc['document_type'] ?? '',
        'purpose' => $doc['purpose'] ?? '',
        'certificate_for' => $doc['certificate_for'] ?? '',
        'certificate_for_fullname' => $doc['certificate_for_fullname'] ?? '',
        'business_name' => $doc['business_name'] ?? '',
        'business_location' => $doc['business_location'] ?? '',
        'reason' => $doc['reason'] ?? '',
        'request_date' => $doc['request_date'] ?? '',
        'request_time' => $doc['request_time'] ?? '',
        'status' => ucwords(strtolower($doc['status'] ?? 'Pending')),

        'certificate_for' => $doc['certificate_for'] ?? '',
        'certificate_for_fullname' => $doc['certificate_for_fullname'] ?? '',
        'certificate_other_relationship' => $doc['certificate_other_relationship'] ?? ''
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
