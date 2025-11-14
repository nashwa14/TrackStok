<?php
// Start the session
session_start();

// Destroy the session
session_unset();
session_destroy();

// Redirect to the login page or homepage
header("Location: logout_proses.php"); // Replace 'index.php' with your login page or homepage URL
exit();
?>