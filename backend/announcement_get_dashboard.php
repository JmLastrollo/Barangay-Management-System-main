<?php
require __DIR__ . '/../vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $db = $client->bms_db;
    $collection = $db->announcements;

    // Only active announcements (not archived)
    $result = $collection->find(
        ["status" => "active"],
        ["sort" => ["date" => 1]]
    );

    $announcements = [];

    foreach ($result as $doc) {
        $announcements[] = [
            "title" => $doc->title ?? "",
            "details" => $doc->details ?? "",
            "location" => $doc->location ?? "",
            "date" => $doc->date ?? "",
            "time" => $doc->time ?? ""
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($announcements);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
