<?php
require_once "config.php";

$id = $_POST["id"];
$title = $_POST["title"] ?? null;
$details = $_POST["details"] ?? null;
$location = $_POST["location"] ?? null;
$date = $_POST["date"] ?? null;
$time = $_POST["time"] ?? null;
$status = $_POST["status"] ?? null; // NEW

$updateData = [];

if ($title) $updateData["title"] = $title;
if ($details) $updateData["details"] = $details;
if ($location) $updateData["location"] =$location;
if ($date) $updateData["date"] = $date;
if ($time) $updateData["time"] = $time;
if ($status) $updateData["status"] = $status; // NEW

/* CHECK IF NEW IMAGE WAS UPLOADED */
if (!empty($_FILES["photo"]["name"])) {
    $filename = time() . "_" . basename($_FILES["photo"]["name"]);
    $target = "../uploads/announcements/" . $filename;
    move_uploaded_file($_FILES["photo"]["tmp_name"], $target);

    $updateData["image"] = $filename;
}

$announcementCollection->updateOne(
    ["_id" => new MongoDB\BSON\ObjectId($id)],
    ['$set' => $updateData]
);

// Redirect depending on status
if ($status === "archived") {
    header("Location: ../pages/admin/admin_announcement_archive.php");
} else {
    header("Location: ../pages/admin/admin_announcement.php");
}
exit;
?>
