<?php
require_once 'functions.php';

// Cek login
if (!isLoggedIn()) {
    redirect('login.php', 'Silakan login terlebih dahulu.', 'error');
}

$laporanId = $_GET['id'] ?? null;
if (!$laporanId) {
    redirect('praktikum.php', 'ID laporan tidak valid.', 'error');
}

// Ambil data laporan
$stmt = $conn->prepare("SELECT l.*, m.judul as modul_judul, p.nama as praktikum_nama, u.nama as mahasiswa_nama 
                       FROM laporan l 
                       JOIN modul m ON l.modul_id = m.id 
                       JOIN praktikum p ON m.praktikum_id = p.id 
                       JOIN users u ON l.mahasiswa_id = u.id 
                       WHERE l.id = ?");
$stmt->bind_param("i", $laporanId);
$stmt->execute();
$laporan = $stmt->get_result()->fetch_assoc();

if (!$laporan) {
    redirect('praktikum.php', 'Laporan tidak ditemukan.', 'error');
}

// Cek akses: mahasiswa hanya bisa download laporan sendiri, asisten bisa download semua
if (isMahasiswa() && $laporan['mahasiswa_id'] != $_SESSION['user_id']) {
    redirect('mahasiswa/praktikum_saya.php', 'Anda tidak memiliki akses ke laporan ini.', 'error');
}

// Cek apakah file ada
if (!$laporan['file_laporan']) {
    redirect('praktikum.php', 'File laporan tidak tersedia.', 'error');
}

$filePath = 'uploads/laporan/' . $laporan['file_laporan'];

// Download file
if (file_exists($filePath)) {
    downloadFile($filePath, $laporan['nama_file']);
} else {
    redirect('praktikum.php', 'File laporan tidak ditemukan.', 'error');
}
?> 