<?php
// auth_check.php - Universal authentication checker
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php?pesan=belum_login");
    exit();
}

// Function to check role access
function checkRoleAccess($allowed_roles) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: login.php?pesan=akses_ditolak");
        exit();
    }
}

// Get current user info
function getCurrentUser() {
    return [
        'username' => $_SESSION['username'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'display_name' => ucfirst($_SESSION['username'] ?? 'User')
    ];
}

// Log activity function
function logActivity($connect, $aktivitas, $detail = '') {
    $username = $_SESSION['username'] ?? 'system';
    $role = $_SESSION['role'] ?? 'unknown';
    
    $stmt = $connect->prepare("INSERT INTO log_aktivitas (username, role, aktivitas, detail) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $role, $aktivitas, $detail);
    $stmt->execute();
    $stmt->close();
}
?>