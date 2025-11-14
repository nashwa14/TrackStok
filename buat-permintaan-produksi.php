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

// Fetch the latest request number
$max_query = "SELECT MAX(nomor_permintaan) AS last_number FROM permintaan_produksi";
$max_result = $connect->query($max_query);
$last_number = $max_result->fetch_assoc()['last_number'] ?? '#REQ000';

if ($last_number === '#REQ000') {
    $next_number = '#REQ001';
} else {
    $number = (int)substr($last_number, 4) + 1; // Extract numeric part and increment
    $next_number = '#REQ' . str_pad($number, 3, '0', STR_PAD_LEFT); // Pad with leading zeros
}

// Fetch barang data for dropdowns
$barang_query = "SELECT id_barang, nama_barang, (SELECT jumlah_stok FROM stok WHERE stok.id_barang = barang.id_barang LIMIT 1) AS stok_saat_ini FROM barang";
$barang_result = $connect->query($barang_query);

// Cek jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nomor_permintaan = $_POST['nomor_permintaan'];
    $tanggal_permintaan = $_POST['tanggal_permintaan'];
    $divisi_pemohon = "Divisi Gudang"; // Hardcoded for now
    $catatan_tambahan = $_POST['catatan_tambahan'] ?? '';

    // Set kode_barang_permintaan and nama_barang_permintaan from the first item
    $kode_barang_permintaan = '';
    $nama_barang_permintaan = '';
    if (isset($_POST['items'][0]['id_barang']) && !empty($_POST['items'][0]['id_barang'])) {
        $kode_barang_permintaan = $_POST['items'][0]['id_barang'];
        $nama_barang_permintaan = $_POST['items'][0]['nama_barang'];
    }

    // Simpan data ke tabel permintaan_produksi
    $query = "INSERT INTO permintaan_produksi (nomor_permintaan, tanggal_permintaan, divisi_pemohon, catatan_tambahan, kode_barang_permintaan, nama_barang_permintaan) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("ssssss", $nomor_permintaan, $tanggal_permintaan, $divisi_pemohon, $catatan_tambahan, $kode_barang_permintaan, $nama_barang_permintaan);
    $stmt->execute();

    // Ambil ID permintaan yang baru saja dibuat
    $id_permintaan = $connect->insert_id;

    // Simpan detail permintaan produksi
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            $id_barang = $item['id_barang'] ?? '';
            $jumlah_permintaan = $item['jumlah_permintaan'] ?? 0;
            $catatan = $item['catatan'] ?? '';

            if (!empty($id_barang)) {
                $detail_query = "INSERT INTO detail_permintaan_produksi (id_permintaan, id_barang, jumlah_permintaan, catatan) VALUES (?, ?, ?, ?)";
                $detail_stmt = $connect->prepare($detail_query);
                $detail_stmt->bind_param("issi", $id_permintaan, $id_barang, $jumlah_permintaan, $catatan);
                $detail_stmt->execute();
                $detail_stmt->close();
            }
        }
    }

    // Redirect atau tampilkan pesan sukses
    header("Location: status-permintaan.php");
    exit();
}
?>

