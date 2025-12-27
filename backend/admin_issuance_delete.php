<?php
require_once "config.php";
require_once "auth_admin.php";

if (!isset($_POST["issuance_id"]) || empty($_POST["issuance_id"])) {
    die("Error: Missing request ID.");
}

$id = $_POST["issuance_id"];

$issuanceCollection->deleteOne([
    "_id" => new MongoDB\BSON\ObjectId($id)
]);

header("Location: ../pages/admin/admin_issuance_archive.php");
exit();
?>
