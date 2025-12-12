<?php
// Include database connection (assuming koneksi.php sets $connect)
include 'koneksi.php';

// Fallback connection if koneksi.php is not available
if (!isset($connect) || !$connect) {
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "rbpl_kingland";
    $connect = new mysqli($hostname, $username, $password, $database);
    if ($connect->connect_error) {
        die("Connection failed: " . $connect->connect_error);
    }
}

// Fetch latest 3 reports
$latestReportsQuery = "SELECT no_laporan, tanggal_laporan, periode, status_laporan FROM laporan_gudang ORDER BY tanggal_laporan DESC LIMIT 3";
$latestReportsResult = $connect->query($latestReportsQuery);

// Fetch counts for Laporan Diterima and Disetujui (to update right cards, though hardcoded in this version)
$diterimaQuery = "SELECT COUNT(*) as count FROM laporan_gudang WHERE status_laporan = 'Diterima'";
$disetujuiQuery = "SELECT COUNT(*) as count FROM laporan_gudang WHERE status_laporan = 'Disetujui'";
$diterimaResult = $connect->query($diterimaQuery);
$disetujuiResult = $connect->query($disetujuiQuery);
$diterimaCount = $diterimaResult->fetch_assoc()['count'] ?? 0;
$disetujuiCount = $disetujuiResult->fetch_assoc()['count'] ?? 0;
?>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Top Management Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<link href="universal-styles.css" rel="stylesheet" />

