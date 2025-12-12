<?php
session_start();
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'rbpl_kingland';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fetch statistics
$total_stok_query = "SELECT SUM(jumlah_stok) as total FROM stok";
$total_stok_result = $conn->query($total_stok_query);
$total_stok = $total_stok_result->fetch_assoc()['total'] ?? 0;

// Barang masuk hari ini (dari BSTB yang selesai hari ini)
$barang_masuk_query = "SELECT COUNT(*) as total FROM detail_permintaan_produksi WHERE status_bstb = 'Selesai' AND DATE(created_at) = CURDATE()";
$barang_masuk_result = $conn->query($barang_masuk_query);
$barang_masuk = $barang_masuk_result->fetch_assoc()['total'] ?? 0;

// Barang keluar hari ini (dari pesanan yang dikirim hari ini)
$barang_keluar_query = "SELECT SUM(jumlah) as total FROM pesanan_pelanggan WHERE status_pesanan = 'Dikirim' AND DATE(tanggal) = CURDATE()";
$barang_keluar_result = $conn->query($barang_keluar_query);
$barang_keluar = $barang_keluar_result->fetch_assoc()['total'] ?? 0;

// Permintaan pending
$pending_query = "SELECT COUNT(*) as total FROM permintaan_produksi WHERE status = 'Menunggu Konfirmasi'";
$pending_result = $conn->query($pending_query);
$permintaan_pending = $pending_result->fetch_assoc()['total'] ?? 0;

// Fetch the 3 most recent orders
$query = "SELECT no_pesanan, tanggal, nama_pelanggan, subtotal, status_pesanan 
          FROM pesanan_pelanggan 
          ORDER BY tanggal DESC 
          LIMIT 3";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Warehouse Dashboard</title>
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
        }

        .main-content {
            margin-left: 0rem;
            flex: 1;
            overflow-y: auto;
            height: 100vh;
        }

        .notification-badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>