<html>

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Gudang-Permintaan Produksi Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: "Inter", sans-serif;
        }

        .sidebar {
            width: 16rem;
            background-color: #9B141A;
            flex-shrink: 0;
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .main-content {
            margin-left: 0rem;
            flex: 1;
            overflow-y: auto;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>

<body class="bg-white text-black">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="bg-[#9B1919] w-64 flex flex-col justify-between sticky top-0 h-screen">
            <div>
                <div class="px-6 py-8">
                    <img alt="" class="w-36" src="https://kingland.co.id/wp-content/uploads/2022/01/LOGO-KINGLAND-Tire-Tube-white-1.png" />
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
                    <a class="flex items-center gap-3 bg-white text-[#9B1919] rounded-full px-4 py-3 font-bold shadow-md" href="buat-permintaan-produksi.php">
                        <i class="fas fa-file-alt text-lg"></i>
                        <span>Permintaan Produksi</span>
                        <div class="ml-auto bg-[#9B1919] text-white rounded-full w-8 h-7 flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-arrow-right text-sm"></i>
                        </div>
                    </a>
                    <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="BSTB-gudang.php">
                        <i class="fas fa-book text-base"></i>
                        <span>BSTB</span>
                    </a>
                    <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="pesanan_pelanggan.php">
                        <i class="fas fa-check-square text-base"></i>
                        <span>Pesanan Pelanggan</span>
                    </a>
                    <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="buat-laporan-gudang.php">
                        <i class="fas fa-chart-bar text-base"></i>
                        <span>Laporan</span>
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
                <h1 class="font-bold text-lg text-black">Permintaan Produksi</h1>
                <p class="text-gray-500 text-xs mt-1 max-w-[400px]">
                    Ajukan permintaan produksi barang ke Divisi Produksi
                </p>
            </div>

            <!-- Table container -->
            <section aria-label="Permintaan Produksi Form" class="bg-[#F5F5F5] rounded-xl p-6 max-w-full overflow-x-auto">
                <div class="bg-white rounded-xl p-6 max-w-full">
                    <nav class="flex border-b border-gray-200 mb-6">
                        <a href="buat-permintaan-produksi.php" class="text-[#9B1919] font-bold border-b-2 border-[#9B1919] pb-1 mr-6 text-sm">Buat Permintaan</a>
                        <a href="status-permintaan.php" class="text-black font-semibold text-sm pb-1">Status Permintaan</a>
                    </nav>

                    <div>
                        <form class="space-y-6" method="POST">
                            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">
                                <div class="flex-1 min-w-[250px]">
                                    <label class="block text-sm font-semibold text-black mb-1" for="nomor-permintaan">Nomor Permintaan</label>
                                    <input class="bg-gray-300 rounded-lg py-2 px-4 w-full font-bold text-gray-700 cursor-not-allowed" id="nomor-permintaan" name="nomor_permintaan" readonly type="text" value="<?= htmlspecialchars($next_number) ?>" />
                                </div>

                                <div class="flex-1 min-w-[250px] relative">
                                    <label class="block text-sm font-semibold text-black mb-1" for="tanggal-permintaan">Tanggal Permintaan</label>
                                    <div id="tanggal-permintaan" class="border border-gray-300 rounded-lg py-2 px-4 w-full text-black cursor-pointer bg-white">
                                        <!-- Tanggal akan ditampilkan di sini -->
                                    </div>
                                    <input type="hidden" name="tanggal_permintaan" id="tanggal-permintaan-hidden" value="" />
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const tanggalPermintaan = document.getElementById('tanggal-permintaan');
                                    const hiddenInput = document.getElementById('tanggal-permintaan-hidden');

                                    // Format tanggal default
                                    const today = new Date();
                                    tanggalPermintaan.textContent = today.toLocaleDateString('id-ID');
                                    hiddenInput.value = today.toISOString().split('T')[0]; // format YYYY-MM-DD

                                    // Klik untuk ubah tanggal
                                    tanggalPermintaan.addEventListener('click', () => {
                                        if (document.getElementById('hidden-datepicker')) return;

                                        const hiddenDatepicker = document.createElement('input');
                                        hiddenDatepicker.type = 'date';
                                        hiddenDatepicker.id = 'hidden-datepicker';
                                        hiddenDatepicker.style.position = 'fixed';
                                        hiddenDatepicker.style.opacity = 0;
                                        hiddenDatepicker.style.pointerEvents = 'none';

                                        hiddenDatepicker.addEventListener('change', function() {
                                            const selectedDate = new Date(this.value);
                                            tanggalPermintaan.textContent = selectedDate.toLocaleDateString('id-ID');
                                            hiddenInput.value = this.value;
                                            document.body.removeChild(hiddenDatepicker);
                                        });

                                        document.body.appendChild(hiddenDatepicker);
                                        hiddenDatepicker.focus();
                                        hiddenDatepicker.click();
                                    });
                                });
                            </script>

                            <!-- Baris: Divisi Pemohon dan Tombol Tambah Item -->
                            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">
                                <div class="flex-1 min-w-[250px]">
                                    <label class="block text-sm font-semibold text-black mb-1" for="divisi-pemohon">Divisi Pemohon</label>
                                    <input class="bg-gray-300 rounded-lg py-2 px-4 w-full text-gray-700 cursor-not-allowed" id="divisi-pemohon" readonly type="text" value="Divisi Gudang" />
                                </div>
                                <div class="flex-1 min-w-[250px] flex justify-end">
                                    <button id="tambah-item" class="bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md px-4 py-2 flex items-center gap-2 transition-colors duration-200" type="button">
                                        <i class="fas fa-plus"></i>
                                        Tambah Item
                                    </button>
                                </div>
                            </div>

                            <!-- Table for items -->
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse text-left text-xs text-gray-600 mt-4">
                                    <thead>
                                        <tr class="bg-gray-300 rounded-t-lg">
                                            <th class="py-2 px-4 font-semibold">KODE BARANG</th>
                                            <th class="py-2 px-4 font-semibold">NAMA BARANG</th>
                                            <th class="py-2 px-4 font-semibold">STOK SAAT INI</th>
                                            <th class="py-2 px-4 font-semibold">JUMLAH PERMINTAAN</th>
                                            <th class="py-2 px-4 font-semibold">CATATAN</th>
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
                                                <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm" type="number" name="items[0][jumlah_permintaan]" placeholder="Jumlah" />
                                            </td>
                                            <td class="py-3 px-4">
                                                <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm" type="text" name="items[0][catatan]" placeholder="Catatan" />
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

                            <!-- Script for adding and deleting rows with synchronization -->
                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    const tambahBtn = document.getElementById("tambah-item");
                                    const tableBody = document.getElementById("item-table-body");

                                    // Function to sync nama barang and stok based on kode barang
                                    function syncDetails(kodeSelect) {
                                        const row = kodeSelect.closest('tr');
                                        const namaInput = row.querySelector('.nama-barang');
                                        const stokInput = row.querySelector('.stok-saat-ini');
                                        const selectedOption = kodeSelect.options[kodeSelect.selectedIndex];
                                        namaInput.value = selectedOption ? selectedOption.getAttribute('data-nama') || '' : '';
                                        stokInput.value = selectedOption ? parseInt(selectedOption.getAttribute('data-stok') || 0) : 0;
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
                                                <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm" type="number" name="items[${rowCount}][jumlah_permintaan]" placeholder="Jumlah" />
                                            </td>
                                            <td class="py-3 px-4">
                                                <input class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm" type="text" name="items[${rowCount}][catatan]" placeholder="Catatan" />
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <button type="button" class="text-red-600 hover:text-red-700 text-lg delete-row-btn">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>`;

                                        tableBody.appendChild(newRow);

                                        // Add event listener for the new row
                                        const kodeSelect = newRow.querySelector('.kode-barang');
                                        kodeSelect.addEventListener('change', () => syncDetails(kodeSelect));

                                        // Add delete functionality
                                        newRow.querySelector(".delete-row-btn").addEventListener("click", function() {
                                            tableBody.removeChild(newRow);
                                        });
                                    });

                                    // Add event listeners to existing rows
                                    document.querySelectorAll(".kode-barang").forEach(kodeSelect => {
                                        kodeSelect.addEventListener('change', () => syncDetails(kodeSelect));
                                    });

                                    document.querySelectorAll(".delete-row-btn").forEach(btn => {
                                        btn.addEventListener("click", function() {
                                            const row = this.closest("tr");
                                            tableBody.removeChild(row);
                                        });
                                    });
                                });
                            </script>

                            <div>
                                <label class="block text-sm font-normal text-black mb-1" for="catatan-tambahan">Catatan Tambahan</label>
                                <textarea class="border border-gray-300 rounded-md px-3 py-2 w-full text-sm text-gray-500 resize-none" id="catatan-tambahan" name="catatan_tambahan" placeholder="Masukkan Catatan Tambahan Jika Memang Diperlukan" rows="3"></textarea>
                            </div>

                            <div class="flex justify-end">
                                <button class="bg-black hover:bg-gray-800 text-white rounded-md px-5 py-2 text-sm font-normal transition-colors duration-200" type="submit">Submit Permintaan</button>
                            </div>
                        </form>
                    </div>
                    <div id="content-status" class="bg-white rounded-xl p-6 hidden">
                        <!-- Tabel Status Permintaan -->
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-300">
                                        <th class="py-2 px-4 text-left">No. Permintaan</th>
                                        <th class="py-2 px-4 text-left">Tanggal</th>
                                        <th class="py-2 px-4 text-left">Status</th>
                                        <th class="py-2 px-4 text-left">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-200">
                                        <td class="py-3 px-4">#REQ121</td>
                                        <td class="py-3 px-4">25/01/2025</td>
                                        <td class="py-3 px-4">
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Pending</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <button class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i> Detail</button>
                                        </td>
                                    </tr>
                                    <!-- Data lainnya... -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <script>
                // Tab Switching Functionality
                document.getElementById('tab-buat').addEventListener('click', function() {
                    document.getElementById('content-buat').classList.remove('hidden');
                    document.getElementById('content-status').classList.add('hidden');
                    this.classList.add('text-[#9B1919]', 'border-b-2', 'border-[#9B1919]');
                    document.getElementById('tab-status').classList.remove('text-[#9B1919]', 'border-b-2', 'border-[#9B1919]');
                });

                document.getElementById('tab-status').addEventListener('click', function() {
                    document.getElementById('content-status').classList.remove('hidden');
                    document.getElementById('content-buat').classList.add('hidden');
                    this.classList.add('text-[#9B1919]', 'border-b-2', 'border-[#9B1919]');
                    document.getElementById('tab-buat').classList.remove('text-[#9B1919]', 'border-b-2', 'border-[#9B1919]');
                });
            </script>
        </div>
    </section>
    </main>
    </div>
</body>

</html>
<?php $connect->close(); ?>