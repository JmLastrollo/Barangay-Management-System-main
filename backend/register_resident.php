<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- 1. DATA SANITIZATION ---
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $lname = trim($_POST['lname']);
    $sname = trim($_POST['sname']);
    $bdate = $_POST['bdate'];
    $bplace = trim($_POST['bplace']);
    $gender = $_POST['gender'];
    $civil_status = $_POST['civil_status'];
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $purok = $_POST['purok'];
    $household_no = trim($_POST['household_no']);
    $resident_since = $_POST['resident_since'];
    $contact = trim($_POST['contact']);
    $occupation = trim($_POST['occupation']);
    $income = $_POST['income'];
    $voter = $_POST['voter'];
    $family_head = $_POST['family_head'];
    $is_pwd = $_POST['is_pwd'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // --- 2. SERVER-SIDE VALIDATION ---
    
    // Password Match
    if ($password !== $cpassword) {
        $_SESSION['toast'] = ['msg' => 'Passwords do not match.', 'type' => 'error'];
        header("Location: ../register.php");
        exit();
    }

    // Strong Password Check (Backend Enforce)
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/';
    if (!preg_match($pattern, $password)) {
        $_SESSION['toast'] = ['msg' => 'Password is too weak. Please follow requirements.', 'type' => 'error'];
        header("Location: ../register.php");
        exit();
    }

    // --- 3. PWD FILE UPLOAD HANDLING ---
    $pwd_filename = null; // Default null

    if ($is_pwd === 'Yes') {
        if (isset($_FILES['pwd_id_file']) && $_FILES['pwd_id_file']['error'] === UPLOAD_ERR_OK) {
            
            $fileTmpPath = $_FILES['pwd_id_file']['tmp_name'];
            $fileName = $_FILES['pwd_id_file']['name'];
            $fileSize = $_FILES['pwd_id_file']['size'];
            $fileType = $_FILES['pwd_id_file']['type'];
            
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'pdf');

            if (in_array($fileExtension, $allowedfileExtensions)) {
                // Rename file: PWD_Lastname_Timestamp.ext
                $newFileName = 'PWD_' . $lname . '_' . time() . '.' . $fileExtension;
                
                // Directory: Make sure this exists!
                $uploadFileDir = '../uploads/pwd_ids/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
                
                $dest_path = $uploadFileDir . $newFileName;

                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $pwd_filename = $newFileName;
                } else {
                    $_SESSION['toast'] = ['msg' => 'Error moving the PWD file.', 'type' => 'error'];
                    header("Location: ../register.php");
                    exit();
                }
            } else {
                $_SESSION['toast'] = ['msg' => 'Invalid file type. Allowed: JPG, PNG, PDF.', 'type' => 'error'];
                header("Location: ../register.php");
                exit();
            }
        } else {
            $_SESSION['toast'] = ['msg' => 'PWD ID is required for PWD applicants.', 'type' => 'error'];
            header("Location: ../register.php");
            exit();
        }
    }

    // --- 4. DATABASE INSERTION ---
    
    // Hash Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $conn->beginTransaction();

        // Check if email exists
        $stmtCheck = $conn->prepare("SELECT email FROM users WHERE email = :email");
        $stmtCheck->execute([':email' => $email]);
        if ($stmtCheck->rowCount() > 0) {
            throw new Exception("Email already registered.");
        }

        // Insert into Users Table
        $stmtUser = $conn->prepare("INSERT INTO users (email, password, role, status) VALUES (:email, :pass, 'Resident', 'Pending')");
        $stmtUser->execute([':email' => $email, ':pass' => $hashed_password]);
        $user_id = $conn->lastInsertId();

        // Insert into Resident Profiles Table
        // Note: Assumes table has 'pwd_id_file' column. Kung wala, i-add mo sa database structure.
        $sqlProfile = "INSERT INTO resident_profiles 
            (user_id, first_name, middle_name, last_name, suffix_name, birth_date, birth_place, gender, civil_status, 
             house_address, city, province, phase, household_no, resident_since, contact_number, occupation, 
             monthly_income, voter_status, family_head, is_pwd, pwd_id_file) 
            VALUES 
            (:uid, :fname, :mname, :lname, :sname, :bdate, :bplace, :gender, :civil, 
             :addr, :city, :prov, :purok, :house, :since, :contact, :work, 
             :income, :voter, :fam_head, :pwd, :pwd_file)";

        $stmtProfile = $conn->prepare($sqlProfile);
        $stmtProfile->execute([
            ':uid' => $user_id,
            ':fname' => $fname,
            ':mname' => $mname,
            ':lname' => $lname,
            ':sname' => $sname,
            ':bdate' => $bdate,
            ':bplace' => $bplace,
            ':gender' => $gender,
            ':civil' => $civil_status,
            ':addr' => $address,
            ':city' => $city,
            ':prov' => $province,
            ':purok' => $purok,
            ':house' => $household_no,
            ':since' => $resident_since,
            ':contact' => $contact,
            ':work' => $occupation,
            ':income' => $income,
            ':voter' => $voter,
            ':fam_head' => $family_head,
            ':pwd' => $is_pwd,
            ':pwd_file' => $pwd_filename // Will be NULL if No
        ]);

        $conn->commit();

        $_SESSION['toast'] = ['msg' => 'Registration successful! Please wait for admin approval.', 'type' => 'success'];
        header("Location: ../login.php");
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
        header("Location: ../register.php");
        exit();
    }

} else {
    header("Location: ../register.php");
    exit();
}
?>