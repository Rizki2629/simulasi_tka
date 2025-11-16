-- Database Backup: simulasi_tka
-- Generated: 2025-11-16 16:05:12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- Table structure for table `cache`
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `cache_locks`
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `failed_jobs`
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `jawaban_peserta`
DROP TABLE IF EXISTS `jawaban_peserta`;
CREATE TABLE `jawaban_peserta` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `simulasi_peserta_id` bigint unsigned NOT NULL,
  `soal_id` bigint unsigned NOT NULL,
  `pilihan_jawaban_id` bigint unsigned DEFAULT NULL,
  `jawaban_teks` text COLLATE utf8mb4_unicode_ci,
  `jawaban_multiple` json DEFAULT NULL,
  `is_benar` tinyint(1) DEFAULT NULL,
  `skor` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jawaban_peserta_simulasi_peserta_id_soal_id_unique` (`simulasi_peserta_id`,`soal_id`),
  KEY `jawaban_peserta_soal_id_foreign` (`soal_id`),
  KEY `jawaban_peserta_pilihan_jawaban_id_foreign` (`pilihan_jawaban_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `job_batches`
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `jobs`
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `mata_pelajaran`
DROP TABLE IF EXISTS `mata_pelajaran`;
CREATE TABLE `mata_pelajaran` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `mata_pelajaran`
INSERT INTO `mata_pelajaran` VALUES ('1', 'Matematika', 'MAT', NULL, '1', '2025-11-15 06:18:24', '2025-11-15 06:18:24');
INSERT INTO `mata_pelajaran` VALUES ('2', 'Matematika', 'MTK', 'Mata Pelajaran Matematika untuk kelas 6 SD', '1', '2025-11-16 00:55:28', '2025-11-16 00:55:28');
INSERT INTO `mata_pelajaran` VALUES ('3', 'Bahasa Indonesia', 'BIN', 'Mata Pelajaran Bahasa Indonesia untuk kelas 6 SD', '1', '2025-11-16 00:55:28', '2025-11-16 00:55:28');
INSERT INTO `mata_pelajaran` VALUES ('4', 'IPA', 'IPA', 'Mata Pelajaran Ilmu Pengetahuan Alam untuk kelas 6 SD', '1', '2025-11-16 00:55:28', '2025-11-16 00:55:28');
INSERT INTO `mata_pelajaran` VALUES ('5', 'IPS', 'IPS', 'Mata Pelajaran Ilmu Pengetahuan Sosial untuk kelas 6 SD', '1', '2025-11-16 00:55:28', '2025-11-16 00:55:28');
INSERT INTO `mata_pelajaran` VALUES ('6', 'PKN', 'PKN', 'Mata Pelajaran Pendidikan Kewarganegaraan untuk kelas 6 SD', '1', '2025-11-16 00:55:28', '2025-11-16 00:55:28');


-- Table structure for table `migrations`
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `migrations`
INSERT INTO `migrations` VALUES ('1', '0001_01_01_000000_create_users_table', '1');
INSERT INTO `migrations` VALUES ('2', '0001_01_01_000001_create_cache_table', '1');
INSERT INTO `migrations` VALUES ('3', '0001_01_01_000002_create_jobs_table', '1');
INSERT INTO `migrations` VALUES ('4', '2025_11_13_120827_add_role_to_users_table', '1');
INSERT INTO `migrations` VALUES ('5', '2025_11_13_120833_add_role_to_users_table', '1');
INSERT INTO `migrations` VALUES ('6', '2025_11_14_000001_add_student_fields_to_users_table', '1');
INSERT INTO `migrations` VALUES ('7', '2025_11_15_000001_create_tokens_table', '1');
INSERT INTO `migrations` VALUES ('8', '2025_11_15_000002_create_mata_pelajaran_table', '1');
INSERT INTO `migrations` VALUES ('9', '2025_11_15_000003_create_soal_table', '1');
INSERT INTO `migrations` VALUES ('10', '2025_11_15_000004_create_pilihan_jawaban_table', '1');
INSERT INTO `migrations` VALUES ('11', '2025_11_15_000005_create_simulasi_table', '1');
INSERT INTO `migrations` VALUES ('12', '2025_11_15_000006_create_simulasi_soal_table', '1');
INSERT INTO `migrations` VALUES ('13', '2025_11_15_000007_create_simulasi_peserta_table', '1');
INSERT INTO `migrations` VALUES ('14', '2025_11_15_000008_create_jawaban_peserta_table', '1');
INSERT INTO `migrations` VALUES ('15', '2025_11_16_015954_add_pembahasan_to_soal_table', '2');


