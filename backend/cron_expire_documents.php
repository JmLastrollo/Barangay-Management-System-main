<?php
/**
 * CRON JOB: Auto-expire documents after 2 days
 * Run this script every hour via cPanel Cron Jobs:
 * 0 * * * * /usr/bin/php /path/to/backend/cron_expire_documents.php
 */

require_once 'db_connect.php';

try {
    // Find documents that are Ready for Pickup but expired
    $sql = "UPDATE document_issuances 
            SET status = 'Expired' 
            WHERE status = 'Ready for Pickup' 
            AND expires_at < NOW() 
            AND is_printed = 0";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $affected = $stmt->rowCount();
    
    // Log the result
    file_put_contents(
        __DIR__ . '/logs/cron_expire.log', 
        date('Y-m-d H:i:s') . " - Expired {$affected} documents\n", 
        FILE_APPEND
    );
    
    echo "Success: {$affected} documents expired.";
    
} catch (PDOException $e) {
    file_put_contents(
        __DIR__ . '/logs/cron_expire.log', 
        date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n", 
        FILE_APPEND
    );
    echo "Error: " . $e->getMessage();
}
?>