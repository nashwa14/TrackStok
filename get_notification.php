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

// Get role from session
$role = $_SESSION['role'] ?? 'gudang';

// Get unread count
$count_query = "SELECT COUNT(*) as unread FROM notifikasi WHERE role = ? AND is_read = FALSE";
$stmt = $connect->prepare($count_query);
$stmt->bind_param("s", $role);
$stmt->execute();
$count_result = $stmt->get_result();
$unread_count = $count_result->fetch_assoc()['unread'];

// Get recent notifications (limit 5)
$notif_query = "SELECT * FROM notifikasi WHERE role = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $connect->prepare($notif_query);
$stmt->bind_param("s", $role);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['id_notifikasi'],
        'judul' => $row['judul'],
        'pesan' => $row['pesan'],
        'icon' => $row['icon'],
        'warna' => $row['warna'],
        'is_read' => (bool)$row['is_read'],
        'waktu' => time_ago($row['created_at']),
        'link' => $row['link']
    ];
}

echo json_encode([
    'unread_count' => $unread_count,
    'notifications' => $notifications
]);

$connect->close();

function time_ago($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return $diff . ' detik yang lalu';
    if ($diff < 3600) return floor($diff / 60) . ' menit yang lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam yang lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari yang lalu';
    
    return date('d/m/Y H:i', $time);
}
?>