-- Table structure for table `password_reset_tokens`
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `pilihan_jawaban`
DROP TABLE IF EXISTS `pilihan_jawaban`;
CREATE TABLE `pilihan_jawaban` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `soal_id` bigint unsigned NOT NULL,
  `label` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teks_jawaban` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `gambar_jawaban` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_benar` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pilihan_jawaban_soal_id_foreign` (`soal_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `pilihan_jawaban`
INSERT INTO `pilihan_jawaban` VALUES ('6', '1', 'B', 'JAWABAN B', NULL, '1', '2025-11-16 02:46:49', '2025-11-16 02:46:49');
INSERT INTO `pilihan_jawaban` VALUES ('5', '1', 'A', 'JAWABAN A', NULL, '0', '2025-11-16 02:46:49', '2025-11-16 02:46:49');
INSERT INTO `pilihan_jawaban` VALUES ('7', '1', 'C', 'JAWABAN C', 'jawaban/p5qnWRTwijXhlV2XVumwSvqigttoPIz85nnTySLh.png', '0', '2025-11-16 02:46:51', '2025-11-16 02:46:51');
INSERT INTO `pilihan_jawaban` VALUES ('8', '1', 'D', 'JAWABAN A D', NULL, '0', '2025-11-16 02:46:51', '2025-11-16 02:46:51');


