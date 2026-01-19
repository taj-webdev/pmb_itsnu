-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 15 Nov 2025 pada 05.14
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
-- Database: `pmb_itsnu`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokumen_pendaftar`
--

CREATE TABLE `dokumen_pendaftar` (
  `id` int(11) NOT NULL,
  `pendaftar_id` int(11) NOT NULL,
  `pasfoto` varchar(255) DEFAULT NULL,
  `kartu_keluarga` varchar(255) DEFAULT NULL,
  `ktp` varchar(255) DEFAULT NULL,
  `ijazah` varchar(255) DEFAULT NULL,
  `raport` varchar(255) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dokumen_pendaftar`
--

INSERT INTO `dokumen_pendaftar` (`id`, `pendaftar_id`, `pasfoto`, `kartu_keluarga`, `ktp`, `ijazah`, `raport`, `bukti_pembayaran`, `created_at`) VALUES
(1, 1, '1762927609_pendaftar1762920458_BRAVO.jpg', '1762927609_pendaftar1762920458_JELI.jpg', '1762927609_pendaftar1762920458_BRAVO.jpg', '1762927609_pendaftar1762920458_JELI.jpg', '1762927609_pendaftar1762920458_BRAVO.jpg', '1762927609_pendaftar1762920458_JELI.jpg', '2025-11-12 06:06:49'),
(2, 2, '1762928261_pendaftar1762920458_BRAVO.jpg', '1762928261_pendaftar1762920458_JELI.jpg', '1762928261_pendaftar1762920458_JELI.jpg', '1762928261_pendaftar1762920458_JELI.jpg', '1762928261_pendaftar1762920458_BRAVO.jpg', '1762928261_pendaftar1762927609_pendaftar1762920458_BRAVO.jpg', '2025-11-12 06:17:41'),
(3, 3, '1763175631_FLCN_KyleTzy_S15.png', '1763175631_KK.PNG', '1763175631_KTP.png', '1763175631_IJAZAH.png', '1763175631_RAPORT.png', '1763175631_INVOICE.png', '2025-11-15 03:00:31'),
(4, 4, '1763176388_ONPH_Super_Frince_S16.png', '1763176388_KK.PNG', '1763176389_KTP.png', '1763176389_IJAZAH.png', '1763176389_RAPORT.png', '1763176389_INVOICE.png', '2025-11-15 03:13:09'),
(5, 5, '1763176955_TNC_Yawi_S15.png', '1763176955_KK.PNG', '1763176955_KTP.png', '1763176955_IJAZAH.png', '1763176955_RAPORT.png', '1763176955_INVOICE.png', '2025-11-15 03:22:35');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `laporan_pendaftar`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `laporan_pendaftar` (
`nama_lengkap` varchar(100)
,`nisn` varchar(20)
,`email` varchar(100)
,`asal_sekolah` varchar(100)
,`minat_prodi` enum('S-1 Teknik Industri','S-1 Teknik Komputer','S-1 Teknik Lingkungan')
,`status_pendaftaran` enum('pending','approved','rejected')
,`tanggal_daftar` timestamp
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `aktivitas` text DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pesan` text NOT NULL,
  `tipe` enum('akun','pendaftaran','dokumen','info') DEFAULT 'info',
  `status_baca` enum('belum_dibaca','dibaca') DEFAULT 'belum_dibaca',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `user_id`, `pesan`, `tipe`, `status_baca`, `created_at`) VALUES
(2, 1, 'Akun baru terdaftar: Michael Angelo Sayson (kyle)', 'akun', 'belum_dibaca', '2025-11-14 09:00:17'),
(3, 5, 'Akun kamu berhasil dibuat dan sedang menunggu persetujuan admin.', 'akun', 'belum_dibaca', '2025-11-14 09:00:17'),
(4, 1, 'Akun baru terdaftar: Frince Miguel Ramirez (frince)', 'akun', 'belum_dibaca', '2025-11-15 02:36:11'),
(5, 6, 'Akun kamu berhasil dibuat dan sedang menunggu persetujuan admin.', 'akun', 'belum_dibaca', '2025-11-15 02:36:11'),
(6, 1, 'Akun baru terdaftar: Tristan Cabrera (yawi)', 'akun', 'belum_dibaca', '2025-11-15 03:14:10'),
(7, 7, 'Akun kamu berhasil dibuat dan sedang menunggu persetujuan admin.', 'akun', 'belum_dibaca', '2025-11-15 03:14:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pendaftar`
--

CREATE TABLE `pendaftar` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_wa` varchar(20) DEFAULT NULL,
  `asal_sekolah` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `hobby` varchar(100) DEFAULT NULL,
  `minat_bakat` varchar(100) DEFAULT NULL,
  `kompetisi` varchar(255) DEFAULT NULL,
  `prestasi_akademik` text DEFAULT NULL,
  `prestasi_non_akademik` text DEFAULT NULL,
  `minat_prodi` enum('S-1 Teknik Industri','S-1 Teknik Komputer','S-1 Teknik Lingkungan') DEFAULT NULL,
  `pesantren_status` enum('Pernah','Tidak Pernah','Minat','Belum Minat') DEFAULT NULL,
  `info_pendaftaran` text DEFAULT NULL,
  `status_pendaftaran` enum('pending','approved','rejected') DEFAULT 'pending',
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pendaftar`
--

INSERT INTO `pendaftar` (`id`, `user_id`, `nisn`, `email`, `no_wa`, `asal_sekolah`, `alamat`, `tempat_lahir`, `tanggal_lahir`, `hobby`, `minat_bakat`, `kompetisi`, `prestasi_akademik`, `prestasi_non_akademik`, `minat_prodi`, `pesantren_status`, `info_pendaftaran`, `status_pendaftaran`, `tanggal_daftar`) VALUES
(1, 2, '03034040', 'baronnd@gmail.com', '081120203030', 'SMAN 1 DUSUN SELATAN', 'JL. PANGLIMA BATUR GG. KEPASTURAN', 'BUNTOK', '2002-02-20', 'MEMANCING', 'NGODING', 'NGODING PHYTON, PHP &amp; JAVA', 'JUARA 1 NGODING WEBSITE ABSENSI', 'M7 WORLD CHAMPIONS', 'S-1 Teknik Komputer', 'Belum Minat', 'SOSMED', 'approved', '2025-11-12 06:05:51'),
(2, 3, '020201010', 'loorandspoofy@gmail.com', '081230304040', 'SMKN 3 PALANGKA RAYA', 'JL. P. SAMUDRA III', 'WESTALIS', '2002-11-01', 'JADI AGEN INTEL', 'DEADLY SNIPPING', 'PENEMBAK JITU KELAS KAKAP', 'JUARA 1 OLIMPIADE NETWORK ENGINEERING', 'ELITE SNIPER WORLD CHAMPIONS', 'S-1 Teknik Lingkungan', 'Tidak Pernah', 'DARI INTEL', 'approved', '2025-11-12 06:16:59'),
(3, 5, '04041010', 'kylesayson@gmail.com', '081520102050', 'SMAN 3 PALANGKA RAYA', 'JL. YOS SUDARSO VI', 'MINDANAO', '2005-08-19', 'GAMING', 'E-SPORT', 'MPL PH', 'JUARA 1 OLIMPIADE BAHASA INGGRIS', 'M5 WORLD CHAMPIONS, MPL PH S12 CHAMPIONS', 'S-1 Teknik Lingkungan', 'Tidak Pernah', 'SOSMED', 'approved', '2025-11-15 02:59:40'),
(4, 6, '05057070', 'frinceramirez@gmail.com', '082156657070', 'SMKN 2 PALANGKA RAYA', 'JL. GARUDA V', 'MANILA', '2003-11-22', 'GAMING', 'E-SPORT', 'MPL PH', 'JUARA 1 KONTES MELUKIS ALAM', 'M6 WORLD CHAMPIONS, MPL PH S14 CHAMPIONS', 'S-1 Teknik Komputer', 'Belum Minat', 'ORDAL', 'approved', '2025-11-15 03:11:39'),
(5, 7, '060602525', 'yawigaming@gmail.com', '082230491624', 'SMAN 1 PALANGKA RAYA', 'JL. KINIBALU NO. 21', 'MANILA', '2001-12-02', 'GAMING', 'E-SPORT', 'MPL PH, MPL ID, HOK PH', 'JUARA 1 NGODING PYHTON', 'M4 WORLD CHAMPIONS, MPL PH S11 CHAMPIONS, MPL ID S14 CHAMPIONS', 'S-1 Teknik Komputer', 'Minat', 'ORDAL JUGA', 'approved', '2025-11-15 03:20:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','calon_mahasiswa') NOT NULL DEFAULT 'calon_mahasiswa',
  `status_akun` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `username`, `password`, `role`, `status_akun`, `created_at`) VALUES
