<?php
session_start();
header('Content-Type: application/json');

$hostname = "localhost";
$username = "root";
$password = "";
$database = "rbpl_kingland";

$connect = new mysqli($hostname, $username, $password, $database);
if ($connect->connect_error) {
    echo json_encode(["error" => "Koneksi gagal: " . $connect->connect_error]);
    exit;
}

$id_permintaan = $_GET['id_permintaan'] ?? null;
if (!$id_permintaan) {
    echo json_encode(["error" => "id_permintaan tidak valid"]);
    exit;
}

// Start transaction
$connect->begin_transaction();

try {
    // Ambil no_bstb terakhir
    $result = $connect->query("SELECT MAX(CAST(SUBSTRING(no_bstb, 5) AS UNSIGNED)) as max_bstb FROM detail_permintaan_produksi WHERE no_bstb != '0'");
    $row = $result->fetch_assoc();
    $last_number = $row['max_bstb'] ?? 0;
    $next_number = (int)$last_number + 1;
    $newBstb = 'BSTB' . str_pad($next_number, 3, '0', STR_PAD_LEFT);

    // Update detail_permintaan_produksi
    $update = $connect->prepare("UPDATE detail_permintaan_produksi 
        SET no_bstb = ?, status_bstb = 'Selesai' 
        WHERE id_permintaan = ?");
    $update->bind_param("si", $newBstb, $id_permintaan);
    $update->execute();

    // Update status permintaan_produksi
    $update_status = $connect->prepare("UPDATE permintaan_produksi 
        SET status = 'Selesai Produksi' 
        WHERE id_permintaan = ?");
    $update_status->bind_param("i", $id_permintaan);
    $update_status->execute();

    // Create notification for gudang
    $notif_judul = "BSTB Baru";
    $notif_pesan = "BSTB " . $newBstb . " telah dibuat dan siap dikirim ke gudang.";
    $notif_icon = "fa-file-alt";
    $notif_warna = "#10B981";
    $notif_role = "gudang";
    $notif_link = "BSTB-gudang.php";

    $insert_notif = $connect->prepare("INSERT INTO notifikasi (role, judul, pesan, icon, warna, link) VALUES (?, ?, ?, ?, ?, ?)");
    $insert_notif->bind_param("ssssss", $notif_role, $notif_judul, $notif_pesan, $notif_icon, $notif_warna, $notif_link);
    $insert_notif->execute();

    // Log aktivitas
    $username = $_SESSION['username'] ?? 'system';
    $log_aktivitas = "Generate BSTB " . $newBstb;
    $log_detail = "ID Permintaan: " . $id_permintaan;

    $insert_log = $connect->prepare("INSERT INTO log_aktivitas (username, role, aktivitas, detail) VALUES (?, 'produksi', ?, ?)");
    $insert_log->bind_param("sss", $username, $log_aktivitas, $log_detail);
    $insert_log->execute();

    // Commit transaction
    $connect->commit();

    echo json_encode([
        "success" => true,
        "no_bstb" => $newBstb,
        "message" => "BSTB berhasil dibuat"
    ]);
} catch (Exception $e) {
    $connect->rollback();
    echo json_encode([
        "error" => "Gagal membuat BSTB: " . $e->getMessage()
    ]);
}

$connect->close();
