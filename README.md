# DonaCare - Sistem Pengelolaan Data Donasi

Aplikasi web untuk mencatat data donatur, mencatat transaksi donasi, dan menampilkan rekapitulasi dana secara otomatis. Dibangun dengan PHP native dan MySQL sebagai tugas Ujian Tengah Semester mata kuliah **Testing dan Implementasi Sistem** menggunakan pendekatan *vibe coding* (Claude sebagai asisten pengembangan).

| Keterangan | Isi |
|---|---|
| Nama | Frita Rosa Ramadani |
| NIM | 2305102014 |
| Kelas | 7A |
| Nomor presensi | 11 |
| Studi kasus | Sistem Pengelolaan Data Donasi |
| Dosen pengampu | Andria, S.Kom. |
| Program studi | Sistem Informasi, Universitas PGRI Madiun |

## Coba Aplikasi (tanpa instalasi)

**Link aplikasi:** [ISI SETELAH HOSTING: https://alamat-hosting-kamu]

Buka tautan di atas pada peramban. Tidak perlu login dan tidak perlu menginstal apa pun.

> Jika tautan hosting tidak dapat dibuka (misalnya hosting gratis sedang gangguan), gunakan cara menjalankan lokal di bawah.

**Repository:** https://github.com/[USERNAME-GITHUB]/donasi-app

## Fitur Utama

- **Dashboard**: ringkasan total donatur, total transaksi, total dana, rata-rata donasi, ringkasan per metode pembayaran, dan lima transaksi terbaru.
- **Data Donatur**: tambah, ubah, dan hapus donatur (nama, kontak, catatan).
- **Data Donasi**: catat, ubah, dan hapus transaksi; pencarian berdasarkan nama donatur; filter berdasarkan metode pembayaran dan rentang tanggal.
- **Rekap Donasi**: total dana, jumlah transaksi, rata-rata, rekap per metode pembayaran, rekap per bulan, dan detail transaksi.
- **Validasi input** dengan pesan galat berbahasa Indonesia.

## Aturan Validasi

| Data | Aturan |
|---|---|
| Nama donatur | Wajib diisi dan tidak boleh sama dengan donatur lain (tidak membedakan huruf besar/kecil) |
| Kontak | Wajib, hanya angka, 10-13 digit, diawali `08` |
| Donatur pada donasi | Wajib memilih donatur yang sudah terdaftar |
| Tanggal donasi | Wajib, format valid, tidak boleh melebihi hari ini |
| Nominal | Wajib berupa angka lebih dari 0 |
| Metode pembayaran | Tunai, Transfer, atau E-Wallet |
| Hapus donatur | Ditolak apabila donatur masih memiliki transaksi donasi |

## Teknologi

- PHP 8 (native, tanpa framework) dengan PDO dan *prepared statement*
- MySQL / MariaDB
- HTML, CSS, dan JavaScript (tanpa CDN, seluruh aset lokal)
- XAMPP (Apache + MySQL) untuk menjalankan di komputer lokal
- Visual Studio Code, GitHub

## Cara Menjalankan di Komputer Lokal (XAMPP)

1. **Instal XAMPP** dari [apachefriends.org](https://www.apachefriends.org), lalu buka **XAMPP Control Panel** dan klik **Start** pada **Apache** dan **MySQL**.
2. **Unduh proyek ini**: klik tombol hijau **Code**, pilih **Download ZIP**, lalu ekstrak. Folder hasil ekstrak biasanya bernama `donasi-app-main`; **ganti namanya menjadi `donasi-app`**.
3. **Salin folder `donasi-app`** ke dalam folder `htdocs` XAMPP (biasanya `C:\xampp\htdocs\`). Hasil akhirnya: `C:\xampp\htdocs\donasi-app\index.php`.
4. **Buat database**: buka `http://localhost/phpmyadmin`, klik **New / Baru**, isi nama database **`db_donasi`**, pilih collation `utf8mb4_general_ci`, lalu **Create**.
5. **Import data**: pilih database `db_donasi`, buka tab **Import**, pilih berkas **`database.sql`** dari proyek ini, lalu klik **Import / Kirim**.
6. **Buka aplikasi** di peramban: `http://localhost/donasi-app/`

### Pengaturan koneksi database

Berkas `config/koneksi.php` sudah menggunakan pengaturan bawaan XAMPP:

```php
$host = "localhost";
$db   = "db_donasi";
$user = "root";
$pass = "";
```

Ubah nilai tersebut hanya jika pengaturan MySQL Anda berbeda.

## Struktur Folder

```
donasi-app/
├── index.php              (pintu masuk dan router halaman)
├── config/
│   └── koneksi.php        (koneksi database PDO)
├── includes/
│   ├── header.php         (head HTML dan pemuatan CSS)
│   ├── sidebar.php        (menu navigasi)
│   └── footer.php
├── pages/
│   ├── dashboard.php      (ringkasan data)
│   ├── donatur.php        (kelola donatur)
│   ├── donasi.php         (kelola transaksi, cari, filter)
│   └── rekap.php          (rekapitulasi dana)
├── assets/
│   └── css/style.css      (tema biru)
├── database.sql           (struktur dan data contoh)
└── README.md
```

## Struktur Database

**Tabel `donatur`**: `id_donatur` (PK, auto increment), `nama_donatur`, `kontak`, `catatan`

**Tabel `donasi`**: `id_donasi` (PK, auto increment), `id_donatur` (FK ke `donatur`), `tanggal_donasi`, `nominal`, `metode_pembayaran`, `keterangan`

## Keterbatasan

- Aplikasi tidak memiliki autentikasi (login). Semua pengguna memiliki hak akses yang sama.
- Validasi dilakukan di sisi server dan sebagian di sisi peramban (atribut HTML).
- Belum ada fitur ekspor laporan (PDF/Excel) dan notifikasi.

## Dokumentasi Pengujian

Rencana pengujian, test case, hasil eksekusi, analisis defect, dan evaluasi kelayakan aplikasi terdapat pada **laporan UTS (PDF)** yang dikumpulkan bersama repository ini.