(1, 'Tertu Akikkuti Jordan', 'tertu', '$2y$10$fs/QSnBIk2hkv93aCiogau8XxTXGQLHsBloluQA3yftaDeHefHnZK', 'admin', 'approved', '2025-11-05 07:17:02'),
(2, 'Ronaldo Dwi Anaku Aminu', 'ronaldo', '$2y$10$OyXtHviBGOgIHnc00WGZe.hB/SjJZ3vahQUuoKofmmT2oXN6pD4V2', 'calon_mahasiswa', 'approved', '2025-11-05 07:22:43'),
(3, 'Loorand Spoofy', 'loorand', '$2y$10$9UcKy1U.3ghk3pQeetZioO6qzkF4P.lVCrlzBIdUrN9TLznEhf3bW', 'calon_mahasiswa', 'approved', '2025-11-12 06:11:00'),
(5, 'Michael Angelo Sayson', 'kyle', '$2y$10$sVwKCplBbquPavQVU0zO1e9X643zNvmR4ryt6tyCh.bXjQT/qw/Y.', 'calon_mahasiswa', 'approved', '2025-11-14 09:00:17'),
(6, 'Frince Miguel Ramirez', 'frince', '$2y$10$WmHwDjR2iC6nPr2ttY7V7.BgDMSb5/shXVR8pXozQoIEkjf84dl8u', 'calon_mahasiswa', 'approved', '2025-11-15 02:36:11'),
(7, 'Tristan Cabrera', 'yawi', '$2y$10$V5lfgHwR4qXHjaV8m3zKb.LNjrfRLzc4LguGuFglLIqccyxjLnbii', 'calon_mahasiswa', 'approved', '2025-11-15 03:14:10');

