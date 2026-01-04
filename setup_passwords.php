<?php
require_once 'backend/db_connect.php';

echo "<h1>Updating Staff Passwords...</h1>";

// Format: 'Email' => 'GUSTO MONG PASSWORD'
$accounts = [
    // 1. Secretary (Sec. Juan)
    'sec.juan@langkaan2.com' => 'secJuan2026', 

    // 2. Staff 1 (Maria)
    'maria.staff1@langkaan2.com' => 'mariaStaff#1',

    // 3. Staff 2 (Roberto)
    'roberto.staff2@langkaan2.com' => 'robertoStaff#2'
];

try {
    foreach ($accounts as $email => $plain_password) {
        // I-hash ang password
        $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

        // I-update sa database
        $stmt = $conn->prepare("UPDATE users SET password = :pass WHERE email = :email");
        $stmt->execute([
            ':pass' => $hashed_password,
            ':email' => $email
        ]);

        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green'>✅ Password updated for: <strong>$email</strong> (Password: $plain_password)</p>";
        } else {
            echo "<p style='color:orange'>⚠️ No changes for: <strong>$email</strong> (Baka mali ang email o updated na)</p>";
        }
    }
    echo "<hr><h3>SUCCESS! Pwede mo na i-delete ang file na ito.</h3>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>