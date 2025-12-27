<?php
require_once "config.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request.");
}

$residentId = new MongoDB\BSON\ObjectId($_POST["user_id"]);

$uploadedImage = $_FILES['profile_image'] ?? null;
$existingImage = $_POST['existing_image'] ?? "";
$finalImageName = $existingImage; 

if ($uploadedImage && $uploadedImage['error'] === UPLOAD_ERR_OK) {

    $ext = pathinfo($uploadedImage['name'], PATHINFO_EXTENSION);
    $newFileName = uniqid("img_") . "." . $ext;
    $destination = "../uploads/residents/" . $newFileName;

    move_uploaded_file($uploadedImage["tmp_name"], $destination);

    $finalImageName = $newFileName;
}

$residentsCollection->updateOne(
    ["_id" => $residentId],
    ['$set' => [
        "first_name" => $_POST["fname"],
        "middle_name" => $_POST["mname"],
        "last_name" => $_POST["lname"],
        "occupation" => $_POST["occupation"],
        "contact" => $_POST["contact"],
        "email" => $_POST["email"],
        "civil_status" => $_POST["civil_status"],
        "profile_image" => $finalImageName,
    ]]
);

header("Location: ../pages/resident/resident_dashboard.php?updated=true");
exit;
?>
