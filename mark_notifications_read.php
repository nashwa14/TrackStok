<?php
session_start();
header('Content-Type: application/json');
$hostname = "localhost";
$username = "root";
$password = "";
$database = "rbpl_kingland";
$connect = new mysqli($hostname, $username, $password, $database);

if ($connect->connect_error) {
    echo json_encode(['error' => 'Connection failed']);
    exit;
}

$role = $_SESSION['role'] ?? 'gudang';
$query = "UPDATE notifikasi SET is_read = TRUE WHERE role = ? AND is_read = FALSE";
$stmt = $connect->prepare($query);
$stmt->bind_param("s", $role);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to update']);
}

$stmt->close();
$connect->close();
?>