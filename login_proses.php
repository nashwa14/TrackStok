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

    // Determine redirect based on username
    if ($username === 'gudang') {
        header("Location: dashboard-gudang.php");
    } elseif ($username === 'manajer') {
        header("Location: dashboard-manajemen-puncak.php");
    } elseif ($username === 'produksi') {
        header("Location: dashboard-produksi.php");
    } else {
        header("Location: login.php?pesan=gagal"); // Fallback for unknown users
    }
} else {
    header("Location: login.php?pesan=gagal");
}

$stmt->close();
$connect->close();
exit();
?>