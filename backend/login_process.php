<?php
session_start();
require_once 'db_connect.php'; 
require_once 'log_audit.php'; 

// --- CONFIGURATION: PATHS ---
$loginPage          = '../login.php';
$adminDashboard     = '../pages/admin/admin_dashboard.php';
$staffDashboard     = '../pages/staff/staff_dashboard.php';
$residentDashboard  = '../pages/resident/resident_dashboard.php';

// Check Request Method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize Inputs
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 2. Basic Validation
    if (empty($email) || empty($password)) {
        $_SESSION['toast'] = ['msg' => 'Please fill in all fields.', 'type' => 'error'];
        header("Location: $loginPage");
        exit();
    }

    try {
        // 3. Database Query
        $stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, password, role, status FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 4. Verification Logic
        if ($user) {
            // Check 1: Active ba ang account?
            if ($user['status'] !== 'Active') {
                $_SESSION['toast'] = ['msg' => 'Your account is Inactive or Archived. Please contact Admin.', 'type' => 'error'];
                header("Location: $loginPage");
                exit();
            }

            // Check 2: Tama ba ang password?
            if (password_verify($password, $user['password'])) {
                
                // --- SUCCESS LOGIN ---
                session_regenerate_id(true);

                // Set Session Variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role']    = $user['role'];
                $_SESSION['email']   = $user['email']; 
                $_SESSION['username'] = explode('@', $user['email'])[0]; 
                $_SESSION['login_welcome'] = true;      

                // Record Log
                if (function_exists('logActivity')) {
                    logActivity($conn, $user['user_id'], "Logged in to the system");
                }

                // 5. Role Redirection
                $role = $user['role'];

                if ($role === 'Admin') {
                    header("Location: $adminDashboard");
                } elseif ($role === 'Staff') {
                    header("Location: $staffDashboard"); 
                } elseif ($role === 'Resident') {
                    header("Location: $residentDashboard");
                } else {
                    session_unset();
                    session_destroy();
                    $_SESSION['toast'] = ['msg' => 'Access Denied: Invalid User Role.', 'type' => 'error'];
                    header("Location: $loginPage");
                }
                exit();

            } else {
                // Password Mismatch
                $_SESSION['toast'] = ['msg' => 'Incorrect password. Please try again.', 'type' => 'error'];
                header("Location: $loginPage");
                exit();
            }

        } else {
            // Email not found
            $_SESSION['toast'] = ['msg' => 'Email address not found.', 'type' => 'error'];
            header("Location: $loginPage");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'System Error: ' . $e->getMessage(), 'type' => 'error'];
        header("Location: $loginPage");
        exit();
    }
} else {
    // Kapag in-access ang file nang direkta
    header("Location: $loginPage");
    exit();
}
?>