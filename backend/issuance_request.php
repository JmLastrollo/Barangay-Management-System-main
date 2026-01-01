<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

$email = $_SESSION['email'];

$stmt = $conn->prepare("SELECT * FROM resident_profiles WHERE email = :email");
$stmt->execute([':email' => $email]);
$resident = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resident) {
    echo json_encode(['status'=>'error', 'message'=>'Resident not found']);
    exit;
}

$docType = $_POST['document_type'];
$purpose = $_POST['purpose'];
$price = $_POST['price'];
$busName = $_POST['business_name'] ?? null;
$busLoc = $_POST['business_location'] ?? null;
$fullName = $resident['first_name'] . ' ' . $resident['last_name'];

try {
    $sql = "INSERT INTO issuance 
            (resident_id, resident_name, document_type, purpose, business_name, business_location, price, status, request_date) 
            VALUES (:rid, :rname, :dtype, :purpose, :bname, :bloc, :price, 'Pending', NOW())";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':rid' => $resident['resident_id'],
        ':rname' => $fullName,
        ':dtype' => $docType,
        ':purpose' => $purpose,
        ':bname' => $busName,
        ':bloc' => $busLoc,
        ':price' => $price
    ]);

    echo json_encode(['status'=>'success', 'message'=>'Request Submitted Successfully']);
} catch (Exception $e) {
    echo json_encode(['status'=>'error', 'message'=>'Database Error: ' . $e->getMessage()]);
}
?>