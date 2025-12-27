<?php
require_once 'auth_resident.php';
require_once 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';

if (!$id) {
    echo json_encode(['status'=>'error','message'=>'Missing request ID']);
    exit;
}

// DELETE (not update)
$deleteResult = $issuanceCollection->deleteOne([
    '_id' => new MongoDB\BSON\ObjectId($id)
]);

if ($deleteResult->getDeletedCount() > 0) {
    echo json_encode(['status'=>'success','message'=>'Request deleted successfully']);
} else {
    echo json_encode(['status'=>'error','message'=>'Failed to delete request']);
}
