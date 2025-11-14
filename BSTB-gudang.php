<?php
$hostname = "localhost";
$username = "root";
$password = '';
$database = "rbpl_kingland";

$connect = new mysqli($hostname, $username, $password, $database);

if ($connect->connect_error) {
    die('Maaf koneksi gagal:' . $connect->connect_error);
}

// Pagination settings
$per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Search functionality
$search = isset($_GET['search']) ? $connect->real_escape_string($_GET['search']) : '';

// Fetch total rows with search filter
$query_total = "SELECT COUNT(DISTINCT dp.no_bstb) as total 
                FROM detail_permintaan_produksi dp 
                JOIN permintaan_produksi p ON dp.id_permintaan = p.id_permintaan 
                WHERE dp.no_bstb LIKE ? OR p.id_permintaan LIKE ?";
$stmt_total = $connect->prepare($query_total);
$search_param = "%$search%";
$stmt_total->bind_param("ss", $search_param, $search_param);
$stmt_total->execute();
$total_rows_result = $stmt_total->get_result();
$total_rows = $total_rows_result ? $total_rows_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_rows / $per_page);

// Fetch data with pagination and search
$query = "SELECT 
    dp.no_bstb, 
    p.tanggal_permintaan, 
    p.id_permintaan 
FROM detail_permintaan_produksi dp 
JOIN permintaan_produksi p ON dp.id_permintaan = p.id_permintaan 
WHERE dp.no_bstb LIKE ? OR p.id_permintaan LIKE ? 
LIMIT ?, ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("ssii", $search_param, $search_param, $offset, $per_page);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die('Query failed: ' . $connect->error);
}
?>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Kingland BSTB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            min-height: 100vh;
            overflow: hidden;
            background-color: #F0EFED;
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

        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content button {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            width: 100%;
            text-align: left;
        }

        .dropdown-content button:hover {
            background-color: #f1f1f1;
        }

        .modal {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <script>
        let currentCategory = 'all'; // Track current category

        function filterTable() {
            const searchInput = document.querySelector('input[type="search"]').value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('search', searchInput);
            window.history.replaceState({}, '', '?' + urlParams.toString());

            rows.forEach(row => {
                const noBSTB = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                const idPermintaan = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const status = row.querySelector('td:nth-child(4) span').textContent.trim();

                const matchesSearch = noBSTB.includes(searchInput) || idPermintaan.includes(searchInput);
                const matchesCategory = currentCategory === 'all' ||
                    (currentCategory === 'accepted' && status === 'Diterima');

                row.style.display = matchesSearch && matchesCategory ? '' : 'none';
            });

            // Update displayed row count
            const visibleRows = document.querySelectorAll('tbody tr:not([style*="display: none"])').length;
            document.querySelector('.row-count').textContent = `Menampilkan ${visibleRows} data`;
        }

        function filterCategory(category) {
            currentCategory = category;
            const dropdownButton = document.querySelector('.dropdown button span');
            if (category === 'all') {
                dropdownButton.textContent = 'Semua Status';
            } else if (category === 'accepted') {
                dropdownButton.textContent = 'Diterima';
            }
            filterTable();
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('input[type="search"]').addEventListener('input', filterTable);

            // Detail button functionality
            document.querySelectorAll('button[type="button"]').forEach(button => {
                if (button.textContent.trim() === 'Detail') {
                    button.addEventListener('click', function() {
                        const row = this.closest('tr');
                        const noBSTB = row.querySelector('td:nth-child(1)').textContent;
                        const tanggal = row.querySelector('td:nth-child(2)').textContent;
                        const idPermintaan = row.querySelector('td:nth-child(3)').textContent.replace('#', '');

                        // Update modal fields
                        document.getElementById('modal-no-bstb').textContent = noBSTB;
                        document.getElementById('modal-tanggal').textContent = tanggal;
                        document.getElementById('modal-id-permintaan').textContent = `#${idPermintaan}`;

                        // Fetch and populate details
                        fetch(`get_details.php?id_permintaan=${idPermintaan}`)
                            .then(response => response.json())
                            .then(data => {
                                const tbody = document.getElementById('modal-table-content');
                                tbody.innerHTML = '';
                                if (data.length > 0) {
                                    data.forEach(item => {
                                        const row = document.createElement('tr');
                                        row.className = 'border-t border-gray-200';
                                        row.innerHTML = `
                                            <td class="px-4 py-4 border-r border-gray-200">${item.no_permintaan}</td>
                                            <td class="px-4 py-4 border-r border-gray-200">${item.tanggal}</td>
                                            <td class="px-4 py-4 border-r border-gray-200">${item.kode_barang}</td>
                                            <td class="px-4 py-4 border-r border-gray-200">${item.nama_barang}</td>
                                            <td class="text-center px-4 py-4">${item.jumlah_permintaan}</td>
                                        `;
                                        tbody.appendChild(row);
                                    });
                                } else {
                                    tbody.innerHTML = '<tr><td colspan="5">No details found</td></tr>';
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching details:', error);
                                const tbody = document.getElementById('modal-table-content');
                                tbody.innerHTML = '<tr><td colspan="5">Error loading details</td></tr>';
                            });

                        // Show modal
                        document.getElementById('invoiceModal').classList.remove('hidden');
                        document.getElementById('invoiceModalContent').classList.remove('hidden');
                    });
                }
            });

            // Close modal functionality
            document.querySelectorAll('.close-modal').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('invoiceModal').classList.add('hidden');
                    document.getElementById('invoiceModalContent').classList.add('hidden');
                });
            });

            // Close modal when clicking outside
            document.getElementById('invoiceModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                    document.getElementById('invoiceModalContent').classList.add('hidden');
                }
            });
        });
    </script>
