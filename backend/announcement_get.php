<?php
require_once "config.php";

header("Content-Type: application/json");

$announcements = $announcementCollection->find(
    ["status" => "active"],
    ["sort" => ["date" => -1, "time" => -1]]
);

$output = [];

foreach ($announcements as $a) {
    $output[] = [
        "_id" => (string)$a->_id,
        "title" => $a->title ?? "",
        "details" => $a->details ?? "",
        "location" => $a->location ?? "",
        "date" => $a->date ?? "",
        "time" => $a->time ?? "",
        "image" => $a->image ?? ""
    ];
}

echo json_encode($output);
?>
