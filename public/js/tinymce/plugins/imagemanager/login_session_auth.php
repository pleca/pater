<?php
// Set session
session_start();
$_SESSION['isLoggedIn'] = true;
header("location: " . $_REQUEST['return_url']);
die;
?>
