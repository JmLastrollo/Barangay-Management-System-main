<?php
// Function para mag-save sa history_logs table
function logActivity($conn, $userId, $action) {
    try {
        // Siguraduhing tama ang spelling ng columns: user_id, action
        // Ang timestamp ay automatic na sa database (CURRENT_TIMESTAMP)
        $stmt = $conn->prepare("INSERT INTO history_logs (user_id, action) VALUES (:uid, :act)");
        $stmt->execute([
            ':uid' => $userId,
            ':act' => $action
        ]);
    } catch (PDOException $e) {
        // Silent error lang (huwag i-stop ang login pag nag-fail ang log)
    }
}
?>