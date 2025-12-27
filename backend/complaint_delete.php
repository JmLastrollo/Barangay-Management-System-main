<?php
require_once 'config.php';

$id = new MongoDB\BSON\ObjectId($_POST['complaint_id']);

$contactsCollection->deleteOne(['_id' => $id]);

header("Location: ../pages/admin/admin_rec_complaints_archive.php?deleted=1");
exit;
