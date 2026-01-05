<?php
session_start();
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['toast'] = ["msg" => "Invalid request method.", "type" => "error"];
    header("Location: ../pages/resident/resident_dashboard.php");
    exit;
}

// Kunin ang Resident ID (Primary Key sa resident_profiles)
$residentId = $_POST["user_id"]; 

// Image Upload Logic
$uploadedImage = $_FILES['profile_image'] ?? null;
$existingImage = $_POST['existing_image'] ?? "";
$finalImageName = $existingImage; 

if ($uploadedImage && $uploadedImage['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($uploadedImage['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array(strtolower($ext), $allowed)) {
        $newFileName = uniqid("res_") . "." . $ext;
        $destination = "../uploads/residents/" . $newFileName;

        if (move_uploaded_file($uploadedImage["tmp_name"], $destination)) {
            $finalImageName = $newFileName;
        }
    }
}

try {
    // UPDATE Query (MySQL)
    $sql = "UPDATE resident_profiles SET 
            first_name = :fname,
            middle_name = :mname,
            last_name = :lname,
            occupation = :occup,
            contact_no = :contact,
            email = :email,
            civil_status = :civil,
            profile_image = :img
            WHERE resident_id = :rid";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':fname'  => $_POST["fname"],
        ':mname'  => $_POST["mname"],
        ':lname'  => $_POST["lname"],
        ':occup'  => $_POST["occupation"],
        ':contact'=> $_POST["contact"],
        ':email'  => $_POST["email"],
        ':civil'  => $_POST["civil_status"],
        ':img'    => $finalImageName,
        ':rid'    => $residentId
    ]);

    // Optional: Update din ang users table kung nagbago ang name/email
    $userSql = "UPDATE users SET first_name = :fname, last_name = :lname, email = :email 
                WHERE email = (SELECT email FROM resident_profiles WHERE resident_id = :rid)";
    $userStmt = $conn->prepare($userSql);
    $userStmt->execute([
        ':fname' => $_POST["fname"], 
        ':lname' => $_POST["lname"], 
        ':email' => $_POST["email"], 
        ':rid'   => $residentId
    ]);

    // Update Session kung nagbago email
    $_SESSION['email'] = $_POST["email"];
    
    $_SESSION['toast'] = ["msg" => "Profile updated successfully!", "type" => "success"];

} catch (PDOException $e) {
    $_SESSION['toast'] = ["msg" => "Error updating profile: " . $e->getMessage(), "type" => "error"];
}

header("Location: ../pages/resident/resident_dashboard.php");
exit;
?>