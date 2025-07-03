<?php
require_once 'functions.php';

// Ambil semua data praktikum
$query = "SELECT * FROM praktikum ORDER BY semester, nama";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Mata Praktikum - STUDYKU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
      body { font-family: 'Inter', sans-serif; }
      .glass {
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(8px);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
      }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-100 via-indigo-100 to-cyan-100 min-h-screen">
    <!-- Header -->
    <header class="bg-gradient-to-r from-indigo-600 to-blue-500 shadow-lg rounded-b-3xl mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center space-x-4">
                <img src="https://img.icons8.com/color/48/000000/classroom.png" class="h-12 w-12" alt="Logo"/>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-wide drop-shadow-lg">Katalog Mata Praktikum</h1>
            </div>
            <div class="flex items-center space-x-4 mt-4 md:mt-0">
                <?php if (isLoggedIn()): ?>
                    <?php if (isMahasiswa()): ?>
                        <a href="mahasiswa/dashboard.php" class="text-white font-semibold hover:underline">Dashboard Mahasiswa</a>
                    <?php elseif (isAsisten()): ?>
                        <a href="asisten/dashboard.php" class="text-white font-semibold hover:underline">Dashboard Asisten</a>
                    <?php endif; ?>
                    <a href="logout.php" class="bg-white/20 hover:bg-white/40 text-white font-bold px-4 py-2 rounded-lg shadow transition">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="text-white font-semibold hover:underline">Login</a>
                    <a href="register.php" class="bg-white/20 hover:bg-white/40 text-white font-bold px-4 py-2 rounded-lg shadow transition">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php echo showMessage(); ?>
        
        <!-- Description -->
        <div class="mb-10 text-center">
            <p class="text-gray-700 text-lg md:text-xl font-medium">
                Temukan dan daftar ke mata praktikum yang tersedia di bawah ini. <br class="hidden md:block">Klik "Daftar" untuk mulai belajar!
            </p>
        </div>

        <!-- Praktikum Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($praktikum = $result->fetch_assoc()): ?>
                    <div class="glass rounded-2xl shadow-xl border border-blue-100 hover:shadow-2xl transition-all duration-300 p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold bg-blue-100 text-blue-700 px-3 py-1 rounded-full shadow"> <?php echo htmlspecialchars($praktikum['kode']); ?> </span>
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-semibold shadow"> <?php echo $praktikum['sks']; ?> SKS </span>
                            </div>
                            <h3 class="text-xl font-extrabold text-gray-900 mb-2 flex items-center gap-2">
                                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0 0H6m6 0h6" /></svg>
                                <?php echo htmlspecialchars($praktikum['nama']); ?>
                            </h3>
                            <p class="text-gray-600 mb-4 line-clamp-3 min-h-[60px]">
                                <?php echo htmlspecialchars($praktikum['deskripsi']); ?>
                            </p>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <span class="text-sm text-gray-500 flex items-center gap-1">
                                <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" /></svg>
                                Semester <?php echo $praktikum['semester']; ?>
                            </span>
                            <div class="flex gap-2">
                                <?php if (isLoggedIn() && isMahasiswa()): ?>
                                    <?php if (isMahasiswaTerdaftar($_SESSION['user_id'], $praktikum['id'])): ?>
                                        <span class="text-green-600 text-sm font-bold flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Sudah Terdaftar</span>
                                        <a href="mahasiswa/praktikum_detail.php?id=<?php echo $praktikum['id']; ?>" 
                                           class="bg-gradient-to-r from-green-500 to-green-400 text-white px-4 py-2 rounded-lg shadow hover:from-green-600 hover:to-green-500 transition text-sm font-bold">
                                            Lihat Detail
                                        </a>
                                    <?php else: ?>
                                        <form method="POST" action="daftar_praktikum.php" class="inline">
                                            <input type="hidden" name="praktikum_id" value="<?php echo $praktikum['id']; ?>">
                                            <button type="submit" 
                                                    class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-4 py-2 rounded-lg shadow hover:from-blue-600 hover:to-cyan-500 transition text-sm font-bold"
                                                    onclick="return confirm('Apakah Anda yakin ingin mendaftar ke praktikum ini?')">
                                                Daftar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php elseif (isLoggedIn() && isAsisten()): ?>
                                    <a href="asisten/praktikum_detail.php?id=<?php echo $praktikum['id']; ?>" 
                                       class="bg-gradient-to-r from-indigo-500 to-blue-500 text-white px-4 py-2 rounded-lg shadow hover:from-indigo-600 hover:to-blue-600 transition text-sm font-bold">
                                        Kelola Praktikum
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-500 text-sm">Login untuk mendaftar</span>
                                    <a href="login.php" class="bg-gradient-to-r from-gray-500 to-gray-700 text-white px-4 py-2 rounded-lg shadow hover:from-gray-600 hover:to-gray-800 transition text-sm font-bold">
                                        Login
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada praktikum</h3>
                        <p class="mt-1 text-sm text-gray-500">Belum ada praktikum yang tersedia saat ini.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-indigo-600 to-blue-500 border-t border-blue-200 mt-12 rounded-t-3xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-white text-sm font-semibold tracking-wide">
                &copy; 2024 STUDYKU. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html> 