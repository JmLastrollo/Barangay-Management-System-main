<?php
session_start();

// 1. DB CONNECTION
// Dahil nasa loob na tayo ng 'backend' folder at ang db_connect.php ay nandito rin:
require_once 'db_connect.php'; 

// --- CONFIGURATION: PATHS ---
$loginPage          = '../login.php'; // Updated: Iisa na lang ang login page
$adminDashboard     = '../pages/admin/admin_dashboard.php';
$residentDashboard  = '../pages/resident/resident_dashboard.php'; // Added: Path para sa residents

// Function para sa Error Message
function redirectWithError($message, $location) {
    $_SESSION['toast'] = $message;
    $_SESSION['toast_type'] = 'error';
    header("Location: $location");
    exit();
}

// 2. RECEIVE INPUTS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // VALIDATION
    if (empty($email) || empty($password)) {
        redirectWithError("Please fill in all fields.", $loginPage);
    }

    try {
        // 3. DATABASE CHECK
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 4. VERIFY USER & PASSWORD
        if ($user && password_verify($password, $user['password'])) {
            
            // --- SET SESSION VARIABLES (Common sa lahat) ---
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role']    = $user['user_type']; // Assumed column name: 'user_type'
            
            // Check kung anong column name ang gamit mo para sa pangalan (username or full_name)
            $_SESSION['username'] = $user['username'] ?? $user['full_name']; 

            // 5. ROLE TRAFFIC ENFORCER (Dito nagkakahiwalay ang landas)
            $role = $user['user_type'];
            
            // Listahan ng Admin Roles
            $admin_roles = ['Admin', 'Staff', 'Barangay Staff'];

            if (in_array($role, $admin_roles)) {
                // KUNG ADMIN/STAFF -> Punta sa Admin Dashboard
                header("Location: $adminDashboard");
                exit();

            } elseif ($role === 'Resident') {
                // KUNG RESIDENT -> Punta sa Resident Dashboard
                header("Location: $residentDashboard");
                exit();

            } else {
                // KUNG WALANG ROLE -> Error
                // Logout muna para malinis ang session
                session_unset();
                session_destroy();
                redirectWithError("Access Denied: Invalid User Role.", $loginPage);
            }

        } else {
            redirectWithError("Invalid Email or Password.", $loginPage);
        }

    } catch (PDOException $e) {
        redirectWithError("System Error: " . $e->getMessage(), $loginPage);
    }
} else {
    // Kapag in-access ang file na ito nang hindi nag-submit ng form
    header("Location: $loginPage");
    exit();
}
?>