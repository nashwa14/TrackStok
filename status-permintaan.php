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

// Pagination settings
$per_page = 10; // Items per page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Fetch total rows
$total_rows_result = $connect->query("SELECT COUNT(DISTINCT p.id_permintaan) as total FROM permintaan_produksi p LEFT JOIN detail_permintaan_produksi d ON p.id_permintaan = d.id_permintaan");
$total_rows = $total_rows_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);

// Fetch data for the current page, including status
$result = $connect->query("SELECT p.id_permintaan, p.nomor_permintaan, p.tanggal_permintaan, p.kode_barang_permintaan, p.nama_barang_permintaan, d.jumlah_permintaan, b.gambar_url, p.status 
    FROM permintaan_produksi p 
    LEFT JOIN detail_permintaan_produksi d ON p.id_permintaan = d.id_permintaan 
    LEFT JOIN barang b ON p.kode_barang_permintaan = b.id_barang 
    LIMIT $offset, $per_page");

if (!$result) {
    die('Query failed: ' . $connect->error);
}
?>

<html>

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Permintaan Produksi Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Roboto+Mono&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .sidebar {
            width: 16rem;
            background-color: #9B1919;
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
            flex: 1;
            overflow-y: auto;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>

<body class="bg-white min-h-screen flex">
    <!-- Sidebar -->
    <!-- Sidebar -->
    <aside class="sidebar">
        <div>
            <div class="px-6 py-8">
                <img alt="Kingland Tire and Tube logo white on red background" class="w-36" src="https://kingland.co.id/wp-content/uploads/2022/01/LOGO-KINGLAND-Tire-Tube-white-1.png" />
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
    <main class="main-content">
        <!-- Header -->
        <header class="flex items-center justify-end gap-6 px-8 py-4 border-b border-gray-300">
            <div class="relative"></div>
            <div class="flex items-center gap-3 cursor-pointer select-none">
                <img alt="Profile image of a man with black hair wearing white shirt and black jacket" class="w-10 h-10 rounded-full object-cover" height="40" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQm6ZdhZ6kaV6TKsbZ45FXeyc1nWBPIHyONlg&s" width="40" />
                <span class="font-bold text-black">Jay</span>
                <i class="fas fa-chevron-down text-gray-600"></i>
            </div>
        </header>

        <div class="px-8 py-4 border-b border-gray-300">
            <h1 class="font-bold text-lg text-black">Permintaan Produksi</h1>
            <p class="text-gray-500 text-xs mt-1 max-w-[400px]">Ajukan permintaan produksi barang ke Divisi Produksi</p>
        </div>

        <!-- Content -->
        <section class="flex-1 p-6 bg-[#F1F1F1]">
            <div class="bg-white rounded-xl p-4 max-w-full overflow-x-auto shadow-sm">
                <div class="flex border-b border-gray-300 mb-2 text-sm font-semibold">
                    <a href="buat-permintaan-produksi.php" class="py-2 px-4 border-b-2 border-transparent hover:border-gray-300 text-black">
                        Buat Permintaan
                    </a>
                    <a href="status-permintaan.php" class="py-2 px-4 border-b-2 border-[#9B1B1B] text-[#9B1B1B]">
                        Status Permintaan
                    </a>
                </div>

                <div class="flex items-center justify-between bg-gray-100 rounded-md px-4 py-2 mb-4 font-['Roboto_Mono'] text-xs text-gray-400 tracking-widest">
                    Status Permintaan Produksi Barang
                    <div class="flex items-center gap-2">
                        <!-- Input Search -->
                        <div class="relative">
                            <input class="rounded-md border border-gray-300 bg-white py-1 pl-8 pr-3 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-[#9B1B1B]" placeholder="Search" type="search" />
                            <i class="fas fa-search absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        </div>

                        <!-- Dropdown Button -->
                        <div class="relative inline-block text-left">
                            <button onclick="toggleKategoriDropdown()" id="kategoriBtn" class="flex items-center gap-1 rounded-md border border-gray-300 bg-white px-3 py-1 text-xs text-gray-600 hover:bg-gray-50">
                                Semua Kategori
                                <i class="fas fa-chevron-down text-xs ml-1"></i>
                            </button>
                            <!-- Dropdown Menu -->
                            <div id="kategoriDropdown" class="hidden absolute right-0 z-10 mt-1 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                <div class="py-1 text-xs">
                                    <button class="w-full text-left px-4 py-2 text-[#000000] hover:bg-gray-100" onclick="setKategori('Semua Kategori')">Semua Kategori</button>
                                    <button class="w-full text-left px-4 py-2 text-[#000000] hover:bg-gray-100" onclick="setKategori('Menunggu Konfirmasi')">Menunggu Konfirmasi</button>
                                    <button class="w-full text-left px-4 py-2 text-[#000000] hover:bg-gray-100" onclick="setKategori('Sedang Diproduksi')">Sedang Diproduksi</button>
                                    <button class="w-full text-left px-4 py-2 text-[#000000] hover:bg-gray-100" onclick="setKategori('Selesai Diproduksi')">Selesai Diproduksi</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="w-full text-left text-xs text-gray-700 font-semibold">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-2 px-3">No. Permintaan</th>
                            <th class="py-2 px-3">Tanggal</th>
                            <th class="py-2 px-3">Kode Barang</th>
                            <th class="py-2 px-3">Nama Barang</th>
                            <th class="py-2 px-3">Jumlah Permintaan</th>
                            <th class="py-2 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="bg-white">
                                <td class="py-6 px-3"><?php echo htmlspecialchars($row['nomor_permintaan'] ?? 'N/A'); ?></td>
                                <td class="py-6 px-3"><?php echo htmlspecialchars($row['tanggal_permintaan'] ?? 'N/A'); ?></td>
                                <td class="py-6 px-3"><?php echo htmlspecialchars($row['kode_barang_permintaan'] ?? 'N/A'); ?></td>
                                <td class="py-6 px-3 text-left">
                                    <div class="flex flex-col items-start gap-2">
                                        <span class="font-semibold text-black"><?php echo htmlspecialchars($row['nama_barang_permintaan'] ?? 'N/A'); ?></span>
                                        <img alt="Product image" src="<?php echo htmlspecialchars($row['gambar_url'] ?? 'https://storage.googleapis.com/a1aa/image/12e25fdc-5dcb-4717-9bfa-ca314660023f.jpg'); ?>" class="w-20 h-20 object-contain rounded-md shadow-sm" />
                                    </div>
                                </td>
                                <td class="py-6 px-3"><?php echo htmlspecialchars($row['jumlah_permintaan'] ?? 'N/A'); ?></td>
                                <td class="py-6 px-3 text-center"><?php echo htmlspecialchars($row['status'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="flex justify-between items-center text-gray-400 text-xs mt-6 select-none">
                    <span class="row-count">
                        Menampilkan <?php echo $result->num_rows; ?> data
                    </span>
                    <nav class="flex items-center space-x-2">
                        <button aria-label="Previous page" class="border border-gray-300 rounded px-2 py-1 <?php echo ($page <= 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'); ?>" <?php echo ($page <= 1 ? 'disabled' : ''); ?> onclick="window.location.href='?page=<?php echo $page - 1; ?>'">
                            ‹
                        </button>
                        <?php for ($i = 1; $i <= min(5, $total_pages); $i++): ?>
                            <button aria-label="Page <?php echo $i; ?>" class="rounded px-3 py-1 font-semibold <?php echo ($i == $page ? 'bg-[#3B82F6] text-white' : 'border border-gray-300 hover:bg-gray-100'); ?>" onclick="window.location.href='?page=<?php echo $i; ?>'">
                                <?php echo $i; ?>
                            </button>
                        <?php endfor; ?>
                        <?php if ($total_pages > 5): ?>
                            <span class="px-2">...</span>
                            <button aria-label="Page <?php echo $total_pages; ?>" class="rounded border border-gray-300 px-3 py-1 hover:bg-gray-100" onclick="window.location.href='?page=<?php echo $total_pages; ?>'">
                                <?php echo $total_pages; ?>
                            </button>
                        <?php endif; ?>
                        <button aria-label="Next page" class="border border-gray-300 rounded px-2 py-1 <?php echo ($page >= $total_pages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100'); ?>" <?php echo ($page >= $total_pages ? 'disabled' : ''); ?> onclick="window.location.href='?page=<?php echo $page + 1; ?>'">
                            ›
                        </button>
                    </nav>
                </div>
            </div>
        </section>
        <script>
            function toggleKategoriDropdown() {
                const dropdown = document.getElementById("kategoriDropdown");
                dropdown.classList.toggle("hidden");
            }

            function setKategori(label) {
                const button = document.getElementById("kategoriBtn");
                button.textContent = label + ' ';
                button.appendChild(document.createElement('i')).className = 'fas fa-chevron-down text-xs ml-1';
                toggleKategoriDropdown();
            }

            // Close dropdown if click outside
            document.addEventListener("click", function(event) {
                const dropdown = document.getElementById("kategoriDropdown");
                const button = document.getElementById("kategoriBtn");
                if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                    dropdown.classList.add("hidden");
                }
            });
        </script>
    </main>
</body>

</html>
<?php $connect->close(); ?>