-- Table structure for table `sessions`
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `sessions`
INSERT INTO `sessions` VALUES ('sjoTyXeUYhpfD0v97byNsdpxtTYiwX662itVaN7A', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiR3dFWnlCczhLRmFZeUk5Uks0WGxSUWljZExqUk5pVWhZZXpGNm1rViI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zb2FsLzEvZWRpdCI7czo1OiJyb3V0ZSI7czo5OiJzb2FsLmVkaXQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjEwOiJzdHVkZW50X2lkIjtpOjY7czoxMjoic3R1ZGVudF9uYW1lIjtzOjE5OiJBaG1hZCBSaXpraSBQcmF0YW1hIjtzOjEyOiJzdHVkZW50X25pc24iO3M6MTA6IjAwNTEyMzQ1NjciO3M6OToiZXhhbV9kYXRhIjthOjQ6e3M6MTM6ImplbmlzX2tlbGFtaW4iO3M6MToiTCI7czoxMDoibWF0YV91amlhbiI7czoxMDoiTWF0ZW1hdGlrYSI7czoxMjoibmFtYV9wZXNlcnRhIjtzOjE5OiJBSE1BRCBSSVpLSSBQUkFUQU1BIjtzOjEzOiJ0YW5nZ2FsX2xhaGlyIjtzOjEwOiIyMDEzLTAxLTE1Ijt9czoxMjoiZXhhbV9hbnN3ZXJzIjthOjE6e2k6MTtzOjE6IkQiO319', '1763261214');
INSERT INTO `sessions` VALUES ('3e7URPUI0WgUvM7Xzv9PCIv8CNMgeSO0hjtzDg2J', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicHNCblBaWkxOZXBSaU1ZeDhhYVRUZ0xHdzcwM3FwUUZrWVM2S3plQiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1763303981');


-- Table structure for table `simulasi`
DROP TABLE IF EXISTS `simulasi`;
CREATE TABLE `simulasi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_simulasi` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `mata_pelajaran_id` bigint unsigned NOT NULL,
  `waktu_mulai` datetime NOT NULL,
  `waktu_selesai` datetime NOT NULL,
  `durasi_menit` int NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `simulasi_mata_pelajaran_id_foreign` (`mata_pelajaran_id`),
  KEY `simulasi_created_by_foreign` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `simulasi`
INSERT INTO `simulasi` VALUES ('1', 'SIMULASI MATEMATIKA 1', NULL, '1', '2025-11-16 08:16:00', '2025-11-16 11:19:00', '90', '1', '1', '2025-11-16 01:17:05', '2025-11-16 01:17:05');


-- Table structure for table `simulasi_peserta`
DROP TABLE IF EXISTS `simulasi_peserta`;
CREATE TABLE `simulasi_peserta` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `simulasi_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `status` enum('belum_mulai','sedang_mengerjakan','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_mulai',
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `nilai` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `simulasi_peserta_simulasi_id_user_id_unique` (`simulasi_id`,`user_id`),
  KEY `simulasi_peserta_user_id_foreign` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `simulasi_soal`
DROP TABLE IF EXISTS `simulasi_soal`;
CREATE TABLE `simulasi_soal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `simulasi_id` bigint unsigned NOT NULL,
  `soal_id` bigint unsigned NOT NULL,
  `urutan` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `simulasi_soal_simulasi_id_soal_id_unique` (`simulasi_id`,`soal_id`),
  KEY `simulasi_soal_soal_id_foreign` (`soal_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `simulasi_soal`
INSERT INTO `simulasi_soal` VALUES ('1', '1', '1', '1', '2025-11-16 01:17:05', '2025-11-16 01:17:05');


-- Table structure for table `soal`
DROP TABLE IF EXISTS `soal`;
CREATE TABLE `soal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_soal` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mata_pelajaran_id` bigint unsigned NOT NULL,
  `jenis_soal` enum('pilihan_ganda','benar_salah','mcma','isian','uraian') COLLATE utf8mb4_unicode_ci NOT NULL,
  `pertanyaan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `gambar_pertanyaan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jawaban_benar` text COLLATE utf8mb4_unicode_ci,
  `pembahasan` text COLLATE utf8mb4_unicode_ci,
  `gambar_pembahasan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kunci_jawaban` text COLLATE utf8mb4_unicode_ci,
  `bobot` int NOT NULL DEFAULT '1',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `soal_kode_soal_unique` (`kode_soal`),
  KEY `soal_mata_pelajaran_id_foreign` (`mata_pelajaran_id`),
  KEY `soal_created_by_foreign` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `soal`
INSERT INTO `soal` VALUES ('1', 'MAT-20251115-061824-1', '1', 'pilihan_ganda', 'adada', NULL, 'B', NULL, NULL, NULL, '1', '1', '2025-11-15 06:18:24', '2025-11-16 02:46:51');


-- Table structure for table `tokens`
DROP TABLE IF EXISTS `tokens`;
CREATE TABLE `tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `tokens`
INSERT INTO `tokens` VALUES ('1', 'QTPGLC', '2025-11-15 01:22:24', '0', '2025-11-15 00:22:24', '2025-11-15 00:22:27');
INSERT INTO `tokens` VALUES ('2', 'SSNFWR', '2025-11-15 01:22:27', '0', '2025-11-15 00:22:27', '2025-11-15 00:22:30');
INSERT INTO `tokens` VALUES ('3', 'VBLLVE', '2025-11-15 01:22:30', '1', '2025-11-15 00:22:30', '2025-11-15 00:22:30');
INSERT INTO `tokens` VALUES ('4', 'FEFTSH', '2025-11-15 06:28:17', '1', '2025-11-15 05:28:17', '2025-11-15 05:28:17');
INSERT INTO `tokens` VALUES ('5', 'QNFGLQ', '2025-11-16 01:55:40', '1', '2025-11-16 00:55:40', '2025-11-16 00:55:40');


-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nisn` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `rombongan_belajar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'siswa',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`
INSERT INTO `users` VALUES ('1', 'Florence Shaw', 'florence@untitledul.com', NULL, NULL, NULL, NULL, 'admin', NULL, '$2y$12$ujcxHxA2pRVbPsfVmY2ru.h7sIS7IMCmteJ7ghY3MEFf2/g7MeLRm', NULL, '2025-11-15 00:21:06', '2025-11-15 00:21:06');
INSERT INTO `users` VALUES ('2', 'Amélie Laurent', 'amelie@untitledul.com', NULL, NULL, NULL, NULL, 'admin', NULL, '$2y$12$F8/jhrW9AfMYXYjN2KPvVOFoJ9iDrlzpPBPelm/wy8aBeFlEcfij2', NULL, '2025-11-15 00:21:06', '2025-11-15 00:21:06');
INSERT INTO `users` VALUES ('3', 'Ammar Foley', NULL, NULL, NULL, NULL, NULL, 'guru', NULL, '$2y$12$h/i6Coxg4uEkMDWN5rD5lubmy1EDeOAQ3lZlfFl9QS16U7nq54CvO', NULL, '2025-11-15 00:21:06', '2025-11-15 00:21:06');
INSERT INTO `users` VALUES ('4', 'Caitlyn King', NULL, NULL, NULL, NULL, NULL, 'guru', NULL, '$2y$12$9ZqQb5LW5RICXVscZIJGu.GUykDIFL8nGZIRdBgmGnuIKhRv2tbh2', NULL, '2025-11-15 00:21:06', '2025-11-15 00:21:06');
INSERT INTO `users` VALUES ('5', 'Sienna Hewitt', NULL, NULL, NULL, NULL, NULL, 'guru', NULL, '$2y$12$ZeIy90YQlF5il42yBBMu4.WacePJ3WHrupsPIH6cvccNltd7/74Vu', NULL, '2025-11-15 00:21:07', '2025-11-15 00:21:07');
INSERT INTO `users` VALUES ('6', 'Ahmad Rizki Pratama', NULL, '0051234567', 'Jakarta', '2013-01-15', '6A', 'siswa', NULL, '$2y$12$UrqgpOdJsT4ZfTZ35BWLl.hvE3Nz8tAzXYBoCgAHB/7NSU2gABI3G', NULL, '2025-11-15 00:21:07', '2025-11-15 00:21:07');
INSERT INTO `users` VALUES ('7', 'Siti Nurhaliza', NULL, '0051234568', 'Bandung', '2013-02-20', '6A', 'siswa', NULL, '$2y$12$.RYEtPKnUhNcummA9d8Tbuq4nlLV2NF3BLkOBvBJrGZm8E.FVsQ5a', NULL, '2025-11-15 00:21:07', '2025-11-15 00:21:07');
INSERT INTO `users` VALUES ('8', 'Budi Santoso', NULL, '0051234569', 'Surabaya', '2013-03-10', '6A', 'siswa', NULL, '$2y$12$Wxzjxp0T9Qmu1VuDnuAwNeB4LFeMmOklM5N/nT/D3e0ArZ4QqckNm', NULL, '2025-11-15 00:21:07', '2025-11-15 00:21:07');
INSERT INTO `users` VALUES ('9', 'Dewi Lestari', NULL, '0051234570', 'Yogyakarta', '2013-04-05', '6A', 'siswa', NULL, '$2y$12$BWzUCv5iHgB97L/P3UNZE./np8R7KPnco0D/AEwaKnmQ5IWR46QJy', NULL, '2025-11-15 00:21:07', '2025-11-15 00:21:07');
INSERT INTO `users` VALUES ('10', 'Eko Prasetyo', NULL, '0051234571', 'Semarang', '2013-05-12', '6A', 'siswa', NULL, '$2y$12$HOzRCOXQFjGmi7O7iCPCP.jxlK6D.R6Q9M7dESv/5XtmE29eTSwfC', NULL, '2025-11-15 00:21:08', '2025-11-15 00:21:08');
INSERT INTO `users` VALUES ('11', 'Fitri Handayani', NULL, '0051234572', 'Medan', '2013-06-18', '6B', 'siswa', NULL, '$2y$12$Z/.QJ3XXrLouQyYK8G52IOr55mJreC3Gr2s6zknfxpWLexgeSRH3O', NULL, '2025-11-15 00:21:08', '2025-11-15 00:21:08');
INSERT INTO `users` VALUES ('12', 'Gilang Ramadhan', NULL, '0051234573', 'Palembang', '2013-07-22', '6B', 'siswa', NULL, '$2y$12$MpWF8qUO7WagTDLveOOLfeVJscI4c12NsbV5KLoLdmbqCbffTYQCa', NULL, '2025-11-15 00:21:08', '2025-11-15 00:21:08');
INSERT INTO `users` VALUES ('13', 'Hana Safitri', NULL, '0051234574', 'Makassar', '2013-08-14', '6B', 'siswa', NULL, '$2y$12$SK9AI4nJU7PS1L47BHz3VO.Mgk6YHxAoHMowF6QlbzAUCEbwqCq0O', NULL, '2025-11-15 00:21:08', '2025-11-15 00:21:08');
INSERT INTO `users` VALUES ('14', 'Indra Kusuma', NULL, '0051234575', 'Denpasar', '2013-09-08', '6B', 'siswa', NULL, '$2y$12$1PyzAutqFG2iYOIcawdwOOYCKm810VihA3wmn0tNvrICVWbdr94e2', NULL, '2025-11-15 00:21:08', '2025-11-15 00:21:08');
INSERT INTO `users` VALUES ('15', 'Jihan Azzahra', NULL, '0051234576', 'Malang', '2013-10-25', '6B', 'siswa', NULL, '$2y$12$LgO8zM0xKpdnKy00jkZMsu/LAdzr/.8A6tUX3h2aCkdqxNgFwwCQq', NULL, '2025-11-15 00:21:08', '2025-11-15 00:21:08');
INSERT INTO `users` VALUES ('16', 'Krisna Wijaya', NULL, '0051234577', 'Bogor', '2013-11-30', '6C', 'siswa', NULL, '$2y$12$z0mL5FWWGmmIHzBSdEPTJe6hbdZaRChaIiNn3Ah5.LpS5.QdmhYje', NULL, '2025-11-15 00:21:09', '2025-11-15 00:21:09');
INSERT INTO `users` VALUES ('17', 'Lina Marlina', NULL, '0051234578', 'Bekasi', '2013-12-17', '6C', 'siswa', NULL, '$2y$12$VE3PEpvt9CStckCf41XAB.5v4zBsOvVrQvNW/t4pD2fUDND5ai.qW', NULL, '2025-11-15 00:21:09', '2025-11-15 00:21:09');
INSERT INTO `users` VALUES ('18', 'Muhammad Farhan', NULL, '0051234579', 'Tangerang', '2013-01-28', '6C', 'siswa', NULL, '$2y$12$jUfNuszO67cfy7eM16QbKeL59go8hnYRB7QteNsMN60hSc/WlX37e', NULL, '2025-11-15 00:21:09', '2025-11-15 00:21:09');
INSERT INTO `users` VALUES ('19', 'Nur Aini', NULL, '0051234580', 'Depok', '2013-02-14', '6C', 'siswa', NULL, '$2y$12$6xfD1tWEfq0Yn/RgaD3uNe5Yg5hDY5N0mqYzeNXETzupwA0VBlxCi', NULL, '2025-11-15 00:21:09', '2025-11-15 00:21:09');
INSERT INTO `users` VALUES ('20', 'Oscar Ramadhan', NULL, '0051234581', 'Padang', '2013-03-21', '6C', 'siswa', NULL, '$2y$12$EvvebIvvn1Kp4T69lBIpAu9zLVCMbIx1ELf0ZctkrbGrsrDr4h/I.', NULL, '2025-11-15 00:21:09', '2025-11-15 00:21:09');
INSERT INTO `users` VALUES ('21', 'Putri Amelia', NULL, '0051234582', 'Pontianak', '2013-04-19', '6D', 'siswa', NULL, '$2y$12$N5xD9BtQRZ2S.tzUXf5nS.DTYT6nGdNMcJ9UPY1TVpDYn8SZuku9q', NULL, '2025-11-15 00:21:09', '2025-11-15 00:21:09');
INSERT INTO `users` VALUES ('22', 'Qori Ramadhan', NULL, '0051234583', 'Banjarmasin', '2013-05-26', '6D', 'siswa', NULL, '$2y$12$U5BDtbR3ZKVeQLJu37TthetvRp4SqBvAG/ag4n.GRD5wIsf/uDao6', NULL, '2025-11-15 00:21:10', '2025-11-15 00:21:10');
INSERT INTO `users` VALUES ('23', 'Rina Susanti', NULL, '0051234584', 'Balikpapan', '2013-06-11', '6D', 'siswa', NULL, '$2y$12$xoBOa/alry16HF.ZeMWqAOlf2FzFFVw46Fk8CL55UJQrrBNpvjxK.', NULL, '2025-11-15 00:21:10', '2025-11-15 00:21:10');
INSERT INTO `users` VALUES ('24', 'Surya Pratama', NULL, '0051234585', 'Manado', '2013-07-04', '6D', 'siswa', NULL, '$2y$12$ilnr0g4bM4L8XUKnT5SSXeM9ROrIz03oXbOBrqA9SxrXkmiBY/HSK', NULL, '2025-11-15 00:21:10', '2025-11-15 00:21:10');
INSERT INTO `users` VALUES ('25', 'Tara Kusuma', NULL, '0051234586', 'Pekanbaru', '2013-08-23', '6D', 'siswa', NULL, '$2y$12$ytiZs67FNJ0hRmmbcnUGUuDw1KnXmhxNzqN7ljBHMkP0lm0xQuPVa', NULL, '2025-11-15 00:21:10', '2025-11-15 00:21:10');

