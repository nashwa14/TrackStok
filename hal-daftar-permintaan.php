<?php
$hostname = "localhost";
$username = "root";
$password = '';
$database = "rbpl_kingland";

$connect = new mysqli($hostname, $username, $password, $database);

if ($connect->connect_error) {
    die('Maaf koneksi gagal:' . $connect->connect_error);
}
session_start();

// Handle status update on form submission
if (isset($_POST['terima_permintaan'])) {
    $id_detail = $_POST['id_detail'];
    $queryUpdate = "
        UPDATE permintaan_produksi
        SET status = 'Proses'
        WHERE id_permintaan = (
            SELECT id_permintaan FROM detail_permintaan_produksi WHERE id_detail = ?
        )
    ";
    $stmt = $connect->prepare($queryUpdate);
    $stmt->bind_param("s", $id_detail);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle status edit on form submission from Tindak Lanjut modal
if (isset($_POST['update_status']) && isset($_POST['id_detail']) && isset($_POST['new_status'])) {
    $id_detail = $_POST['id_detail'];
    $new_status = $_POST['new_status'];
    $queryUpdate = "
        UPDATE permintaan_produksi
        SET status = ?
        WHERE id_permintaan = (
            SELECT id_permintaan FROM detail_permintaan_produksi WHERE id_detail = ?
        )
    ";
    $stmt = $connect->prepare($queryUpdate);
    $stmt->bind_param("ss", $new_status, $id_detail);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Pagination settings
$per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Fetch total rows for warehouse requests
$query_total = "SELECT COUNT(DISTINCT p.id_permintaan) as total FROM permintaan_produksi p LEFT JOIN detail_permintaan_produksi d ON p.id_permintaan = d.id_permintaan WHERE p.divisi_pemohon = 'Divisi Gudang'";
$total_rows_result = $connect->query($query_total);
$total_rows = $total_rows_result ? $total_rows_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_rows / $per_page);

// Fetch warehouse-specific data with debugging
$query = "
    SELECT 
        d.id_detail,
        p.nomor_permintaan,
        p.tanggal_permintaan,
        p.kode_barang_permintaan,
        p.nama_barang_permintaan,
        d.jumlah_permintaan,
        b.gambar_url,
        COALESCE(p.status, 'Menunggu Konfirmasi') AS status
    FROM permintaan_produksi p 
    LEFT JOIN detail_permintaan_produksi d ON p.id_permintaan = d.id_permintaan 
    LEFT JOIN barang b ON p.kode_barang_permintaan = b.id_barang
    WHERE p.divisi_pemohon LIKE '%Gudang%'
    LIMIT ?, ?
";

$stmt = $connect->prepare($query);
$stmt->bind_param("ii", $offset, $per_page);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die('Query failed: ' . $connect->error . ' Query: ' . $query);
}
// echo '<pre>Debug: Rows returned = ' . $result->num_rows . '</pre>'; // Debugging output
?>

