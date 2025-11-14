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

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status']) && isset($_POST['no_laporan']) && isset($_POST['new_status'])) {
    $no_laporan = $connect->real_escape_string($_POST['no_laporan']);
    $new_status = $connect->real_escape_string($_POST['new_status']);
    $updateSql = "UPDATE laporan_gudang SET status_laporan = ? WHERE no_laporan = ?";
    $updateStmt = $connect->prepare($updateSql);
    $updateStmt->bind_param("ss", $new_status, $no_laporan);
    $updateStmt->execute();
    $updateStmt->close();
    header("Location: " . $_SERVER['PHP_SELF'] . "?page=" . $page);
    exit();
}
?>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Kingland Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: "Poppins", sans-serif;
        }
    </style>
</head>

<body class="bg-[#E9E9E9] min-h-screen flex flex-col">
    <div class="flex flex-1 min-h-0">

        <!-- Sidebar -->
        <aside class="bg-[#A9161A] w-64 flex flex-col justify-between">
            <div>
                <div class="px-6 py-6 border-b border-[#7A1A1E]">
                    <img alt="" class="w-40" height="70" src="logoputih.png" width="170" />
                </div>
                <nav class="mt-6 space-y-2 px-4">
                    <a class="flex items-center space-x-3 text-white font-semibold text-lg mt-6" href="dashboard-manajemen-puncak.php">
                        <i class="fas fa-th-large text-lg"></i>
                        <span>Dashboard</span>
                    </a>
                    <a class="flex items-center justify-between bg-white rounded-full py-3 px-5 text-black font-semibold shadow-md" href="hal-laporan-manajemen-puncak.php">
                        <i class="fas fa-file-alt text-lg"></i>
                        <div class="flex items-center space-x-5">
                            <span>Laporan</span>
                        </div>
                        <div class="bg-[#9c171b] rounded-full w-8 h-8 flex items-center justify-center text-white text-lg">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                </nav>
            </div>
            <div class="px-6 py-6 border-t border-[#7A1A1E]">
                <a href="logout.php" class="flex items-center gap-3 text-white text-sm font-normal">
                    <button class="flex items-center gap-3 text-white text-sm font-normal" type="button">

                        <i class="fas fa-sign-out-alt text-lg"></i>
                        Logout</button>
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 flex flex-col min-h-0">
            <!-- Top bar -->
            <header class="flex justify-end items-center border-b border-gray-300 pt-3 pb-4 mb-4 bg-white">
                <div class="flex items-center gap-7">
                    <img alt="" class="rounded-full w-10 h-10 object-cover" height="40" src="https://cdn1-production-images-kly.akamaized.net/hj8n5c1x96Th98FDqPDGfmHRRb8=/800x1066/smart/filters:quality(75):strip_icc():format(webp)/kly-media-production/medias/5070762/original/000701600_1735526608-Snapinsta.app_471941000_909794658009262_3513192486160537358_n_1080.jpg" width="40" />
                    <span class="font-semibold text-black text-sm">Sunjae</span>
                    <i class="fas fa-chevron-down text-gray-600 text-xs mr-6"></i>
                </div>
            </header>

            <!-- Page title -->
            <div class="px-8 py-4 border-b border-gray-300">
                <h1 class="font-bold text-lg text-black">Laporan</h1>
            </div>

            <!-- Content -->
            <section class="flex-1 overflow-auto p-8">
                <div class="bg-white rounded-lg shadow-sm p-6 max-w-full overflow-x-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-lg text-[#1A1A1A]">Daftar Laporan</h3>
                        <div class="relative">
                            <input class="pl-10 pr-4 py-2 rounded-lg bg-[#F5F7FA] text-sm text-[#1A1A1A] placeholder:text-[#1A1A1A]/50 focus:outline-none focus:ring-2 focus:ring-[#9B1717]" placeholder="Search" type="text" id="search" />
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#1A1A1A]/50 text-sm"></i>
                        </div>
                    </div>
                    <table class="w-full text-left text-sm text-[#1A1A1A]">
                        <thead class="bg-[#F5F7FA] text-[#1A1A1A] font-bold">
                            <tr>
                                <th class="py-3 px-4 min-w-[140px]">No. Laporan</th>
                                <th class="py-3 px-4 min-w-[120px]">Tanggal</th>
                                <th class="py-3 px-4 min-w-[120px]">Periode</th>
                                <th class="py-3 px-4 min-w-[120px]">Status</th>
                                <th class="py-3 px-4 min-w-[120px]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="reportTableBody">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="border-b border-gray-100">
                                    <td class="py-4 px-4 text-xs font-normal text-[#1A1A1A]"><?php echo htmlspecialchars($row['no_laporan']); ?></td>
                                    <td class="py-4 px-4 text-xs font-normal text-[#1A1A1A]"><?php echo date('d/m/Y', strtotime($row['tanggal_laporan'])); ?></td>
                                    <td class="py-4 px-4 text-xs font-normal text-[#1A1A1A]"><?php echo htmlspecialchars($row['periode']); ?></td>
                                    <td class="py-4 px-4">
                                        <?php if ($row['status_laporan'] == 'Diterima'): ?>
                                            <button class="inline-block bg-[#2DB34A] text-white text-xs font-normal rounded-full px-3 py-1">Diterima</button>
                                        <?php elseif ($row['status_laporan'] == 'Disetujui'): ?>
                                            <button class="inline-block bg-[#2B7BFF] text-white text-xs font-normal rounded-full px-3 py-1">Disetujui</button>
                                        <?php elseif ($row['status_laporan'] == 'Ditolak'): ?>
                                            <button class="inline-block bg-[#D80000] text-white text-xs font-normal rounded-full px-3 py-1">Ditolak</button>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-4 flex items-center gap-4 text-lg">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="no_laporan" value="<?php echo htmlspecialchars($row['no_laporan']); ?>">
                                            <select name="new_status" onchange="this.form.submit()" class="bg-white border border-gray-300 rounded-md px-2 py-1 text-sm text-[#1A1A1A] focus:outline-none focus:ring-2 focus:ring-[#9B1717]">
                                                <option value="Diterima" <?php echo $row['status_laporan'] == 'Diterima' ? 'selected' : ''; ?>>Diterima</option>
                                                <option value="Disetujui" <?php echo $row['status_laporan'] == 'Disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                                                <option value="Ditolak" <?php echo $row['status_laporan'] == 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                        <button aria-label="Print report <?php echo htmlspecialchars($row['no_laporan']); ?>" onclick="window.print()" class="text-gray-600 hover:text-gray-900">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <p class="mt-6 text-xs text-gray-400">Menampilkan <?php echo $offset + 1; ?>–<?php echo min($offset + $result->num_rows, $totalRows); ?> data dari <?php echo $totalRows; ?> data</p>
                    <div class="mt-6 flex justify-end items-center gap-2 text-xs font-semibold text-[#1A1A1A]">
                        <button aria-label="Previous page" class="w-7 h-7 rounded border border-gray-400 text-gray-600 flex justify-center items-center <?php echo $page <= 1 ? 'disabled:opacity-50' : ''; ?>" <?php echo $page <= 1 ? 'disabled' : ''; ?> onclick="window.location.href='?page=<?php echo $page - 1; ?>'">
                            <</button>
                                <button aria-current="page" class="w-7 h-7 rounded bg-[#2B7BFF] text-white flex justify-center items-center"><?php echo $page; ?></button>
                                <button aria-label="Next page" class="w-7 h-7 rounded border border-gray-400 text-gray-600 flex justify-center items-center" <?php echo $offset + $result->num_rows >= $totalRows ? 'disabled' : ''; ?> onclick="window.location.href='?page=<?php echo $page + 1; ?>'">></button>
                    </div>
                </div>
            </section>
        </main>
    </div>
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
            const info = document.querySelector('.mt-6.text-xs.text-gray-400');
            if (visibleCount === 0) {
                info.textContent = 'Menampilkan 0 data dari ' + totalRowsInitial + ' data';
            } else {
                info.textContent = `Menampilkan 1–${visibleCount} data dari ${totalRowsInitial} data`;
            }
        });
    </script>
</body>

</html>
<?php
$stmt->close();
$connect->close();
?>