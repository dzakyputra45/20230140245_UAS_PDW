<?php
require_once 'functions.php';

// Cek login
if (!isLoggedIn()) {
    redirect('login.php', 'Silakan login terlebih dahulu.', 'error');
}

$modulId = $_GET['id'] ?? null;
if (!$modulId) {
    redirect('praktikum.php', 'ID modul tidak valid.', 'error');
}

// Ambil data modul
$modul = getModulById($modulId);
if (!$modul) {
    redirect('praktikum.php', 'Modul tidak ditemukan.', 'error');
}

// Jika mahasiswa, cek apakah terdaftar di praktikum
if (isMahasiswa()) {
    if (!isMahasiswaTerdaftar($_SESSION['user_id'], $modul['praktikum_id'])) {
        redirect('mahasiswa/praktikum_saya.php', 'Anda tidak terdaftar di praktikum ini.', 'error');
    }
}

// Cek apakah file materi ada
if (!$modul['file_materi']) {
    redirect('praktikum.php', 'File materi tidak tersedia.', 'error');
}

$filePath = 'uploads/materi/' . $modul['file_materi'];

// Download file
if (file_exists($filePath)) {
    // Log download untuk mahasiswa
    if (isMahasiswa()) {
        $stmt = $conn->prepare("INSERT INTO download_log (mahasiswa_id, modul_id, downloaded_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $_SESSION['user_id'], $modulId);
        $stmt->execute();
    }
    
    downloadFile($filePath, $modul['file_materi']);
} else {
    redirect('praktikum.php', 'File materi tidak ditemukan.', 'error');
}
?> 