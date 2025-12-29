<?php
// backend/db_connect.php

$host = 'localhost';
$db_name = 'wbbms_langkaan2';
$username = 'root';
$password = '';

try {
    // Gumamit tayo ng PDO para sa mas secure na connection
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    
    // Set error mode to exception para makita natin kung may error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    // Mas maganda kung 'exit' ang gamit kaysa 'die' para malinis
    exit("Connection failed: " . $e->getMessage());
}

?>