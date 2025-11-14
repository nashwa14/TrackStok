<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'rbpl_kingland';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $kode_barang = $_POST['kode_barang'];
    $penambahan_stok = (int)$_POST['penambahan_stok'];
    $current_stok = (int)$_POST['current_stok'];
    $tgl_update = $_POST['tgl_update'];

    $new_stok = $current_stok + $penambahan_stok;
    $status = $new_stok <= 10 ? 'Stok Menipis' : 'Tersedia';

    $sql = "UPDATE stok SET jumlah_stok = ?, status_stok = ?, tgl_update = ? WHERE id_barang = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $new_stok, $status, $tgl_update, $kode_barang);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<script>alert('Gagal memperbarui stok: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

$sql = "SELECT s.id_barang_stok, b.id_barang, b.nama_barang, b.gambar_url, s.jumlah_stok, s.status_stok, s.tgl_update 
        FROM stok s 
        JOIN barang b ON s.id_barang = b.id_barang 
        ORDER BY s.id_barang_stok DESC";
$result = $conn->query($sql);
?>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Kingland Stock Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            min-height: 100vh;
            overflow: hidden;
            background-color: #E6E8E8;
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

        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.4);
            position: fixed;
            inset: 0;
            z-index: 50;
            display: none;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .modal-backdrop.show {
            display: flex;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(0.5);
        }
    </style>
    <script>
        let currentCategory = 'all'; // Track current category

        function filterTable() {
            const searchInput = document.querySelector('input[type="search"]').value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const namaBarang = row.querySelector('td:nth-child(2) span').textContent.toLowerCase();
                const stockStatus = row.querySelector('td:nth-child(5) span').textContent.trim();

                const matchesSearch = namaBarang.includes(searchInput);
                const matchesCategory = currentCategory === 'all' ||
                    (currentCategory === 'available' && stockStatus === 'Tersedia') ||
                    (currentCategory === 'low' && stockStatus === 'Stok Menipis');

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
                dropdownButton.textContent = 'Semua Kategori';
            } else if (category === 'available') {
                dropdownButton.textContent = 'Tersedia';
            } else if (category === 'low') {
                dropdownButton.textContent = 'Stok Menipis';
            }
            filterTable();
        }

        function openEditModal(row) {
            const kodeBarang = row.querySelector('td:nth-child(1)').textContent.trim().replace('#', '');
            const namaBarang = row.querySelector('td:nth-child(2) span').textContent.trim();
            const gambarUrl = row.querySelector('td:nth-child(2) img').src;
            const stokSaatIni = row.querySelector('td:nth-child(4)').textContent.trim();
            const statusStok = row.querySelector('td:nth-child(5) span').textContent.trim();
            const tglUpdate = row.querySelector('td:nth-child(3)').textContent.trim();
            const [day, month, year] = tglUpdate.split('/');
            const formattedTglUpdate = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;

            document.getElementById('modalKodeBarang').value = kodeBarang;
            document.getElementById('modalNamaBarang').textContent = namaBarang;
            document.getElementById('modalGambar').src = gambarUrl;
            document.getElementById('modalStokSaatIni').value = stokSaatIni;
            document.getElementById('modalStatus').value = statusStok;
            document.getElementById('modalPenambahanStok').value = 0;
            document.getElementById('modalTglUpdate').value = formattedTglUpdate;

            document.getElementById('modalBackdrop').classList.add('show');
        }

        function closeEditModal() {
            document.getElementById('modalBackdrop').classList.remove('show');
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.btn-tindak-lanjut').forEach(button => {
                button.addEventListener('click', (e) => {
                    const row = e.target.closest('tr');
                    openEditModal(row);
                });
            });

            document.getElementById('modalBackdrop').addEventListener('click', (e) => {
                if (e.target === e.currentTarget) {
                    closeEditModal();
                }
            });

            // Add event listener for search input
            document.querySelector('input[type="search"]').addEventListener('input', filterTable);
        });
    </script>
</head>

