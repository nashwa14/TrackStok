<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "rbpl_kingland";

$connect = new mysqli($hostname, $username, $password, $database);
if ($connect->connect_error) {
    die(json_encode(["error" => "Koneksi gagal: " . $connect->connect_error]));
}

$id_permintaan = $_GET['id_permintaan'] ?? null;
if (!$id_permintaan) {
    die(json_encode(["error" => "id_permintaan tidak valid"]));
}

// Ambil no_bstb terakhir
$result = $connect->query("SELECT MAX(CAST(SUBSTRING(no_bstb, 5) AS UNSIGNED)) as max_bstb FROM detail_permintaan_produksi WHERE no_bstb != '0'");
$row = $result->fetch_assoc();
$last_number = $row['max_bstb'] ?? 0;
$next_number = (int)$last_number + 1;

// Format no_bstb seperti BSTB001, BSTB002
$newBstb = 'BSTB' . str_pad($next_number, 3, '0', STR_PAD_LEFT);

// Update semua baris dengan id_permintaan terkait
$update = $connect->query("UPDATE detail_permintaan_produksi 
    SET no_bstb = '$newBstb', status_bstb = 'Selesai' 
    WHERE id_permintaan = '$id_permintaan'");

if ($update) {
    echo json_encode(["no_bstb" => $newBstb]);
} else {
    echo json_encode(["error" => "Update gagal: " . $connect->error]);
}

$connect->close();
?>
