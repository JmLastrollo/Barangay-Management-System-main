<?php
require_once "config.php";
require_once "auth_admin.php";

header("Content-Type: application/json");

// Validate ID
if (!isset($_POST["issuance_id"]) || empty($_POST["issuance_id"])) {
    echo json_encode(["status" => "error", "message" => "Missing request ID."]);
    exit();
}

$id = $_POST["issuance_id"];
$status = $_POST["status"] ?? "Pending";

// Convert to CamelCase
$status = ucwords(strtolower($status));

// Prepare fields to update
$updateFields = ['status' => $status];

// Optional editable fields
if (isset($_POST['certificate_for'])) $updateFields['certificate_for'] = trim($_POST['certificate_for']);
if (isset($_POST['purpose'])) $updateFields['purpose'] = trim($_POST['purpose']);
if (isset($_POST['business_name'])) $updateFields['business_name'] = trim($_POST['business_name']);
if (isset($_POST['business_location'])) $updateFields['business_location'] = trim($_POST['business_location']);
if (isset($_POST['reason'])) $updateFields['reason'] = trim($_POST['reason']);

try {
    $issuanceCollection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($id)],
        ['$set' => $updateFields]
    );

    // AJAX response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_POST['ajax'])) {
        echo json_encode(["status" => "success", "newStatus" => $status]);
        exit();
    }

    // Non-AJAX redirect
    header("Location: ../pages/admin/admin_issuance.php");
    exit();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit();
}
