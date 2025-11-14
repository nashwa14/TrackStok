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
$result = $connect->query("SELECT * FROM produksi");
?>

<html>

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>
    Production Dashboard
  </title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      display: flex;
      min-height: 100vh;
      overflow: hidden;
      /* Prevent body scrolling, let main handle it */
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
  </style>
</head>

<body class="bg-[#E6E8E8] min-h-screen flex">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div>
      <div class="px-6 py-8">
        <img alt="Kingland Tire and Tube logo white on red background" class="w-36" src="https://kingland.co.id/wp-content/uploads/2022/01/LOGO-KINGLAND-Tire-Tube-white-1.png" />
      </div>
      <nav class="mt-6 flex flex-col gap-3 px-6">
        <a class="flex items-center justify-between bg-white rounded-full py-2 px-4 font-semibold text-black" href="dashboard-produksi.php">
          <div class="flex items-center gap-2">
            <i class="fas fa-th-large"></i>
            Dashboard
          </div>
          <div class="bg-[#9B1919] rounded-full w-6 h-6 flex items-center justify-center text-white">
            <i class="fas fa-arrow-right"></i>
          </div>
        </a>
        <a class="flex items-center gap-2 text-white font-semibold" href="hal-daftar-permintaan.php">
          <i class="fas fa-box-open">
          </i>
          Permintaan Produksi
        </a>
        <a class="flex items-center gap-2 text-white font-semibold" href="hal-bstb.php"> <!-- connect to halaman-bstb.php -->
          <i class="fas fa-file-alt"> <!-- dari bootstrap -->
          </i>
          BSTB
        </a>

      </nav>
    </div>
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
  <main class="flex-1 px-6 py-8 overflow-y-auto max-w-7xl mx-auto w-full">


    <!-- Header -->
    <header class="flex justify-between items-center border-b border-gray-300 pb-3 mb-4">
      <div>
        <h2 class="font-semibold text-sm text-black">
          Dashboard
        </h2>
        <p class="text-xs text-gray-500">
          Hi, welcome to management production.
        </p>
      </div>
      <!-- foto profil yg pojok kanan atas -->
      <div class="flex items-center gap-6">
        <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-600 rounded-full">
        </span>
        </button>
        <img alt="" class="rounded-full w-10 h-10 object-cover" height="40" src="https://i.pinimg.com/736x/6d/a5/cf/6da5cf7d70417b1ed1e9946ddcd7ea1b.jpg" width="40" />
        <span class="font-semibold text-black text-sm">
          Jake
        </span>
        <i class="fas fa-chevron-down text-gray-600 text-xs">
        </i>
      </div>
    </header>

    <!-- Welcome + Stats in one row -->
    <div class="flex flex-col md:flex-row gap-2 mb-6">
      <!-- Welcome Banner -->
      <section class="rounded-lg flex-1 flex bg-gradient-to-r from-[#E6D9C6] via-[#D94B4B] to-[#A9161A] text-black min-h-[100px]">
        <img
          alt="" class="w-1/4 object-cover hidden md:block" height="50"
          src="https://i.pinimg.com/736x/6d/a5/cf/6da5cf7d70417b1ed1e9946ddcd7ea1b.jpg" />
        <div class="p-4 flex flex-col justify-center">
          <p class="text-xs mb-1">Hi, Jake!</p>
          <h3 class="font-extrabold text-2xl mb-1">Welcome to Production</h3>
          <p class="text-xs max-w-xs">All production tasks will appear here, always monitor the tasks.</p>
        </div>
      </section>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 gap-4 flex-2 mr-2 ml-5">
        <div class="bg-white rounded-lg p-3 flex items-center gap-3 shadow-sm w-40">
          <div class="p-2 rounded-md bg-yellow-100">
            <i class="fas fa-truck-moving text-yellow-500 text-xl"></i>
          </div>
          <div>
            <p class="text-xs text-gray-600">Total Produksi</p>
            <p class="font-semibold text-sm text-black">920<!--<?= $totalProduksi ?> --></p>
          </div>
        </div>
        <div class="bg-white rounded-lg p-3 flex items-center gap-3 shadow-sm w-40">
          <div class="p-2 rounded-md bg-blue-100">
            <i class="fas fa-clipboard-check text-blue-500 text-xl"></i>
          </div>
          <div>
            <p class="text-xs text-gray-600">Selesai</p>
            <p class="font-semibold text-sm text-black">20<!--<?= $selesai ?>--></p>
          </div>
        </div>
        <div class="bg-white rounded-lg p-3 flex items-center gap-3 shadow-sm w-40">
          <div class="p-2 rounded-md bg-purple-100">
            <i class="fas fa-hourglass-half text-purple-500 text-xl"></i>
          </div>
          <div>
            <p class="text-xs text-gray-600">Dalam Proses</p>
            <p class="font-semibold text-sm text-black">50<!--<?= $proses ?>--></p>
          </div>
        </div>
        <div class="bg-white rounded-lg p-3 flex items-center gap-3 shadow-sm w-40">
          <div class="p-2 rounded-md bg-red-100">
            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
          </div>
          <div>
            <p class="text-xs text-gray-600">Pending</p>
            <p class="font-semibold text-sm text-black">0<!--<?= $pending ?>--></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Container untuk chart + notifications -->
    <div class="flex gap-6 mb-6">
      <!-- Production trend chart -->
      <section class="bg-white rounded-lg p-6 flex-1">
        <h4 class="font-semibold text-sm mb-4 text-black">
          Tren Produksi <!-- graph -->
        </h4>
        <svg aria-label="Line chart showing production trend from Monday to Sunday"
          class="w-full h-48" fill="none" role="img" viewBox="0 0 700 200"
          xmlns="http://www.w3.org/2000/svg">
          <line stroke="#ccc" stroke-width="1" x1="50" x2="50" y1="10" y2="180" />
          <line stroke="#ccc" stroke-width="1" x1="50" x2="650" y1="180" y2="180" />
          <text fill="#333" font-size="12" text-anchor="middle" x="50" y="195">Senin</text>
          <text fill="#333" font-size="12" text-anchor="middle" x="130" y="195">Selasa</text>
          <text fill="#333" font-size="12" text-anchor="middle" x="210" y="195">Rabu</text>
          <text fill="#333" font-size="12" text-anchor="middle" x="290" y="195">Kamis</text>
          <text fill="#333" font-size="12" text-anchor="middle" x="370" y="195">Jumat</text>
          <text fill="#333" font-size="12" text-anchor="middle" x="450" y="195">Sabtu</text>
          <text fill="#333" font-size="12" text-anchor="middle" x="530" y="195">Minggu</text>
          <polyline fill="none" points="50,140 130,110 210,160 290,70 370,120 450,40 530,90"
            stroke="#2563EB" stroke-width="3" />
        </svg>
      </section>

      <!-- Notifications -->
      <aside aria-label="Notifications"
        class="bg-white rounded-lg p-4 max-w-xs w-80 text-xs text-gray-700 flex flex-col gap-4 shadow-sm">
        <div class="flex items-start gap-3">
          <i class="fas fa-project-diagram text-yellow-500 text-lg mt-1"></i>
          <div>
            <p class="font-semibold text-black leading-tight">
              Permintaan produksi
              <span class="font-normal">#REQ003</span><br />
              <span class="font-semibold">menunggu dikonfirmasi</span>
            </p>
            <p class="text-gray-500 mt-1">2 menit yang lalu</p>
          </div>
        </div>

        <div class="flex items-start gap-3">
          <i class="fas fa-check-circle text-green-600 text-lg mt-1"></i>
          <div>
            <p class="font-semibold text-black leading-tight">
              BSTB <span class="font-normal">#BSTB002</span> telah dikirim ke gudang
            </p>
            <p class="text-gray-500 mt-1">17 menit yang lalu</p>
          </div>
        </div>

        <div class="flex items-start gap-3">
          <i class="fas fa-exclamation-triangle text-yellow-500 text-lg mt-1"></i>
          <div>
            <p class="font-semibold text-black leading-tight">
              Stok Ban Luar King Shark mendekati batas minimum
            </p>
            <p class="text-gray-500 mt-1">1 jam yang lalu</p>
          </div>
        </div>
      </aside>
    </div>

    <!-- Production request table -->
    <section class="bg-white rounded-lg p-4 max-w-7xl mx-auto w-full">

      <h4 class="font-semibold text-sm mb-4 text-black">
        Daftar Permintaan Produksi Terbaru
      </h4>
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left text-gray-700 border-collapse border border-gray-200">
          <thead class="bg-[#F9FAFB]">
            <tr>
              <th class="py-2 px-3 border border-gray-200 font-semibold">
                ID Permintaan
              </th>
              <th class="py-2 px-3 border border-gray-200 font-semibold">
                Tanggal
              </th>
              <th class="py-2 px-3 border border-gray-200 font-semibold">
                Jenis Barang
              </th>
              <th class="py-2 px-3 border border-gray-200 font-semibold">
                Jumlah
              </th>
              <th class="py-2 px-3 border border-gray-200 font-semibold">
                Status
              </th>
            </tr>
          </thead>
          <tbody>
            <tr class="border border-gray-200">
              <td class="py-2 px-3 border border-gray-200 font-mono font-semibold">
                #REQ001 <!-- connect to production table at kingland database -->
              </td>
              <td class="py-2 px-3 border border-gray-200">
                25/01/2025 <!-- calender -->
              </td>
              <td class="py-2 px-3 border border-gray-200 uppercase font-semibold">
                KING ANACONDA <!-- connect to production table at kingland database -->
              </td>
              <td class="py-2 px-3 border border-gray-200">
                100 <!-- connect to production table at kingland database -->
              </td>
              <td class="py-2 px-3 border border-gray-200">
                <span class="bg-green-500 text-white rounded-full px-3 py-1 text-xs font-semibold inline-block">
                  Selesai <!-- dropdown menu/radibox -->
                </span>
              </td>
            </tr>
            <tr class="border border-gray-200">
              <td class="py-2 px-3 border border-gray-200 font-mono font-semibold">
                #REQ002 <!-- connect to production table at kingland database -->
              </td>
              <td class="py-2 px-3 border border-gray-200">
                27/01/2025 <!-- calender -->
              </td>
              <td class="py-2 px-3 border border-gray-200 uppercase font-semibold">
                KING WOLF <!-- connect to production table at kingland database -->
              </td>
              <td class="py-2 px-3 border border-gray-200">
                200 <!-- connect to production table at kingland database -->
              </td>
              <td class="py-2 px-3 border border-gray-200">
                <span class="bg-green-500 text-white rounded-full px-3 py-1 text-xs font-semibold inline-block">
                  Selesai <!-- dropdown menu/radiobox -->
                </span>
              </td>
            </tr>
            <tr class="border border-gray-200">
              <td class="py-2 px-3 border border-gray-200 font-mono font-semibold">
                #REQ003 <!-- connect to production table at kingland database -->
              </td>
              <td class="py-2 px-3 border border-gray-200">
                28/01/2025 <!-- calender -->
              </td>
              <td class="py-2 px-3 border border-gray-200 uppercase font-semibold">
                KING ALLIGATOR MTX <!-- connect to production table at kingland database -->
              </td>
              <td class="py-2 px-3 border border-gray-200">
                450 <!-- connect to production table at kingland database -->
              </td>
              <td class="py-2 px-3 border border-gray-200">
                <span class="bg-yellow-400 text-black rounded-full px-3 py-1 text-xs font-semibold inline-block">
                  Proses
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>

</html>
<?php $connect->close(); ?>