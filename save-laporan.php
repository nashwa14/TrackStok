<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$hostname = "localhost";
$username = "root";
$password = "";
$database = "rbpl_kingland";

$connect = new mysqli($hostname, $username, $password, $database);

if ($connect->connect_error) {
    die('Maaf koneksi gagal: ' . $connect->connect_error);
}

// Debug: Output POST data
echo "<pre>"; var_dump($_POST); echo "</pre>"; exit; // Remove after testing

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no_laporan = $_POST['no_laporan'] ?? '';
    $periode = $_POST['periode'] ?? '';
    $tanggal = $_POST['tanggal-laporan'] ?? '';
    $catatan = $_POST['catatan-tambahan'] ?? '';
    $username = "Jay"; // Replace with session data if available

    // Validate required fields
    if (empty($no_laporan) || empty($periode) || empty($tanggal)) {
        die('Missing required fields: no_laporan, periode, or tanggal');
    }

    // Insert into laporan_gudang
    $sql = "INSERT INTO laporan_gudang (no_laporan, periode, tanggal, id_gudang, username, catatan) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $connect->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . $connect->error);
    }
    $id_gudang = 1; // Assuming fixed gudang ID
    $stmt->bind_param("sssiss", $no_laporan, $periode, $tanggal, $id_gudang, $username, $catatan);
    $success = $stmt->execute();
    if (!$success) {
        die('Execute failed for laporan_gudang: ' . $stmt->error);
    }
    $laporan_id = $connect->insert_id;
    $stmt->close();

    // Process each item and insert into stok
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            $id_barang = $item['id_barang'] ?? '';
            $stok_awal = $item['stok_awal'] ?? 0;
            $stok_masuk = $item['stok_masuk'] ?? 0;
            $stok_keluar = $item['stok_keluar'] ?? 0;
            $stok_akhir = $stok_awal + $stok_masuk - $stok_keluar;
            $nama_barang = $item['nama_barang'] ?? '';

            if (!empty($id_barang)) {
                $sql = "INSERT INTO stok (id_barang, stok_awal, stok_masuk, stok_keluar, stok_akhir, tanggal_update, nama_barang, id_laporan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $connect->prepare($sql);
                if ($stmt === false) {
                    die('Prepare failed for stok: ' . $connect->error);
                }
                $stmt->bind_param("iiiiissi", $id_barang, $stok_awal, $stok_masuk, $stok_keluar, $stok_akhir, $tanggal, $nama_barang, $laporan_id);
                $success = $stmt->execute();
                if (!$success) {
                    die('Execute failed for stok: ' . $stmt->error);
                }
                $stmt->close();
            }
        }
    }

    // Redirect with success parameter
    header("Location: buat-laporan-gudang.php.php?success=1");
    exit();
} else {
    // Redirect if not a POST request
    header("Location: buat-laporan-gudang.php.php");
    exit();
}
?>
<?php $connect->close(); ?>