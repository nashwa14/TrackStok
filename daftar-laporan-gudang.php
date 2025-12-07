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
    die("Connection failed: " . $connect->connect_error);
}

// Pagination
$perPage = 4;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Fetch total records
$totalQuery = "SELECT COUNT(*) as total FROM laporan_gudang";
$totalResult = $connect->query($totalQuery);
$totalRows = $totalResult->fetch_assoc()['total'];

// Fetch data with limit and offset
$sql = "SELECT no_laporan, tanggal_laporan, periode, status_laporan FROM laporan_gudang ORDER BY tanggal_laporan DESC LIMIT ? OFFSET ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Handle delete action
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $no_laporan = $connect->real_escape_string($_GET['delete']);
    $deleteSql = "DELETE FROM laporan_gudang WHERE no_laporan = ?";
    $deleteStmt = $connect->prepare($deleteSql);
    $deleteStmt->bind_param("s", $no_laporan);
    $deleteStmt->execute();
    $deleteStmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Kingland Laporan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: "Poppins", sans-serif;
        }
    </style>
</head>
<body class="bg-[#f0efef] min-h-screen flex">
    <!-- Sidebar -->
    <aside class="bg-[#9B1919] w-64 flex flex-col justify-between sticky top-0 h-screen">
        <div>
            <div class="px-6 py-8">
                <img alt="Kingland Tire and Tube logo white on red background" class="w-36" decoding="async" height="auto" loading="lazy" src="https://kingland.co.id/wp-content/uploads/2022/01/LOGO-KINGLAND-Tire-Tube-white-1.png" width="144"/>
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
                <a class="flex items-center gap-3 bg-white text-[#9B1919] rounded-full px-4 py-3 font-bold shadow-md" href="buat-laporan-gudang.php">
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
                <img alt="Profile image of a man with black hair wearing white shirt and black jacket" class="w-10 h-10 rounded-full object-cover" height="40" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQm6ZdhZ6kaV6TKsbZ45FXeyc1nWBPIHyONlg&s" width="40"/>
                <span class="font-bold text-black">Jay</span>
                <i class="fas fa-chevron-down text-gray-600"></i>
            </div>
        </header>
        <!-- Page title -->
        <div class="px-8 py-4 border-b border-gray-300">
            <h1 class="font-bold text-lg text-black">Laporan Gudang PT. KingLand</h1>
        </div>
        <!-- Content area -->
        <section class="flex-1 p-8">
            <div class="bg-white rounded-xl p-6 shadow-sm max-w-full overflow-x-auto">
                <!-- Tabs -->
                <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
                    <div class="flex gap-8 text-sm font-semibold text-gray-900">
                        <button class="text-gray-700 hover:text-black" onclick="window.location.href='buat-laporan-gudang.php-gudang.php'">Buat Laporan</button>
                        <button aria-current="true" class="text-[#9B1919] border-b-2 border-[#9B1919] pb-1">Daftar Laporan</button>
                    </div>
                    <div>
                        <label class="relative block text-gray-500 focus-within:text-gray-700" for="search">
                            <input aria-label="Search reports" class="rounded-lg bg-[#e9e9e9] py-2 pl-10 pr-4 text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#9B1919] focus:bg-white" id="search" placeholder="Search" type="search"/>
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500"></i>
                        </label>
                    </div>
                </div>
                <!-- Table -->
                <table class="w-full text-sm text-left text-gray-700">
                    <thead class="bg-[#f9f9f9] text-gray-900 font-semibold">
                        <tr>
                            <th class="py-3 px-4">No. Laporan</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Periode</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="reportTableBody">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b border-gray-200">
                                <td class="py-4 px-4"><?php echo htmlspecialchars($row['no_laporan']); ?></td>
                                <td class="py-4 px-4"><?php echo date('d/m/Y', strtotime($row['tanggal_laporan'])); ?></td>
                                <td class="py-4 px-4"><?php echo htmlspecialchars($row['periode']); ?></td>
                                <td class="py-4 px-4">
                                    <span class="bg-<?php echo $row['status_laporan'] == 'Disetujui' ? 'blue' : 'green'; ?>-500 text-white rounded-full px-4 py-1 text-xs font-semibold">
                                        <?php echo $row['status_laporan'] == 'Disetujui' ? 'Disetujui' : 'Terkirim'; ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 flex items-center gap-6 text-xl">
                                    <button aria-label="Print report <?php echo htmlspecialchars($row['no_laporan']); ?>" onclick="window.print()">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button aria-label="Delete report <?php echo htmlspecialchars($row['no_laporan']); ?>" class="text-red-600 delete-row" onclick="if(confirm('Are you sure you want to delete this report?')) window.location.href='?delete=<?php echo urlencode($row['no_laporan']); ?>'">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <!-- Footer -->
                <div class="flex justify-between items-center mt-8 text-xs text-gray-400 font-normal">
                    <div id="tableInfo">
                        Menampilkan <?php echo $offset + 1; ?>–<?php echo min($offset + $result->num_rows, $totalRows); ?> data dari <?php echo $totalRows; ?> data
                    </div>
                    <div class="flex items-center gap-2">
                        <button aria-label="Previous page" class="rounded border border-gray-300 px-2 py-1 <?php echo $page <= 1 ? 'disabled:opacity-50' : ''; ?>" <?php echo $page <= 1 ? 'disabled' : ''; ?> onclick="window.location.href='?page=<?php echo $page - 1; ?>'">
                            <
                        </button>
                        <button aria-current="page" class="bg-[#5a8ea3] text-white rounded px-3 py-1">
                            <?php echo $page; ?>
                        </button>
                        <button aria-label="Next page" class="rounded border border-gray-300 px-2 py-1" <?php echo $offset + $result->num_rows >= $totalRows ? 'disabled' : ''; ?> onclick="window.location.href='?page=<?php echo $page + 1; ?>'">
                            >
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script>
        // Search functionality
        const searchInput = document.getElementById('search');
        const tbody = document.getElementById('reportTableBody');
        const totalRowsInitial = <?php echo $totalRows; ?>;

        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase().trim();
            let visibleCount = 0;
            tbody.querySelectorAll('tr').forEach(row => {
                const noLaporan = row.cells[0].textContent.toLowerCase();
                const tanggal = row.cells[1].textContent.toLowerCase();
                const periode = row.cells[2].textContent.toLowerCase();
                if (
                    noLaporan.includes(filter) ||
                    tanggal.includes(filter) ||
                    periode.includes(filter)
                ) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            updateTableInfo(visibleCount, totalRowsInitial);
        });

        // Update table info function
        function updateTableInfo(visibleCount, totalCount) {
            const info = document.getElementById('tableInfo');
            if (visibleCount === 0) {
                info.textContent = 'Menampilkan 0 data dari ' + totalCount + ' data';
            } else {
                info.textContent = `Menampilkan 1–${visibleCount} data dari ${totalCount} data`;
            }
        }

        // Delete functionality (handled via PHP redirect)
        document.querySelectorAll('.delete-row').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this report?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
<?php
$stmt->close();
$connect->close();
?>