<body class="bg-[#ededed] min-h-screen flex flex-col">
    <div class="flex flex-1 min-h-0">

        <!-- Sidebar -->
        <aside class="bg-[#A9161A] w-64 flex flex-col justify-between">
            <div>
                <div class="px-6 py-6 border-b border-[#7A1A1E]">
                    <img alt="" class="w-40" height="70" src="logoputih.png" width="170" />
                </div>
                <nav class="mt-6 space-y-2 px-4">
                    <a class="flex items-center justify-between bg-white rounded-full py-3 px-5 text-black font-semibold shadow-md" href="dashboard-manajemen-puncak.php">
                        <div class="flex items-center space-x-5">
                            <i class="fas fa-th-large text-lg"></i>
                            <span>Dashboard</span>
                        </div>
                        <div class="bg-[#9c171b] rounded-full w-8 h-8 flex items-center justify-center text-white text-lg">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    <a class="flex items-center space-x-3 text-white font-semibold text-lg mt-6" href="hal-laporan-manajemen-puncak.php">
                        <i class="fas fa-file-alt text-white"></i>
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
        <main class="flex-1 flex flex-col overflow-auto p-6">
            <!-- Header -->
            <header class="flex items-center justify-end space-x-6 border-b border-gray-200 pb-4 mb-4">
                <div class="flex items-center space-x-3 cursor-pointer select-none">
                    <img alt="Profile photo of Sunjae, a young man with black hair wearing a black suit and turtleneck" class="w-10 h-10 rounded-full object-cover" height="40" src="https://cdn1-production-images-kly.akamaized.net/hj8n5c1x96Th98FDqPDGfmHRRb8=/800x1066/smart/filters:quality(75):strip_icc():format(webp)/kly-media-production/medias/5070762/original/000701600_1735526608-Snapinsta.app_471941000_909794658009262_3513192486160537358_n_1080.jpg" width="40" />
                    <span class="font-semibold text-gray-900">Sunjae</span>
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </div>
            </header>

            <!-- Dashboard Title -->
            <section class="mb-6 max-w-5xl">
                <h2 class="font-semibold text-black text-base mb-1">Dashboard</h2>
                <p class="text-gray-500 text-sm">Hi, welcome to manager management.</p>
                <hr class="mt-4 border-gray-200" />
            </section>

            <!-- Welcome Banner and Right Cards -->
            <section class="flex flex-col lg:flex-row lg:space-x-10 w-full container mx-auto mb-6">
                <!-- Welcome Banner -->
                <section class="w-full max-w-10xl h-80 rounded-lg flex bg-gradient-to-r from-[#E6D9C6] via-[#D94B4B] to-[#A9161A] text-black">
                    <img alt="Profile" class="w-1/4 object-cover hidden md:block" height="80" src="https://cdn1-production-images-kly.akamaized.net/hj8n5c1x96Th98FDqPDGfmHRRb8=/800x1066/smart/filters:quality(75):strip_icc():format(webp)/kly-media-production/medias/5070762/original/000701600_1735526608-Snapinsta.app_471941000_909794658009262_3513192486160537358_n_1080.jpg" />
                    <div class="ml-4 flex flex-col justify-center">
                        <p class="text-xs mb-1">Hi, Sunjae!</p>
                        <h3 class="font-extrabold text-2xl mb-1">Welcome to Production</h3>
                        <p class="text-xs max-w-xs">All production tasks will appear here, always monitor the tasks.</p>
                    </div>
                </section>

                <!-- Right Cards -->
                <div class="flex flex-col space-y-4 mt-6 lg:mt-0 w-full lg:w-72">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center space-x-1 bg-white rounded-xl p-3 shadow-sm">
                            <i class="fas fa-clipboard-check text-[#00a651] w-8 h-8 text-2xl flex-shrink-0"></i>
                            <div>
                                <p class="text-xs text-gray-600">Laporan Diterima</p>
                                <p class="font-semibold text-sm text-black"><?php echo $diterimaCount; ?></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-1 bg-white rounded-xl p-3 shadow-sm">
                            <i class="fas fa-clipboard-check text-blue-500 w-8 h-8 text-2xl flex-shrink-0"></i>
                            <div>
                                <p class="text-xs text-gray-600">Laporan Disetujui</p>
                                <p class="font-semibold text-sm text-black"><?php echo $disetujuiCount; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="bg-white rounded-xl p-2 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-semibold text-black text-sm">Notifikasi</h4>
                            <button class="text-xs text-blue-600 hover:underline">Tandai semua dibaca</button>
                        </div>
                        <ul class="divide-y divide-gray-200 text-xs text-gray-800">
                            <li class="flex space-x-3 py-3">
                                <i class="fas fa-file-alt text-[#00a651] w-8 h-8 text-2xl flex-shrink-0"></i>
                                <div>
                                    <p class="font-semibold text-black text-[13px] leading-tight">Laporan Baru</p>
                                    <p class="leading-tight">Laporan baru periode harian diterima.<br />14 menit yang lalu</p>
                                </div>
                            </li>
                            <li class="flex space-x-3 py-3">
                                <i class="fas fa-file-signature text-blue-500 w-8 h-8 text-2xl flex-shrink-0"></i>
                                <div>
                                    <p class="font-semibold text-black text-[13px] leading-tight">2 Laporan Menunggu Disetujui</p>
                                    <p class="leading-tight">42 menit yang lalu</p>
                                </div>
                            </li>
                            <li class="flex space-x-3 py-3">
                                <i class="fas fa-file-export text-purple-700 w-8 h-8 text-2xl flex-shrink-0"></i>
                                <div>
                                    <p class="font-semibold text-black text-[13px] leading-tight">1 Laporan Berhasil Diekspor</p>
                                    <p class="leading-tight">#RPT-050225-002 Berhasil Diunduh<br />1 jam yang lalu</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Latest Reports Table -->
            <section class="max-w-7xl">
                <div aria-label="Latest reports table" class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="flex justify-between items-center px-6 py-3 border-b border-gray-200">
                        <h3 class="font-semibold text-black text-base">Laporan Terbaru</h3>
                        <a class="text-blue-600 text-sm hover:underline font-normal" href="hal-laporan-manajemen-puncak.php">Lihat Semua</a>
                    </div>
                    <table class="w-full text-sm text-left text-gray-900">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold" scope="col">No. Laporan</th>
                                <th class="px-6 py-3 font-semibold" scope="col">Tanggal</th>
                                <th class="px-6 py-3 font-semibold" scope="col">Periode</th>
                                <th class="px-6 py-3 font-semibold" scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $latestReportsResult->fetch_assoc()): ?>
                                <tr class="border-b border-gray-200">
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['no_laporan']); ?></td>
                                    <td class="px-6 py-4"><?php echo date('d/m/Y', strtotime($row['tanggal_laporan'])); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['periode']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="bg-<?php echo $row['status_laporan'] == 'Disetujui' ? 'blue' : 'green'; ?>-500 text-white text-xs font-semibold px-4 py-1 rounded-full">
                                            <?php echo $row['status_laporan'] == 'Disetujui' ? 'Disetujui' : 'Terkirim'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>

</html>
<?php
$connect->close();
?>