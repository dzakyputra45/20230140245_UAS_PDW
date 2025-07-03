<?php
require_once '../functions.php';

// Cek login
if (!isLoggedIn() || !isMahasiswa()) {
    redirect('../login.php', 'Silakan login sebagai mahasiswa terlebih dahulu.', 'error');
}

$praktikumId = $_GET['id'] ?? null;
if (!$praktikumId) {
    redirect('praktikum_saya.php', 'ID praktikum tidak valid.', 'error');
}

// Cek apakah mahasiswa terdaftar di praktikum ini
if (!isMahasiswaTerdaftar($_SESSION['user_id'], $praktikumId)) {
    redirect('praktikum_saya.php', 'Anda tidak terdaftar di praktikum ini.', 'error');
}

// Ambil data praktikum
$praktikum = getPraktikumById($praktikumId);
if (!$praktikum) {
    redirect('praktikum_saya.php', 'Praktikum tidak ditemukan.', 'error');
}

// Ambil semua modul praktikum
$stmt = $conn->prepare("SELECT m.*, 
                               (SELECT COUNT(*) FROM laporan WHERE modul_id = m.id AND mahasiswa_id = ?) as laporan_count,
                               (SELECT l.status FROM laporan l WHERE l.modul_id = m.id AND l.mahasiswa_id = ? ORDER BY l.submitted_at DESC LIMIT 1) as status_laporan,
                               (SELECT l.nilai FROM laporan l WHERE l.modul_id = m.id AND l.mahasiswa_id = ? ORDER BY l.submitted_at DESC LIMIT 1) as nilai_terakhir
                        FROM modul m 
                        WHERE m.praktikum_id = ? 
                        ORDER BY m.pertemuan_ke");
$stmt->bind_param("iiii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $praktikumId);
$stmt->execute();
$modulList = $stmt->get_result();

$pageTitle = $praktikum['nama'];
$activePage = 'praktikum_saya';
require_once 'templates/header_mahasiswa.php'; 
?>

<div class="bg-gradient-to-r from-purple-500 to-pink-400 text-white p-8 rounded-3xl shadow-2xl mb-8 flex flex-col md:flex-row items-center justify-between gap-6">
    <div>
        <h1 class="text-3xl md:text-4xl font-extrabold drop-shadow-lg"><?php echo htmlspecialchars($praktikum['nama']); ?></h1>
        <p class="mt-2 opacity-90 text-lg font-medium"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></p>
        <div class="mt-4 flex items-center space-x-4 text-sm">
            <span>Kode: <?php echo htmlspecialchars($praktikum['kode']); ?></span>
            <span>Semester: <?php echo $praktikum['semester']; ?></span>
            <span>SKS: <?php echo $praktikum['sks']; ?></span>
        </div>
    </div>
    <a href="praktikum_saya.php" class="bg-white/20 hover:bg-white/40 text-white font-bold px-4 py-2 rounded-lg shadow transition">← Kembali</a>
</div>

<?php echo showMessage(); ?>

<!-- Modul List -->
<div class="glass rounded-2xl shadow-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-100 to-pink-100">
        <h2 class="text-xl font-semibold text-gray-900">Daftar Modul</h2>
        <p class="text-gray-600 text-sm mt-1">Download materi dan kumpulkan laporan untuk setiap modul</p>
    </div>
    
    <div class="divide-y divide-gray-200">
        <?php if ($modulList->num_rows > 0): ?>
            <?php while ($modul = $modulList->fetch_assoc()): ?>
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <h3 class="text-lg font-bold text-gray-900">
                                    Modul <?php echo $modul['pertemuan_ke']; ?>: <?php echo htmlspecialchars($modul['judul']); ?>
                                </h3>
                                <?php if ($modul['status_laporan']): ?>
                                    <?php echo getStatusBadge($modul['status_laporan']); ?>
                                <?php endif; ?>
                            </div>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($modul['deskripsi']); ?></p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Download Materi -->
                                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-blue-900 mb-2">📚 Materi</h4>
                                    <?php if ($modul['file_materi']): ?>
                                        <a href="../download_materi.php?id=<?php echo $modul['id']; ?>" 
                                           class="inline-flex items-center text-blue-600 hover:text-blue-800 font-bold">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Download Materi
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-500 text-sm">Materi belum tersedia</span>
                                    <?php endif; ?>
                                </div>
                                <!-- Upload Laporan -->
                                <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-green-900 mb-2">📝 Laporan</h4>
                                    <?php if ($modul['laporan_count'] > 0): ?>
                                        <div class="text-sm text-green-700 mb-2 font-semibold">
                                            Laporan sudah dikumpulkan
                                        </div>
                                        <a href="upload_laporan.php?modul_id=<?php echo $modul['id']; ?>" 
                                           class="text-green-600 hover:text-green-800 text-sm font-bold">
                                            Upload Ulang
                                        </a>
                                    <?php else: ?>
                                        <a href="upload_laporan.php?modul_id=<?php echo $modul['id']; ?>" 
                                           class="inline-flex items-center text-green-600 hover:text-green-800 font-bold">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            Upload Laporan
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <!-- Nilai -->
                                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-lg">
                                    <h4 class="font-medium text-yellow-900 mb-2">📊 Nilai</h4>
                                    <?php if ($modul['nilai_terakhir'] !== null): ?>
                                        <div class="text-2xl font-bold text-green-600">
                                            <?php echo formatNilai($modul['nilai_terakhir']); ?>
                                        </div>
                                        <a href="lihat_nilai.php?modul_id=<?php echo $modul['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-bold">
                                            Lihat Detail
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-500 text-sm">
                                            <?php if ($modul['status_laporan'] === 'menunggu'): ?>
                                                Menunggu penilaian
                                            <?php else: ?>
                                                Belum ada nilai
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="p-6 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada modul</h3>
                <p class="mt-1 text-sm text-gray-500">Modul untuk praktikum ini belum tersedia.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?> 