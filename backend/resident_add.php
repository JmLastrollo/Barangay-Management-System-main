<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Get & Sanitize Data
    $fname = trim($_POST['first_name']);
    $mname = trim($_POST['middle_name']);
    $lname = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact_no']);
    $phase = $_POST['purok']; 
    $address = trim($_POST['address']);
    $bday = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $occupation = trim($_POST['occupation']);
    $civil_status = $_POST['civil_status'];
    $is_pwd = $_POST['is_pwd'];
    
    // NEW FIELDS
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $household_no = trim($_POST['household_no']);
    $resident_since = $_POST['resident_since'];
    $income = $_POST['monthly_income'];
    $is_family_head = $_POST['is_family_head'];
    $voter_status = $_POST['voter_status'];

    // Password Handling
    $raw_password = $_POST['password']; 
    $password = password_hash($raw_password, PASSWORD_DEFAULT);
    $status = 'Active'; 

    // Compute Age
    $dob = new DateTime($bday);
    $now = new DateTime();
    $age = $now->diff($dob)->y;

    // 2. Handle Image Upload (Ensure NULL if empty)
    $image_name = null; // Default to NULL
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $upload_dir = "../uploads/residents/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $image_name = "res_" . time() . "." . $file_ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $image_name);
    }

    try {
        $conn->beginTransaction();

        // Check Email
        $check = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $check->execute([':email' => $email]);
        if($check->fetchColumn() > 0) {
            throw new Exception("Email already exists.");
        }

        // Insert into Users Table
        $userSql = "INSERT INTO users (email, password, first_name, last_name, role, status) 
                    VALUES (:email, :pass, :fname, :lname, 'Resident', :status)";
        $userStmt = $conn->prepare($userSql);
        $userStmt->execute([
            ':email' => $email,
            ':pass' => $password,
            ':fname' => $fname,
            ':lname' => $lname,
            ':status' => $status
        ]);
        $user_id = $conn->lastInsertId();

        // Insert into Resident Profiles (Complete Fields)
        $sql = "INSERT INTO resident_profiles 
                (user_id, first_name, middle_name, last_name, email, contact_no, purok, address, 
                 city, province, household_no, resident_since, birthdate, age, gender, occupation, 
                 monthly_income, civil_status, is_pwd, voter_status, is_family_head, status, image) 
                VALUES 
                (:uid, :fname, :mname, :lname, :email, :contact, :phase, :addr, 
                 :city, :prov, :hh_no, :since, :bday, :age, :gender, :occup, 
                 :income, :civil, :pwd, :voter, :fam_head, :status, :image)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':uid' => $user_id,
            ':fname' => $fname,
            ':mname' => $mname,
            ':lname' => $lname,
            ':email' => $email,
            ':contact' => $contact,
            ':phase' => $phase,
            ':addr' => $address,
            ':city' => $city,
            ':prov' => $province,
            ':hh_no' => $household_no,
            ':since' => $resident_since,
            ':bday' => $bday,
            ':age' => $age,
            ':gender' => $gender,
            ':occup' => $occupation,
            ':income' => $income,
            ':civil' => $civil_status,
            ':pwd' => $is_pwd,
            ':voter' => $voter_status,
            ':fam_head' => $is_family_head,
            ':status' => $status,
            ':image' => $image_name 
        ]);

        $conn->commit();
        header("Location: ../pages/admin/resident_list.php?success=added");
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>