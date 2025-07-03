<?php
require_once '../functions.php';

// Cek login
if (!isLoggedIn() || !isAsisten()) {
    redirect('../login.php', 'Silakan login sebagai asisten terlebih dahulu.', 'error');
}

$praktikumId = $_GET['praktikum_id'] ?? null;

// Handle delete
if (isset($_GET['delete']) && $_GET['delete']) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM modul WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        redirect('modul.php' . ($praktikumId ? "?praktikum_id=$praktikumId" : ''), 'Modul berhasil dihapus!', 'success');
    } else {
        redirect('modul.php' . ($praktikumId ? "?praktikum_id=$praktikumId" : ''), 'Gagal menghapus modul.', 'error');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $praktikum_id = $_POST['praktikum_id'] ?? '';
    $judul = $_POST['judul'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $pertemuan_ke = $_POST['pertemuan_ke'] ?? '';
    
    if (empty($praktikum_id) || empty($judul) || empty($pertemuan_ke)) {
        $error = 'Semua field harus diisi.';
    } else {
        // Handle file upload
        $file_materi = null;
        if (isset($_FILES['file_materi']) && $_FILES['file_materi']['tmp_name']) {
            $uploadResult = uploadFile($_FILES['file_materi'], '../uploads/materi', ['pdf', 'doc', 'docx']);
            if ($uploadResult['success']) {
                $file_materi = $uploadResult['filename'];
            } else {
                $error = $uploadResult['message'];
            }
        }
        
        if (!$error) {
            if ($id) {
                // Update
                if ($file_materi) {
                    $stmt = $conn->prepare("UPDATE modul SET praktikum_id = ?, judul = ?, deskripsi = ?, pertemuan_ke = ?, file_materi = ? WHERE id = ?");
                    $stmt->bind_param("issisi", $praktikum_id, $judul, $deskripsi, $pertemuan_ke, $file_materi, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE modul SET praktikum_id = ?, judul = ?, deskripsi = ?, pertemuan_ke = ? WHERE id = ?");
                    $stmt->bind_param("issii", $praktikum_id, $judul, $deskripsi, $pertemuan_ke, $id);
                }
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO modul (praktikum_id, judul, deskripsi, pertemuan_ke, file_materi) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issis", $praktikum_id, $judul, $deskripsi, $pertemuan_ke, $file_materi);
            }
            
            if ($stmt->execute()) {
                redirect('modul.php' . ($praktikumId ? "?praktikum_id=$praktikumId" : ''), 'Modul berhasil disimpan!', 'success');
            } else {
                $error = 'Gagal menyimpan modul.';
            }
        }
    }
}

// Ambil data untuk edit
$editData = null;
if (isset($_GET['edit']) && $_GET['edit']) {
    $editData = getModulById($_GET['edit']);
}

// Ambil semua praktikum untuk dropdown
$praktikumList = $conn->query("SELECT * FROM praktikum ORDER BY nama");

// Ambil modul berdasarkan praktikum atau semua
if ($praktikumId) {
    $stmt = $conn->prepare("SELECT m.*, p.nama as praktikum_nama,
                                  (SELECT COUNT(*) FROM laporan WHERE modul_id = m.id) as total_laporan
                           FROM modul m 
                           JOIN praktikum p ON m.praktikum_id = p.id 
                           WHERE m.praktikum_id = ? 
                           ORDER BY m.pertemuan_ke");
    $stmt->bind_param("i", $praktikumId);
    $stmt->execute();
    $modulList = $stmt->get_result();
    
    $praktikumInfo = getPraktikumById($praktikumId);
} else {
    $query = "SELECT m.*, p.nama as praktikum_nama,
                     (SELECT COUNT(*) FROM laporan WHERE modul_id = m.id) as total_laporan
              FROM modul m 
              JOIN praktikum p ON m.praktikum_id = p.id 
              ORDER BY p.nama, m.pertemuan_ke";
    $modulList = $conn->query($query);
}

$pageTitle = 'Kelola Modul';
$activePage = 'modul';
require_once 'templates/header.php'; 
?>

<div class="bg-gradient-to-r from-green-500 to-emerald-400 text-white p-8 rounded-xl shadow-lg mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Kelola Modul</h1>
            <p class="mt-2 opacity-90">
                <?php if ($praktikumId && $praktikumInfo): ?>
                    Praktikum: <?php echo htmlspecialchars($praktikumInfo['nama']); ?>
                <?php else: ?>
                    Tambah, edit, atau hapus modul praktikum
                <?php endif; ?>
            </p>
        </div>
        <div class="flex space-x-3">
            <?php if ($praktikumId): ?>
                <a href="praktikum.php" class="bg-white text-green-600 px-4 py-2 rounded-md hover:bg-gray-100">
                    ← Kembali ke Praktikum
                </a>
            <?php endif; ?>
            <button onclick="openModal()" class="bg-white text-green-600 px-4 py-2 rounded-md hover:bg-gray-100">
                + Tambah Modul
            </button>
        </div>
    </div>
</div>

<?php echo showMessage(); ?>

<!-- Modul List -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Daftar Modul</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pertemuan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                    <?php if (!$praktikumId): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Praktikum</th>
                    <?php endif; ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Materi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laporan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($modulList->num_rows > 0): ?>
                    <?php while ($modul = $modulList->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    Ke-<?php echo $modul['pertemuan_ke']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($modul['judul']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($modul['deskripsi']); ?></div>
                                </div>
                            </td>
                            <?php if (!$praktikumId): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($modul['praktikum_nama']); ?>
                                </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if ($modul['file_materi']): ?>
                                    <a href="../download_materi.php?id=<?php echo $modul['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-800">
                                        Download
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-500">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $modul['total_laporan']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="laporan.php?modul_id=<?php echo $modul['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">Laporan</a>
                                    <button onclick="editModul(<?php echo htmlspecialchars(json_encode($modul)); ?>)" 
                                            class="text-green-600 hover:text-green-900">Edit</button>
                                    <button onclick="deleteModul(<?php echo $modul['id']; ?>, '<?php echo htmlspecialchars($modul['judul']); ?>')" 
                                            class="text-red-600 hover:text-red-900">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $praktikumId ? '5' : '6'; ?>" class="px-6 py-4 text-center text-gray-500">
                            Belum ada modul yang ditambahkan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form -->
<div id="modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">
                <?php echo $editData ? 'Edit Modul' : 'Tambah Modul'; ?>
            </h3>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="formId" value="<?php echo $editData['id'] ?? ''; ?>">
                
                <div class="mb-4">
                    <label for="praktikum_id" class="block text-sm font-medium text-gray-700 mb-2">Praktikum</label>
                    <select name="praktikum_id" id="formPraktikumId" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Pilih Praktikum</option>
                        <?php while ($praktikum = $praktikumList->fetch_assoc()): ?>
                            <option value="<?php echo $praktikum['id']; ?>" 
                                    <?php echo ($editData && $editData['praktikum_id'] == $praktikum['id']) || $praktikumId == $praktikum['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($praktikum['nama']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="judul" class="block text-sm font-medium text-gray-700 mb-2">Judul Modul</label>
                    <input type="text" name="judul" id="formJudul" 
                           value="<?php echo htmlspecialchars($editData['judul'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="deskripsi" id="formDeskripsi" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($editData['deskripsi'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="pertemuan_ke" class="block text-sm font-medium text-gray-700 mb-2">Pertemuan Ke</label>
                    <input type="number" name="pertemuan_ke" id="formPertemuanKe" min="1" max="16"
                           value="<?php echo $editData['pertemuan_ke'] ?? ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="file_materi" class="block text-sm font-medium text-gray-700 mb-2">File Materi</label>
                    <input type="file" name="file_materi" id="formFileMateri" 
                           accept=".pdf,.doc,.docx"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">PDF, DOC, atau DOCX (maksimal 5MB)</p>
                    <?php if ($editData && $editData['file_materi']): ?>
                        <p class="text-xs text-blue-600 mt-1">File saat ini: <?php echo htmlspecialchars($editData['file_materi']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" 
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Tambah Modul';
    document.getElementById('formId').value = '';
    document.getElementById('formJudul').value = '';
    document.getElementById('formDeskripsi').value = '';
    document.getElementById('formPertemuanKe').value = '';
    document.getElementById('formFileMateri').value = '';
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
}

function editModul(data) {
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit Modul';
    document.getElementById('formId').value = data.id;
    document.getElementById('formPraktikumId').value = data.praktikum_id;
    document.getElementById('formJudul').value = data.judul;
    document.getElementById('formDeskripsi').value = data.deskripsi;
    document.getElementById('formPertemuanKe').value = data.pertemuan_ke;
}

function deleteModul(id, judul) {
    if (confirm(`Apakah Anda yakin ingin menghapus modul "${judul}"?`)) {
        window.location.href = `modul.php?delete=${id}`<?php echo $praktikumId ? " + '&praktikum_id=$praktikumId'" : ''; ?>;
    }
}
</script>

<?php
require_once 'templates/footer.php';
?> 