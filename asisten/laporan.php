<?php
require_once '../functions.php';

// Cek login
if (!isLoggedIn() || !isAsisten()) {
    redirect('../login.php', 'Silakan login sebagai asisten terlebih dahulu.', 'error');
}

$modulId = $_GET['modul_id'] ?? null;

// Handle grading
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $laporanId = $_POST['laporan_id'] ?? null;
    $nilai = $_POST['nilai'] ?? null;
    $feedback = $_POST['feedback'] ?? '';
    $status = $_POST['status'] ?? 'dinilai';
    
    if ($laporanId && $nilai !== null) {
        $stmt = $conn->prepare("UPDATE laporan SET nilai = ?, feedback = ?, status = ?, dinilai_at = NOW() WHERE id = ?");
        $stmt->bind_param("dssi", $nilai, $feedback, $status, $laporanId);
        
        if ($stmt->execute()) {
            redirect('laporan.php' . ($modulId ? "?modul_id=$modulId" : ''), 'Laporan berhasil dinilai!', 'success');
        } else {
            $error = 'Gagal menilai laporan.';
        }
    }
}

// Build query with filters
$whereConditions = [];
$params = [];
$paramTypes = '';

if ($modulId) {
    $whereConditions[] = "l.modul_id = ?";
    $params[] = $modulId;
    $paramTypes .= 'i';
}

$statusFilter = $_GET['status'] ?? '';
if ($statusFilter) {
    $whereConditions[] = "l.status = ?";
    $params[] = $statusFilter;
    $paramTypes .= 's';
}

$mahasiswaFilter = $_GET['mahasiswa'] ?? '';
if ($mahasiswaFilter) {
    $whereConditions[] = "u.nama LIKE ?";
    $params[] = "%$mahasiswaFilter%";
    $paramTypes .= 's';
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

$query = "SELECT l.*, u.nama as mahasiswa_nama, u.email as mahasiswa_email,
                 m.judul as modul_judul, p.nama as praktikum_nama
          FROM laporan l 
          JOIN users u ON l.mahasiswa_id = u.id 
          JOIN modul m ON l.modul_id = m.id 
          JOIN praktikum p ON m.praktikum_id = p.id 
          $whereClause
          ORDER BY l.submitted_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $laporanList = $stmt->get_result();
} else {
    $laporanList = $conn->query($query);
}

// Get modul info if filtering by modul
$modulInfo = null;
if ($modulId) {
    $modulInfo = getModulById($modulId);
}

$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
require_once 'templates/header.php'; 
?>

<div class="bg-gradient-to-r from-yellow-500 to-orange-400 text-white p-8 rounded-xl shadow-lg mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Laporan Masuk</h1>
            <p class="mt-2 opacity-90">
                <?php if ($modulId && $modulInfo): ?>
                    Modul: <?php echo htmlspecialchars($modulInfo['judul']); ?> 
                    (<?php echo htmlspecialchars($modulInfo['praktikum_nama']); ?>)
                <?php else: ?>
                    Nilai laporan mahasiswa
                <?php endif; ?>
            </p>
        </div>
        <div class="flex space-x-3">
            <?php if ($modulId): ?>
                <a href="modul.php?praktikum_id=<?php echo $modulInfo['praktikum_id']; ?>" class="bg-white text-yellow-600 px-4 py-2 rounded-md hover:bg-gray-100">
                    ← Kembali ke Modul
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php echo showMessage(); ?>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter</h2>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <?php if ($modulId): ?>
            <input type="hidden" name="modul_id" value="<?php echo $modulId; ?>">
        <?php endif; ?>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="">Semua Status</option>
                <option value="menunggu" <?php echo $statusFilter === 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                <option value="dinilai" <?php echo $statusFilter === 'dinilai' ? 'selected' : ''; ?>>Dinilai</option>
                <option value="ditolak" <?php echo $statusFilter === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Mahasiswa</label>
            <input type="text" name="mahasiswa" value="<?php echo htmlspecialchars($mahasiswaFilter); ?>" 
                   placeholder="Cari nama mahasiswa" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md">
        </div>
        
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                Filter
            </button>
        </div>
        
        <div class="flex items-end">
            <a href="laporan.php<?php echo $modulId ? "?modul_id=$modulId" : ''; ?>" 
               class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Laporan List -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Daftar Laporan</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modul</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dikumpulkan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($laporanList->num_rows > 0): ?>
                    <?php while ($laporan = $laporanList->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($laporan['mahasiswa_nama']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($laporan['mahasiswa_email']); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($laporan['modul_judul']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($laporan['praktikum_nama']); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <a href="../download_laporan.php?id=<?php echo $laporan['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-800">
                                    <?php echo htmlspecialchars($laporan['nama_file']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo formatDate($laporan['submitted_at']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo getStatusBadge($laporan['status']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if ($laporan['nilai'] !== null): ?>
                                    <span class="text-2xl font-bold text-green-600"><?php echo formatNilai($laporan['nilai']); ?></span>
                                <?php else: ?>
                                    <span class="text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="gradeLaporan(<?php echo htmlspecialchars(json_encode($laporan)); ?>)" 
                                        class="text-blue-600 hover:text-blue-900">
                                    <?php echo $laporan['nilai'] !== null ? 'Edit Nilai' : 'Nilai'; ?>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada laporan yang ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Grading -->
<div id="modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Nilai Laporan</h3>
            
            <form method="POST">
                <input type="hidden" name="laporan_id" id="formLaporanId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mahasiswa</label>
                    <p class="text-gray-900" id="formMahasiswa"></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Modul</label>
                    <p class="text-gray-900" id="formModul"></p>
                </div>
                
                <div class="mb-4">
                    <label for="nilai" class="block text-sm font-medium text-gray-700 mb-2">Nilai (0-100)</label>
                    <input type="number" name="nilai" id="formNilai" min="0" max="100" step="0.1" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="formStatus" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="dinilai">Dinilai</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="feedback" class="block text-sm font-medium text-gray-700 mb-2">Feedback</label>
                    <textarea name="feedback" id="formFeedback" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Berikan feedback untuk mahasiswa..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Simpan Nilai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function gradeLaporan(data) {
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('formLaporanId').value = data.id;
    document.getElementById('formMahasiswa').textContent = data.mahasiswa_nama;
    document.getElementById('formModul').textContent = data.modul_judul + ' (' + data.praktikum_nama + ')';
    document.getElementById('formNilai').value = data.nilai || '';
    document.getElementById('formStatus').value = data.status || 'dinilai';
    document.getElementById('formFeedback').value = data.feedback || '';
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
}
</script>

<?php
require_once 'templates/footer.php';
?> 