<?php
require_once '../functions.php';

// Cek login
if (!isLoggedIn() || !isMahasiswa()) {
    redirect('../login.php', 'Silakan login sebagai mahasiswa terlebih dahulu.', 'error');
}

$modulId = $_GET['modul_id'] ?? null;
if (!$modulId) {
    redirect('praktikum_saya.php', 'ID modul tidak valid.', 'error');
}

// Ambil data modul
$modul = getModulById($modulId);
if (!$modul) {
    redirect('praktikum_saya.php', 'Modul tidak ditemukan.', 'error');
}

// Cek apakah mahasiswa terdaftar di praktikum
if (!isMahasiswaTerdaftar($_SESSION['user_id'], $modul['praktikum_id'])) {
    redirect('praktikum_saya.php', 'Anda tidak terdaftar di praktikum ini.', 'error');
}

// Ambil semua laporan mahasiswa untuk modul ini
$stmt = $conn->prepare("SELECT * FROM laporan WHERE mahasiswa_id = ? AND modul_id = ? ORDER BY submitted_at DESC");
$stmt->bind_param("ii", $_SESSION['user_id'], $modulId);
$stmt->execute();
$laporanList = $stmt->get_result();

$pageTitle = 'Lihat Nilai';
$activePage = 'praktikum_saya';
require_once 'templates/header_mahasiswa.php'; 
?>

<div class="bg-gradient-to-r from-green-500 to-teal-400 text-white p-8 rounded-xl shadow-lg mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Nilai & Feedback</h1>
            <p class="mt-2 opacity-90">Lihat nilai dan feedback untuk laporan Anda</p>
        </div>
        <a href="praktikum_detail.php?id=<?php echo $modul['praktikum_id']; ?>" class="bg-white text-green-600 px-4 py-2 rounded-md hover:bg-gray-100">
            ← Kembali
        </a>
    </div>
</div>

<?php echo showMessage(); ?>

<div class="max-w-4xl mx-auto">
    <!-- Modul Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Informasi Modul</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500">Praktikum</span>
                <p class="text-gray-900"><?php echo htmlspecialchars($modul['praktikum_nama']); ?></p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Modul</span>
                <p class="text-gray-900"><?php echo htmlspecialchars($modul['judul']); ?></p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500">Pertemuan</span>
                <p class="text-gray-900">Ke-<?php echo $modul['pertemuan_ke']; ?></p>
            </div>
        </div>
    </div>

    <!-- Laporan History -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Riwayat Laporan</h2>
            <p class="text-gray-600 text-sm mt-1">Semua laporan yang telah Anda kumpulkan untuk modul ini</p>
        </div>
        
        <?php if ($laporanList->num_rows > 0): ?>
            <div class="divide-y divide-gray-200">
                <?php while ($laporan = $laporanList->fetch_assoc()): ?>
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-3">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        Laporan #<?php echo $laporan['id']; ?>
                                    </h3>
                                    <?php echo getStatusBadge($laporan['status']); ?>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">File</span>
                                        <p class="text-gray-900"><?php echo htmlspecialchars($laporan['nama_file']); ?></p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Dikumpulkan</span>
                                        <p class="text-gray-900"><?php echo formatDate($laporan['submitted_at']); ?></p>
                                    </div>
                                    <?php if ($laporan['nilai'] !== null): ?>
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Nilai</span>
                                            <p class="text-2xl font-bold text-green-600"><?php echo formatNilai($laporan['nilai']); ?></p>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Dinilai pada</span>
                                            <p class="text-gray-900"><?php echo formatDate($laporan['dinilai_at']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($laporan['feedback']): ?>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h4 class="font-medium text-gray-900 mb-2">Feedback dari Asisten</h4>
                                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($laporan['feedback'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-4 flex items-center space-x-3">
                                    <a href="../download_laporan.php?id=<?php echo $laporan['id']; ?>" 
                                       class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Download Laporan
                                    </a>
                                    
                                    <?php if ($laporan['status'] === 'ditolak'): ?>
                                        <a href="upload_laporan.php?modul_id=<?php echo $modulId; ?>" 
                                           class="inline-flex items-center text-green-600 hover:text-green-800">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            Upload Ulang
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="p-6 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada laporan</h3>
                <p class="mt-1 text-sm text-gray-500">Anda belum mengumpulkan laporan untuk modul ini.</p>
                <div class="mt-6">
                    <a href="upload_laporan.php?modul_id=<?php echo $modulId; ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Upload Laporan Pertama
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?> 