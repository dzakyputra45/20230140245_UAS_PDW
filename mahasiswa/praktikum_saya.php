<?php
require_once '../functions.php';

// Cek login
if (!isLoggedIn() || !isMahasiswa()) {
    redirect('../login.php', 'Silakan login sebagai mahasiswa terlebih dahulu.', 'error');
}

$userId = $_SESSION['user_id'];

// Ambil praktikum yang diikuti mahasiswa
$stmt = $conn->prepare("SELECT p.*, mp.status as enrollment_status, mp.created_at as enrollment_date,
                               (SELECT COUNT(*) FROM modul WHERE praktikum_id = p.id) as total_modul,
                               (SELECT COUNT(*) FROM laporan l 
                                JOIN modul m ON l.modul_id = m.id 
                                WHERE m.praktikum_id = p.id AND l.mahasiswa_id = ? AND l.status = 'dinilai') as tugas_selesai
                        FROM praktikum p 
                        JOIN mahasiswa_praktikum mp ON p.id = mp.praktikum_id 
                        WHERE mp.mahasiswa_id = ? 
                        ORDER BY mp.created_at DESC");
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$praktikumList = $stmt->get_result();

$pageTitle = 'Praktikum Saya';
$activePage = 'praktikum_saya';
require_once 'templates/header_mahasiswa.php'; 
?>

<div class="bg-gradient-to-r from-green-400 to-emerald-500 text-white p-8 rounded-3xl shadow-2xl mb-8 flex flex-col md:flex-row items-center justify-between gap-6">
    <div>
        <h1 class="text-3xl md:text-4xl font-extrabold drop-shadow-lg">Praktikum Saya</h1>
        <p class="mt-2 opacity-90 text-lg font-medium">Kelola dan pantau progress praktikum yang Anda ikuti 📚</p>
    </div>
    <img src="https://img.icons8.com/color/96/000000/classroom.png" class="h-20 w-20 md:h-28 md:w-28 drop-shadow-xl" alt="Praktikum"/>
</div>

<?php echo showMessage(); ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php if ($praktikumList->num_rows > 0): ?>
        <?php while ($praktikum = $praktikumList->fetch_assoc()): ?>
            <div class="glass rounded-2xl shadow-xl border border-green-100 hover:shadow-2xl transition-all duration-300 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full shadow"> <?php echo htmlspecialchars($praktikum['kode']); ?> </span>
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-semibold shadow"> <?php echo $praktikum['sks']; ?> SKS </span>
                    </div>
                    <h3 class="text-xl font-extrabold text-gray-900 mb-2 flex items-center gap-2">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0 0H6m6 0h6" /></svg>
                        <?php echo htmlspecialchars($praktikum['nama']); ?>
                    </h3>
                    <p class="text-gray-600 mb-4 line-clamp-2 min-h-[40px]">
                        <?php echo htmlspecialchars($praktikum['deskripsi']); ?>
                    </p>
                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                        <div class="text-gray-500">
                            <span class="block font-medium">Semester</span>
                            <span><?php echo $praktikum['semester']; ?></span>
                        </div>
                        <div class="text-gray-500">
                            <span class="block font-medium">SKS</span>
                            <span><?php echo $praktikum['sks']; ?></span>
                        </div>
                        <div class="text-gray-500">
                            <span class="block font-medium">Total Modul</span>
                            <span><?php echo $praktikum['total_modul']; ?></span>
                        </div>
                        <div class="text-gray-500">
                            <span class="block font-medium">Tugas Selesai</span>
                            <span><?php echo $praktikum['tugas_selesai']; ?></span>
                        </div>
                    </div>
                    <?php if ($praktikum['total_modul'] > 0): ?>
                        <?php $progress = ($praktikum['tugas_selesai'] / $praktikum['total_modul']) * 100; ?>
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Progress</span>
                                <span><?php echo round($progress); ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-sm text-gray-500 flex items-center gap-1">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" /></svg>
                        Terdaftar: <?php echo formatDate($praktikum['enrollment_date']); ?>
                    </span>
                    <a href="praktikum_detail.php?id=<?php echo $praktikum['id']; ?>" 
                       class="bg-gradient-to-r from-green-500 to-green-400 text-white px-4 py-2 rounded-lg shadow hover:from-green-600 hover:to-green-500 transition text-sm font-bold">
                        Lihat Detail
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-span-full text-center py-12">
            <div class="text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada praktikum</h3>
                <p class="mt-1 text-sm text-gray-500">Anda belum mendaftar ke praktikum apapun.</p>
                <div class="mt-6">
                    <a href="../praktikum.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-blue-500 to-cyan-400 hover:from-blue-600 hover:to-cyan-500">
                        Lihat Katalog Praktikum
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?> 