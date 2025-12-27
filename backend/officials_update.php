<?php
require_once "config.php";

$id = $_POST["id"];
$name = $_POST["name"] ?? null;
$position = $_POST["position"] ?? null;
$status = $_POST["status"] ?? null;

$updateData = [];

if ($name) $updateData["name"] = $name;
if ($position) $updateData["position"] = $position;
if ($status) $updateData["status"] = $status;

// HANDLE IMAGE UPLOAD
if (!empty($_FILES["photo"]["name"])) {

    // Ensure folder exists
    $uploadDir = "../uploads/officials/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = time() . "_" . basename($_FILES["photo"]["name"]);
    $target = $uploadDir . $filename;

    move_uploaded_file($_FILES["photo"]["tmp_name"], $target);

    $updateData["image"] = $filename;
}

$officialsCollection->updateOne(
    ["_id" => new MongoDB\BSON\ObjectId($id)],
    ['$set' => $updateData]
);

if ($status === "archived") {
    header("Location: ../pages/admin/admin_officials_archive.php");
} else {
    header("Location: ../pages/admin/admin_officials.php");
}
exit;
?>
