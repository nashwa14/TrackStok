<?php
include 'koneksi.php';

session_start();
$username = $_POST['username'];
$password = $_POST['password'];

$connect = new mysqli('localhost', 'root', '', 'rbpl_kingland');
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

$query = "SELECT * FROM login WHERE username = ? AND password = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();
$cek = $result->num_rows;

if ($cek > 0) {
    $data_user = $result->fetch_assoc();
    $_SESSION['username'] = $username;
    $_SESSION['password'] = $password;
    
    // Set role based on username
    if ($username === 'gudang') {
        $_SESSION['role'] = 'gudang';
        header("Location: dashboard-gudang.php");
    } elseif ($username === 'manajer') {
        $_SESSION['role'] = 'manajer';
        header("Location: dashboard-manajemen-puncak.php");
    } elseif ($username === 'produksi') {
        $_SESSION['role'] = 'produksi';
        header("Location: dashboard-produksi.php");
    } else {
        header("Location: login.php?pesan=gagal");
    }
    
    // Log login activity
    $log_aktivitas = "User login";
    $log_detail = "Username: " . $username;
    $log_role = $_SESSION['role'];
    $insert_log = $connect->prepare("INSERT INTO log_aktivitas (username, role, aktivitas, detail) VALUES (?, ?, ?, ?)");
    $insert_log->bind_param("ssss", $username, $log_role, $log_aktivitas, $log_detail);
    $insert_log->execute();
    
} else {
    header("Location: login.php?pesan=gagal");
}
$stmt->close();
$connect->close();
exit();
?>