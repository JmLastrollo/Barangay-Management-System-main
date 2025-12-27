<?php
session_start();
require_once 'config.php';

$isLoggedIn = false;
$residentName = "";
$residentId = "";

if (isset($_SESSION['email'])) {

    $email = $_SESSION['email'];

    $user = $usersCollection->findOne(['email' => $email]);

    if ($user) {
        $resident = $residentsCollection->findOne(['user_id' => $user['_id']]);

        if ($resident) {
            $isLoggedIn = true;
            $residentName = $resident['first_name'];
            $residentId = (string)$resident['_id'];
        }
    }
}
?>