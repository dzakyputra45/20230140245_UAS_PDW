<?php
require_once 'functions.php';

// Cek apakah user sudah login dan adalah mahasiswa
if (!isLoggedIn() || !isMahasiswa()) {
    redirect('login.php', 'Silakan login sebagai mahasiswa terlebih dahulu.', 'error');
}

// Cek apakah ada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $praktikumId = $_POST['praktikum_id'] ?? null;
    
    if (!$praktikumId) {
        redirect('praktikum.php', 'ID praktikum tidak valid.', 'error');
    }
    
    // Cek apakah praktikum exists
    $praktikum = getPraktikumById($praktikumId);
    if (!$praktikum) {
        redirect('praktikum.php', 'Praktikum tidak ditemukan.', 'error');
    }
    
    // Cek apakah sudah terdaftar
    if (isMahasiswaTerdaftar($_SESSION['user_id'], $praktikumId)) {
        redirect('praktikum.php', 'Anda sudah terdaftar di praktikum ini.', 'error');
    }
    
    // Daftar ke praktikum
    $stmt = $conn->prepare("INSERT INTO mahasiswa_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $_SESSION['user_id'], $praktikumId);
    
    if ($stmt->execute()) {
        redirect('mahasiswa/praktikum_saya.php', 'Berhasil mendaftar ke praktikum ' . $praktikum['nama'], 'success');
    } else {
        redirect('praktikum.php', 'Gagal mendaftar ke praktikum. Silakan coba lagi.', 'error');
    }
} else {
    // Jika bukan POST request, redirect ke katalog
    redirect('praktikum.php');
}
?> 