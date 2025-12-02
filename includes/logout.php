<?php
session_start();


$_SESSION = array();


session_destroy();


header("location: ../views/login.php");
exit;
?>
