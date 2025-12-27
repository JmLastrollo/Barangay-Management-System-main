<?php
require_once "config.php";

$title = $_POST["title"];
$details = $_POST["details"];
$location = $_POST["location"];
$date = $_POST["date"];
$time = $_POST["time"];

$filename = "";

if (!empty($_FILES["photo"]["name"])) {
    $filename = time() . "_" . basename($_FILES["photo"]["name"]);
    $target = "../uploads/announcements/" . $filename;
    move_uploaded_file($_FILES["photo"]["tmp_name"], $target);
}

$announcementCollection->insertOne([
    "title"   => $title,
    "details" => $details,
    "location"=> $location,
    "date"    => $date,
    "time"    => $time,
    "image"   => $filename,
    "status"  => "active",
]);

header("Location: ../pages/admin/admin_announcement.php");
exit;
?>
