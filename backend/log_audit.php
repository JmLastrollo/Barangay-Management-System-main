<?php
function logActivity($conn, $userId, $action) {
    try {
        // Prepare SQL: insert user_id, action, and let timestamp be auto-generated
        $stmt = $conn->prepare("INSERT INTO history_logs (user_id, action) VALUES (:uid, :act)");
        $stmt->execute([
            ':uid' => $userId,
            ':act' => $action
        ]);
    } catch (PDOException $e) {
        // Ideally, log this error to a file if database logging fails
    }
}
?>