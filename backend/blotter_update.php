<?php
require_once "config.php";

if (!isset($_POST["blotter_id"])) {
    die("Missing blotter ID.");
}

$id = $_POST["blotter_id"];

$updateFields = [];

$allowedFields = ["case_no", "date_filed", "date_happened", "complainant", "respondent", "subject", "description", "status"];

foreach ($allowedFields as $field) {
    if (isset($_POST[$field]) && $_POST[$field] !== "") {
        $updateFields[$field] = $_POST[$field];
    }
}

$incidentsCollection->updateOne(
    ["_id" => new MongoDB\BSON\ObjectId($id)],
    ['$set' => $updateFields]
);

if (isset($_POST["status"]) && $_POST["status"] === "archived") {
    header("Location: ../pages/admin/admin_rec_blotter_archive.php");
    exit;
}

header("Location: ../pages/admin/admin_rec_blotter.php");
exit;

?>
