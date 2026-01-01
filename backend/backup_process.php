<?php
session_start();
// Security: Only Admin can backup
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once 'db_connect.php';

// Configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Default XAMPP password
$db_name = 'wbbms_langkaan2';

// Get All Tables
$tables = array();
$sql = "SHOW TABLES";
$result = $conn->query($sql);

while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

$sqlScript = "";

foreach ($tables as $table) {
    // Prepare SQLscript for creating table structure
    $query = "SHOW CREATE TABLE $table";
    $result = $conn->query($query);
    $row = $result->fetch(PDO::FETCH_NUM);
    
    $sqlScript .= "\n\n" . $row[1] . ";\n\n";
    
    $query = "SELECT * FROM $table";
    $result = $conn->query($query);
    
    $columnCount = $result->columnCount();
    
    for ($i = 0; $i < $columnCount; $i ++) {
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $sqlScript .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $columnCount; $j ++) {
                $row[$j] = $row[$j];
                
                if (isset($row[$j])) {
                    $sqlScript .= '"' . addslashes($row[$j]) . '"';
                } else {
                    $sqlScript .= '""';
                }
                if ($j < ($columnCount - 1)) {
                    $sqlScript .= ',';
                }
            }
            $sqlScript .= ");\n";
        }
    }
    
    $sqlScript .= "\n"; 
}

if(!empty($sqlScript))
{
    // Save the SQL script to a backup file
    $backup_file_name = $db_name . '_backup_' . date("Y-m-d_H-i-s") . '.sql';
    
    // Header for download
    header('Content-Type: application/octet-stream');   
    header("Content-Transfer-Encoding: Binary"); 
    header("Content-disposition: attachment; filename=\"".$backup_file_name."\"");  
    echo $sqlScript;
    exit;
}
?>