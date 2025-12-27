<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // If complaint_id exists → Update (archive/restore)
    if (isset($_POST['complaint_id'])) {

        $id = new MongoDB\BSON\ObjectId($_POST['complaint_id']);
        $status = $_POST['status'];

        $contactsCollection->updateOne(
            ['_id' => $id],
            ['$set' => ['status' => $status]]
        );

        // redirect based on status
        if ($status === "archived") {
            header("Location: ../pages/admin/admin_rec_complaints_archive.php?archived=1");
        } else {
            header("Location: ../pages/admin/admin_rec_complaints.php?restored=1");
        }
        exit;
    }

    // Otherwise → INSERT new complaint
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    $contactsCollection->insertOne([
        'fullname' => $fullname,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'date' => new MongoDB\BSON\UTCDateTime(),
        'status' => "active",
    ]);

    header("Location: ../contact.php?success=1");
    exit;
}
?>
