-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 03:46 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bts-coba`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `penerbit` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `cover_path` varchar(255) DEFAULT NULL,
  `content_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tahun_akademik_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `judul`, `penerbit`, `category_id`, `cover_path`, `content_path`, `created_at`, `updated_at`, `tahun_akademik_id`) VALUES
(1, 'Akuntansi 2', 'SMKN 1 LUMAJANG', 1, 'cover/cover_681efe333ef83.jpg', 'content/content_681efe333f572.pdf', '2025-05-10 07:20:19', '2025-05-13 05:06:53', 1),
(2, 'Pembina Kesiswaan', 'SMKN 1 LUMAJANG', 2, 'cover/cover_681efe811377f.jpg', 'content/content_681efe8115091.pdf', '2025-05-10 07:21:37', '2025-05-10 07:21:37', 1),
(3, 'Jurnalistik', 'SMKN 1 LUMAJANG', 3, 'cover/cover_681eff5adc09a.jpg', 'content/content_681eff5add66f.pdf', '2025-05-10 07:25:14', '2025-05-10 07:25:14', 1),
(4, 'Foto Angkatan', 'SMKN 1 LUMAJANG', 4, 'cover/cover_682032fd0624a.png', 'content/content_682032fd08df2.pdf', '2025-05-11 05:17:49', '2025-05-11 05:17:49', 1),
(5, 'KOMITE SMK', 'SMKN 1 LUMAJANG', 2, 'cover/cover_682033ccc23b5.png', 'content/content_682033ccc5f99.pdf', '2025-05-11 05:21:16', '2025-05-11 05:21:16', 1),
(6, 'OSIS GEN 54', 'SMKN 1 LUMAJANG', 5, 'cover/cover_6822ced2f4060.png', 'content/content_6822ced30809d.pdf', '2025-05-13 04:47:15', '2025-05-13 04:47:15', 1),
(7, 'OSIS GEN 55', 'SMKN 1 LUMAJANG', 5, 'cover/cover_6822d1803a105.png', 'content/content_6822d1803c3bf.pdf', '2025-05-13 04:58:40', '2025-05-13 04:58:40', 1),
(8, 'asdasdasd', 'asdasd', 6, 'cover/cover_6823f35b96591.jpg', 'content/content_6823f35b998ef.pdf', '2025-05-14 01:35:23', '2025-05-14 01:35:23', 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Siswa dan Siswi', '2025-05-10 06:00:15', '2025-05-10 06:00:15'),
(2, 'Guru', '2025-05-10 06:30:43', '2025-05-10 06:30:43'),
(3, 'Ekstrakurikuler', '2025-05-10 07:23:27', '2025-05-10 07:23:27'),
(4, 'Lain Lain', '2025-05-11 05:16:20', '2025-05-11 05:16:20'),
(5, 'Osis', '2025-05-13 04:36:10', '2025-05-13 04:36:10'),
(6, 'cona', '2025-05-14 01:34:38', '2025-05-14 01:34:38');

-- --------------------------------------------------------

--
-- Table structure for table `tahun_akademik`
--

CREATE TABLE `tahun_akademik` (
  `id` int(11) NOT NULL,
  `tahun` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tahun_akademik`
--

INSERT INTO `tahun_akademik` (`id`, `tahun`, `created_at`) VALUES
(1, 2024, '2025-04-29 06:30:53'),
(2, 2025, '2025-04-29 06:31:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user','moderator','guest') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$Egpsm6Cf1uafBUxW.P0md.PDFaGjEiEoxdliF3CmLtals2lbCrg0e', 'admin', '2025-04-26 08:29:15', '2025-04-26 08:29:15'),
(2, 'hayyin', '19711/071.075', 'user', '2025-05-09 04:11:14', '2025-05-09 05:56:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_books_category` (`category_id`),
  ADD KEY `fk_books_tahun_akademik` (`tahun_akademik_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tahun_akademik`
--
ALTER TABLE `tahun_akademik`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tahun` (`tahun`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tahun_akademik`
--
ALTER TABLE `tahun_akademik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `fk_books_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_books_tahun_akademik` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `tahun_akademik` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
