<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection for fetching barang data
$hostname = "localhost";
$username = "root";
$password = "";
$database = "rbpl_kingland";

$connect = new mysqli($hostname, $username, $password, $database);

if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Fetch latest no_laporan for display
$no_laporan_query = "SELECT MAX(no_laporan) AS last_number FROM laporan_gudang";
$no_laporan_result = $connect->query($no_laporan_query);
$last_number = $no_laporan_result->fetch_assoc()['last_number'] ?? '#LAP000';
if ($last_number === '#LAP000') {
    $no_laporan_display = '#LAP001';
} else {
    $number = (int)substr($last_number, 4) + 1;
    $no_laporan_display = '#LAP' . str_pad($number, 3, '0', STR_PAD_LEFT);
}

// Fetch barang data for dropdowns
$barang_query = "SELECT id_barang, nama_barang, (SELECT jumlah_stok FROM stok WHERE stok.id_barang = barang.id_barang LIMIT 1) AS stok_saat_ini FROM barang";
$barang_result = $connect->query($barang_query);
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
        <!-- Sidebar -->
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
                    <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="hal-pesanan-pelanggan.php">
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
            <div class="px-6 pb-8">
                <hr class="border-white border-opacity-40 mb-6" />
                <button class="flex items-center gap-3 text-white text-sm font-normal" type="button">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                    <span>Logout</span>
                </button>
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

                    <!-- Form top -->
                    <form method="POST" action="save-laporan.php" class="flex flex-col md:flex-row md:justify-between md:items-center gap-6 mb-6" id="laporan-form">
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
                    </form>
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
                                <!-- Initial row with dropdown -->
                                <tr class="border-b border-gray-200">
                                    <td class="py-3 px-4">
                                        <select class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm kode-barang" name="items[0][id_barang]">
                                            <option value="">Pilih Kode Barang</option>
                                            <?php $barang_result->data_seek(0);
                                            while ($row = $barang_result->fetch_assoc()): ?>
                                                <option value="<?= htmlspecialchars($row['id_barang']) ?>" data-nama="<?= htmlspecialchars($row['nama_barang']) ?>" data-stok="<?= htmlspecialchars($row['stok_saat_ini'] ?? 0) ?>"><?= htmlspecialchars($row['id_barang']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </td>
                                    <td class="py-3 px-4">
                                        <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm nama-barang" type="text" name="items[0][nama_barang]" readonly />
                                    </td>
                                    <td class="py-3 px-4">
                                        <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-saat-ini" type="number" value="0" readonly />
                                    </td>
                                    <td class="py-3 px-4">
                                        <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-awal" type="number" name="items[0][stok_awal]" placeholder="Stok Awal" />
                                    </td>
                                    <td class="py-3 px-4">
                                        <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-masuk" type="number" name="items[0][stok_masuk]" placeholder="Stok Masuk" />
                                    </td>
                                    <td class="py-3 px-4">
                                        <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-keluar" type="number" name="items[0][stok_keluar]" placeholder="Stok Keluar" />
                                    </td>
                                    <td class="py-3 px-4">
                                        <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-akhir bg-gray-100 cursor-not-allowed" type="number" name="items[0][stok_akhir]" readonly />
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button type="button" class="text-red-600 hover:text-red-700 text-lg delete-row-btn">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Script for adding and deleting rows with synchronization and stock calculation -->
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            const tambahBtn = document.createElement("button");
                            tambahBtn.id = "tambah-item";
                            tambahBtn.className = "bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md px-4 py-2 flex items-center gap-2 transition-colors duration-200 mt-4";
                            tambahBtn.innerHTML = '<i class="fas fa-plus"></i> Tambah Item';
                            document.querySelector('.overflow-x-auto').appendChild(tambahBtn);

                            const tableBody = document.getElementById("item-table-body");

                            // Function to sync nama barang, stok saat ini, and calculate stok akhir
                            function syncDetails(row) {
                                const kodeSelect = row.querySelector('.kode-barang');
                                const namaInput = row.querySelector('.nama-barang');
                                const stokSaatIniInput = row.querySelector('.stok-saat-ini');
                                const stokAwalInput = row.querySelector('.stok-awal');
                                const stokMasukInput = row.querySelector('.stok-masuk');
                                const stokKeluarInput = row.querySelector('.stok-keluar');
                                const stokAkhirInput = row.querySelector('.stok-akhir');
                                const selectedOption = kodeSelect.options[kodeSelect.selectedIndex];
                                namaInput.value = selectedOption ? selectedOption.getAttribute('data-nama') || '' : '';
                                stokSaatIniInput.value = selectedOption ? parseInt(selectedOption.getAttribute('data-stok') || 0) : 0;
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
                                const stokAkhir = stokAwal + stokMasuk - stokKeluar;
                                stokAkhirInput.value = stokAkhir >= 0 ? stokAkhir : 0;
                            }

                            // Add new row
                            tambahBtn.addEventListener("click", function() {
                                const rowCount = tableBody.getElementsByTagName("tr").length;
                                const newRow = document.createElement("tr");
                                newRow.className = "border-b border-gray-200";

                                newRow.innerHTML = `
                                    <td class="py-3 px-4">
                                        <select class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm kode-barang" name="items[${rowCount}][id_barang]">
                                            <option value="">Pilih Kode Barang</option>
                                            <?php $barang_result->data_seek(0);
                                            while ($row = $barang_result->fetch_assoc()): ?>
                                                <option value="<?= htmlspecialchars($row['id_barang']) ?>" data-nama="<?= htmlspecialchars($row['nama_barang']) ?>" data-stok="<?= htmlspecialchars($row['stok_saat_ini'] ?? 0) ?>"><?= htmlspecialchars($row['id_barang']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </td>
                                    <td class="py-3 px-4">
                                        <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm nama-barang" type="text" name="items[${rowCount}][nama_barang]" readonly />
                                    </td>
                                    <td class="py-3 px-4">
                                        <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-saat-ini" type="number" value="0" readonly />
                                    </td>
                                    <td class="py-3 px-4">
                                        <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-awal" type="number" name="items[${rowCount}][stok_awal]" placeholder="Stok Awal" />
                                    </td>
                                    <td class="py-3 px-4">
                                        <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-masuk" type="number" name="items[${rowCount}][stok_masuk]" placeholder="Stok Masuk" />
                                    </td>
                                    <td class="py-3 px-4">
                                        <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-keluar" type="number" name="items[${rowCount}][stok_keluar]" placeholder="Stok Keluar" />
                                    </td>
                                    <td class="py-3 px-4">
                                        <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm stok-akhir bg-gray-100 cursor-not-allowed" type="number" name="items[${rowCount}][stok_akhir]" readonly />
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button type="button" class="text-red-600 hover:text-red-700 text-lg delete-row-btn">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>`;

                                tableBody.appendChild(newRow);

                                // Add event listeners for the new row
                                const kodeSelect = newRow.querySelector('.kode-barang');
                                kodeSelect.addEventListener('change', () => syncDetails(newRow));
                                const inputs = newRow.querySelectorAll('.stok-awal, .stok-masuk, .stok-keluar');
                                inputs.forEach(input => {
                                    input.addEventListener('input', () => updateStokAkhir(newRow));
                                });

                                // Add delete functionality
                                newRow.querySelector(".delete-row-btn").addEventListener("click", function() {
                                    tableBody.removeChild(newRow);
                                });
                            });

                            // Add event listeners to existing rows
                            document.querySelectorAll(".kode-barang").forEach(kodeSelect => {
                                kodeSelect.addEventListener('change', () => syncDetails(kodeSelect.closest('tr')));
                            });

                            document.querySelectorAll(".stok-awal, .stok-masuk, .stok-keluar").forEach(input => {
                                input.addEventListener('input', () => updateStokAkhir(input.closest('tr')));
                            });

                            document.querySelectorAll(".delete-row-btn").forEach(btn => {
                                btn.addEventListener("click", function() {
                                    const row = this.closest("tr");
                                    tableBody.removeChild(row);
                                });
                            });

                            // Search filter
                            const searchInput = document.getElementById('search');
                            searchInput.addEventListener('input', () => {
                                const filter = searchInput.value.toLowerCase();
                                const rows = document.querySelectorAll('#item-table-body tr');
                                rows.forEach(row => {
                                    const kode = row.querySelector('td:nth-child(1) select').value.toLowerCase();
                                    const nama = row.querySelector('td:nth-child(2) input').value.toLowerCase();
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
                        <button class="bg-white border border-gray-300 rounded-md px-6 py-2 text-gray-900 text-base font-normal hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#9B1719]" type="button" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>'">
                            Batal
                        </button>
                        <button class="bg-black text-white rounded-md px-6 py-2 text-base font-normal flex items-center gap-2 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-700" type="submit">
                            <i class="fas fa-paper-plane"></i>
                            Kirim Laporan
                        </button>
                    </div>
                    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                        <div class="text-green-600 text-sm mt-4">Laporan berhasil disimpan!</div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
<?php $connect->close(); ?>