<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $case_no = $_POST["case_no"];
    $date_filed = $_POST["date_filed"];       
    $date_happened = $_POST["date_happened"];
    $complainant = $_POST["complainant"];
    $respondent = $_POST["respondent"];
    $subject = $_POST["subject"];
    $description = $_POST["description"];

    $document = [
        "case_no" => $case_no,
        "date_filed" => $date_filed,
        "date_happened" => $date_happened,
        "complainant" => $complainant,
        "respondent" => $respondent,
        "subject" => $subject,
        "description" => $description,
        "status" => "active",
    ];

    $incidentsCollection->insertOne($document);

    header("Location: ../pages/admin/admin_rec_blotter.php");
    exit();
}
?>