<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Detail Permintaan Produksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: "Inter", sans-serif;
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }
        .sidebar {
            width: 16rem;
            background-color: #9B141A;
            flex-shrink: 0;
            height: 100vh;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        /* Adjusted table styles */
        table {
            font-size: 0.875rem; /* 14px */
        }
        thead th {
            padding: 6px 8px;
            font-size: 0.75rem; /* 12px */
            background-color: #F9F9F9;
        }
        tbody td {
            padding: 4px 8px;
            vertical-align: top;
        }
        tbody img {
            width: 35px;
            height: 52px;
            margin-top: 2px;
        }
        .status-dropdown {
            padding: 2px 6px;
            font-size: 0.75rem;
        }
        .open-modal-btn {
            padding: 2px 6px;
            font-size: 0.75rem;
        }
    </style>
</head>
<body class="bg-[#f3f4f6]">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
            <div>
                <div class="px-6 py-8">
                    <img alt="Kingland Tire and Tube logo white on red background" class="w-36" src="https://kingland.co.id/wp-content/uploads/2022/01/LOGO-KINGLAND-Tire-Tube-white-1.png" />
                </div>
                <nav class="mt-6 flex flex-col gap-3 px-6">
                    <a class="flex items-center gap-2 text-white font-semibold" href="dashboard-produksi.php"><i class="fas fa-th-large"></i>Dashboard</a>
                    <a class="flex items-center justify-between bg-white rounded-full py-1 px-3 font-semibold text-black" href="hal-daftar-permintaan.php">
                        <div class="flex items-center gap-2"><i class="fas fa-box-open"></i>Permintaan Produksi</div>
                        <div class="bg-[#A9161A] rounded-full w-8 h-6 flex items-center justify-center text-white mr-2"><i class="fas fa-arrow-right"></i></div>
                    </a>
                    <a class="flex items-center gap-2 text-white font-semibold" href="hal-bstb.php"><i class="fas fa-file-alt"></i>BSTB</a>
                </nav>
            </div>
            <div class="px-6 py-6 border-t border-[#7A1A1E]">
                <a href="logout.php" class="flex items-center gap-3 text-white text-sm font-normal">
                    <button class="flex items-center gap-3 text-white text-sm font-normal" type="button">
                        <i class="fas fa-sign-out-alt text-lg"></i>
                        Logout
                    </button>
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 flex flex-col">
            <!-- Top bar -->
            <header class="flex justify-end items-center border-b border-gray-300 pt-3 pb-4 mb-4">
                <div class="flex items-center gap-6">
                    <img alt="" class="rounded-full w-10 h-10 object-cover" height="40" src="https://i.pinimg.com/736x/6d/a5/cf/6da5cf7d70417b1ed1e9946ddcd7ea1b.jpg" width="40" />
                    <span class="font-semibold text-black text-sm">Jake</span>
                    <i class="fas fa-chevron-down text-gray-600 text-xs"></i>
                </div>
            </header>

            <!-- Page title -->
            <div class="px-8 py-4 border-b border-gray-300">
                <h1 class="font-bold text-lg text-black">Permintaan Produksi</h1>
            </div>

            <!-- Content area -->
            <section class="flex-1 bg-[#F7F7F7] p-6">
                <div class="bg-white rounded-xl p-6 max-w-full overflow-x-auto shadow-sm">
                    <h2 class="font-bold text-sm mb-4">Daftar Permintaan Produksi dari Gudang</h2>
                    <table class="w-full text-left text-sm text-[#1E1E1E]">
                        <thead class="bg-[#F9F9F9] border-b border-gray-300">
                            <tr>
                                <th class="py-3 px-4 font-bold">No. Permintaan</th>
                                <th class="py-3 px-4 font-bold">Tanggal</th>
                                <th class="py-3 px-4 font-bold">Kode Barang</th>
                                <th class="py-3 px-4 font-bold">Nama Barang</th>
                                <th class="py-3 px-4 font-bold">Jumlah</th>
                                <th class="py-3 px-4 font-bold text-center">Status</th>
                                <th class="py-3 px-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $row_count = 0;
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $row_count++;
                            ?>
                                <tr class="border-b border-gray-200">
                                    <td class="py-2 px-2 align-top"><?= htmlspecialchars($row['nomor_permintaan'] ?? 'N/A') ?></td>
                                    <td class="py-2 px-2 align-top"><?= htmlspecialchars($row['tanggal_permintaan'] ?? 'N/A') ?></td>
                                    <td class="py-2 px-2 align-top"><?= htmlspecialchars($row['kode_barang_permintaan'] ?? 'N/A') ?></td>
                                    <td class="py-2 px-2 align-top">
                                        <div class="flex flex-col items-center">
                                            <span class="mb-1"><?= htmlspecialchars($row['nama_barang_permintaan'] ?? 'N/A') ?></span>
                                            <img src="<?= htmlspecialchars($row['gambar_url'] ?? 'https://storage.googleapis.com/a1aa/image/12e25fdc-5dcb-4717-9bfa-ca314660023f.jpg') ?>" alt="<?= htmlspecialchars($row['nama_barang_permintaan'] ?? 'N/A') ?>" class="w-20 h-28 object-contain" />
                                        </div>
                                    </td>
                                    <td class="py-2 px-2 align-top"><?= htmlspecialchars($row['jumlah_permintaan'] ?? 'N/A') ?></td>
                                    <td class="py-2 px-2 align-top text-center">
                                        <span class="text-white font-semibold rounded px-2 py-1" style="background-color: <?= $row['status'] == 'Menunggu Konfirmasi' ? '#EF4444' : ($row['status'] == 'Sedang Diproduksi' ? '#FBBF24' : ($row['status'] == 'Selesai Produksi' ? '#22C55E' : '#D1D5DB')) ?>;">
                                            <?= htmlspecialchars($row['status'] ?? 'Menunggu Konfirmasi') ?>
                                        </span>
                                    </td>
                                    <td class="py-2 px-2 align-top text-center">
                                        <button class="open-modal-btn bg-[#9B171B] text-white text-xs rounded px-2 py-1" type="button" data-detail="<?= $row['id_detail'] ?>" data-tanggal="<?= htmlspecialchars($row['tanggal_permintaan'] ?? 'N/A') ?>" data-kode-barang="<?= htmlspecialchars($row['kode_barang_permintaan'] ?? 'N/A') ?>" data-barang="<?= htmlspecialchars($row['nama_barang_permintaan'] ?? 'N/A') ?>" data-jumlah="<?= htmlspecialchars($row['jumlah_permintaan'] ?? 'N/A') ?>" data-status="<?= htmlspecialchars($row['status'] ?? 'Menunggu Konfirmasi') ?>" data-gambar="<?= htmlspecialchars($row['gambar_url'] ?? 'https://storage.googleapis.com/a1aa/image/12e25fdc-5dcb-4717-9bfa-ca314660023f.jpg') ?>">Tindak Lanjut</button>
                                    </td>
                                </tr>
                            <?php
                                }
                            } else {
                                echo '<tr><td colspan="7" class="py-2 px-2 text-center text-gray-500">Tidak ada data permintaan produksi dari gudang. Query: ' . htmlspecialchars($query) . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                    <div class="mt-4 flex justify-between items-center text-gray-400 text-xs select-none">
                        <span>Menampilkan <?php echo $row_count; ?> data dari <?php echo $total_rows; ?> data</span>
                        <nav class="flex items-center gap-2">
                            <button aria-label="Previous page" class="border border-gray-300 rounded px-2 py-1 <?php echo ($page <= 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'); ?>" <?php echo ($page <= 1 ? 'disabled' : ''); ?> onclick="window.location.href='?page=<?php echo $page - 1; ?>'">‹</button>
                            <?php for ($i = 1; $i <= min(5, $total_pages); $i++): ?>
                                <button aria-label="Page <?php echo $i; ?>" class="rounded px-2 py-1 font-semibold <?php echo ($i == $page ? 'bg-[#4A90E2] text-white' : 'border border-gray-300 hover:bg-gray-100'); ?>" onclick="window.location.href='?page=<?php echo $i; ?>'"><?php echo $i; ?></button>
                            <?php endfor; ?>
                            <?php if ($total_pages > 5): ?>
                                <span class="px-1">...</span>
                                <button aria-label="Page <?php echo $total_pages; ?>" class="border border-gray-300 rounded px-2 py-1 hover:bg-gray-100" onclick="window.location.href='?page=<?php echo $total_pages; ?>'"><?php echo $total_pages; ?></button>
                            <?php endif; ?>
                            <button aria-label="Next page" class="border border-gray-300 rounded px-2 py-1 <?php echo ($page >= $total_pages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'); ?>" <?php echo ($page >= $total_pages ? 'disabled' : ''); ?> onclick="window.location.href='?page=<?php echo $page + 1; ?>'">›</button>
                        </nav>
                    </div>
                </div>
            </section>

            <!-- Modal overlay -->
            <div id="modalOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                <div aria-labelledby="modal-title" aria-modal="true" class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 relative" role="dialog">
                    <button aria-label="Close modal" class="absolute top-6 right-6 text-2xl text-[#1f1f1f] hover:text-black" onclick="closeModal()"><i class="fas fa-times"></i></button>
                    <h2 class="font-extrabold text-sm mb-4 text-[#1f1f1f]">Detail Permintaan Produksi</h2>
                    <div class="grid grid-cols-2 gap-y-4 gap-x-8 text-[#1f1f1f] max-h-[60vh] overflow-y-auto border border-gray-200" style="border-width: 1px;">
                        <div><p class="mb-1 font-normal text-xs">No. Permintaan</p><p id="modal-id-detail" class="font-extrabold text-xs">-</p></div>
                        <div><p class="mb-1 font-normal text-xs">Tanggal Permintaan</p><p id="modal-tanggal" class="font-extrabold text-xs">-</p></div>
                        <div><p class="mb-1 font-normal text-xs">Kode Barang</p><p id="modal-kode-barang" class="font-extrabold text-xs mb-2">-</p></div>
                        <div><p class="mb-1 font-normal text-xs">Nama Barang</p><p id="modal-barang" class="font-extrabold text-xs mb-2">-</p><img id="modal-gambar" src="" alt="Gambar Barang" class="w-20 h-20 object-contain" /></div>
                        <div><p class="mb-1 font-normal text-xs">Jumlah</p><p id="modal-jumlah" class="font-extrabold text-xs">-</p></div>
                        <div class="col-span-2"><p class="mb-1 font-normal text-xs">Status</p><p id="modal-current-status" class="font-extrabold text-xs">-</p></div>
                    </div>
                    <form method="post" class="mt-6">
                        <input type="hidden" name="id_detail" id="modal-id-detail-input">
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-700">Ubah Status</label>
                            <select name="new_status" id="modal-status-select" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-xs">
                                <option value="Menunggu Konfirmasi">Menunggu Konfirmasi</option>
                                <option value="Sedang Diproduksi">Sedang Diproduksi</option>
                                <option value="Selesai Produksi">Selesai Produksi</option>
                            </select>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button id="btn-terima" name="terima_permintaan" class="bg-[#1f1f1f] text-white px-4 py-2 rounded-md font-normal mr-2 text-xs">Terima Permintaan</button>
                            <button type="submit" name="update_status" class="bg-green-500 text-white px-4 py-2 rounded-md font-normal text-xs">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>
        function closeModal() { document.getElementById('modalOverlay').classList.add('hidden'); }

        // Open and populate modal
        document.querySelectorAll('.open-modal-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('modal-id-detail').textContent = this.dataset.detail;
                document.getElementById('modal-tanggal').textContent = this.dataset.tanggal;
                document.getElementById('modal-kode-barang').textContent = this.dataset.kode_barang || 'N/A';
                document.getElementById('modal-barang').textContent = this.dataset.barang;
                document.getElementById('modal-jumlah').textContent = this.dataset.jumlah;
                document.getElementById('modal-current-status').textContent = this.dataset.status || 'Menunggu Konfirmasi';
                document.getElementById('modal-gambar').src = this.dataset.gambar;
                document.getElementById('modal-id-detail-input').value = this.dataset.detail;
                document.getElementById('modal-status-select').value = this.dataset.status || 'Menunggu Konfirmasi';

                const btnTerima = document.getElementById('btn-terima');
                if (!this.dataset.status || this.dataset.status === 'Menunggu Konfirmasi') {
                    btnTerima.removeAttribute('disabled');
                    btnTerima.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    btnTerima.setAttribute('disabled', true);
                    btnTerima.classList.add('opacity-50', 'cursor-not-allowed');
                }

                document.getElementById('modalOverlay').classList.remove('hidden');
            });
        });

        // Update status via dropdown (table)
        function updateStatus(select) {
            const id_detail = select.dataset.id;
            const new_status = select.value;
            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencode' },
                body: `id_detail=${id_detail}&status=${new_status}`
            }).then(response => response.text()).then(data => {
                select.classList.remove('bg-red-500', 'bg-yellow-400', 'bg-green-500');
                if (new_status === 'Menunggu Konfirmasi') select.classList.add('bg-red-500');
                else if (new_status === 'Sedang Diproduksi') select.classList.add('bg-yellow-400');
                else if (new_status === 'Selesai Produksi') select.classList.add('bg-green-500');
            }).catch(err => {
                alert('Gagal memperbarui status');
                console.error(err);
            });
        }
    </script>
</body>
</html>
<?php $connect->close(); ?>