<body>
<aside class="bg-[#9B1919] w-64 flex flex-col justify-between sticky top-0 h-screen">
        <div>
            <div class="px-6 py-8">
                <img alt="Kingland Tire and Tube logo white on red background" class="w-36" src="https://kingland.co.id/wp-content/uploads/2022/01/LOGO-KINGLAND-Tire-Tube-white-1.png" />
            </div>
            <nav class="flex flex-col space-y-3 px-6 text-white font-semibold text-sm">
                <a class="flex items-center gap-3 hover:text-white/90 transition-colors" href="dashboard-gudang.php">
                    <i class="fas fa-th-large text-base"></i>
                    <span>Dashboard</span>
                </a>
                <a class="flex items-center gap-3 bg-white text-[#9B1919] rounded-full px-4 py-3 font-bold shadow-md" href="manajemen-stok-gudang.php">
                    <i class="fas fa-cube text-base"></i>
                    <span>Manajemen Stok</span>
                    <div class="ml-auto bg-[#9B1919] text-white rounded-full w-8 h-7 flex items-center justify-center text-sm font-bold">
                        <i class="fas fa-arrow-right text-sm"></i>
                    </div>
                </a>
                <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="buat-permintaan-produksi.php">
                    <i class="fas fa-file-alt text-lg"></i>
                    <span>Permintaan Produksi</span>
                </a>
                <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="pesanan_pelanggan.php">
                    <i class="fas fa-book text-base"></i>
                    <span>BSTB</span>
                </a>
                <a class="flex items-center gap-3 font-bold text-white hover:text-white/90 transition-colors" href="BSTB-gudang.php">
                    <i class="fas fa-check-square text-base"></i>
                    <span>Pesanan Pelanggan</span>
                </a>
                <a class="flex items-center space-x-3 text-white font-bold text-sm" href="buat-laporan-gudang.php">
                    <i class="fas fa-chart-bar text-white text-base"></i>
                    <span>Laporan</span>
                </a>
            </nav>
        </div>
        <div class="px-6 pb-8">
            <hr class="border-white border-opacity-40 mb-6" />
            <a href="logout.php" class="flex items-center gap-3 text-white text-sm font-normal">
                <button class="flex items-center gap-3 text-white text-sm font-normal" type="button">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                    <span>Logout</span>
                </button>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="flex items-center justify-between border-b border-gray-300 px-8 py-4 bg-white">
            <h1 class="font-semibold text-lg text-[#1E1E1E]">
                Manajemen dan Pengelolaan Stok
            </h1>
            <div class="flex items-center space-x-6">
                <div class="flex items-center space-x-3 cursor-pointer select-none">
                    <img alt="Profile image of a man with black hair wearing white shirt and black jacket" class="w-10 h-10 rounded-full object-cover" height="40" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQm6ZdhZ6kaV6TKsbZ45FXeyc1nWBPIHyONlg&s" width="40" />
                    <span class="font-semibold text-black text-sm md:text-base">
                        Jay
                    </span>
                    <i class="fas fa-chevron-down text-gray-600">
                    </i>
                </div>
            </div>
        </header>

        <section class="flex-1 overflow-auto p-8 bg-gray-100">
            <div class="bg-white rounded-xl p-6 space-y-4 max-w-full overflow-x-auto">
                <div class="flex flex-wrap justify-end gap-3 mb-4">
                    <input id="searchInput" class="border border-gray-300 rounded-lg py-2 px-4 w-48 focus:outline-none focus:ring-2 focus:ring-[#9B1919] focus:border-transparent text-sm" placeholder="Cari Nama Barang" type="search" />
                    <div class="dropdown">
                        <button class="flex items-center border border-gray-300 rounded-lg py-2 px-4 text-sm text-gray-600 hover:bg-gray-100">
                            <span>Semua Kategori</span>
                            <i class="fas fa-chevron-down ml-2"></i>
                        </button>
                        <div class="dropdown-content">
                            <button onclick="filterCategory('all')">Semua Kategori</button>
                            <button onclick="filterCategory('available')">Tersedia</button>
                            <button onclick="filterCategory('low')">Stok Menipis</button>
                        </div>
                    </div>
                </div>
                <table class="w-full text-left text-sm text-[#1E1E1E]">
                    <thead class="bg-[#F9F9F9] border-b border-gray-300">
                        <tr>
                            <th class="py-3 px-4 font-bold">Kode Barang</th>
                            <th class="py-3 px-4 font-bold">Nama Barang</th>
                            <th class="py-3 px-4 font-bold">Terakhir Update</th>
                            <th class="py-3 px-4 font-bold">Stok</th>
                            <th class="py-3 px-4 font-bold">Status</th>
                            <th class="py-3 px-4 font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()):
                            // Ensure status is correct based on stock level
                            $status = (int)$row['jumlah_stok'] <= 10 ? 'Stok Menipis' : 'Tersedia';
                            // Update status in database if needed
                            if ($status !== $row['status_stok']) {
                                $update_sql = "UPDATE stok SET status_stok = ? WHERE id_barang = ?";
                                $update_stmt = $conn->prepare($update_sql);
                                $update_stmt->bind_param("ss", $status, $row['id_barang']);
                                $update_stmt->execute();
                                $update_stmt->close();
                                $row['status_stok'] = $status;
                            }
                        ?>
                            <tr class="border-b border-gray-200">
                                <td class="py-6 px-4 align-top">#<?= htmlspecialchars($row['id_barang']) ?></td>
                                <td class="py-6 px-4 align-top">
                                    <div class="flex flex-col items-center">
                                        <span class="mb-4"><?= htmlspecialchars($row['nama_barang']) ?></span>
                                        <img src="<?= htmlspecialchars($row['gambar_url']) ?>" alt="Gambar Barang" class="w-20 h-30 object-contain" width="80" height="120" />
                                    </div>
                                </td>
                                <td class="py-6 px-4 align-top"><?= date("d/m/Y", strtotime($row['tgl_update'])) ?></td>
                                <td class="py-6 px-4 align-top"><?= (int)$row['jumlah_stok'] ?></td>
                                <td class="py-6 px-4 align-top">
                                    <span class="inline-block px-4 py-1 rounded-full text-white text-sm font-semibold
                                <?= $row['status_stok'] === 'Tersedia' ? 'bg-green-500' : 'bg-yellow-400' ?>">
                                        <?= htmlspecialchars($row['status_stok']) ?>
                                    </span>
                                </td>
                                <td class="py-6 px-4 align-top">
                                    <button type="button" class="btn-tindak-lanjut bg-[#9B1919] text-white text-xs px-4 py-2 rounded">Tindak Lanjut</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="flex justify-between items-center text-gray-400 text-xs mt-6 select-none">
                    <span class="row-count">
                        Menampilkan <?php echo $result->num_rows; ?> data
                    </span>
                    <nav class="flex items-center space-x-2">
                        <button aria-label="Previous page" class="border border-gray-300 rounded px-2 py-1 bg-gray-200 text-gray-400 cursor-not-allowed" disabled>
                            ‹
                        </button>
                        <button aria-current="page" class="bg-[#3B82F6] text-white rounded px-3 py-1 font-semibold">
                            1
                        </button>
                        <button aria-label="Next page" class="border border-gray-300 rounded px-2 py-1 bg-gray-200 text-gray-400 cursor-not-allowed" disabled>
                            ›
                        </button>
                    </nav>
                </div>
            </div>
        </section>
    </main>

    <div id="modalBackdrop" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalDesc">
        <div class="bg-white rounded-3xl p-10 shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" style="max-width: 720px;">
            <h3 id="modalTitle" class="font-bold text-xl mb-8">
                Edit Informasi Barang
            </h3>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div>
                        <label for="modalKodeBarang" class="block mb-2 text-base font-normal text-black">Kode Barang</label>
                        <input id="modalKodeBarang" name="kode_barang" type="text" readonly class="w-full rounded-lg border border-gray-300 px-4 py-2 font-bold text-gray-800 bg-gray-100 cursor-not-allowed" value="#<?= htmlspecialchars($row['id_barang']) ?>" />
                    </div>
                    <div>
                        <label for="modalTglUpdate" class="block mb-2 text-base font-normal text-black">Terakhir Update</label>
                        <input id="modalTglUpdate" name="tgl_update" type="date" class="w-full rounded-lg border border-gray-300 px-4 py-2 font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-300" />
                    </div>
                    <div>
                        <label for="modalStokSaatIni" class="block mb-2 text-base font-normal text-black">Stok Saat Ini</label>
                        <input id="modalStokSaatIni" name="current_stok" type="number" readonly class="w-full rounded-lg border border-gray-300 px-4 py-2 font-bold text-gray-800 bg-gray-100 cursor-not-allowed" />
                    </div>
                    <div>
                        <label for="modalPenambahanStok" class="block mb-2 text-base font-normal text-black">Penambahan Stok</label>
                        <input id="modalPenambahanStok" name="penambahan_stok" type="number" min="0" class="w-full rounded-lg border border-gray-300 px-4 py-2 font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-300" />
                    </div>
                    <div>
                        <label for="modalStatus" class="block mb-2 text-base font-normal text-black">Status</label>
                        <select id="modalStatus" name="status" class="w-full rounded-lg border border-gray-300 px-4 py-2 font-bold text-gray-800 shadow-sm appearance-none pr-10 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option value="Tersedia">Tersedia</option>
                            <option value="Stok Menipis">Stok Menipis</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block mb-2 text-base font-normal text-black font-bold" for="modalNamaBarang">Nama Barang</label>
                    <div class="border border-gray-300 rounded-lg p-6 flex flex-col items-center justify-center text-center text-black font-bold text-lg" style="height: 280px;">
                        <span id="modalNamaBarang"><?= htmlspecialchars($row['nama_barang']) ?></span>
                        <img id="modalGambar" alt="Gambar barang" src="<?= htmlspecialchars($row['gambar_url']) ?>" class="mt-6 max-h-[200px] object-contain" width="120" height="200" />
                    </div>
                </div>
                <div class="md:col-span-2 flex justify-end gap-6 mt-8">
                    <button type="button" onclick="closeEditModal()" class="border border-gray-900 rounded-md px-8 py-3 text-base font-normal text-black hover:bg-gray-100 transition">
                        Batal
                    </button>
                    <button type="submit" name="update_stock" class="bg-black rounded-md px-8 py-3 text-base font-normal text-white hover:bg-gray-900 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
<?php $conn->close(); ?>