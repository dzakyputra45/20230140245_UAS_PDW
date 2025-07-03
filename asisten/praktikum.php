<?php
require_once '../functions.php';

// Cek login
if (!isLoggedIn() || !isAsisten()) {
    redirect('../login.php', 'Silakan login sebagai asisten terlebih dahulu.', 'error');
}

// Handle delete
if (isset($_GET['delete']) && $_GET['delete']) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM praktikum WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        redirect('praktikum.php', 'Praktikum berhasil dihapus!', 'success');
    } else {
        redirect('praktikum.php', 'Gagal menghapus praktikum.', 'error');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nama = $_POST['nama'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $kode = $_POST['kode'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $sks = $_POST['sks'] ?? '';
    
    if (empty($nama) || empty($kode) || empty($semester) || empty($sks)) {
        $error = 'Semua field harus diisi.';
    } else {
        if ($id) {
            // Update
            $stmt = $conn->prepare("UPDATE praktikum SET nama = ?, deskripsi = ?, kode = ?, semester = ?, sks = ? WHERE id = ?");
            $stmt->bind_param("sssiii", $nama, $deskripsi, $kode, $semester, $sks, $id);
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO praktikum (nama, deskripsi, kode, semester, sks) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssii", $nama, $deskripsi, $kode, $semester, $sks);
        }
        
        if ($stmt->execute()) {
            redirect('praktikum.php', 'Praktikum berhasil disimpan!', 'success');
        } else {
            $error = 'Gagal menyimpan praktikum.';
        }
    }
}

// Ambil data untuk edit
$editData = null;
if (isset($_GET['edit']) && $_GET['edit']) {
    $editData = getPraktikumById($_GET['edit']);
}

// Ambil semua praktikum
$query = "SELECT p.*, 
                 (SELECT COUNT(*) FROM modul WHERE praktikum_id = p.id) as total_modul,
                 (SELECT COUNT(*) FROM mahasiswa_praktikum WHERE praktikum_id = p.id) as total_mahasiswa
          FROM praktikum p 
          ORDER BY p.created_at DESC";
$result = $conn->query($query);

$pageTitle = 'Kelola Praktikum';
$activePage = 'praktikum';
require_once 'templates/header.php'; 
?>

<div class="bg-gradient-to-r from-blue-500 to-indigo-400 text-white p-8 rounded-xl shadow-lg mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Kelola Praktikum</h1>
            <p class="mt-2 opacity-90">Tambah, edit, atau hapus mata praktikum</p>
        </div>
        <button onclick="openModal()" class="bg-white text-blue-600 px-4 py-2 rounded-md hover:bg-gray-100">
            + Tambah Praktikum
        </button>
    </div>
</div>

<?php echo showMessage(); ?>

<!-- Praktikum List -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Daftar Praktikum</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKS</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modul</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($praktikum = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    <?php echo htmlspecialchars($praktikum['kode']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($praktikum['nama']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $praktikum['semester']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $praktikum['sks']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $praktikum['total_modul']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $praktikum['total_mahasiswa']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="modul.php?praktikum_id=<?php echo $praktikum['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900">Modul</a>
                                    <button onclick="editPraktikum(<?php echo htmlspecialchars(json_encode($praktikum)); ?>)" 
                                            class="text-green-600 hover:text-green-900">Edit</button>
                                    <button onclick="deletePraktikum(<?php echo $praktikum['id']; ?>, '<?php echo htmlspecialchars($praktikum['nama']); ?>')" 
                                            class="text-red-600 hover:text-red-900">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            Belum ada praktikum yang ditambahkan.
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
                <?php echo $editData ? 'Edit Praktikum' : 'Tambah Praktikum'; ?>
            </h3>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="id" id="formId" value="<?php echo $editData['id'] ?? ''; ?>">
                
                <div class="mb-4">
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Praktikum</label>
                    <input type="text" name="nama" id="formNama" 
                           value="<?php echo htmlspecialchars($editData['nama'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="deskripsi" id="formDeskripsi" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($editData['deskripsi'] ?? ''); ?></textarea>
                </div>
                
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="kode" class="block text-sm font-medium text-gray-700 mb-2">Kode</label>
                        <input type="text" name="kode" id="formKode" 
                               value="<?php echo htmlspecialchars($editData['kode'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label for="semester" class="block text-sm font-medium text-gray-700 mb-2">Semester</label>
                        <input type="number" name="semester" id="formSemester" min="1" max="8"
                               value="<?php echo $editData['semester'] ?? ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label for="sks" class="block text-sm font-medium text-gray-700 mb-2">SKS</label>
                        <input type="number" name="sks" id="formSks" min="1" max="6"
                               value="<?php echo $editData['sks'] ?? ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
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
    document.getElementById('modalTitle').textContent = 'Tambah Praktikum';
    document.getElementById('formId').value = '';
    document.getElementById('formNama').value = '';
    document.getElementById('formDeskripsi').value = '';
    document.getElementById('formKode').value = '';
    document.getElementById('formSemester').value = '';
    document.getElementById('formSks').value = '';
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
}

function editPraktikum(data) {
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit Praktikum';
    document.getElementById('formId').value = data.id;
    document.getElementById('formNama').value = data.nama;
    document.getElementById('formDeskripsi').value = data.deskripsi;
    document.getElementById('formKode').value = data.kode;
    document.getElementById('formSemester').value = data.semester;
    document.getElementById('formSks').value = data.sks;
}

function deletePraktikum(id, nama) {
    if (confirm(`Apakah Anda yakin ingin menghapus praktikum "${nama}"?`)) {
        window.location.href = `praktikum.php?delete=${id}`;
    }
}
</script>

<?php
require_once 'templates/footer.php';
?> 