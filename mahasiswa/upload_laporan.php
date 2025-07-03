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

// Ambil laporan terakhir jika ada
$stmt = $conn->prepare("SELECT * FROM laporan WHERE mahasiswa_id = ? AND modul_id = ? ORDER BY submitted_at DESC LIMIT 1");
$stmt->bind_param("ii", $_SESSION['user_id'], $modulId);
$stmt->execute();
$laporanTerakhir = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['laporan'] ?? null;
    
    if (!$file) {
        $error = 'Silakan pilih file laporan.';
    } else {
        // Upload file
        $uploadResult = uploadFile($file, '../uploads/laporan', ['pdf', 'doc', 'docx']);
        
        if ($uploadResult['success']) {
            // Simpan ke database
            $stmt = $conn->prepare("INSERT INTO laporan (mahasiswa_id, modul_id, file_laporan, nama_file) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $_SESSION['user_id'], $modulId, $uploadResult['filename'], $uploadResult['original_name']);
            
            if ($stmt->execute()) {
                redirect('praktikum_detail.php?id=' . $modul['praktikum_id'], 'Laporan berhasil dikumpulkan!', 'success');
            } else {
                $error = 'Gagal menyimpan laporan. Silakan coba lagi.';
            }
        } else {
            $error = $uploadResult['message'];
        }
    }
}

$pageTitle = 'Upload Laporan';
$activePage = 'praktikum_saya';
require_once 'templates/header_mahasiswa.php'; 
?>

<div class="bg-gradient-to-r from-blue-500 to-indigo-400 text-white p-8 rounded-xl shadow-lg mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Upload Laporan</h1>
            <p class="mt-2 opacity-90">Kumpulkan laporan untuk modul yang dipilih</p>
        </div>
        <a href="praktikum_detail.php?id=<?php echo $modul['praktikum_id']; ?>" class="bg-white text-blue-600 px-4 py-2 rounded-md hover:bg-gray-100">
            ← Kembali
        </a>
    </div>
</div>

<?php echo showMessage(); ?>

<div class="max-w-2xl mx-auto">
    <!-- Modul Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Informasi Modul</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
            <div>
                <span class="text-sm font-medium text-gray-500">Deskripsi</span>
                <p class="text-gray-900"><?php echo htmlspecialchars($modul['deskripsi']); ?></p>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Upload Laporan</h2>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Laporan Terakhir -->
        <?php if ($laporanTerakhir): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-medium text-yellow-800 mb-2">Laporan Terakhir</h3>
                <div class="text-sm text-yellow-700">
                    <p><strong>File:</strong> <?php echo htmlspecialchars($laporanTerakhir['nama_file']); ?></p>
                    <p><strong>Dikumpulkan:</strong> <?php echo formatDate($laporanTerakhir['submitted_at']); ?></p>
                    <p><strong>Status:</strong> <?php echo getStatusBadge($laporanTerakhir['status']); ?></p>
                    <?php if ($laporanTerakhir['nilai'] !== null): ?>
                        <p><strong>Nilai:</strong> <?php echo formatNilai($laporanTerakhir['nilai']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-6">
                <label for="laporan" class="block text-sm font-medium text-gray-700 mb-2">
                    File Laporan
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="laporan" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload file</span>
                                <input id="laporan" name="laporan" type="file" class="sr-only" accept=".pdf,.doc,.docx" required>
                            </label>
                            <p class="pl-1">atau drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PDF, DOC, atau DOCX (maksimal 5MB)</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="praktikum_detail.php?id=<?php echo $modul['praktikum_id']; ?>" 
                   class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Batal
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Upload Laporan
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?> 