-- --------------------------------------------------------

--
-- Struktur untuk view `laporan_pendaftar`
--
DROP TABLE IF EXISTS `laporan_pendaftar`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `laporan_pendaftar`  AS SELECT `u`.`nama_lengkap` AS `nama_lengkap`, `p`.`nisn` AS `nisn`, `p`.`email` AS `email`, `p`.`asal_sekolah` AS `asal_sekolah`, `p`.`minat_prodi` AS `minat_prodi`, `p`.`status_pendaftaran` AS `status_pendaftaran`, `p`.`tanggal_daftar` AS `tanggal_daftar` FROM (`users` `u` join `pendaftar` `p` on(`u`.`id` = `p`.`user_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `dokumen_pendaftar`
--
ALTER TABLE `dokumen_pendaftar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftar_id` (`pendaftar_id`);

--
-- Indeks untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `pendaftar`
--
ALTER TABLE `pendaftar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `dokumen_pendaftar`
--
ALTER TABLE `dokumen_pendaftar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pendaftar`
--
ALTER TABLE `pendaftar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `dokumen_pendaftar`
--
ALTER TABLE `dokumen_pendaftar`
  ADD CONSTRAINT `dokumen_pendaftar_ibfk_1` FOREIGN KEY (`pendaftar_id`) REFERENCES `pendaftar` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pendaftar`
--
ALTER TABLE `pendaftar`
  ADD CONSTRAINT `pendaftar_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
