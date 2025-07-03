<?php
require_once '../functions.php';

// Cek login
if (!isLoggedIn() || !isMahasiswa()) {
    redirect('../login.php', 'Silakan login sebagai mahasiswa terlebih dahulu.', 'error');
}

// Ambil statistik mahasiswa
$userId = $_SESSION['user_id'];

// Jumlah praktikum yang diikuti
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM mahasiswa_praktikum WHERE mahasiswa_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$praktikumCount = $stmt->get_result()->fetch_assoc()['total'];

// Jumlah tugas yang sudah dinilai
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM laporan l 
                       JOIN modul m ON l.modul_id = m.id 
                       WHERE l.mahasiswa_id = ? AND l.status = 'dinilai'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$tugasSelesaiCount = $stmt->get_result()->fetch_assoc()['total'];

// Jumlah tugas yang menunggu penilaian
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM laporan l 
                       JOIN modul m ON l.modul_id = m.id 
                       WHERE l.mahasiswa_id = ? AND l.status = 'menunggu'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$tugasMenungguCount = $stmt->get_result()->fetch_assoc()['total'];

// Notifikasi terbaru (nilai baru, deadline, dll)
$notifications = [];

// Nilai terbaru
$stmt = $conn->prepare("SELECT l.*, m.judul as modul_judul, p.nama as praktikum_nama 
                       FROM laporan l 
                       JOIN modul m ON l.modul_id = m.id 
                       JOIN praktikum p ON m.praktikum_id = p.id 
                       WHERE l.mahasiswa_id = ? AND l.status = 'dinilai' 
                       ORDER BY l.dinilai_at DESC LIMIT 3");
$stmt->bind_param("i", $userId);
$stmt->execute();
$nilaiTerbaru = $stmt->get_result();

while ($nilai = $nilaiTerbaru->fetch_assoc()) {
    $notifications[] = [
        'type' => 'nilai',
        'message' => "Nilai untuk {$nilai['modul_judul']} ({$nilai['praktikum_nama']}) telah diberikan: " . formatNilai($nilai['nilai']),
        'time' => $nilai['dinilai_at']
    ];
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php'; 
?>

<div class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white p-8 rounded-3xl shadow-2xl mb-8 flex flex-col md:flex-row items-center justify-between gap-6">
    <div>
        <h1 class="text-3xl md:text-4xl font-extrabold drop-shadow-lg">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
        <p class="mt-2 opacity-90 text-lg font-medium">Semangat menyelesaikan semua modul praktikummu 🎓</p>
    </div>
    <img src="https://img.icons8.com/color/96/000000/student-male--v2.png" class="h-20 w-20 md:h-28 md:w-28 drop-shadow-xl" alt="Mahasiswa"/>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
    <div class="glass rounded-2xl shadow-xl p-8 flex flex-col items-center justify-center border border-blue-100">
        <div class="bg-gradient-to-br from-blue-500 to-cyan-400 p-4 rounded-full mb-3 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0 0H6m6 0h6" /></svg>
        </div>
        <div class="text-4xl font-extrabold text-blue-600"><?php echo $praktikumCount; ?></div>
        <div class="mt-2 text-lg text-gray-700 font-semibold">Praktikum Diikuti</div>
        <a href="praktikum_saya.php" class="mt-2 text-blue-600 hover:text-blue-800 text-sm font-bold underline">Lihat Praktikum Saya</a>
    </div>
    <div class="glass rounded-2xl shadow-xl p-8 flex flex-col items-center justify-center border border-green-100">
        <div class="bg-gradient-to-br from-green-400 to-green-600 p-4 rounded-full mb-3 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2l4-4" /></svg>
        </div>
        <div class="text-4xl font-extrabold text-green-600"><?php echo $tugasSelesaiCount; ?></div>
        <div class="mt-2 text-lg text-gray-700 font-semibold">Tugas Selesai</div>
        <a href="praktikum_saya.php" class="mt-2 text-green-600 hover:text-green-800 text-sm font-bold underline">Lihat Nilai</a>
    </div>
    <div class="glass rounded-2xl shadow-xl p-8 flex flex-col items-center justify-center border border-yellow-100">
        <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 p-4 rounded-full mb-3 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" /></svg>
        </div>
        <div class="text-4xl font-extrabold text-yellow-500"><?php echo $tugasMenungguCount; ?></div>
        <div class="mt-2 text-lg text-gray-700 font-semibold">Tugas Menunggu</div>
        <a href="praktikum_saya.php" class="mt-2 text-yellow-600 hover:text-yellow-800 text-sm font-bold underline">Lihat Status</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Quick Actions -->
    <div class="glass rounded-2xl shadow-xl p-8 border border-blue-100">
        <h3 class="text-2xl font-bold text-blue-800 mb-4 flex items-center gap-2"><svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0 0H6m6 0h6" /></svg>Aksi Cepat</h3>
        <div class="space-y-3">
            <a href="../praktikum.php" class="flex items-center p-3 bg-gradient-to-r from-blue-100 to-cyan-100 rounded-lg hover:from-blue-200 hover:to-cyan-200 transition-colors shadow">
                <span class="text-2xl mr-3">📚</span>
                <div>
                    <div class="font-semibold text-blue-800">Katalog Praktikum</div>
                    <div class="text-sm text-blue-600">Lihat semua praktikum yang tersedia</div>
                </div>
            </a>
            <a href="praktikum_saya.php" class="flex items-center p-3 bg-gradient-to-r from-green-100 to-green-200 rounded-lg hover:from-green-200 hover:to-green-300 transition-colors shadow">
                <span class="text-2xl mr-3">📋</span>
                <div>
                    <div class="font-semibold text-green-800">Praktikum Saya</div>
                    <div class="text-sm text-green-600">Kelola praktikum yang diikuti</div>
                </div>
            </a>
            <?php if ($tugasMenungguCount > 0): ?>
            <a href="praktikum_saya.php" class="flex items-center p-3 bg-gradient-to-r from-yellow-100 to-yellow-200 rounded-lg hover:from-yellow-200 hover:to-yellow-300 transition-colors shadow">
                <span class="text-2xl mr-3">⏳</span>
                <div>
                    <div class="font-semibold text-yellow-800">Tugas Menunggu</div>
                    <div class="text-sm text-yellow-600"><?php echo $tugasMenungguCount; ?> tugas belum dikumpulkan</div>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Notifications -->
    <div class="glass rounded-2xl shadow-xl p-8 border border-indigo-100">
        <h3 class="text-2xl font-bold text-indigo-800 mb-4 flex items-center gap-2"><svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>Notifikasi Terbaru</h3>
        <?php if (!empty($notifications)): ?>
            <ul class="space-y-4">
                <?php foreach ($notifications as $notif): ?>
                    <li class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
                        <span class="text-xl mr-4">🔔</span>
                        <div>
                            <div class="text-sm text-gray-500"><?php echo formatDate($notif['time']); ?></div>
                            <div class="mt-1 font-semibold text-gray-800"><?php echo htmlspecialchars($notif['message']); ?></div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="text-center py-8 text-gray-500">
                <span class="text-4xl mb-4 block">📭</span>
                <p>Tidak ada notifikasi terbaru</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Panggil Footer
require_once 'templates/footer_mahasiswa.php';
?>