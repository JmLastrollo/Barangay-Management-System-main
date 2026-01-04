<?php
session_start();
require_once 'db_connect.php'; 
require_once 'log_audit.php'; // Siguraduhin na nandito ito

// --- CONFIGURATION: PATHS ---
$loginPage          = '../login.php';
$adminDashboard     = '../pages/admin/admin_dashboard.php';
$staffDashboard     = '../pages/staff/staff_dashboard.php'; 
$residentDashboard  = '../pages/resident/resident_dashboard.php';

// Function para sa Error Message
function redirectWithError($message, $location) {
    $_SESSION['toast'] = $message;
    $_SESSION['toast_type'] = 'danger'; // FIX: Ginawang 'danger' para maging pula (Red)
    header("Location: $location");
    exit();
}

// Check Request Method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize Inputs
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 2. Basic Validation
    if (empty($email) || empty($password)) {
        redirectWithError("Please fill in all fields.", $loginPage);
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
                redirectWithError("Your account is Inactive or Archived. Please contact the Admin.", $loginPage);
            }

            // Check 2: Tama ba ang password?
            if (password_verify($password, $user['password'])) {
                
                // --- SUCCESS LOGIN ---
                
                // Security: Regenerate Session ID
                session_regenerate_id(true);

                // Set Session Variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role']    = $user['role'];
                $_SESSION['email']   = $user['email']; 
                
                // Kunin ang pangalan
                $_SESSION['username'] = explode('@', $user['email'])[0]; 
                $_SESSION['login_welcome'] = true;      

                // ---------------------------------------------------------
                // RECORD SA HISTORY LOGS
                // ---------------------------------------------------------
                logActivity($conn, $user['user_id'], "Logged in to the system");

                // 5. Role Redirection
                $role = $user['role'];

                if ($role === 'Admin') {
                    header("Location: $adminDashboard");
                } elseif ($role === 'Staff') {
                    // FIX: Ginamit na natin ang $staffDashboard variable
                    header("Location: $staffDashboard"); 
                } elseif ($role === 'Resident') {
                    header("Location: $residentDashboard");
                } else {
                    // Unknown Role
                    session_unset();
                    session_destroy();
                    redirectWithError("Access Denied: Invalid User Role.", $loginPage);
                }
                exit();

            } else {
                // Password Mismatch
                redirectWithError("Incorrect Password.", $loginPage);
            }

        } else {
            // Email not found
            redirectWithError("Email address not found.", $loginPage);
        }

    } catch (PDOException $e) {
        redirectWithError("System Error: " . $e->getMessage(), $loginPage);
    }
} else {
    // Kapag in-access ang file nang direkta
    header("Location: $loginPage");
    exit();
}
?>