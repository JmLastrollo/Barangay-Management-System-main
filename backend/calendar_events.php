<?php
require_once "config.php";

header("Content-Type: application/json");

$events = $announcementCollection->find(
    ["status" => "active"],
    ["sort" => ["date" => 1]]
);

$output = [];

foreach ($events as $event) {
    $output[] = [
        "title" => $event->title,
        "details" => $event->details,
        "location" => $event->location,
        "date" => $event->date,
        "time" => $event->time,
        "image" => isset($event->image) ? "../uploads/announcements/" . $event->image : ""
    ];
}

echo json_encode($output);
?>
