<?php
session_start();
session_unset();
session_destroy();
header("Location: ../resident_login.php");
exit();
?>
