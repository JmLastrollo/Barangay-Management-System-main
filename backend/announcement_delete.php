<?php
require "config.php";

if (isset($_POST["id"])) {
    $announcementCollection->deleteOne([
        "_id" => new MongoDB\BSON\ObjectId($_POST["id"])
    ]);

    header("Location: ../pages/admin/admin_announcement_archive.php");
    exit();
}
?>
