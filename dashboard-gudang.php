<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'rbpl_kingland';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

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

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>
        Warehouse Dashboard
    </title>
    <script src="https://cdn.tailwindcss.com">
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            /* Menggunakan flex untuk layout */
            display: flex;
            min-height: 100vh;
            overflow: hidden;
            /* Mencegah scroll global */
        }

        /* Sidebar styling */
        .sidebar {
            width: 16rem;
            /* w-64 */
            background-color: #9B141A;
            flex-shrink: 0;
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
            /* Scroll internal jika konten panjang */
        }

        /* Main content area */
        .main-content {
            margin-left: 0rem;
            flex: 1;
            overflow-y: auto;
            /* Scroll hanya untuk konten utama */
            height: 100vh;
        }
    </style>
</head>

<body class="bg-[#E6E8E8] min-h-screen flex">
    <!-- Sidebar -->
    <!-- Sidebar -->
    <aside class="sidebar flex flex-col justify-between">
        <div>
            <div class="px-6 py-8">
                <img alt="Kingland Tire and Tube logo white on red background" class="w-36" src="https://kingland.co.id/wp-content/uploads/2022/01/LOGO-KINGLAND-Tire-Tube-white-1.png" />
            </div>
            <nav class="px-6 space-y-3">
                <a class="flex items-center gap-3 bg-white text-[#9B1919] rounded-full px-4 py-3 font-bold shadow-md"" href=" dashboard-gudang.php">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-th-large text-[#9B1919] text-lg"></i>
                        <span>Dashboard</span>
                        <div class="ml-auto bg-[#9B1919] text-white rounded-full w-8 h-7 flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-arrow-right text-sm"></i>
                        </div>
                    </div>
                    <div class="w-8 h-8 rounded-full text-[#9B1919] flex items-center justify-center text-white">
                        <i class="fas fa-arrow-right text-white"></i>
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
                    <h2 class="font-semibold text-black text-base md:text-lg">
                        Dashboard
                    </h2>
                    <p class="text-gray-500 text-xs md:text-sm">
                        Hi, welcome to warehouse management.
                    </p>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="relative cursor-pointer">
                        <i class="fas fa-bell text-2xl text-gray-700">
                        </i>
                        <span class="absolute top-0 right-0 w-3 h-3 bg-red-600 rounded-full border-2 border-white">
                        </span>
                    </div>
                    <div class="flex items-center space-x-2 cursor-pointer">
                        <img alt="Profile image of a man with black hair wearing white shirt and black jacket" class="w-10 h-10 rounded-full object-cover" height="40" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQm6ZdhZ6kaV6TKsbZ45FXeyc1nWBPIHyONlg&s" width="40" />
                        <span class="font-semibold text-black text-sm md:text-base">
                            Jay
                        </span>
                        <i class="fas fa-chevron-down text-gray-600">
                        </i>
                    </div>
                </div>
            </header>
            <!-- Welcome banner and stats -->
            <section class="flex flex-col lg:flex-row lg:space-x-6 space-y-6 lg:space-y-0">
                <!-- Welcome banner -->
                <div class="flex-1 rounded-xl overflow-hidden relative" style="min-height: 160px">
                  
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-[#ede5c9] via-[#c96f6a] to-[#c31a1a] opacity-90">
                    </div>
                    <div class="relative z-10 p-6 md:p-10 flex flex-col justify-center h-full">
                        <p class="text-black text-sm mb-1">
                            Hi, Jay!
                        </p>
                        <h3 class="text-black font-extrabold text-2xl md:text-3xl mb-1 leading-tight">
                            Welcome to Warehouse
                        </h3>
                        <p class="text-black text-xs md:text-sm max-w-xs">
                            All warehouse tasks will appear here, always monitor the tasks.
                        </p>
                    </div>
                </div>
                <!-- Stats cards -->
                <div class="grid grid-cols-2 gap-4 w-full max-w-sm">
                    <div class="bg-white rounded-xl p-4 flex items-center space-x-3 shadow-md border-2 border-gray-100" style="min-width: 140px">
                        <i class="fas fa-boxes text-blue-500 text-2xl"></i> <!-- Icon Font Awesome -->
                        <div>
                            <p class="text-xs text-gray-600">
                                Total Stok
                            </p>
                            <p class="font-bold text-sm">
                                1,248
                            </p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 flex items-center space-x-3 shadow-sm" style="min-width: 140px">
                        <i class="fas fa-arrow-down text-green-500 text-2xl"></i>
                        <div>
                            <p class="text-xs text-gray-600">
                                Barang Masuk
                            </p>
                            <p class="font-bold text-sm">
                                Hari Ini 87
                            </p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 flex items-center space-x-3 shadow-sm" style="min-width: 140px">
                        <i class="fas fa-arrow-up text-red-500 text-2xl"></i>
                        <div>
                            <p class="text-xs text-gray-600">
                                Barang Keluar
                            </p>
                            <p class="font-bold text-sm">
                                Hari Ini 64
                            </p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 flex items-center space-x-3 shadow-sm" style="min-width: 140px">
                        <i class="fas fa-exclamation text-yellow-500 text-2xl"></i>
                        <div>
                            <p class="text-xs text-gray-600">
                                Permintaan Pending
                            </p>
                            <p class="font-bold text-sm">
                                23
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Chart and notifications -->
            <section class="flex flex-col lg:flex-row lg:space-x-6 space-y-6 lg:space-y-0">
                <!-- Chart container -->
                <div class="bg-white rounded-xl p-6 flex-1 max-w-4xl" style="min-height: 280px">
                    <h4 class="font-semibold text-black mb-4 text-sm md:text-base">
                        Statistik Pengelolaan Barang
                    </h4>
                    <div class="overflow-x-auto">
                        <svg aria-label="Line chart showing Barang Masuk and Barang Keluar statistics over days of the week" class="w-full h-72" role="img" viewbox="0 0 700 300">
                            <style>
                                .axis {
                                    stroke: #ccc;
                                    stroke-width: 1;
                                }

                                .line-masuk {
                                    fill: none;
                                    stroke: #f97316;
                                    stroke-width: 2;
                                }

                                .line-keluar {
                                    fill: none;
                                    stroke: #3b82f6;
                                    stroke-width: 2;
                                }

                                .circle-masuk {
                                    fill: #f97316;
                                    stroke: #f97316;
                                }

                                .circle-keluar {
                                    fill: #3b82f6;
                                    stroke: #3b82f6;
                                }

                                .legend-text {
                                    font-size: 12px;
                                    font-family: Inter, sans-serif;
                                    fill: #333;
                                }
                            </style>
                            <!-- Y axis lines -->
                            <line class="axis" x1="50" x2="50" y1="20" y2="260">
                            </line>
                            <line class="axis" x1="50" x2="650" y1="260" y2="260">
                            </line>
                            <line class="axis" x1="50" x2="650" y1="200" y2="200">
                            </line>
                            <line class="axis" x1="50" x2="650" y1="140" y2="140">
                            </line>
                            <line class="axis" x1="50" x2="650" y1="80" y2="80">
                            </line>
                            <line class="axis" x1="50" x2="650" y1="20" y2="20">
                            </line>
                            <!-- Y axis labels -->
                            <text fill="#666" font-size="10" x="30" y="265">
                                0
                            </text>
                            <text fill="#666" font-size="10" x="20" y="205">
                                300
                            </text>
                            <text fill="#666" font-size="10" x="20" y="145">
                                600
                            </text>
                            <text fill="#666" font-size="10" x="20" y="85">
                                900
                            </text>
                            <text fill="#666" font-size="10" x="20" y="25">
                                1500
                            </text>
                            <!-- X axis labels -->
                            <text fill="#333" font-size="12" x="90" y="280">
                                Senin
                            </text>
                            <text fill="#333" font-size="12" x="180" y="280">
                                Selasa
                            </text>
                            <text fill="#333" font-size="12" x="270" y="280">
                                Rabu
                            </text>
                            <text fill="#333" font-size="12" x="360" y="280">
                                Kamis
                            </text>
                            <text fill="#333" font-size="12" x="450" y="280">
                                Jumat
                            </text>
                            <text fill="#333" font-size="12" x="540" y="280">
                                Sabtu
                            </text>
                            <text fill="#333" font-size="12" x="630" y="280">
                                Minggu
                            </text>
                            <!-- Lines Barang Masuk -->
                            <polyline class="line-masuk" points="90,230 180,100 270,140 360,200 450,210 540,130 630,180">
                            </polyline>
                            <circle class="circle-masuk" cx="90" cy="230" r="5">
                            </circle>
                            <circle class="circle-masuk" cx="180" cy="100" r="5">
                            </circle>
                            <circle class="circle-masuk" cx="270" cy="140" r="5">
                            </circle>
                            <circle class="circle-masuk" cx="360" cy="200" r="5">
                            </circle>
                            <circle class="circle-masuk" cx="450" cy="210" r="5">
                            </circle>
                            <circle class="circle-masuk" cx="540" cy="130" r="5">
                            </circle>
                            <circle class="circle-masuk" cx="630" cy="180" r="5">
                            </circle>
                            <!-- Lines Barang Keluar -->
                            <polyline class="line-keluar" points="90,180 180,140 270,230 360,80 450,160 540,210 630,140">
                            </polyline>
                            <circle class="circle-keluar" cx="90" cy="180" r="5">
                            </circle>
                            <circle class="circle-keluar" cx="180" cy="140" r="5">
                            </circle>
                            <circle class="circle-keluar" cx="270" cy="230" r="5">
                            </circle>
                            <circle class="circle-keluar" cx="360" cy="80" r="5">
                            </circle>
                            <circle class="circle-keluar" cx="450" cy="160" r="5">
                            </circle>
                            <circle class="circle-keluar" cx="540" cy="210" r="5">
                            </circle>
                            <circle class="circle-keluar" cx="630" cy="140" r="5">
                            </circle>
                            <!-- Legend -->
                            <circle class="circle-masuk" cx="520" cy="40" r="6">
                            </circle>
                            <text class="legend-text" x="535" y="45">
                                Barang Masuk
                            </text>
                            <circle class="circle-keluar" cx="620" cy="40" r="6">
                            </circle>
                            <text class="legend-text" x="635" y="45">
                                Barang Keluar
                            </text>
                        </svg>
                    </div>
                </div>
                <!-- Notifications -->
                <aside class="bg-white rounded-xl p-4 w-full max-w-xs flex flex-col space-y-2" style="min-height: 280px">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-bold text-black text-sm md:text-base">
                            Notifikasi
                        </h4>
                        <a class="text-xs text-blue-600 hover:underline" href="#">
                            Tandai semua dibaca
                        </a>
                    </div>
                    <div class="bg-blue-200 rounded-md p-3 flex space-x-3 items-start cursor-pointer">
                        <div class="p-2 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-shopping-cart text-blue-500 text-1xl"></i>
                        </div>
                        <div class="text-xs md:text-sm">
                            <p class="font-bold text-black leading-tight">
                                Pesanan Pelanggan Baru
                            </p>
                            <p class="text-gray-700 leading-tight">
                                Nishimura Riki membuat pesanan baru #ORD-007.
                            </p>
                            <p class="text-gray-700 leading-tight">
                                10 menit yang lalu
                            </p>
                        </div>
                    </div>
                    <div class="border-t border-gray-300 pt-3 flex space-x-3 items-start cursor-pointer">
                    <i class="fas fa-truck text-purple-500 text-1xl"></i>
                        <div class="text-xs md:text-sm">
                            <p class="font-bold text-black leading-tight">
                                Barang Siap Dikirim
                            </p>
                            <p class="text-gray-700 leading-tight">
                                Pesanan #ORD-003 siap untuk dikirim.
                            </p>
                            <p class="text-gray-700 leading-tight">
                                42 menit yang lalu
                            </p>
                        </div>
                    </div>
                    <div class="border-t border-gray-300 pt-3 flex space-x-3 items-start cursor-pointer">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-1xl"></i>
                        <div class="text-xs md:text-sm">
                            <p class="font-bold text-black leading-tight">
                                Stok Menipis
                            </p>
                            <p class="text-gray-700 leading-tight">
                                Ban King Wolf tersisa 5 unit.
                            </p>
                            <p class="text-gray-700 leading-tight">
                                1 jam yang lalu
                            </p>
                        </div>
                    </div>
                </aside>
            </section>
            <!-- Table -->
            <section class="bg-white rounded-xl p-6 max-w-6xl w-full">
                <h4 class="font-normal text-black text-sm md:text-base mb-4">
                    Daftar Pesanan Pelanggan Terbaru
                </h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs md:text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-3 px-4 font-semibold text-black">
                                    ID Pesanan
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    Tanggal
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    Pelanggan
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    Total
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    Status
                                </th>
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
                                    if ($row['status_pesanan'] == 'Dikirim') {
                                        echo "<button class='bg-green-500 text-white rounded-full px-5 py-1 text-xs md:text-sm'>Dikirim</button>";
                                    } elseif ($row['status_pesanan'] == 'Menunggu Konfirmasi' || $row['status_pesanan'] == 'Sedang Diproses') {
                                        echo "<button class='bg-yellow-400 text-white rounded-full px-5 py-1 text-xs md:text-sm'>" . htmlspecialchars($row['status_pesanan']) . "</button>";
                                    } else {
                                        echo "<button class='bg-gray-300 text-white rounded-full px-5 py-1 text-xs md:text-sm'>N/A</button>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='py-3 px-4 text-center'>No orders available</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</body>

</html>

<?php $conn->close(); ?>