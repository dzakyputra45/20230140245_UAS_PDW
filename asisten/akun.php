<?php
require_once '../functions.php';

// Cek login
if (!isLoggedIn() || !isAsisten()) {
    redirect('../login.php', 'Silakan login sebagai asisten terlebih dahulu.', 'error');
}

// Handle delete
if (isset($_GET['delete']) && $_GET['delete']) {
    $id = $_GET['delete'];
    // Tidak bisa menghapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        redirect('akun.php', 'Anda tidak bisa menghapus akun sendiri.', 'error');
    }
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        redirect('akun.php', 'Akun berhasil dihapus!', 'success');
    } else {
        redirect('akun.php', 'Gagal menghapus akun.', 'error');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    if (empty($nama) || empty($email) || empty($role)) {
        $error = 'Nama, email, dan role harus diisi.';
    } else {
        // Cek email unik
        $idCek = $id ?: 0; // FIX: Tidak pakai ekspresi langsung
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $idCek);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email sudah digunakan.';
        } else {
            if ($id) {
                // Update
                if ($password) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, password = ?, role = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $nama, $email, $hashedPassword, $role, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $nama, $email, $role, $id);
                }
            } else {
                // Insert
                if (empty($password)) {
                    $error = 'Password harus diisi untuk akun baru.';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $nama, $email, $hashedPassword, $role);
                }
            }
            
            if (!$error && $stmt->execute()) {
                redirect('akun.php', 'Akun berhasil disimpan!', 'success');
            } else if (!$error) {
                $error = 'Gagal menyimpan akun.';
            }
        }
    }
}


// Ambil data untuk edit
$editData = null;
if (isset($_GET['edit']) && $_GET['edit']) {
    $editData = getUserById($_GET['edit']);
}

// Ambil semua user
$query = "SELECT u.*, 
                 (SELECT COUNT(*) FROM mahasiswa_praktikum WHERE mahasiswa_id = u.id) as total_praktikum,
                 (SELECT COUNT(*) FROM laporan WHERE mahasiswa_id = u.id) as total_laporan
          FROM users u 
          ORDER BY u.created_at DESC";
$result = $conn->query($query);

$pageTitle = 'Kelola Akun';
$activePage = 'akun';
require_once 'templates/header.php'; 
?>

<div class="bg-gradient-to-r from-purple-500 to-pink-400 text-white p-8 rounded-xl shadow-lg mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Kelola Akun</h1>
            <p class="mt-2 opacity-90">Tambah, edit, atau hapus akun mahasiswa dan asisten</p>
        </div>
        <button onclick="openModal()" class="bg-white text-purple-600 px-4 py-2 rounded-md hover:bg-gray-100">
            + Tambah Akun
        </button>
    </div>
</div>

<?php echo showMessage(); ?>

<!-- User List -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Daftar Akun</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Praktikum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laporan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terdaftar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                        <span class="font-bold text-gray-500"><?php echo strtoupper(substr($user['nama'], 0, 2)); ?></span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['nama']); ?></div>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <span class="text-xs text-blue-600">(Anda)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo getStatusBadge($user['role']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $user['role'] === 'mahasiswa' ? $user['total_praktikum'] : '-'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $user['role'] === 'mahasiswa' ? $user['total_laporan'] : '-'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo formatDate($user['created_at']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" 
                                            class="text-green-600 hover:text-green-900">Edit</button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nama']); ?>')" 
                                                class="text-red-600 hover:text-red-900">Hapus</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            Belum ada akun yang ditambahkan.
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
                <?php echo $editData ? 'Edit Akun' : 'Tambah Akun'; ?>
            </h3>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="id" id="formId" value="<?php echo $editData['id'] ?? ''; ?>">
                
                <div class="mb-4">
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" id="formNama" 
                           value="<?php echo htmlspecialchars($editData['nama'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="formEmail" 
                           value="<?php echo htmlspecialchars($editData['email'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password <?php echo $editData ? '(kosongkan jika tidak ingin mengubah)' : ''; ?>
                    </label>
                    <input type="password" name="password" id="formPassword" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           <?php echo $editData ? '' : 'required'; ?>>
                </div>
                
                <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select name="role" id="formRole" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Pilih Role</option>
                        <option value="mahasiswa" <?php echo ($editData && $editData['role'] === 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                        <option value="asisten" <?php echo ($editData && $editData['role'] === 'asisten') ? 'selected' : ''; ?>>Asisten</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" 
                            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
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
    document.getElementById('modalTitle').textContent = 'Tambah Akun';
    document.getElementById('formId').value = '';
    document.getElementById('formNama').value = '';
    document.getElementById('formEmail').value = '';
    document.getElementById('formPassword').value = '';
    document.getElementById('formRole').value = '';
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
}

function editUser(data) {
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit Akun';
    document.getElementById('formId').value = data.id;
    document.getElementById('formNama').value = data.nama;
    document.getElementById('formEmail').value = data.email;
    document.getElementById('formPassword').value = '';
    document.getElementById('formRole').value = data.role;
}

function deleteUser(id, nama) {
    if (confirm(`Apakah Anda yakin ingin menghapus akun "${nama}"?`)) {
        window.location.href = `akun.php?delete=${id}`;
    }
}
</script>

<?php
require_once 'templates/footer.php';
?> 