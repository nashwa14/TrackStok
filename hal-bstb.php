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
$per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Fetch total rows
$query_total = "SELECT COUNT(DISTINCT dp.id_detail) as total 
                FROM detail_permintaan_produksi dp 
                JOIN permintaan_produksi p ON dp.id_permintaan = p.id_permintaan";
$total_rows_result = $connect->query($query_total);
$total_rows = $total_rows_result ? $total_rows_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_rows / $per_page);

// Fetch data
$query = "SELECT 
    dp.no_bstb, 
    p.tanggal_permintaan, 
    p.id_permintaan, 
    dp.status_bstb 
FROM detail_permintaan_produksi dp 
JOIN permintaan_produksi p ON dp.id_permintaan = p.id_permintaan 
LIMIT ?, ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("ii", $offset, $per_page);
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
    <title>
        Bukti Serah Terima Barang
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
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

        .modal {
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>

<body class="bg-white text-black">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
<!-- Sidebar -->
<aside class="sidebar">
            <div>
                <div class="px-6 py-8">
                    <img alt="Kingland Tire and Tube logo white on red background" class="w-36" src="https://kingland.co.id/wp-content/uploads/2022/01/LOGO-KINGLAND-Tire-Tube-white-1.png" />
                </div>
                <nav class="mt-6 flex flex-col gap-3 px-6">
                    <a class="flex items-center gap-2 text-white font-semibold" href="dashboard-produksi.php"><i class="fas fa-th-large"></i>Dashboard</a>
                    <a class="flex items-center gap-2 text-white font-semibold" href="hal-daftar-permintaan.php"><i class="fas fa-box-open"></i>Permintaan Produksi</a>
                    <a class="flex items-center justify-between bg-white rounded-full py-1 px-3 font-semibold text-black" href="hal-bstb.php">
                        <div class="flex items-center gap-2"><i class="fas fa-file-alt"></i>BSTB</div>
                        <div class="bg-[#A9161A] rounded-full w-8 h-6 flex items-center justify-center text-white mr-2"><i class="fas fa-arrow-right"></i></div>
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
            <header class="flex justify-end items-center border-b border-gray-300 pt-3 pb-4 mb-4">
                <div class="flex items-center gap-7">
                    <button class="relative text-gray-700 text-xl">
                        <i class="fas fa-bell"></i>
                        <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-600 rounded-full"></span>
                    </button>
                    <img alt="" class="rounded-full w-10 h-10 object-cover" height="40" src="https://i.pinimg.com/736x/6d/a5/cf/6da5cf7d70417b1ed1e9946ddcd7ea1b.jpg" width="40" />
                    <span class="font-semibold text-black text-sm">Jake</span>
                    <i class="fas fa-chevron-down text-gray-600 text-xs mr-6"></i>
                </div>
            </header>

            <!-- Page title -->
            <div class="px-8 py-4 border-b border-gray-300">
                <h1 class="font-bold text-lg text-black">Bukti Serah Terima Barang</h1>
            </div>
            <!-- Content area -->
            <section class="flex-1 bg-[#F2F2F2] p-8">
                <div class="bg-white rounded-xl p-6 max-w-full overflow-x-auto shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="font-bold text-lg text-black">Daftar Bukti Serah Terima Barang (BSTB)</h1>
                        <form>
                            <label class="sr-only" for="search">Search</label>
                            <div class="relative text-gray-600">
                                <input class="bg-[#E6E6E6] rounded-lg py-2 px-4 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#9B1919] focus:bg-white" id="search" name="search" placeholder="Search" type="search" />
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <i class="fas fa-search text-gray-500 text-sm"></i>
                                </div>
                            </div>
                        </form>
                    </div>
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-[#F7F7F7] text-gray-700">
                            <tr>
                                <th class="py-3 px-4 font-semibold border-b border-gray-200">No. BSTB</th>
                                <th class="py-3 px-4 font-semibold border-b border-gray-200">Tanggal</th>
                                <th class="py-3 px-4 font-semibold border-b border-gray-200">ID Permintaan</th>
                                <th class="py-3 px-4 font-semibold border-b border-gray-200">Status</th>
                                <th class="py-3 px-4 font-semibold border-b border-gray-200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-900">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="border-b border-gray-200">
                                    <td class="py-4 px-4"><?= htmlspecialchars($row['no_bstb'] ?? 'N/A') ?></td>
                                    <td class="py-4 px-4"><?= htmlspecialchars($row['tanggal_permintaan'] ?? 'N/A') ?></td>
                                    <td class="py-4 px-4"><?= htmlspecialchars($row['id_permintaan'] ?? 'N/A') ?></td>
                                    <td class="py-4 px-4">
                                        <span class="bg-[#2DBE4F] text-white text-xs font-semibold rounded-full px-3 py-1 inline-block"
                                            style="background-color: <?= $row['status_bstb'] == 'Selesai' ? '#2DBE4F' : '#D1D5DB' ?>">
                                            <?= htmlspecialchars($row['status_bstb'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <button class="bg-[#9B1919] text-white text-xs font-normal rounded-md px-4 py-2 tindak-lanjut-btn"
                                            data-no-bstb="<?= htmlspecialchars($row['no_bstb'] ?? 'N/A') ?>"
                                            data-tanggal="<?= htmlspecialchars($row['tanggal_permintaan'] ?? 'N/A') ?>"
                                            data-id-permintaan="<?= htmlspecialchars($row['id_permintaan'] ?? 'N/A') ?>"
                                            data-status="<?= htmlspecialchars($row['status_bstb'] ?? 'N/A') ?>">Tindak Lanjut</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <div class="mt-8 flex items-center justify-between text-gray-400 text-xs font-normal">
                        <div>Menampilkan <?php echo $offset + 1; ?>–<?php echo min($offset + $result->num_rows, $total_rows); ?> data dari <?php echo $total_rows; ?> data</div>
                        <nav class="flex items-center gap-2 select-none">
                            <button aria-label="Previous page" class="rounded border border-gray-300 px-2 py-1 <?= $page <= 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100' ?>" <?= $page <= 1 ? 'disabled' : '' ?> onclick="window.location.href='?page=<?= $page - 1 ?>'">
                                <i class="fas fa-angle-left"></i>
                            </button>
                            <?php for ($i = 1; $i <= min(5, $total_pages); $i++): ?>
                                <button aria-label="Page <?= $i ?>" class="rounded px-3 py-1 font-semibold <?= $i == $page ? 'bg-[#2B6CB0] text-white' : 'border border-gray-300 hover:bg-gray-100' ?>" onclick="window.location.href='?page=<?= $i ?>'">
                                    <?= $i ?>
                                </button>
                            <?php endfor; ?>
                            <?php if ($total_pages > 5): ?>
                                <span class="px-2">...</span>
                                <button aria-label="Page <?= $total_pages ?>" class="rounded border border-gray-300 px-3 py-1 hover:bg-gray-100" onclick="window.location.href='?page=<?= $total_pages ?>'">
                                    <?= $total_pages ?>
                                </button>
                            <?php endif; ?>
                            <button aria-label="Next page" class="rounded border border-gray-300 px-2 py-1 <?= $page >= $total_pages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'hover:bg-gray-100' ?>" <?= $page >= $total_pages ? 'disabled' : '' ?> onclick="window.location.href='?page=<?= $page + 1 ?>'">
                                <i class="fas fa-angle-right"></i>
                            </button>
                        </nav>
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
                        <h2 class="text-red-800 font-bold text-lg leading-none">Invoice BSTB</h2>
                        <span class="text-red-800 font-bold text-lg" id="modal-bstb">BSTB</span>
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
                            <div id="modal-no-bstb">#BSTB003</div>
                            <div class="mt-1" id="modal-tanggal">30/01/2025</div>
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
                            <p class="text-gray-500" id="modal-id-permintaan">#REQ003</p>
                        </div>
                    </div>
                    <table class="w-full text-sm text-gray-700 border-collapse border border-gray-200 rounded-md overflow-hidden" id="modal-table-body">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left font-semibold px-4 py-3 border-r border-gray-200">No. Permintaan</th>
                                <th class="text-left font-semibold px-4 py-3 border-r border-gray-200">Tanggal</th>
                                <th class="text-left font-semibold px-4 py-3 border-r border-gray-200">Kode Barang</th>
                                <th class="text-left font-semibold px-4 py-3 border-r border-gray-200">Nama Barang</th>
                                <th class="text-center font-semibold px-4 py-3 border-r border-gray-200">Jumlah Permintaan</th>
                            </tr>
                        </thead>
                        <tbody id="modal-table-content">
                            <!-- Dynamic content will be inserted here -->
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t border-gray-200 flex justify-end">
                    <button id="kirimInvoiceBtn" class="bg-black text-white px-6 py-3 rounded-md hover:bg-gray-900 transition" type="button">Kirim Invoice</button>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.tindak-lanjut-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const idPermintaan = this.getAttribute('data-id-permintaan');
                    const tanggal = this.getAttribute('data-tanggal');
                    const noBstb = this.getAttribute('data-no-bstb');
                    const status = this.getAttribute('data-status');

                    // Ambil / generate no BSTB dari PHP
                    fetch(`generate_bstb.php?id_permintaan=${idPermintaan}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.no_bstb) {
                                document.getElementById('modal-no-bstb').textContent = `#${data.no_bstb}`;
                            } else {
                                document.getElementById('modal-no-bstb').textContent = '#N/A';
                                console.error(data.error || 'Unknown error');
                            }
                        });

                    // Update modal
                    document.getElementById('modal-tanggal').textContent = tanggal;
                    document.getElementById('modal-id-permintaan').textContent = `#${idPermintaan}`;

                    // Fetch detail isi BSTB
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
                                tbody.innerHTML = '<tr><td colspan="6">No details found</td></tr>';
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching details:', error);
                            const tbody = document.getElementById('modal-table-content');
                            tbody.innerHTML = '<tr><td colspan="6">Error loading details</td></tr>';
                        });

                    // Tampilkan modal
                    document.getElementById('invoiceModal').classList.remove('hidden');
                    document.getElementById('invoiceModalContent').classList.remove('hidden');
                });
            });


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
        document.getElementById('kirimInvoiceBtn').addEventListener('click', function() {
    const idPermintaan = document.getElementById('modal-id-permintaan').textContent.replace('#', '');

    fetch(`generate_bstb.php?id_permintaan=${idPermintaan}`)
        .then(response => response.json())
        .then(data => {
            if (data.no_bstb) {
                alert(`No. BSTB berhasil dibuat: ${data.no_bstb}`);
                location.reload(); // reload biar data update
            } else {
                alert(`Gagal membuat BSTB: ${data.error}`);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan saat mengirim BSTB');
            console.error(error);
        });
});

    </script>
</body>

</html>
<?php $connect->close(); ?>