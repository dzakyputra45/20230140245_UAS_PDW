CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `praktikum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(200) NOT NULL,
  `deskripsi` text,
  `kode` varchar(20) NOT NULL,
  `semester` int(2) NOT NULL,
  `sks` int(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `modul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `praktikum_id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text,
  `pertemuan_ke` int(2) NOT NULL,
  `file_materi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `praktikum_id` (`praktikum_id`),
  CONSTRAINT `modul_ibfk_1` FOREIGN KEY (`praktikum_id`) REFERENCES `praktikum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mahasiswa_praktikum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` int(11) NOT NULL,
  `praktikum_id` int(11) NOT NULL,
  `status` enum('aktif','selesai') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `mahasiswa_praktikum_unique` (`mahasiswa_id`, `praktikum_id`),
  KEY `praktikum_id` (`praktikum_id`),
  CONSTRAINT `mahasiswa_praktikum_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mahasiswa_praktikum_ibfk_2` FOREIGN KEY (`praktikum_id`) REFERENCES `praktikum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `laporan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` int(11) NOT NULL,
  `modul_id` int(11) NOT NULL,
  `file_laporan` varchar(255) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `nilai` decimal(5,2) DEFAULT NULL,
  `feedback` text,
  `status` enum('menunggu','dinilai','ditolak') NOT NULL DEFAULT 'menunggu',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dinilai_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mahasiswa_id` (`mahasiswa_id`),
  KEY `modul_id` (`modul_id`),
  CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `praktikum` (`nama`, `deskripsi`, `kode`, `semester`, `sks`) VALUES
('Pemrograman Web', 'Praktikum dasar pemrograman web menggunakan HTML, CSS, dan JavaScript', 'PW001', 3, 2),
('Jaringan Komputer', 'Praktikum konfigurasi jaringan dan protokol komunikasi', 'JK002', 4, 2),
('Basis Data', 'Praktikum desain dan implementasi basis data', 'BD003', 3, 2),
('Pemrograman Berorientasi Objek', 'Praktikum konsep OOP menggunakan Java', 'PBO004', 4, 2);

INSERT INTO `modul` (`praktikum_id`, `judul`, `deskripsi`, `pertemuan_ke`, `file_materi`) VALUES
(1, 'Pengenalan HTML', 'Materi dasar HTML dan struktur dokumen web', 1, 'modul1_html.pdf'),
(1, 'CSS Dasar', 'Styling dengan CSS untuk tampilan web', 2, 'modul2_css.pdf'),
(1, 'JavaScript Dasar', 'Pemrograman client-side dengan JavaScript', 3, 'modul3_javascript.pdf'),
(1, 'Responsive Design', 'Membuat website yang responsif', 4, 'modul4_responsive.pdf');

INSERT INTO `modul` (`praktikum_id`, `judul`, `deskripsi`, `pertemuan_ke`, `file_materi`) VALUES
(2, 'Pengenalan Jaringan', 'Konsep dasar jaringan komputer', 1, 'modul1_jaringan.pdf'),
(2, 'IP Addressing', 'Konfigurasi IP address dan subnetting', 2, 'modul2_ip.pdf'),
(2, 'Routing Dasar', 'Konfigurasi routing statis dan dinamis', 3, 'modul3_routing.pdf'),
(2, 'Network Security', 'Keamanan jaringan dan firewall', 4, 'modul4_security.pdf');