<?php
require_once '../functions.php';

// Cek login
if (!isLoggedIn() || !isAsisten()) {
    redirect('../login.php', 'Silakan login sebagai asisten terlebih dahulu.', 'error');
}

// Ambil statistik asisten
$userId = $_SESSION['user_id'];

// Total modul yang diajarkan
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM modul");
$stmt->execute();
$modulCount = $stmt->get_result()->fetch_assoc()['total'];

// Total laporan masuk
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM laporan");
$stmt->execute();
$laporanCount = $stmt->get_result()->fetch_assoc()['total'];

// Laporan yang belum dinilai
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM laporan WHERE status = 'menunggu'");
$stmt->execute();
$laporanMenungguCount = $stmt->get_result()->fetch_assoc()['total'];

// Aktivitas laporan terbaru
$stmt = $conn->prepare("SELECT l.*, u.nama as mahasiswa_nama, m.judul as modul_judul, p.nama as praktikum_nama 
                       FROM laporan l 
                       JOIN users u ON l.mahasiswa_id = u.id 
                       JOIN modul m ON l.modul_id = m.id 
                       JOIN praktikum p ON m.praktikum_id = p.id 
                       ORDER BY l.submitted_at DESC LIMIT 5");
$stmt->execute();
$aktivitasTerbaru = $stmt->get_result();

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header.php'; 
?>

<div class="bg-gradient-to-r from-purple-500 to-pink-400 text-white p-8 rounded-3xl shadow-2xl mb-8 flex flex-col md:flex-row items-center justify-between gap-6">
    <div>
        <h1 class="text-3xl md:text-4xl font-extrabold drop-shadow-lg">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
        <p class="mt-2 opacity-90 text-lg font-medium">Kelola praktikum dan nilai laporan mahasiswa dengan mudah 👨‍🏫</p>
    </div>
    <img src="https://img.icons8.com/color/96/000000/teacher.png" class="h-20 w-20 md:h-28 md:w-28 drop-shadow-xl" alt="Asisten"/>
</div>

<?php echo showMessage(); ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
    <div class="glass rounded-2xl shadow-xl p-8 flex flex-col items-center justify-center border border-purple-100">
        <div class="bg-gradient-to-br from-purple-500 to-pink-400 p-4 rounded-full mb-3 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
        </div>
        <div class="text-4xl font-extrabold text-purple-600"><?php echo $modulCount; ?></div>
        <div class="mt-2 text-lg text-gray-700 font-semibold">Total Modul</div>
    </div>
    <div class="glass rounded-2xl shadow-xl p-8 flex flex-col items-center justify-center border border-green-100">
        <div class="bg-gradient-to-br from-green-400 to-green-600 p-4 rounded-full mb-3 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div class="text-4xl font-extrabold text-green-600"><?php echo $laporanCount; ?></div>
        <div class="mt-2 text-lg text-gray-700 font-semibold">Total Laporan</div>
    </div>
    <div class="glass rounded-2xl shadow-xl p-8 flex flex-col items-center justify-center border border-yellow-100">
        <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 p-4 rounded-full mb-3 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div class="text-4xl font-extrabold text-yellow-500"><?php echo $laporanMenungguCount; ?></div>
        <div class="mt-2 text-lg text-gray-700 font-semibold">Menunggu Penilaian</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Quick Actions -->
    <div class="glass rounded-2xl shadow-xl p-8 border border-purple-100">
        <h3 class="text-2xl font-bold text-purple-800 mb-4 flex items-center gap-2"><svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>Aksi Cepat</h3>
        <div class="space-y-3">
            <a href="praktikum.php" class="flex items-center p-3 bg-gradient-to-r from-blue-100 to-cyan-100 rounded-lg hover:from-blue-200 hover:to-cyan-200 transition-colors shadow">
                <span class="text-2xl mr-3">📚</span>
                <div>
                    <div class="font-semibold text-blue-800">Kelola Praktikum</div>
                    <div class="text-sm text-blue-600">Tambah, edit, atau hapus praktikum</div>
                </div>
            </a>
            <a href="modul.php" class="flex items-center p-3 bg-gradient-to-r from-green-100 to-green-200 rounded-lg hover:from-green-200 hover:to-green-300 transition-colors shadow">
                <span class="text-2xl mr-3">📝</span>
                <div>
                    <div class="font-semibold text-green-800">Kelola Modul</div>
                    <div class="text-sm text-green-600">Tambah materi dan modul praktikum</div>
                </div>
            </a>
            <a href="laporan.php" class="flex items-center p-3 bg-gradient-to-r from-yellow-100 to-yellow-200 rounded-lg hover:from-yellow-200 hover:to-yellow-300 transition-colors shadow">
                <span class="text-2xl mr-3">📊</span>
                <div>
                    <div class="font-semibold text-yellow-800">Laporan Masuk</div>
                    <div class="text-sm text-yellow-600">Nilai laporan mahasiswa</div>
                </div>
            </a>
            <a href="akun.php" class="flex items-center p-3 bg-gradient-to-r from-purple-100 to-pink-100 rounded-lg hover:from-purple-200 hover:to-pink-200 transition-colors shadow">
                <span class="text-2xl mr-3">👥</span>
                <div>
                    <div class="font-semibold text-purple-800">Kelola Akun</div>
                    <div class="text-sm text-purple-600">Kelola akun mahasiswa dan asisten</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="glass rounded-2xl shadow-xl p-8 border border-pink-100">
        <h3 class="text-2xl font-bold text-pink-800 mb-4 flex items-center gap-2"><svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>Aktivitas Laporan Terbaru</h3>
        <?php if ($aktivitasTerbaru->num_rows > 0): ?>
            <div class="space-y-4">
                <?php while ($aktivitas = $aktivitasTerbaru->fetch_assoc()): ?>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                            <span class="font-bold text-gray-500"><?php echo strtoupper(substr($aktivitas['mahasiswa_nama'], 0, 2)); ?></span>
                        </div>
                        <div>
                            <p class="text-gray-800 font-semibold">
                                <strong><?php echo htmlspecialchars($aktivitas['mahasiswa_nama']); ?></strong> 
                                mengumpulkan laporan untuk 
                                <strong><?php echo htmlspecialchars($aktivitas['modul_judul']); ?></strong>
                                (<?php echo htmlspecialchars($aktivitas['praktikum_nama']); ?>)
                            </p>
                            <p class="text-sm text-gray-500"><?php echo formatDate($aktivitas['submitted_at']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-500">
                <span class="text-4xl mb-4 block">📭</span>
                <p>Tidak ada aktivitas terbaru</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>