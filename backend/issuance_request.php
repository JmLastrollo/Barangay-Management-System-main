<?php
require_once 'auth_resident.php';
require_once 'config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$document_type = trim($data['document_type'] ?? '');
$reason = trim($data['reason'] ?? '');

if (!$document_type || !$reason) {
    echo json_encode(['status'=>'error','message'=>'Missing required fields']);
    exit;
}

$residentEmail = $_SESSION['email'] ?? '';
$user = $usersCollection->findOne(['email'=>$residentEmail]);
$resident = $residentsCollection->findOne(['user_id'=>$user['_id'] ?? null]);

if (!$resident) {
    echo json_encode(['status'=>'error','message'=>'Resident not found']);
    exit;
}

$residentId = (string)$resident['_id'];
$fullname = trim($resident['first_name'].' '.($resident['middle_name']??'').' '.$resident['last_name'].' '.($resident['suffix']??''));

$now = new DateTime('now', new DateTimeZone('Asia/Manila'));

$doc = [
    'resident_id' => $residentId,
    'resident_email' => $residentEmail,
    'resident_name' => $fullname,
    'document_type' => $document_type,
    'reason' => $reason,
    'request_date' => $now->format('Y-m-d'),
    'request_time' => $now->format('H:i:s'),
    'status' => 'Pending',
];


if ($document_type === 'Certificate of Indigency') {

    $certificate_for = trim($data['certificate_for'] ?? '');
    $certificate_other_relationship = trim($data['certificate_other_relationship'] ?? '');
    $certificate_for_fullname = trim($data['certificate_for_fullname'] ?? '');
    $purpose = trim($data['purpose'] ?? '');

    if (!$certificate_for) {
        echo json_encode(['status'=>'error','message'=>'Certificate For is required']);
        exit;
    }

    if ($certificate_for !== 'Self' && !$certificate_for_fullname) {
        echo json_encode(['status'=>'error','message'=>'Full name required for selected person']);
        exit;
    }

    if ($certificate_for === 'Other' && !$certificate_other_relationship) {
        echo json_encode(['status'=>'error','message'=>'Specify the relationship for "Other"']);
        exit;
    }

    if (!$purpose) {
        echo json_encode(['status'=>'error','message'=>'Purpose is required']);
        exit;
    }

    $doc['certificate_for'] = $certificate_for;
    $doc['certificate_for_fullname'] = $certificate_for === 'Self' ? '' : $certificate_for_fullname;
    $doc['certificate_other_relationship'] = $certificate_for === 'Other' ? $certificate_other_relationship : '';
    $doc['purpose'] = $purpose;
}

if ($document_type === 'Barangay Business Clearance') {

    $businessName = trim($data['business_name'] ?? '');
    $businessLocation = trim($data['business_location'] ?? '');

    if (!$businessName || !$businessLocation) {
        echo json_encode(['status'=>'error','message'=>'Business name and location required']);
        exit;
    }

    $doc['business_name'] = $businessName;
    $doc['business_location'] = $businessLocation;
}


try {
    $insertResult = $issuanceCollection->insertOne($doc);
    if ($insertResult->getInsertedCount() > 0) {
        echo json_encode(['status'=>'success','message'=>'Request submitted successfully']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to submit request']);
    }
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>'Exception: '.$e->getMessage()]);
}
