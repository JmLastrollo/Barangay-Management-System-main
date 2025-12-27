<?php
require_once 'auth_resident.php';
require_once 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';
$reason = isset($data['reason']) ? trim($data['reason']) : null;
$purpose = isset($data['purpose']) ? trim($data['purpose']) : null;

if (!$id) {
    echo json_encode(['status'=>'error','message'=>'Missing required fields']);
    exit;
}

// build $set array
$set = [];
if ($reason !== null) $set['reason'] = $reason;
if ($purpose !== null) $set['purpose'] = $purpose;

if (empty($set)) {
    echo json_encode(['status'=>'error','message'=>'Nothing to update']);
    exit;
}

try {
    $updateResult = $issuanceCollection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($id)],
        ['$set' => $set]
    );

    if ($updateResult->getModifiedCount() > 0) {
        echo json_encode(['status'=>'success','message'=>'Request updated successfully']);
    } else {
        // even if no modified (same values), reply success to keep UI simple
        echo json_encode(['status'=>'success','message'=>'No changes were made or update saved']);
    }
} catch (Exception $e) {
    echo json_encode(['status'=>'error','message'=>'Update failed: ' . $e->getMessage()]);
}
