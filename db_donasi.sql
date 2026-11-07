-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Nov 2026 pada 17.19
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_donasi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `donasi`
--

CREATE TABLE `donasi` (
  `id_donasi` int(11) NOT NULL,
  `id_donatur` int(11) NOT NULL,
  `tanggal_donasi` date NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `metode_pembayaran` varchar(30) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `donasi`
--

INSERT INTO `donasi` (`id_donasi`, `id_donatur`, `tanggal_donasi`, `nominal`, `metode_pembayaran`, `keterangan`, `created_at`) VALUES
(4, 5, '2026-08-12', 100000.00, 'Tunai', 'Bantuan pendidikan anak kurang mampu', '2026-11-07 02:13:39'),
(5, 6, '2026-09-05', 250000.00, 'Transfer', 'Donasi kegiatan sosial masyarakat', '2026-11-07 02:14:55'),
(6, 7, '2026-10-18', 75000.00, 'E-Wallet', 'Donasi kebutuhan pokok warga', '2026-11-07 02:15:34'),
(7, 8, '2026-11-02', 150000.00, 'Transfer', 'Bantuan untuk bantuan kesehatan', '2026-11-07 02:16:26'),
(8, 9, '2026-08-25', 300000.00, 'Tunai', 'Bantuan pembangunan fasilitas umum', '2026-11-07 02:17:16'),
(9, 10, '2026-09-21', 125000.00, 'E-Wallet', 'Donasi kegiatan kemanusiaan', '2026-11-07 02:23:45'),
(10, 11, '2026-10-07', 200000.00, 'Transfer', 'Bantuan perlengkapan sekolah', '2026-11-07 02:24:26'),
(11, 12, '2026-11-04', 175000.00, 'Tunai', 'Donasi untuk santuan anak yatim', '2026-11-07 02:24:58'),
(12, 13, '2026-10-29', 50000.00, 'E-Wallet', 'Bantuan sosial untuk masyarakat', '2026-11-07 02:25:32'),
(13, 14, '2026-09-14', 400000.00, 'Transfer', 'Donasi program pemberdayaan masyarakat', '2026-11-07 02:26:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `donatur`
--

CREATE TABLE `donatur` (
  `id_donatur` int(11) NOT NULL,
  `nama_donatur` varchar(100) NOT NULL,
  `kontak` varchar(15) NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `donatur`
--

INSERT INTO `donatur` (`id_donatur`, `nama_donatur`, `kontak`, `catatan`, `created_at`, `updated_at`) VALUES
(5, 'Andi Saputra', '081234567890', 'Donatur Umum', '2026-11-07 02:07:33', '2026-11-07 02:07:33'),
(6, 'Siti Rahmawati', '082345678901', 'Donatur Tetap', '2026-11-07 02:08:08', '2026-11-07 02:08:08'),
(7, 'Budi Santoso', '08345678912', 'Donatur Umum', '2026-11-07 02:08:42', '2026-11-07 02:08:42'),
(8, 'Dewi Anggraini', '085678901234', 'Donatur Umum', '2026-11-07 02:09:17', '2026-11-07 02:09:17'),
(9, 'Rizky Pratama', '081987654321', 'Donatur Tetap', '2026-11-07 02:09:49', '2026-11-07 02:09:49'),
(10, 'Nur Aisyah', '082198765432', 'Donatur Umum', '2026-11-07 02:10:29', '2026-11-07 02:10:29'),
(11, 'Dimas Kurniawan', '083198765432', 'Donatur Umum', '2026-11-07 02:10:59', '2026-11-07 02:10:59'),
(12, 'Rina Oktaviani', '085712345678', 'Donatur Tetap', '2026-11-07 02:11:37', '2026-11-07 02:11:37'),
(13, 'Agus Setiawan', '081356789012', 'Donatur Umum', '2026-11-07 02:12:03', '2026-11-07 02:12:03'),
(14, 'Maya Putri', '082156789012', 'Donatur Umum', '2026-11-07 02:12:28', '2026-11-07 02:12:28');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `donasi`
--
ALTER TABLE `donasi`
  ADD PRIMARY KEY (`id_donasi`),
  ADD KEY `fk_donasi_donatur` (`id_donatur`);

--
-- Indeks untuk tabel `donatur`
--
ALTER TABLE `donatur`
  ADD PRIMARY KEY (`id_donatur`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `donasi`
--
ALTER TABLE `donasi`
  MODIFY `id_donasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `donatur`
--
ALTER TABLE `donatur`
  MODIFY `id_donatur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `donasi`
--
ALTER TABLE `donasi`
  ADD CONSTRAINT `fk_donasi_donatur` FOREIGN KEY (`id_donatur`) REFERENCES `donatur` (`id_donatur`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
