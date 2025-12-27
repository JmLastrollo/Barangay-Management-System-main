<?php
require_once "config.php";

header("Content-Type: application/json");

$officials = $officialsCollection->find(
    ["status" => "active"]
);

$output = [];

foreach ($officials as $o) {
    $output[] = [
        "_id" => (string)$o->_id,
        "name" => $o->name ?? "",
        "position" => $o->position ?? "",
        "image" => $o->image ?? ""
    ];
}

echo json_encode($output);
?>