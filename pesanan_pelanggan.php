<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'rbpl_kingland';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Pagination settings
$per_page = 7; // Match the number of rows displayed
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Fetch total rows
$query_total = "SELECT COUNT(id_pesanan) as total FROM pesanan_pelanggan";
$total_rows_result = $conn->query($query_total);
$total_rows = $total_rows_result ? $total_rows_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_rows / $per_page);

// Fetch data with pagination
$query = "SELECT id_pesanan, no_pesanan, tanggal, nama_pelanggan, email, telepon, alamat, subtotal, status_pesanan, barang_pesanan, jumlah, harga 
          FROM pesanan_pelanggan 
          ORDER BY tanggal DESC 
          LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $per_page);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Current date and time for reference
$current_date = date('Y-m-d H:i:s');
?>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Kingland Pesanan Pelanggan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
            z-index: 40;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 50;
            width: 90vw;
            max-width: 80rem;
            background: white;
            padding: 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .modal.active,
        .modal-overlay.active {
            display: block;
        }
    </style>
</head>

<body class="bg-[#f3f3f3] min-h-screen flex">
    <!-- Sidebar -->
    <aside class="bg-[#9B191A] w-64 flex flex-col justify-between text-white fixed top-0 left-0 h-screen z-20">
        <div>
            <div class="px-6 py-8">
                <img alt="Kingland Tire and Tube logo white on red background" class="w-36 h-auto" draggable="false" src="https://kingland.co.id/wp-content/uploads/2022/01/LOGO-KINGLAND-Tire-Tube-white-1.png" />
            </div>
            <nav class="flex flex-col space-y-4 px-6 text-sm font-semibold">
                <a class="flex items-center gap-3 hover:text-white transition-colors" href="dashboard-gudang.php">
                    <i class="fas fa-th-large text-base"></i>
                    Dashboard
                </a>
                <a class="flex items-center gap-3 font-bold hover:text-white transition-colors" href="manajemen-stok-gudang.php">
                    <i class="fas fa-cube text-base"></i>
                    Manajemen Stok
                </a>
                <a class="flex items-center gap-3 font-bold hover:text-white transition-colors" href="buat-permintaan-produksi.php">
                    <i class="fas fa-file-alt text-base"></i>
                    Permintaan Produksi
                </a>
                <a class="flex items-center gap-3 font-bold hover:text-white transition-colors" href="BSTB-gudang.php">
                    <i class="fas fa-book text-base"></i>
                    BSTB
                </a>
                <a aria-current="page" class="flex items-center gap-3 font-bold bg-white text-[#9B191A] rounded-full py-3 px-5 mt-6 relative shadow-md" href="pesanan_pelanggan.php">
                    <i class="fas fa-check-square text-base"></i>
                    <span>Pesanan Pelanggan</span>
                    <div class="ml-auto bg-[#9B1919] text-white rounded-full w-8 h-7 flex items-center justify-center text-sm font-bold">
                        <i class="fas fa-arrow-right text-sm"></i>
                    </div>
                </a>
                <a class="flex items-center gap-3 font-bold hover:text-white transition-colors" href="buat-laporan-gudang.php">
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

    <!-- Main content -->
    <div class="flex-1 flex flex-col ml-64">
        <!-- Top bar -->
        <header class="flex items-center justify-between border-b border-gray-300 px-8 py-4 bg-white sticky top-0 z-10">
            <h2 class="font-semibold text-sm text-black">
                Manajemen Pesanan Pelanggan PT. KingLand
            </h2>
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3 cursor-pointer select-none">
                    <img alt="Profile image of a man with black hair wearing white shirt and black jacket" class="w-10 h-10 rounded-full object-cover" draggable="false" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQm6ZdhZ6kaV6TKsbZ45FXeyc1nWBPIHyONlg&s" />
                    <span class="font-semibold text-black text-sm">
                        Jay
                    </span>
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </div>
            </div>
        </header>

        <!-- Content area -->
        <main class="flex-1 p-8 overflow-auto">
            <section class="bg-white rounded-xl p-8 shadow-sm max-w-full overflow-x-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-xl text-black">
                        Daftar Pesanan Pelanggan
                    </h3>
                    <form class="relative w-56">
                        <input class="w-full rounded-lg bg-[#e9e9e9] py-2 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#9B191A]" placeholder="Search" type="search" />
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                    </form>
                </div>
                <table class="w-full text-left text-sm text-gray-700 border-separate border-spacing-y-1">
                    <thead class="bg-[#f9f9f9] text-black font-semibold">
                        <tr>
                            <th class="py-3 px-4 min-w-[90px]">No. Pesanan</th>
                            <th class="py-3 px-4 min-w-[90px]">Tanggal</th>
                            <th class="py-3 px-4 min-w-[140px]">Pelanggan</th>
                            <th class="py-3 px-4 min-w-[110px]">Total</th>
                            <th class="py-3 px-4 min-w-[160px]">Status</th>
                            <th class="py-3 px-4 min-w-[110px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="bg-white rounded-lg shadow-sm">
                                <td class="py-4 px-4 font-mono text-xs text-[#3a3a3a]"><?= htmlspecialchars($row['no_pesanan'] ?? 'N/A') ?></td>
                                <td class="py-4 px-4 font-mono text-xs text-[#3a3a3a]"><?= htmlspecialchars($row['tanggal'] ?? 'N/A') ?></td>
                                <td class="py-4 px-4 text-xs"><?= htmlspecialchars($row['nama_pelanggan'] ?? 'N/A') ?></td>
                                <td class="py-4 px-4 font-mono text-xs text-[#3a3a3a]">Rp<?= number_format($row['subtotal'], 2, ',', '.') ?>,-</td>
                                <td class="py-4 px-4">
                                    <span class="inline-block text-white text-xs font-semibold rounded-full px-4 py-1" style="background-color: <?= $row['status_pesanan'] == 'Dikirim' ? '#34d399' : ($row['status_pesanan'] == 'Menunggu Konfirmasi' ? '#fbbf24' : ($row['status_pesanan'] == 'Sedang Diproses' ? '#3b82f6' : '#d1d5db')) ?>">
                                        <?= htmlspecialchars($row['status_pesanan'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <button class="bg-[#9B191A] text-white text-xs font-semibold rounded-md px-5 py-2 tindak-lanjut-btn" data-id="<?= htmlspecialchars($row['id_pesanan'] ?? '') ?>" data-no-pesanan="<?= htmlspecialchars($row['no_pesanan'] ?? 'N/A') ?>" data-tanggal="<?= htmlspecialchars($row['tanggal'] ?? 'N/A') ?>" data-nama-pelanggan="<?= htmlspecialchars($row['nama_pelanggan'] ?? 'N/A') ?>" data-email="<?= htmlspecialchars($row['email'] ?? 'N/A') ?>" data-telepon="<?= htmlspecialchars($row['telepon'] ?? 'N/A') ?>" data-alamat="<?= htmlspecialchars($row['alamat'] ?? 'N/A') ?>" data-subtotal="<?= htmlspecialchars($row['subtotal'] ?? '0') ?>" data-status-pesanan="<?= htmlspecialchars($row['status_pesanan'] ?? 'N/A') ?>" data-barang-pesanan="<?= htmlspecialchars($row['barang_pesanan'] ?? 'N/A') ?>" data-jumlah="<?= htmlspecialchars($row['jumlah'] ?? '0') ?>" data-harga="<?= htmlspecialchars($row['harga'] ?? '0') ?>">Tindak Lanjut</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="mt-6 flex items-center justify-end gap-2 text-xs font-semibold text-gray-400 select-none">
                    <span class="mr-auto pl-2 text-gray-300 font-normal">Menampilkan <?php echo $offset + 1; ?>–<?php echo min($offset + $result->num_rows, $total_rows); ?> data dari <?php echo $total_rows; ?> </span>
                    <button aria-label="Previous page" class="rounded border border-gray-300 bg-gray-300 px-3 py-1 text-gray-600 cursor-not-allowed" <?= $page <= 1 ? 'disabled' : '' ?> onclick="window.location.href='?page=<?= $page - 1 ?>'">‹</button>
                    <?php for ($i = 1; $i <= min(5, $total_pages); $i++): ?>
                        <button aria-label="Page <?= $i ?>" class="rounded border border-[#9B191A] px-3 py-1 <?= $i == $page ? 'bg-[#9B191A] text-white' : 'bg-white hover:bg-gray-100' ?>" onclick="window.location.href='?page=<?= $i ?>'"><?= $i ?></button>
                    <?php endfor; ?>
                    <?php if ($total_pages > 5): ?>
                        <span class="px-2">...</span>
                        <button aria-label="Page <?= $total_pages ?>" class="rounded border border-[#9B191A] bg-white px-3 py-1 hover:bg-gray-100" onclick="window.location.href='?page=<?= $total_pages ?>'"><?= $total_pages ?></button>
                    <?php endif; ?>
                    <button aria-label="Next page" class="rounded border border-gray-300 bg-gray-300 px-3 py-1 text-gray-600 cursor-not-allowed" <?= $page >= $total_pages ? 'disabled' : '' ?> onclick="window.location.href='?page=<?= $page + 1 ?>'">›</button>
                </div>
            </section>
        </main>

        <!-- Modal Overlay -->
        <div class="modal-overlay" id="modalOverlay"></div>

        <!-- Detail Pesanan Modal -->
        <section class="modal" id="detailModal" aria-labelledby="modal-title" aria-modal="true" role="dialog">
            <header class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-extrabold text-black leading-tight" id="modal-title">Detail Pesanan</h2>
                <button aria-label="Close modal" class="text-black hover:text-gray-600 close-modal" type="button">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </header>
            <div class="flex flex-col md:flex-row md:justify-between gap-8 mb-10">
                <!-- Informasi Pelanggan -->
                <div class="flex-1">
                    <h3 class="text-gray-600 mb-3 font-normal text-lg">Informasi Pelanggan</h3>
                    <div class="bg-white rounded-lg p-5 shadow-md max-w-md text-xs md:text-sm" style="line-height: 1.3">
                        <p class="font-bold text-black mb-0.5" id="modal-nama-pelanggan"></p>
                        <p class="text-black mb-3" id="modal-email"></p>
                        <p class="flex items-center gap-2 mb-1 text-gray-700" id="modal-telepon">
                            <i class="fas fa-phone-alt"></i>
                            <span></span>
                        </p>
                        <p class="flex items-center gap-2 text-gray-700" id="modal-alamat">
                            <i class="fas fa-map-marker-alt"></i>
                            <span></span>
                        </p>
                    </div>
                </div>
                <!-- Informasi Pesanan -->
                <div class="flex-1">
                    <h3 class="text-gray-600 mb-3 font-normal text-lg">Informasi Pesanan</h3>
                    <div class="bg-white rounded-lg p-5 shadow-md max-w-md text-xs md:text-sm grid grid-cols-2 gap-x-8 gap-y-3" style="line-height: 1.3">
                        <div>
                            <p class="text-gray-700 mb-0.5">Tanggal Pesanan</p>
                            <p class="font-bold text-black mb-3" id="modal-tanggal"></p>
                        </div>
                        <div>
                            <p class="text-gray-700 mb-0.5">Status Pesanan</p>
                            <p class="font-bold text-yellow-600" id="modal-status-pesanan"></p>
                        </div>
                        <div>
                            <p class="text-gray-700 mb-0.5">Metode Pembayaran</p>
                            <p class="font-bold text-black mb-3" id="modal-metode-pembayaran">Transfer Bank</p>
                        </div>
                        <div>
                            <p class="text-gray-700 mb-0.5">Status Pembayaran</p>
                            <p class="font-bold text-green-600" id="modal-status-pembayaran">Lunas</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Item Pesanan -->
            <section class="mb-10 max-w-5xl">
                <h3 class="text-gray-600 mb-3 font-normal text-lg">Item Pesanan</h3>
                <table class="w-full text-xs md:text-sm border border-gray-200 shadow-sm" id="modal-items-table">
                    <thead class="bg-gray-50 text-gray-600 font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-left">Produk</th>
                            <th class="py-3 px-4 text-center">Jumlah</th>
                            <th class="py-3 px-4 text-right">Harga</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="modal-items-body">
                        <!-- Dynamic content will be inserted here -->
                    </tbody>
                </table>
            </section>
            <!-- Update Status -->
            <section class="flex flex-col md:flex-row md:justify-between items-start md:items-center max-w-5xl">
                <div class="mb-6 md:mb-0 w-full md:w-auto">
                    <h3 class="text-gray-600 mb-3 font-normal text-lg">Update Status</h3>
                    <select aria-label="Update status dropdown" class="bg-yellow-500 text-black font-semibold rounded-md px-4 py-2 cursor-pointer w-full max-w-xs" id="modal-status-update">
                        <option selected="">Menunggu Konfirmasi</option>
                        <option>Sedang Diproses</option>
                        <option>Dikirim</option>
                        <option>Selesai</option>
                    </select>
                </div>
                <button class="bg-black text-white font-bold rounded px-6 py-3 whitespace-nowrap" type="button" id="modal-kirim-btn">Kirim ke Pelanggan</button>
            </section>
        </section>
    </div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Tindak Lanjut button clicks
        document.querySelectorAll('.tindak-lanjut-btn').forEach(button => {
            button.addEventListener('click', function() {
                const idPesanan = this.getAttribute('data-id');
                const noPesanan = this.getAttribute('data-no-pesanan');
                const tanggal = this.getAttribute('data-tanggal');
                const namaPelanggan = this.getAttribute('data-nama-pelanggan');
                const email = this.getAttribute('data-email');
                const telepon = this.getAttribute('data-telepon');
                const alamat = this.getAttribute('data-alamat');
                const subtotal = this.getAttribute('data-subtotal');
                const statusPesanan = this.getAttribute('data-status-pesanan');
                const barangPesanan = this.getAttribute('data-barang-pesanan');
                const jumlah = this.getAttribute('data-jumlah');
                const harga = this.getAttribute('data-harga');

                // Populate modal fields
                document.getElementById('modal-nama-pelanggan').textContent = namaPelanggan || 'N/A';
                document.getElementById('modal-email').textContent = email || 'N/A';
                document.getElementById('modal-telepon').querySelector('span').textContent = telepon || 'N/A';
                document.getElementById('modal-alamat').querySelector('span').textContent = alamat || 'N/A';
                document.getElementById('modal-tanggal').textContent = tanggal || 'N/A';
                document.getElementById('modal-status-pesanan').textContent = statusPesanan || 'N/A';

                // Populate item table
                const tbody = document.getElementById('modal-items-body');
                tbody.innerHTML = '';
                const row = document.createElement('tr');
                row.className = 'border-t border-gray-200';
                row.innerHTML = `
                <td class="py-3 px-4 font-bold text-center md:text-left">${barangPesanan || 'N/A'}</td>
                <td class="py-3 px-4 text-center">${jumlah || '0'}</td>
                <td class="py-3 px-4 text-right">Rp${parseFloat(harga || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2})},-</td>
                <td class="py-3 px-4 text-right font-semibold">Rp${parseFloat(subtotal || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2})},-</td>
            `;
                tbody.appendChild(row);

                // Show modal
                document.getElementById('modalOverlay').classList.add('active');
                document.getElementById('detailModal').classList.add('active');
            });
        });

        // Handle modal close
        document.querySelectorAll('.close-modal').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('modalOverlay').classList.remove('active');
                document.getElementById('detailModal').classList.remove('active');
            });
        });

        // Close modal when clicking overlay
        document.getElementById('modalOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.getElementById('detailModal').classList.remove('active');
            }
        });

        // Handle Kirim ke Pelanggan button
        document.getElementById('modal-kirim-btn').addEventListener('click', function() {
            const idPesanan = document.querySelector('.tindak-lanjut-btn[data-id]:not([style*="display: none"])')?.getAttribute('data-id');
            const newStatus = document.getElementById('modal-status-update').value;

            if (idPesanan) {
                fetch(`update_status.php?id_pesanan=${idPesanan}&status_pesanan=${encodeURIComponent(newStatus)}`, {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Status updated successfully');
                            document.getElementById('modal-status-pesanan').textContent = newStatus;
                            // Update the table row if visible (optional, requires DOM manipulation)
                            const row = document.querySelector(`.tindak-lanjut-btn[data-id="${idPesanan}"]`).closest('tr');
                            if (row) {
                                const statusSpan = row.querySelector('span');
                                statusSpan.textContent = newStatus;
                                statusSpan.style.backgroundColor = newStatus === 'Dikirim' ? '#34d399' : (newStatus === 'Menunggu Konfirmasi' ? '#fbbf24' : (newStatus === 'Sedang Diproses' ? '#3b82f6' : '#d1d5db'));
                            }
                            // Optionally refresh the page
                            // location.reload();
                        } else {
                            alert('Failed to update status');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating status:', error);
                        alert('Error updating status');
                    });
            }

            document.getElementById('modalOverlay').classList.remove('active');
            document.getElementById('detailModal').classList.remove('active');
        });
    });
</script>

</html>
<?php $conn->close(); ?>