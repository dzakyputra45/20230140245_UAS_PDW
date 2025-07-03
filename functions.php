<?php
session_start();
require_once 'config.php';

// Fungsi untuk mengecek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk mengecek role user
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

// Fungsi untuk mengecek apakah user adalah mahasiswa
function isMahasiswa() {
    return getUserRole() === 'mahasiswa';
}

// Fungsi untuk mengecek apakah user adalah asisten
function isAsisten() {
    return getUserRole() === 'asisten';
}

// Fungsi untuk redirect dengan pesan
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: $url");
    exit();
}

// Fungsi untuk menampilkan pesan
function showMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'success';
        $message = $_SESSION['message'];
        unset($_SESSION['message'], $_SESSION['message_type']);
        
        $alertClass = $type === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700';
        
        return "<div class='$alertClass border px-4 py-3 rounded relative mb-4' role='alert'>
                    <span class='block sm:inline'>$message</span>
                </div>";
    }
    return '';
}

// Fungsi untuk upload file
function uploadFile($file, $targetDir, $allowedTypes = ['pdf', 'doc', 'docx']) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'Tidak ada file yang diupload'];
    }
    
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileTmp = $file['tmp_name'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Validasi tipe file
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan. Gunakan: ' . implode(', ', $allowedTypes)];
    }
    
    // Validasi ukuran file (max 5MB)
    if ($fileSize > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB'];
    }
    
    // Generate nama file unik
    $newFileName = uniqid() . '_' . $fileName;
    $targetPath = $targetDir . '/' . $newFileName;
    
    // Upload file
    if (move_uploaded_file($fileTmp, $targetPath)) {
        return ['success' => true, 'filename' => $newFileName, 'original_name' => $fileName];
    } else {
        return ['success' => false, 'message' => 'Gagal mengupload file'];
    }
}

// Fungsi untuk download file
function downloadFile($filePath, $originalName) {
    if (file_exists($filePath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $originalName . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit();
    }
}

// Fungsi untuk mendapatkan data user berdasarkan ID
function getUserById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Fungsi untuk mendapatkan data praktikum berdasarkan ID
function getPraktikumById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM praktikum WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Fungsi untuk mendapatkan data modul berdasarkan ID
function getModulById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT m.*, p.nama as praktikum_nama FROM modul m 
                           JOIN praktikum p ON m.praktikum_id = p.id 
                           WHERE m.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Fungsi untuk mengecek apakah mahasiswa sudah terdaftar di praktikum
function isMahasiswaTerdaftar($mahasiswaId, $praktikumId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM mahasiswa_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?");
    $stmt->bind_param("ii", $mahasiswaId, $praktikumId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Fungsi untuk mendapatkan status laporan mahasiswa untuk modul tertentu
function getLaporanStatus($mahasiswaId, $modulId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM laporan WHERE mahasiswa_id = ? AND modul_id = ? ORDER BY submitted_at DESC LIMIT 1");
    $stmt->bind_param("ii", $mahasiswaId, $modulId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Fungsi untuk format tanggal
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Fungsi untuk format nilai
function formatNilai($nilai) {
    if ($nilai === null) return '-';
    return number_format($nilai, 1);
}

// Fungsi untuk mendapatkan badge status
function getStatusBadge($status) {
    $badges = [
        'menunggu' => 'bg-yellow-100 text-yellow-800',
        'dinilai' => 'bg-green-100 text-green-800',
        'ditolak' => 'bg-red-100 text-red-800',
        'aktif' => 'bg-blue-100 text-blue-800',
        'selesai' => 'bg-gray-100 text-gray-800'
    ];
    
    $badgeClass = $badges[$status] ?? 'bg-gray-100 text-gray-800';
    return "<span class='px-2 py-1 text-xs font-medium rounded-full $badgeClass'>" . ucfirst($status) . "</span>";
}
?> 