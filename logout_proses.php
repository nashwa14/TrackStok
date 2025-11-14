<?php
// Start the session
session_start();

// Attempt to destroy the session
session_unset();
$session_destroyed = session_destroy();

if ($session_destroyed) {
    // Redirect to login page with logout success message
    header("Location: login.php?pesan=logout");
} else {
    // Redirect to login page with failure message if session destroy fails
    header("Location: login.php?pesan=gagal_logout");
}
exit();
?>