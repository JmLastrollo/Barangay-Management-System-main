<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Get ID
    $id = $_POST['resident_id'];

    // 2. Get Data 
    $fname = trim($_POST['first_name']);
    $mname = trim($_POST['middle_name']);
    $lname = trim($_POST['last_name']);
    $contact = trim($_POST['contact_no']);
    $email = trim($_POST['email']);
    $phase = $_POST['purok'];
    $address = trim($_POST['address']);
    $is_pwd = $_POST['is_pwd'];
    $voter = $_POST['voter_status'];
    $occupation = trim($_POST['occupation']);

    try {
        $sql = "UPDATE resident_profiles SET 
                first_name = :fname,
                middle_name = :mname,
                last_name = :lname,
                contact_no = :contact,
                email = :email,
                purok = :phase,
                address = :address,
                is_pwd = :pwd,
                voter_status = :voter,
                occupation = :job
                WHERE resident_id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':fname' => $fname,
            ':mname' => $mname,
            ':lname' => $lname,
            ':contact' => $contact,
            ':email' => $email,
            ':phase' => $phase,
            ':address' => $address,
            ':pwd' => $is_pwd,
            ':voter' => $voter,
            ':job' => $occupation,
            ':id' => $id
        ]);

        // Success Redirect
        header("Location: ../pages/admin/resident_list.php?success=updated");
        exit();

    } catch (PDOException $e) {
        // Error handling
        echo "Error updating record: " . $e->getMessage();
    }
}
?>