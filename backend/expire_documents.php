<?php
/**
 * CRON JOB: Auto-expire documents after 2 days
 * Run this script every hour via cron:
 * 0 * * * * php /path/to/backend/cron/expire_documents.php
 */

require_once '../db_connect.php';

try {
    // Find all "Ready for Pickup" documents that have expired
    $sql = "UPDATE document_issuances 
            SET status = 'Expired',
                admin_remarks = 'Document expired - not claimed within 2 days'
            WHERE status = 'Ready for Pickup' 
            AND expires_at < NOW()";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $expired_count = $stmt->rowCount();
    
    // Log the result
    $log_file = __DIR__ . '/../../logs/expire_log.txt';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_message = date('Y-m-d H:i:s') . " - Expired {$expired_count} document(s)\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
    
    echo "Success: {$expired_count} document(s) expired\n";
    
} catch (PDOException $e) {
    $error_log = __DIR__ . '/../../logs/error_log.txt';
    file_put_contents($error_log, date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo "Error: " . $e->getMessage() . "\n";
}
?>