</head>

<body>
    <aside class="sidebar">
        <div>
            <div class="px-6 py-8">
                <img alt="Kingland Tire and Tube logo white on red background" class="w-36" src="https://kingland.co.id/wp-content/uploads/2022/01/LOGO-KINGLAND-Tire-Tube-white-1.png" />
            </div>
            <nav class="flex flex-col space-y-3 px-6 text-white font-semibold text-base">
                <a class="flex items-center gap-3 hover:text-white/90 transition-colors" href="dashboard-gudang.php">
                    <i class="fas fa-th-large"></i>
                    Dashboard
                </a>
                <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="manajemen-stok-gudang.php">
                    <i class="fas fa-cube text-base"></i>
                    Manajemen Stok
                </a>
                <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="buat-permintaan-produksi.php">
                    <i class="fas fa-file-alt text-lg"></i>
                    Permintaan Produksi
                </a>
                <a class="flex items-center gap-3 bg-white text-[#9B1919] rounded-full px-4 py-3 font-bold shadow-md" href="BSTB-gudang.php">
                    <i class="fas fa-book text-base"></i>
                    BSTB
                    <div class="ml-auto bg-[#9B1919] text-white rounded-full w-8 h-7 flex items-center justify-center text-sm font-bold">
                        <i class="fas fa-arrow-right text-sm"></i>
                    </div>
                </a>
                <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="pesanan_pelanggan.php">
                    <i class="fas fa-check-square text-base"></i>
                    Pesanan Pelanggan
                </a>
                <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="buat-laporan.php">
                    <i class="fas fa-chart-bar text-base"></i>
                    Laporan
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

    <main class="main-content flex-1 flex flex-col">
        <header class="flex items-center justify-between border-b border-gray-300 px-8 py-4 bg-white">
            <h2 class="font-semibold text-sm">Bukti Serah Terima Barang</h2>
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3 cursor-pointer select-none">
                    <img alt="Profile picture of user Jay, a man wearing a black jacket and white shirt" class="w-10 h-10 rounded-full object-cover" height="40" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQm6ZdhZ6kaV6TKsbZ45FXeyc1nWBPIHyONlg&s" width="40" />
                    <span class="font-semibold text-black text-base">Jay</span>
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </div>
            </div>
        </header>

        <section class="p-8 flex-1 overflow-auto">
            <div class="bg-white rounded-xl p-8 shadow-sm max-w-full overflow-x-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-xl text-black">Daftar Bukti Serah Terima Barang (BSTB)</h3>
                    <form>
                        <label class="sr-only" for="search">Search</label>
                        <div class="flex items-center bg-gray-200 rounded-lg px-4 py-2 text-gray-600 text-sm">
                            <i class="fas fa-search mr-2"></i>
                            <input id="search" name="search" class="bg-transparent focus:outline-none" type="search" placeholder="Search" value="<?= htmlspecialchars($search) ?>" />
                        </div>
                    </form>
                </div>
                <table class="w-full text-left text-sm border-collapse">
                    <thead class="bg-[#F0EFED] text-gray-700 font-semibold">
                        <tr>
                            <th class="py-3 px-4">No. BSTB</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">ID Permintaan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="py-5 px-4 text-xs text-gray-700"><?= htmlspecialchars($row['no_bstb'] ?? 'N/A') ?></td>
                                <td class="py-5 px-4 text-xs text-gray-700"><?= htmlspecialchars($row['tanggal_permintaan'] ?? 'N/A') ?></td>
                                <td class="py-5 px-4 text-xs text-gray-700"><?= htmlspecialchars($row['id_permintaan'] ?? 'N/A') ?></td>
                                <td class="py-5 px-4">
                                    <span class="inline-block bg-green-500 text-white text-xs font-semibold rounded-full px-3 py-1" style="background-color: #2DBE4F">
                                        Diterima
                                    </span>
                                </td>
                                <td class="py-5 px-4">
                                    <button class="bg-[#9B1919] text-white text-xs font-semibold rounded-md px-4 py-2" type="button">Detail</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="flex justify-between items-center mt-12 text-xs text-gray-400">
                    <span class="row-count">Menampilkan <?php echo $offset + 1; ?>–<?php echo min($offset + $result->num_rows, $total_rows); ?> data dari <?php echo $total_rows; ?> data</span>
                    <div class="flex items-center gap-2">
                        <button aria-label="Previous page" class="bg-gray-400 rounded px-2 py-1 <?= $page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-300' ?>" <?= $page <= 1 ? 'disabled' : '' ?> onclick="window.location.href='?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>'">‹</button>
                        <?php for ($i = 1; $i <= min(5, $total_pages); $i++): ?>
                            <button aria-label="Page <?= $i ?>" class="bg-gray-400 rounded px-3 py-1 <?= $i == $page ? 'bg-[#3B82F6] text-white' : 'hover:bg-gray-300' ?>" onclick="window.location.href='?page=<?= $i ?>&search=<?= urlencode($search) ?>'"><?= $i ?></button>
                        <?php endfor; ?>
                        <?php if ($total_pages > 5): ?>
                            <span class="px-2">...</span>
                            <button aria-label="Page <?= $total_pages ?>" class="bg-gray-400 rounded px-3 py-1 hover:bg-gray-300" onclick="window.location.href='?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>'"><?= $total_pages ?></button>
                        <?php endif; ?>
                        <button aria-label="Next page" class="bg-gray-400 rounded px-2 py-1 <?= $page >= $total_pages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-300' ?>" <?= $page >= $total_pages ? 'disabled' : '' ?> onclick="window.location.href='?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>'">›</button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal Overlay -->
    <div id="invoiceModal" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>
    <div id="invoiceModalContent" class="fixed inset-0 flex items-center justify-center z-50 p-4 hidden" role="dialog">
        <div class="bg-white rounded-md max-w-md w-full shadow-lg relative flex flex-col modal" style="min-width: 400px; max-width: 480px">
            <button aria-label="Close modal" class="absolute top-4 right-4 text-black text-xl font-bold hover:text-gray-700 close-modal">
                <i class="fas fa-times"></i>
            </button>
            <div class="p-6 flex flex-col gap-4">
                <div class="flex justify-between items-start">
                    <h2 class="text-red-800 font-bold text-lg leading-none">Detail BSTB</h2>
                    <span class="text-red-800 font-bold text-lg" id="modal-no-bstb">#BSTB001</span>
                </div>
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm font-semibold leading-tight">
                            PT. King Land<br />
                            Jl. Raya Serang KM. 68, Nambo Ilir, Kibin,<br />
                            Serang, Banten.
                        </p>
                    </div>
                    <div class="text-right text-sm text-gray-600 font-semibold leading-tight">
                        <div id="modal-tanggal">30/01/2025</div>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-md p-4 grid grid-cols-3 gap-x-4 text-sm text-gray-700">
                    <div>
                        <p class="font-semibold text-gray-900">Dari:</p>
                        <p class="text-gray-500">Produksi</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Ke:</p>
                        <p class="text-gray-500">Gudang</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900">ID Permintaan</p>
                        <p class="text-gray-500" id="modal-id-permintaan">#REQ001</p>
                    </div>
                </div>
                <table class="w-full text-sm text-gray-700 border-collapse border border-gray-200 rounded-md overflow-hidden" id="modal-table-body">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left font-semibold px-4 py-3 border-r border-gray-200">No. Permintaan</th>
                            <th class="text-left font-semibold px-4 py-3 border-r border-gray-200">Tanggal</th>
                            <th class="text-left font-semibold px-4 py-3 border-r border-gray-200">Kode Barang</th>
                            <th class="text-left font-semibold px-4 py-3 border-r border-gray-200">Nama Barang</th>
                            <th class="text-center font-semibold px-4 py-3">Jumlah Permintaan</th>
                        </tr>
                    </thead>
                    <tbody id="modal-table-content">
                        <!-- Dynamic content will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
<?php $connect->close(); ?>