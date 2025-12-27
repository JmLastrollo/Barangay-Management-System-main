<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $residentId = new MongoDB\BSON\ObjectId($_POST["user_id"]);

    $resident = $residentsCollection->findOne(["_id" => $residentId]);

    if (!$resident) {
        die("Resident not found!");
    }

    $userId = $resident->user_id; 

    $residentsCollection->updateOne(
        ["_id" => $residentId],
        ['$set' => [
            "first_name" => $_POST["fname"],
            "middle_name" => $_POST["mname"],
            "last_name" => $_POST["lname"],
            "suffix" => $_POST["sname"],

            "gender" => $_POST["gender"],
            "birthdate" => $_POST["bdate"],
            "birthplace" => $_POST["bplace"],

            "purok" => $_POST["purok"],
            "occupation" => $_POST["occupation"],
            "resident_since" => $_POST["resident_since"],
            "civil_status" => $_POST["civil_status"],

            "email" => $_POST["email"],
            "contact" => $_POST["contact"],

            "voter" => $_POST["voter_status"],
            "income" => $_POST["income"],
            "family_head" => $_POST["family_head"]
        ]]
    );

    $usersCollection->updateOne(
        ["_id" => $userId],
        ['$set' => [
            "status" => $_POST["status"]
        ]]
    );

    header("Location: ../pages/admin/admin_rec_residents.php?success=updated");
    exit;
}
?>
