<?php
require_once 'auth_admin.php';
require_once 'config.php';

header('Content-Type: application/json');

try {
    $cursor = $issuanceCollection->find([], ['sort' => ['request_date' => -1]]);
    $data = [];

    foreach ($cursor as $doc) {
        $data[] = [
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
            'status' => ucwords(strtolower($doc['status'] ?? 'Pending'))
        ];
    }

    echo json_encode($data);
} catch(Exception $e) {
    echo json_encode([]);
}
