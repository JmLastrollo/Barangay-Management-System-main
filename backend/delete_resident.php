<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $residentId = new MongoDB\BSON\ObjectId($_POST["resident_id"]);

    $resident = $residentsCollection->findOne(["_id" => $residentId]);

    if (!$resident) {
        die("Resident not found!");
    }


    $usersCollection->deleteOne(["_id" => $resident->user_id]);

    $residentsCollection->deleteOne(["_id" => $residentId]);

    header("Location: ../pages/admin/admin_rec_residents.php?deleted=1");
    exit;
}
?>
