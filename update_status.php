<?php
header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'rbpl_kingland';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Ambil id_pesanan dan status_pesanan dari URL
$id_pesanan = isset($_GET['id_pesanan']) ? (int) $_GET['id_pesanan'] : 0;
$status_pesanan = isset($_GET['status_pesanan']) ? $_GET['status_pesanan'] : '';

if ($id_pesanan > 0 && $status_pesanan !== '') {
    $stmt = $conn->prepare("UPDATE pesanan_pelanggan SET status_pesanan = ? WHERE id_pesanan = ?");
    $stmt->bind_param("si", $status_pesanan, $id_pesanan);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Query failed: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
}

$conn->close();
?>
