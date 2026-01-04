<?php
session_start();
require_once 'db_connect.php';

// Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['resident_id']) && isset($_POST['status'])) {
    
    $resident_id = $_POST['resident_id'];
    $new_status = $_POST['status'];
    $admin_id = $_SESSION['user_id']; 
    
    // Check for redirect flag
    $redirect_to = (isset($_POST['redirect']) && $_POST['redirect'] === 'archive') 
        ? '../pages/admin/resident_archive.php?success=restored' 
        : '../pages/admin/resident_list.php?success=updated';

    try {
        $conn->beginTransaction();

        // 1. GET RESIDENT INFO (Para sa logs)
        $stmtGet = $conn->prepare("SELECT user_id, first_name, last_name FROM resident_profiles WHERE resident_id = :rid");
        $stmtGet->execute([':rid' => $resident_id]);
        $resident = $stmtGet->fetch(PDO::FETCH_ASSOC);

        if (!$resident) {
            throw new Exception("Resident not found.");
        }

        $resident_name = $resident['first_name'] . ' ' . $resident['last_name'];
        $linked_user_id = $resident['user_id'];

        // 2. UPDATE RESIDENT PROFILE STATUS
        $stmtUpdateProfile = $conn->prepare("UPDATE resident_profiles SET status = :status WHERE resident_id = :rid");
        $stmtUpdateProfile->execute([
            ':status' => $new_status,
            ':rid' => $resident_id
        ]);

        // 3. UPDATE LINKED USER ACCOUNT STATUS (Kung meron)
        if ($linked_user_id) {
            $login_status = ($new_status === 'Active') ? 'Active' : 'Inactive';
            if ($new_status === 'Rejected') $login_status = 'Pending'; 

            $stmtUpdateUser = $conn->prepare("UPDATE users SET status = :stat WHERE user_id = :uid");
            $stmtUpdateUser->execute([
                ':stat' => $login_status,
                ':uid' => $linked_user_id
            ]);
        }

        // 4. INSERT INTO HISTORY LOGS (Match sa DB mo)
        // Table: history_logs (log_id, user_id, action, timestamp)
        $action_desc = "Updated resident status for $resident_name to $new_status";
        
        $stmtLog = $conn->prepare("INSERT INTO history_logs (user_id, action, timestamp) VALUES (:uid, :act, NOW())");
        $stmtLog->execute([
            ':uid' => $admin_id,
            ':act' => $action_desc
        ]);

        // 5. COMMIT
        $conn->commit();

        header("Location: " . $redirect_to);
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        header("Location: ../pages/admin/resident_list.php?error=failed");
        exit();
    }

} else {
    header("Location: ../pages/admin/resident_list.php");
    exit();
}
?>