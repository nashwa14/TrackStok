<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* =======================
   DATABASE CONNECTION
======================= */
$connect = new mysqli("localhost", "root", "", "rbpl_kingland");
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

/* =======================
   GENERATE NO LAPORAN
======================= */
$q = $connect->query("SELECT MAX(no_laporan) AS last FROM laporan_gudang");
$last = $q->fetch_assoc()['last'] ?? '#LAP000';
$no_laporan_display = '#LAP' . str_pad(((int)substr($last, 4)) + 1, 3, '0', STR_PAD_LEFT);

/* =======================
   FETCH BARANG - Store as array untuk JavaScript
======================= */
$barang_list = [];
$barang_result = $connect->query("
    SELECT b.id_barang, b.nama_barang,
           COALESCE(s.jumlah_stok, 0) AS stok_saat_ini
    FROM barang b
    LEFT JOIN stok s ON s.id_barang = b.id_barang
");
while ($row = $barang_result->fetch_assoc()) {
    $barang_list[] = $row;
}

/* =======================
   HANDLE SUBMIT
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_laporan = $_POST['no_laporan'] ?? '';
    $periode = $_POST['periode'] ?? '';
    $tanggal = $_POST['tanggal-laporan'] ?? '';
    $catatan = $_POST['catatan-tambahan'] ?? '';
    $status = 'Diterima';

    if (!$no_laporan || !$periode || !$tanggal) {
        die("Data laporan belum lengkap");
    }

    $items = $_POST['items'] ?? [];
    $validItems = [];

    foreach ($items as $item) {
        if (!empty($item['id_barang'])) {
            $validItems[] = $item;
        }
    }

    if (count($validItems) === 0) {
        die("Minimal 1 barang harus dipilih");
    }

    $sql = "INSERT INTO laporan_gudang
        (no_laporan, periode, tanggal_laporan, catatan_tambahan, status_laporan,
         id_barang, nama_barang, stok_awal, stok_masuk, stok_keluar, stok_akhir)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $connect->prepare($sql);
    if (!$stmt) {
        die($connect->error);
    }

    $connect->begin_transaction();

    try {
        foreach ($validItems as $item) {
            $id_barang = $item['id_barang'];
            $nama_barang = $item['nama_barang'] ?? '';

            $stok_awal = (int)($item['stok_awal'] ?? 0);
            $stok_masuk = (int)($item['stok_masuk'] ?? 0);
            $stok_keluar = (int)($item['stok_keluar'] ?? 0);
            $stok_akhir = $stok_awal + $stok_masuk - $stok_keluar;

            $stmt->bind_param(
                "sssssssiiii",
                $no_laporan,
                $periode,
                $tanggal,
                $catatan,
                $status,
                $id_barang,
                $nama_barang,
                $stok_awal,
                $stok_masuk,
                $stok_keluar,
                $stok_akhir
            );

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
        }

        $connect->commit();
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit;

    } catch (Exception $e) {
        $connect->rollback();
        die("Gagal menyimpan: " . $e->getMessage());
    }
}
?>

<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Kingland Laporan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-white text-gray-900">
    <div class="flex min-h-screen">
        <!-- Sidebar (tetap sama) -->
        <aside class="bg-[#9B1919] w-64 flex flex-col justify-between sticky top-0 h-screen">
            <div>
                <div class="px-6 py-8">
                    <img alt="Kingland Tire and Tube logo white on red background" class="w-36" decoding="async" height="auto" loading="lazy" src="https://kingland.co.id/wp-content/uploads/2022/01/LOGO-KINGLAND-Tire-Tube-white-1.png" width="144" />
                </div>
                <nav class="flex flex-col space-y-3 px-6 text-white font-semibold text-sm">
                    <a class="flex items-center gap-3 hover:text-white/90 transition-colors" href="dashboard-gudang.php">
                        <i class="fas fa-th-large text-base"></i>
                        <span>Dashboard</span>
                    </a>
                    <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="manajemen-stok-gudang.php">
                        <i class="fas fa-cube text-base"></i>
                        <span>Manajemen Stok</span>
                    </a>
                    <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="buat-permintaan-produksi.php">
                        <i class="fas fa-file-alt text-lg"></i>
                        <span>Permintaan Produksi</span>
                    </a>
                    <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="BSTB-gudang.php">
                        <i class="fas fa-book text-base"></i>
                        <span>BSTB</span>
                    </a>
                    <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="pesanan_pelanggan.php">
                        <i class="fas fa-check-square text-base"></i>
                        <span>Pesanan Pelanggan</span>
                    </a>
                    <a class="flex items-center gap-3 bg-white text-[#9B1919] rounded-full px-4 py-3 font-bold shadow-md" href="#">
                        <i class="fas fa-chart-bar text-base"></i>
                        <span>Laporan</span>
                        <div class="ml-auto bg-[#9B1919] text-white rounded-full w-8 h-7 flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-arrow-right text-sm"></i>
                        </div>
                    </a>
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
            <header class="flex items-center justify-end gap-6 px-8 py-4 border-b border-gray-300">
                <div class="relative"></div>
                <div class="flex items-center gap-3 cursor-pointer select-none">
                    <img alt="Profile image of a man with black hair wearing white shirt and black jacket" class="w-10 h-10 rounded-full object-cover" height="40" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQm6ZdhZ6kaV6TKsbZ45FXeyc1nWBPIHyONlg&s" width="40" />
                    <span class="font-bold text-black">Jay</span>
                    <i class="fas fa-chevron-down text-gray-600"></i>
                </div>
            </header>

            <!-- Page title -->
            <div class="px-8 py-4 border-b border-gray-300">
                <h1 class="font-bold text-lg text-black">Laporan Gudang PT. KingLand</h1>
            </div>

            <!-- Content area -->
            <section class="flex-1 bg-[#F2F2F2] p-8">
                <div class="bg-white rounded-xl p-6 max-w-7xl mx-auto flex flex-col gap-6">
                    <!-- Tabs -->
                    <div class="flex border-b border-gray-200 mb-6">
                        <a href="buat-laporan.php" class="relative font-semibold text-[#9B1719] pb-2 mr-6 after:absolute after:-bottom-1 after:left-0 after:w-full after:h-[3px] after:bg-[#9B1719]">
                            Buat Laporan
                        </a>
                        <a href="daftar-laporan-gudang.php" class="font-semibold text-gray-700 pb-2 hover:text-[#9B1719]">
                            Daftar Laporan
                        </a>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="flex flex-col gap-6" id="laporan-form">
                        <!-- Form top -->
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6 mb-6">
                            <div class="flex items-center gap-6">
                                <label class="font-semibold text-gray-900 text-base mr-4">
                                    No Laporan
                                </label>
                                <input class="rounded-md border border-gray-300 px-4 py-2 text-gray-900 text-base bg-gray-100 cursor-not-allowed" type="text" name="no_laporan" value="<?= htmlspecialchars($no_laporan_display) ?>" readonly />
                            </div>
                            <fieldset class="flex items-center gap-6">
                                <legend class="font-semibold text-gray-900 text-base mr-4">
                                    Periode Laporan
                                </legend>
                                <label class="flex items-center gap-2 text-gray-900 text-base cursor-pointer">
                                    <input checked="" class="text-[#9B1719] focus:ring-[#9B1719]" name="periode" type="radio" value="harian" />
                                    Harian
                                </label>
                                <label class="flex items-center gap-2 text-gray-900 text-base cursor-pointer">
                                    <input class="text-[#9B1719] focus:ring-[#9B1719]" name="periode" type="radio" value="mingguan" />
                                    Mingguan
                                </label>
                                <label class="flex items-center gap-2 text-gray-900 text-base cursor-pointer">
                                    <input class="text-[#9B1719] focus:ring-[#9B1719]" name="periode" type="radio" value="bulanan" />
                                    Bulanan
                                </label>
                            </fieldset>
                            <div class="flex flex-col w-full md:w-64">
                                <label class="font-semibold text-gray-900 text-base mb-2" for="tanggal-laporan">
                                    Tanggal Laporan
                                </label>
                                <input class="rounded-md border border-gray-300 px-4 py-2 text-gray-900 text-base focus:outline-none focus:ring-2 focus:ring-[#9B1719]" id="tanggal-laporan" name="tanggal-laporan" type="date" value="<?php echo date('Y-m-d'); ?>" />
                            </div>
                        </div>

                        <!-- Daftar Barang (Dynamic items) -->
                        <div class="overflow-x-auto">
                            <div class="flex justify-between items-center mb-3">
                                <h2 class="font-semibold text-gray-900 text-lg">
                                    Daftar Barang
                                </h2>
                                <div class="flex items-center gap-2 bg-gray-200 rounded-full px-4 py-2 w-56">
                                    <i class="fas fa-search text-gray-500"></i>
                                    <input class="bg-transparent text-gray-700 text-sm w-full focus:outline-none" id="search" placeholder="Search" type="search" />
                                </div>
                            </div>
                            <table class="w-full border-collapse text-left text-xs text-gray-600 mt-4">
                                <thead>
                                    <tr class="bg-gray-300 rounded-t-lg">
                                        <th class="py-2 px-4 font-semibold">KODE BARANG</th>
                                        <th class="py-2 px-4 font-semibold">NAMA BARANG</th>
                                        <th class="py-2 px-4 font-semibold">STOK SAAT INI</th>
                                        <th class="py-2 px-4 font-semibold">STOK AWAL</th>
                                        <th class="py-2 px-4 font-semibold">STOK MASUK</th>
                                        <th class="py-2 px-4 font-semibold">STOK KELUAR</th>
                                        <th class="py-2 px-4 font-semibold">STOK AKHIR</th>
                                        <th class="py-2 px-4 font-semibold">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody id="item-table-body">
                                    <!-- Initial row -->
                                    <tr class="border-b border-gray-200" data-row-index="0">
                                        <td class="py-3 px-4">
                                            <select class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm kode-barang" name="items[0][id_barang]">
                                                <option value="">Pilih Kode Barang</option>
                                                <?php foreach ($barang_list as $barang): ?>
                                                    <option value="<?= htmlspecialchars($barang['id_barang']) ?>" 
                                                            data-nama="<?= htmlspecialchars($barang['nama_barang']) ?>" 
                                                            data-stok="<?= htmlspecialchars($barang['stok_saat_ini']) ?>">
                                                        <?= htmlspecialchars($barang['id_barang']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="py-3 px-4">
                                            <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm nama-barang" type="text" name="items[0][nama_barang]" readonly />
                                        </td>
                                        <td class="py-3 px-4">
                                            <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-saat-ini" type="number" value="0" readonly />
                                        </td>
                                        <td class="py-3 px-4">
                                            <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-awal" type="number" min="0" name="items[0][stok_awal]" placeholder="0" />
                                        </td>
                                        <td class="py-3 px-4">
                                            <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-masuk" type="number" min="0" name="items[0][stok_masuk]" placeholder="0" />
                                        </td>
                                        <td class="py-3 px-4">
                                            <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-keluar" type="number" min="0" name="items[0][stok_keluar]" placeholder="0" />
                                        </td>
                                        <td class="py-3 px-4">
                                            <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-akhir bg-gray-100 cursor-not-allowed" type="number" name="items[0][stok_akhir]" readonly />
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <button type="button" class="text-red-600 hover:text-red-700 text-lg delete-row-btn" title="Hapus baris ini">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <!-- Tombol Tambah Item -->
                            <button type="button" id="tambah-item" class="bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md px-6 py-3 flex items-center gap-2 mt-4 transition-colors duration-200 w-full md:w-auto justify-center">
                                <i class="fas fa-plus"></i>
                                Tambah Item
                            </button>
                        </div>

                        <!-- JavaScript -->
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                let rowIndex = 1; // Mulai dari 1 karena row 0 sudah ada
                                
                                // Data barang dari PHP
                                const barangList = <?= json_encode($barang_list) ?>;

                                const tableBody = document.getElementById("item-table-body");
                                const tambahBtn = document.getElementById("tambah-item");

                                // Function to create option HTML
                                function createBarangOptions() {
                                    return barangList.map(barang => 
                                        `<option value="${barang.id_barang}" 
                                                 data-nama="${barang.nama_barang}" 
                                                 data-stok="${barang.stok_saat_ini}">
                                            ${barang.id_barang}
                                        </option>`
                                    ).join('');
                                }

                                // Function to sync nama barang, stok saat ini, and calculate stok akhir
                                function syncDetails(row) {
                                    const kodeSelect = row.querySelector('.kode-barang');
                                    const namaInput = row.querySelector('.nama-barang');
                                    const stokSaatIniInput = row.querySelector('.stok-saat-ini');
                                    const selectedOption = kodeSelect.options[kodeSelect.selectedIndex];
                                    
                                    namaInput.value = selectedOption.dataset.nama || '';
                                    stokSaatIniInput.value = selectedOption.dataset.stok || 0;
                                    updateStokAkhir(row);
                                }

                                // Function to update stok akhir
                                function updateStokAkhir(row) {
                                    const stokAwalInput = row.querySelector('.stok-awal');
                                    const stokMasukInput = row.querySelector('.stok-masuk');
                                    const stokKeluarInput = row.querySelector('.stok-keluar');
                                    const stokAkhirInput = row.querySelector('.stok-akhir');

                                    const stokAwal = parseInt(stokAwalInput.value) || 0;
                                    const stokMasuk = parseInt(stokMasukInput.value) || 0;
                                    const stokKeluar = parseInt(stokKeluarInput.value) || 0;
                                    const stokAkhir = Math.max(0, stokAwal + stokMasuk - stokKeluar);
                                    
                                    stokAkhirInput.value = stokAkhir;
                                }

                                // Add new row
                                tambahBtn.addEventListener("click", function() {
                                    const newRow = document.createElement("tr");
                                    newRow.className = "border-b border-gray-200";
                                    newRow.dataset.rowIndex = rowIndex;

                                    newRow.innerHTML = `
                                        <td class="py-3 px-4">
                                            <select class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm kode-barang" name="items[${rowIndex}][id_barang]">
                                                <option value="">Pilih Kode Barang</option>
                                                ${createBarangOptions()}
                                            </select>
                                        </td>
                                        <td class="py-3 px-4">
                                            <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm nama-barang" type="text" name="items[${rowIndex}][nama_barang]" readonly />
                                        </td>
                                        <td class="py-3 px-4">
                                            <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-saat-ini" type="number" value="0" readonly />
                                        </td>
                                        <td class="py-3 px-4">
                                            <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-awal" type="number" min="0" name="items[${rowIndex}][stok_awal]" placeholder="0" />
                                        </td>
                                        <td class="py-3 px-4">
                                            <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-masuk" type="number" min="0" name="items[${rowIndex}][stok_masuk]" placeholder="0" />
                                        </td>
                                        <td class="py-3 px-4">
                                            <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-keluar" type="number" min="0" name="items[${rowIndex}][stok_keluar]" placeholder="0" />
                                        </td>
                                        <td class="py-3 px-4">
                                            <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-akhir bg-gray-100 cursor-not-allowed" type="number" name="items[${rowIndex}][stok_akhir]" readonly />
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <button type="button" class="text-red-600 hover:text-red-700 text-lg delete-row-btn" title="Hapus baris ini">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    `;

                                    tableBody.appendChild(newRow);
                                    
                                    // Add event listeners for the new row
                                    const newKodeSelect = newRow.querySelector('.kode-barang');
                                    newKodeSelect.addEventListener('change', () => syncDetails(newRow));
                                    
                                    const newInputs = newRow.querySelectorAll('.stok-awal, .stok-masuk, .stok-keluar');
                                    newInputs.forEach(input => {
                                        input.addEventListener('input', () => updateStokAkhir(newRow));
                                    });

                                    // Add delete functionality
                                    newRow.querySelector(".delete-row-btn").addEventListener("click", function() {
                                        if (tableBody.getElementsByTagName("tr").length > 1) {
                                            tableBody.removeChild(newRow);
                                        } else {
                                            alert("Minimal harus ada 1 item!");
                                        }
                                    });

                                    rowIndex++;
                                });

                                // Add event listeners to existing rows
                                document.querySelectorAll(".kode-barang").forEach(kodeSelect => {
                                    kodeSelect.addEventListener('change', function() {
                                        syncDetails(this.closest('tr'));
                                    });
                                });

                                document.querySelectorAll(".stok-awal, .stok-masuk, .stok-keluar").forEach(input => {
                                    input.addEventListener('input', function() {
                                        updateStokAkhir(this.closest('tr'));
                                    });
                                });

                                document.querySelectorAll(".delete-row-btn").forEach(btn => {
                                    btn.addEventListener("click", function() {
                                        const row = this.closest("tr");
                                        const rowCount = tableBody.getElementsByTagName("tr").length;
                                        if (rowCount > 1) {
                                            tableBody.removeChild(row);
                                        } else {
                                            alert("Minimal harus ada 1 item!");
                                        }
                                    });
                                });

                                // Search filter (hanya untuk row yang ada)
                                const searchInput = document.getElementById('search');
                                searchInput.addEventListener('input', function() {
                                    const filter = this.value.toLowerCase();
                                    const rows = tableBody.querySelectorAll('tr');
                                    rows.forEach(row => {
                                        const kodeSelect = row.querySelector('.kode-barang');
                                        const namaInput = row.querySelector('.nama-barang');
                                        const kode = kodeSelect.value.toLowerCase();
                                        const nama = namaInput.value.toLowerCase();
                                        if (kode.includes(filter) || nama.includes(filter)) {
                                            row.style.display = '';
                                        } else {
                                            row.style.display = 'none';
                                        }
                                    });
                                });
                            });
                        </script>

                        <!-- Additional notes -->
                        <div class="flex flex-col gap-2">
                            <label class="text-gray-500 text-sm font-normal" for="catatan-tambahan">
                                Catatan Tambahan
                            </label>
                            <textarea class="resize-none rounded-md border border-gray-300 px-4 py-3 text-gray-400 text-base placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#9B1719]" id="catatan-tambahan" name="catatan-tambahan" placeholder="Masukkan Catatan Tambahan" rows="3"></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end gap-4 mt-6">
                            <button class="bg-black text-white rounded-md px-6 py-2 text-base font-normal flex items-center gap-2 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-700" type="submit">
                                <i class="fas fa-paper-plane"></i>
                                Kirim Laporan
                            </button>
                        </div>

                        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md">
                                Laporan berhasil disimpan!
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
<?php $connect->close(); ?>
