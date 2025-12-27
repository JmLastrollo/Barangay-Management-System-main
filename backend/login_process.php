<?php 
session_start(); 
require_once 'config.php'; 

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$postedRole = trim($_POST['role'] ?? '');

$isResidentForm = ($postedRole === '');
$loginPage = $isResidentForm ? '../resident_login.php' : '../admin_login.php';

// 1. Validation
if (empty($email) || empty($password)) {
    $_SESSION['toast'] = "Email and password are required";
    $_SESSION['toast_type'] = "warn";
    header("Location: $loginPage"); 
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['toast'] = "Invalid email format";
    $_SESSION['toast_type'] = "error";
    header("Location: $loginPage"); 
    exit();
}

// 2. Database Query
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['toast'] = "System error: " . $e->getMessage();
    $_SESSION['toast_type'] = "error";
    header("Location: $loginPage");
    exit();
}

// 3. User Not Found Check
if (!$user) {
    $_SESSION['toast'] = "User does not exist";
    $_SESSION['toast_type'] = "error";
    header("Location: $loginPage"); 
    exit();
}

// 4. Verify Password (THE FIX)
// Check kung plain text ba O hashed ang password
$is_plain_text_match = ($password === $user['password']); // Check kung plain text
$is_hash_match = password_verify($password, $user['password']); // Check kung encrypted

if (!$is_plain_text_match && !$is_hash_match) {
    $_SESSION['toast'] = "Incorrect password";
    $_SESSION['toast_type'] = "error";
    header("Location: $loginPage"); 
    exit();
}

// 5. Role Checking
$userRole = $user['user_type'] ?? 'Resident'; 

// Kung nag-login sa Admin form pero Resident account lang
if (!$isResidentForm && ($userRole !== 'Admin' && $userRole !== 'Staff')) {
     $_SESSION['toast'] = "Access Denied: Only Officials can login here.";
     $_SESSION['toast_type'] = "error";
     header("Location: $loginPage"); 
     exit();
}

// 6. Session Setup
session_regenerate_id(true);
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $userRole;
$_SESSION['user_id'] = $user['user_id'];  
$_SESSION['username'] = $user['username']; 
$_SESSION['status'] = $user['status'];

// 7. Status Checks
if ($userRole === 'Resident') {
    if ($user['status'] === 'Pending') {
        session_unset(); session_destroy();
        $_SESSION['toast'] = "Account pending approval";
        $_SESSION['toast_type'] = "warn";
        header("Location: $loginPage");
        exit();
    }
    if ($user['status'] === 'Inactive' || $user['status'] === 'Rejected') {
        session_unset(); session_destroy();
        $_SESSION['toast'] = "Account inactive or rejected";
        $_SESSION['toast_type'] = "error";
        header("Location: $loginPage");
        exit();
    }
}

// 8. Redirect
if ($userRole === 'Staff' || $userRole === 'Admin') {
    header("Location: ../pages/admin/admin_dashboard.php");
} else {
    header("Location: ../pages/resident/resident_dashboard.php");
}
exit();
?>