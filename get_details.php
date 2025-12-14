<?php
header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost";
$username = "root";
$password = "";
$database = "rbpl_kingland";

$connect = new mysqli($hostname, $username, $password, $database);

if ($connect->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed']);
    exit;
}

// pastikan integer
$id_permintaan = isset($_GET['id_permintaan']) ? (int)$_GET['id_permintaan'] : 0;
if ($id_permintaan <= 0) {
    echo json_encode([]);
    exit;
}

$query = "SELECT 
            d.no_permintaan,
            p.tanggal_permintaan AS tanggal,
            p.kode_barang_permintaan AS kode_barang,
            p.nama_barang_permintaan AS nama_barang,
            d.jumlah_permintaan
          FROM detail_permintaan_produksi d
          JOIN permintaan_produksi p ON d.id_permintaan = p.id_permintaan
          WHERE d.id_permintaan = ?
          ORDER BY d.id_detail ASC";

$stmt = $connect->prepare($query);
$stmt->bind_param("i", $id_permintaan);
$stmt->execute();
$result = $stmt->get_result();

$details = [];
while ($row = $result->fetch_assoc()) {
    $details[] = [
        'no_permintaan'     => $row['no_permintaan'] ?? 'N/A',
        'tanggal'           => $row['tanggal'] ? date('d/m/Y', strtotime($row['tanggal'])) : '',
        'kode_barang'       => $row['kode_barang'] ?? 'N/A',
        'nama_barang'       => $row['nama_barang'] ?? 'N/A',
        'jumlah_permintaan' => (int)($row['jumlah_permintaan'] ?? 0),
    ];
}

echo json_encode($details);

$stmt->close();
$connect->close();

?>