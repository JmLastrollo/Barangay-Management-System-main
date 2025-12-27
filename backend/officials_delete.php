<?php
require "config.php";

if (isset($_POST["id"])) {

    $officialsCollection->deleteOne([
        "_id" => new MongoDB\BSON\ObjectId($_POST["id"])
    ]);

    header("Location: ../pages/admin/admin_officials_archive.php");
    exit();
}

echo "Missing ID";
?>
