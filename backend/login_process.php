<?php
session_start();
// TANDAAN: Dahil nasa loob tayo ng 'backend' folder, kailangan nating lumabas 
// ng isang beses (../) para puntahan ang includes folder.
require_once '../backend/db_connect.php'; 

// --- CONFIGURATION: PATHS ---
// Ito ang mga pupuntahan kapag tapos na mag-process
$adminDashboard = '../pages/admin/admin_dashboard.php';
$loginPage      = '../admin_login.php'; 

// Function para sa Error Message at Redirect pabalik sa Login
function redirectWithError($message, $location) {
    $_SESSION['toast'] = $message;
    $_SESSION['toast_type'] = 'error';
    header("Location: $location");
    exit();
}

// 1. RECEIVE INPUTS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 2. VALIDATION
    if (empty($email) || empty($password)) {
        redirectWithError("Please fill in all fields.", $loginPage);
    }

    try {
        // 3. DATABASE CHECK
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 4. VERIFY USER & PASSWORD
        if ($user && password_verify($password, $user['password'])) {
            
            // 5. CHECK ROLE (Dapat Admin o Staff lang ang makapasok dito)
            // Tinanggal ko ang dependence sa $_POST['role'] para iwas hack.
            // Sa database tayo titingin ng role.
            $allowed_roles = ['Admin', 'Staff', 'Barangay Staff'];

            if (in_array($user['user_type'], $allowed_roles)) {
                
                // SUCCESS: Set Session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role']    = $user['user_type'];
                $_SESSION['username'] = $user['username'];
                // $_SESSION['fullname'] = $user['fullname']; // Kung meron man

                // Redirect to Dashboard
                header("Location: $adminDashboard");
                exit();

            } else {
                redirectWithError("Access Denied: You are not an Admin.", $loginPage);
            }

        } else {
            redirectWithError("Invalid Email or Password.", $loginPage);
        }

    } catch (PDOException $e) {
        redirectWithError("System Error: " . $e->getMessage(), $loginPage);
    }
} else {
    // Kapag in-access ang file na ito ng hindi nag-submit ng form
    header("Location: $loginPage");
    exit();
}
?>