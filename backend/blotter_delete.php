<?php
require_once "config.php";

if (!isset($_POST["blotter_id"]) || empty($_POST["blotter_id"])) {
    die("Error: Missing incident ID.");
}

$id = $_POST["blotter_id"];

$incidentsCollection->deleteOne([
    "_id" => new MongoDB\BSON\ObjectId($id)
]);

header("Location: ../pages/admin/admin_rec_blotter_archive.php");
exit();
?>
