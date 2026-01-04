<?php
session_start();
require_once 'db_connect.php'; // Siguraduhing tama ang path nito relative sa file na ito

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. GET INPUTS & SANITIZE
    $email = trim($_POST['email']);
    $password = $_POST['password']; 
    
    // Personal Info
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $lname = trim($_POST['lname']);
    $sname = trim($_POST['sname']);
    $bdate = $_POST['bdate'];
    $bplace = trim($_POST['bplace']);
    $gender = $_POST['gender'];
    $civil_status = $_POST['civil_status'];
    
    // Address & Demographics
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $purok = $_POST['purok'];
    $household_no = trim($_POST['household_no']);
    $resident_since = $_POST['resident_since'];
    $contact = trim($_POST['contact']);
    $occupation = trim($_POST['occupation']);
    $income = $_POST['income'];
    
    // Dropdowns
    $voter = $_POST['voter']; 
    $family_head = $_POST['family_head'];
    $is_pwd = $_POST['is_pwd'];

    // --- COMPUTE AGE (PHP Side) ---
    // Ito ang solusyon para hindi mag-zero ang age
    $dob = new DateTime($bdate);
    $now = new DateTime();
    $age = $now->diff($dob)->y;

    try {
        // Start Transaction
        $conn->beginTransaction();

        // 2. CHECK DUPLICATE EMAIL
        $checkSql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $conn->prepare($checkSql);
        $stmt->execute([':email' => $email]);
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Email already exists! Please use another one.");
        }

        // 3. INSERT INTO USERS TABLE
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'Resident';
        $status = 'Pending'; 

        $userSql = "INSERT INTO users (email, password, first_name, last_name, role, status) 
                    VALUES (:email, :password, :fname, :lname, :role, :status)";
        
        $userStmt = $conn->prepare($userSql);
        $userStmt->execute([
            ':email' => $email,
            ':password' => $hashed_password,
            ':fname' => $fname, 
            ':lname' => $lname, 
            ':role' => $role,
            ':status' => $status
        ]);
        
        $user_id = $conn->lastInsertId();

        // 4. INSERT INTO RESIDENT_PROFILES TABLE
        $profileSql = "INSERT INTO resident_profiles 
            (user_id, first_name, middle_name, last_name, suffix_name, birthdate, birthplace, age, gender, civil_status, 
             address, city, province, purok, household_no, resident_since, contact_no, email, occupation, monthly_income, 
             voter_status, is_family_head, is_pwd, status) 
            VALUES 
            (:uid, :fname, :mname, :lname, :sname, :bdate, :bplace, :age, :gender, :civil, 
             :addr, :city, :prov, :purok, :hh_no, :res_since, :contact, :email, :occup, :income, 
             :voter, :fam_head, :is_pwd, 'Pending')";
             
        $profileStmt = $conn->prepare($profileSql);
        $profileStmt->execute([
            ':uid' => $user_id,
            ':fname' => $fname,
            ':mname' => $mname,
            ':lname' => $lname,
            ':sname' => $sname,
            ':bdate' => $bdate,
            ':bplace' => $bplace,
            ':age' => $age, // Computed age saved here
            ':gender' => $gender,
            ':civil' => $civil_status,
            ':addr' => $address,
            ':city' => $city,
            ':prov' => $province,
            ':purok' => $purok,
            ':hh_no' => $household_no,
            ':res_since' => $resident_since,
            ':contact' => $contact,
            ':email' => $email, 
            ':occup' => $occupation,
            ':income' => $income,
            ':voter' => $voter,
            ':fam_head' => $family_head,
            ':is_pwd' => $is_pwd
        ]);

        // Commit Transaction
        $conn->commit();

        $_SESSION['toast'] = ["msg" => "Registration successful! Please wait for approval.", "type" => "success"];
        header("Location: ../login.php"); 
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['toast'] = ["msg" => "Error: " . $e->getMessage(), "type" => "error"];
        header("Location: ../register.php");
        exit;
    }
} else {
    // If accessed directly without POST
    header("Location: ../register.php");
    exit;
}
?>