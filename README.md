# Sistem Pengumpulan Tugas (STUDYKU)

STUDYKU adalah aplikasi web berbasis PHP Native untuk mengelola praktikum, modul, dan pengumpulan tugas mahasiswa.

## 🚀 Fitur Utama

TAMPILAN ANTARMUKA (UI)
LOGIN
  ![Screenshot 2025-07-02 092939](https://github.com/user-attachments/assets/098cbac2-702e-463a-b58e-53bfb7219e62)
  
REGISTER
  ![Screenshot 2025-07-02 092946](https://github.com/user-attachments/assets/ddc05561-430e-4d87-b469-8b459c987f5b)
  
DASHBOARD MAHASISWA
![Screenshot 2025-07-02 093004](https://github.com/user-attachments/assets/730d91f7-1baa-44ef-910c-c8d8bee5f14f)

PRAKTIKUM SAYA
![Screenshot 2025-07-02 093014](https://github.com/user-attachments/assets/dcc54379-3a29-42fd-9aa6-17386c23ebe7)

KATALOG MATA PRAKTIKUM
![Screenshot 2025-07-02 093021](https://github.com/user-attachments/assets/a47302e6-13fc-4e54-8fef-09b8a5d81cb3)

DASHBOARD ASISTEN
![Screenshot 2025-07-02 093053](https://github.com/user-attachments/assets/58fb7461-9db8-4862-bfec-9c2bf1498787)

KELOLA PRAKTIKUM ASISTEN
![Screenshot 2025-07-02 093100](https://github.com/user-attachments/assets/d8484693-390b-4e47-9732-65b7f5c8f05e)

KELOLA MODUL ASISTEN
![Screenshot 2025-07-02 093108](https://github.com/user-attachments/assets/9eb9fffc-455e-4b6b-b953-814f83935711)

LAPORAN MASUK ASISTEN
![Screenshot 2025-07-02 093115](https://github.com/user-attachments/assets/49e5fc6a-4691-4622-a1f4-5e2c95805576)

KELOLA AKUN 
![Screenshot 2025-07-02 093123](https://github.com/user-attachments/assets/ed52f296-e7c2-4b88-9c88-1a33bd6fddf6)




## 🛠️ Teknologi

- **Backend**: PHP Native
- **Database**: MySQL/MariaDB
- **Frontend**: HTML, CSS, JavaScript
- **Styling**: Tailwind CSS
- **Server**: Apache/Nginx

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau MariaDB 10.2
- Apache/Nginx web server
- Extensions PHP: mysqli, fileinfo

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/ramaravictor/SistemPengumpulanTugas.git
cd SistemPengumpulanTugas
```

### 2. Setup Database
1. Buat database MySQL baru dengan nama `pengumpulantugas`
2. Import file `database.sql` ke database
3. Update konfigurasi database di `config.php`

### 3. Konfigurasi
1. Edit file `config.php`:
```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'pengumpulantugas');
```

2. Pastikan folder `uploads/` memiliki permission write:
```bash
chmod 755 uploads/
chmod 755 uploads/materi/
chmod 755 uploads/laporan/
```

### 4. Akses Aplikasi
Buka browser dan akses: `http://localhost/SistemPengumpulanTugas`

## 👥 Penggunaan

### Mahasiswa
1. Register akun baru dengan role "mahasiswa"
2. Login ke sistem
3. Lihat katalog praktikum di halaman utama
4. Daftar ke praktikum yang diinginkan
5. Download materi dan upload laporan
6. Lihat nilai dan feedback dari asisten

### Asisten
1. Register akun baru dengan role "asisten"
2. Login ke sistem
3. Kelola praktikum dan modul
4. Upload materi untuk setiap modul
5. Nilai laporan mahasiswa
6. Kelola akun pengguna

## 📁 Struktur File

```
SistemPengumpulanTugas/
├── asisten/                 # Panel asisten
│   ├── dashboard.php       # Dashboard asisten
│   ├── praktikum.php       # CRUD praktikum
│   ├── modul.php          # CRUD modul
│   ├── laporan.php        # Kelola laporan
│   ├── akun.php           # CRUD akun
│   └── templates/         # Template asisten
├── mahasiswa/              # Panel mahasiswa
│   ├── dashboard.php       # Dashboard mahasiswa
│   ├── praktikum_saya.php  # Praktikum yang diikuti
│   ├── praktikum_detail.php # Detail praktikum
│   ├── upload_laporan.php  # Upload laporan
│   ├── lihat_nilai.php     # Lihat nilai
│   └── templates/         # Template mahasiswa
├── uploads/                # File uploads
│   ├── materi/            # File materi
│   └── laporan/           # File laporan
├── config.php             # Konfigurasi database
├── functions.php          # Helper functions
├── database.sql           # Database schema
├── praktikum.php          # Katalog praktikum (publik)
├── login.php              # Halaman login
├── register.php           # Halaman register
├── logout.php             # Logout
└── index.php              # Redirect ke katalog
```

## 🔐 Keamanan

- Password di-hash menggunakan `password_hash()`
- Prepared statements untuk mencegah SQL injection
- Validasi input dan sanitasi output
- Session management yang aman
- File upload validation
- Access control berdasarkan role

## 📝 Database Schema

### Tabel Utama
- `users` - Data pengguna (mahasiswa/asisten)
- `praktikum` - Data mata praktikum
- `modul` - Data modul/pertemuan
- `mahasiswa_praktikum` - Relasi mahasiswa-praktikum
- `laporan` - Data laporan mahasiswa

## 🎨 UI/UX Features

- Responsive design dengan Tailwind CSS
- Modern dashboard dengan statistik
- Modal forms untuk CRUD operations
- Progress tracking untuk mahasiswa
- Filter dan pencarian
- Status badges dan notifications

## 🔧 Customization

### Menambah Praktikum Baru
1. Login sebagai asisten
2. Buka menu "Kelola Praktikum"
3. Klik "Tambah Praktikum"
4. Isi form dan simpan

### Menambah Modul
1. Buka menu "Kelola Modul"
2. Pilih praktikum
3. Klik "Tambah Modul"
4. Upload materi dan simpan

### Mengubah Tampilan
Edit file CSS atau tambahkan custom styles di template header.

## 🐛 Troubleshooting

### Error Koneksi Database
- Periksa konfigurasi di `config.php`
- Pastikan MySQL service berjalan
- Cek username dan password database

### Error Upload File
- Periksa permission folder `uploads/`
- Pastikan ukuran file tidak melebihi limit
- Cek tipe file yang diizinkan

### Error Session
- Pastikan session sudah dimulai di setiap file
- Cek konfigurasi PHP session

## 📞 Support

Untuk bantuan dan pertanyaan, silakan buat issue di repository GitHub.

## 📄 License

Project ini dibuat untuk keperluan akademis. Silakan gunakan sesuai kebutuhan.

---

**Dibuat dengan ❤️ menggunakan PHP Native dan Tailwind CSS** 
