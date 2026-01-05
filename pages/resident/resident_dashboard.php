<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

$email = $_SESSION['email'];

$stmtUser = $conn->prepare("SELECT * FROM users WHERE email = :email");
$stmtUser->execute([':email' => $email]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Error: User not found.");
}

$stmtRes = $conn->prepare("SELECT * FROM resident_profiles WHERE user_id = :uid");
$stmtRes->execute([':uid' => $user['user_id']]);
$resident = $stmtRes->fetch(PDO::FETCH_ASSOC);

if (!$resident) {
    die("Error: Resident record not found.");
}
$fullName = $resident['first_name'] . " " . $resident['last_name'];
$profileImg = !empty($resident['profile_image']) 
    ? "../../uploads/residents/" . $resident['profile_image'] 
    : "../../assets/img/profile.jpg";
?>