<body class="bg-[#E6E8E8] min-h-screen flex">
    <!-- Sidebar -->
    <aside class="sidebar flex flex-col justify-between">
        <div>
            <div class="px-6 py-8">
                <img alt="Kingland Tire and Tube logo" class="w-36" src="https://kingland.co.id/wp-content/uploads/2022/01/LOGO-KINGLAND-Tire-Tube-white-1.png" />
            </div>
            <nav class="px-6 space-y-3">
                <a class="flex items-center gap-3 bg-white text-[#9B1919] rounded-full px-4 py-3 font-bold shadow-md" href="dashboard-gudang.php">
                    <i class="fas fa-th-large text-[#9B1919] text-lg"></i>
                    <span>Dashboard</span>
                    <div class="ml-auto bg-[#9B1919] text-white rounded-full w-8 h-7 flex items-center justify-center">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                <a class="flex items-center space-x-3 text-white font-bold text-sm" href="manajemen-stok-gudang.php">
                    <i class="fas fa-box-open text-white text-base"></i>
                    <span>Manajemen Stok</span>
                </a>
                <a class="flex items-center space-x-3 text-white font-bold text-sm" href="buat-permintaan-produksi.php">
                    <i class="fas fa-file-alt text-white text-base"></i>
                    <span>Permintaan Produksi</span>
                </a>
                <a class="flex items-center space-x-3 text-white font-bold text-sm" href="BSTB-gudang.php">
                    <i class="fas fa-file-contract text-white text-base"></i>
                    <span>BSTB</span>
                </a>
                <a class="flex items-center space-x-3 text-white font-bold text-sm" href="pesanan_pelanggan.php">
                    <i class="fas fa-clipboard-check text-white text-base"></i>
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
                <i class="fas fa-sign-out-alt text-xl"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">
        <div class="flex-1 p-6 md:p-10 flex flex-col space-y-6">
            <!-- Header -->
            <header class="flex justify-between items-center border-b border-gray-300 pb-3 mb-3">
                <div>
                    <h2 class="font-semibold text-black text-base md:text-lg">Dashboard</h2>
                    <p class="text-gray-500 text-xs md:text-sm">Hi, welcome to warehouse management.</p>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="relative cursor-pointer" id="notificationBell">
                        <i class="fas fa-bell text-2xl text-gray-700"></i>
                        <span class="notification-badge absolute top-0 right-0 w-3 h-3 bg-red-600 rounded-full border-2 border-white hidden" id="notificationBadge"></span>
                    </div>
                    <div class="flex items-center space-x-2 cursor-pointer">
                        <img alt="Profile" class="w-10 h-10 rounded-full object-cover" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQm6ZdhZ6kaV6TKsbZ45FXeyc1nWBPIHyONlg&s" />
                        <span class="font-semibold text-black text-sm md:text-base">Jay</span>
                        <i class="fas fa-chevron-down text-gray-600"></i>
                    </div>
                </div>
            </header>

            <!-- Notification Panel (Hidden by default) -->
            <div id="notificationPanel" class="hidden absolute right-10 top-20 bg-white rounded-xl shadow-2xl p-4 w-96 z-50">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-bold text-black text-sm">Notifikasi</h4>
                    <button class="text-xs text-blue-600 hover:underline" onclick="markAllRead()">Tandai semua dibaca</button>
                </div>
                <div id="notificationList" class="space-y-2 max-h-96 overflow-y-auto">
                    <!-- Notifications will be loaded here -->
                </div>
            </div>

            <!-- Welcome banner and stats -->
            <section class="flex flex-col lg:flex-row lg:space-x-6 space-y-6 lg:space-y-0">
                <!-- Welcome banner -->
                <div class="flex-1 rounded-xl overflow-hidden relative" style="min-height: 160px">
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-[#ede5c9] via-[#c96f6a] to-[#c31a1a] opacity-90"></div>
                    <div class="relative z-10 p-6 md:p-10 flex flex-col justify-center h-full">
                        <p class="text-black text-sm mb-1">Hi, Jay!</p>
                        <h3 class="text-black font-extrabold text-2xl md:text-3xl mb-1 leading-tight">Welcome to Warehouse</h3>
                        <p class="text-black text-xs md:text-sm max-w-xs">All warehouse tasks will appear here, always monitor the tasks.</p>
                    </div>
                </div>

                <!-- Stats cards -->
                <div class="grid grid-cols-2 gap-4 w-full max-w-sm">
                    <div class="bg-white rounded-xl p-4 flex items-center space-x-3 shadow-md border-2 border-gray-100">
                        <i class="fas fa-boxes text-blue-500 text-2xl"></i>
                        <div>
                            <p class="text-xs text-gray-600">Total Stok</p>
                            <p class="font-bold text-sm"><?= number_format($total_stok) ?></p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 flex items-center space-x-3 shadow-sm">
                        <i class="fas fa-arrow-down text-green-500 text-2xl"></i>
                        <div>
                            <p class="text-xs text-gray-600">Barang Masuk</p>
                            <p class="font-bold text-sm">Hari Ini <?= $barang_masuk ?></p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 flex items-center space-x-3 shadow-sm">
                        <i class="fas fa-arrow-up text-red-500 text-2xl"></i>
                        <div>
                            <p class="text-xs text-gray-600">Barang Keluar</p>
                            <p class="font-bold text-sm">Hari Ini <?= $barang_keluar ?></p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 flex items-center space-x-3 shadow-sm">
                        <i class="fas fa-exclamation text-yellow-500 text-2xl"></i>
                        <div>
                            <p class="text-xs text-gray-600">Permintaan Pending</p>
                            <p class="font-bold text-sm"><?= $permintaan_pending ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Chart and notifications -->
            <section class="flex flex-col lg:flex-row lg:space-x-6 space-y-6 lg:space-y-0">
                <!-- Chart container (existing) -->
                <div class="bg-white rounded-xl p-6 flex-1 max-w-4xl" style="min-height: 280px">
                    <h4 class="font-semibold text-black mb-4 text-sm md:text-base">Statistik Pengelolaan Barang</h4>
                    <!-- Chart SVG here (keep existing) -->
                </div>

                <!-- Notifications widget -->
                <aside class="bg-white rounded-xl p-4 w-full max-w-xs flex flex-col space-y-2" style="min-height: 280px">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-bold text-black text-sm md:text-base">Notifikasi</h4>
                        <a class="text-xs text-blue-600 hover:underline" href="#" onclick="markAllRead(); return false;">Tandai semua dibaca</a>
                    </div>
                    <div id="dashboardNotifications" class="space-y-2">
                        <!-- Will be populated by JS -->
                    </div>
                </aside>
            </section>

            <!-- Table (keep existing) -->
            <section class="bg-white rounded-xl p-6 max-w-6xl w-full">
                <h4 class="font-normal text-black text-sm md:text-base mb-4">Daftar Pesanan Pelanggan Terbaru</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs md:text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-3 px-4 font-semibold text-black">ID Pesanan</th>
                                <th class="py-3 px-4 font-semibold text-black">Tanggal</th>
                                <th class="py-3 px-4 font-semibold text-black">Pelanggan</th>
                                <th class="py-3 px-4 font-semibold text-black">Total</th>
                                <th class="py-3 px-4 font-semibold text-black">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr class='border-b border-gray-200'>";
                                    echo "<td class='py-3 px-4'>" . htmlspecialchars($row['no_pesanan']) . "</td>";
                                    echo "<td class='py-3 px-4'>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                                    echo "<td class='py-3 px-4'>" . htmlspecialchars($row['nama_pelanggan']) . "</td>";
                                    echo "<td class='py-3 px-4'>Rp" . number_format($row['subtotal'], 0, ',', '.') . ",-</td>";
                                    echo "<td class='py-3 px-4'>";
                                    $bg_color = $row['status_pesanan'] == 'Dikirim' ? 'bg-green-500' : ($row['status_pesanan'] == 'Menunggu Konfirmasi' ? 'bg-yellow-400' : 'bg-blue-500');
                                    echo "<button class='" . $bg_color . " text-white rounded-full px-5 py-1 text-xs md:text-sm'>" . htmlspecialchars($row['status_pesanan']) . "</button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='py-3 px-4 text-center text-gray-500'>No orders available</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <script>
        // Load notifications
        function loadNotifications() {
            fetch('get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    const dashboardList = document.getElementById('dashboardNotifications');
                    const panelList = document.getElementById('notificationList');

                    // Update badge
                    if (data.unread_count > 0) {
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }

                    // Update notification lists
                    if (data.notifications && data.notifications.length > 0) {
                        let dashboardHTML = '';
                        let panelHTML = '';

                        data.notifications.forEach((notif, index) => {
                            const bgClass = index === 0 && !notif.is_read ? 'bg-blue-50' : '';
                            const html = `
                                <div class="${bgClass} rounded-md p-3 flex space-x-3 items-start cursor-pointer hover:bg-gray-50 transition" onclick="window.location.href='${notif.link}'">
                                    <i class="fas ${notif.icon} text-xl" style="color: ${notif.warna}"></i>
                                    <div class="text-xs md:text-sm flex-1">
                                        <p class="font-bold text-black leading-tight">${notif.judul}</p>
                                        <p class="text-gray-700 leading-tight">${notif.pesan}</p>
                                        <p class="text-gray-500 text-xs mt-1">${notif.waktu}</p>
                                    </div>
                                </div>
                            `;

                            if (index < 3) dashboardHTML += html;
                            panelHTML += html;
                        });

                        dashboardList.innerHTML = dashboardHTML;
                        panelList.innerHTML = panelHTML;
                    }
                })
                .catch(error => console.error('Error loading notifications:', error));
        }

        // Toggle notification panel
        document.getElementById('notificationBell').addEventListener('click', function() {
            const panel = document.getElementById('notificationPanel');
            panel.classList.toggle('hidden');
        });

        // Close notification panel when clicking outside
        document.addEventListener('click', function(event) {
            const panel = document.getElementById('notificationPanel');
            const bell = document.getElementById('notificationBell');

            if (!panel.contains(event.target) && !bell.contains(event.target)) {
                panel.classList.add('hidden');
            }
        });

        // Mark all as read
        function markAllRead() {
            fetch('mark_notifications_read.php', {
                    method: 'POST'
                })
                .then(() => loadNotifications())
                .catch(error => console.error('Error:', error));
        }
        // Load notifications on page load
        loadNotifications();
        // Refresh notifications every 30 seconds
        setInterval(loadNotifications, 30000);
    </script>
</body>
</html>
<?php $conn->close(); ?>