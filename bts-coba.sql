-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2025 at 10:18 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
(10, 'aaaa', 'aaaa', 7, 'cover/cover_681712b0c68cc.ico', 'content/content_681712b0d4cd6.pdf', '2025-05-04 07:09:36', '2025-05-04 07:09:36', 1),
(11, 'SSIL', 'aaaa', 7, 'cover/cover_68171785c3708.jpg', 'content/content_68171785e40a6.pdf', '2025-05-04 07:30:13', '2025-05-04 07:30:13', 1),
(12, 'ssssss', 'ssssssss', 7, 'cover/cover_68171a59b4ef8.jpg', 'content/content_68171a59c4d68.pdf', '2025-05-04 07:42:17', '2025-05-04 07:42:17', 1),
(13, 'sssssssss', 'sss', 7, 'cover/cover_68171aca944f5.jpg', 'content/content_68171acab9537.pdf', '2025-05-04 07:44:10', '2025-05-04 07:44:10', 1),
(14, 'dddddd', 'dddd', 7, 'cover/cover_68171ceecfb75.jpg', 'content/content_68171ceee9840.pdf', '2025-05-04 07:53:18', '2025-05-04 07:53:18', 1),
(15, 'fffffffffffff', 'sisil', 7, 'cover/cover_68171d293b290.jpg', 'content/content_68171d2948400.pdf', '2025-05-04 07:54:17', '2025-05-04 07:54:17', 1),
(16, 'WAHYU', 'HALO', 7, 'cover/cover_68171e20a098c.jpg', 'content/content_68171e20b4034.pdf', '2025-05-04 07:58:24', '2025-05-04 07:58:24', 1),
(17, 'WAHYU', 'HALO', 7, 'cover/cover_68171e4a6485b.jpg', 'content/content_68171e4a75778.pdf', '2025-05-04 07:59:06', '2025-05-04 07:59:06', 1),
(18, 'WAHYU', 'HALO', 7, 'cover/cover_68171ea5540e4.jpg', 'content/content_68171ea55ae67.pdf', '2025-05-04 08:00:37', '2025-05-04 08:00:37', 1),
(19, 'aaa', 'aaaa', 7, 'cover/cover_68171ec5bf3fe.jpg', 'content/content_68171ec5c9174.pdf', '2025-05-04 08:01:09', '2025-05-04 08:01:09', 1),
(20, 'jjjjjjjjjjjj', 'jjjjjjjjjjjjj', 7, 'cover/cover_68172098d7280.jpg', 'content/content_68172098f06db.pdf', '2025-05-04 08:08:56', '2025-05-04 08:08:56', 1),
(21, 'djsdsfdshfwdf', 'dsifjdsfjdsflkdslfksdjdfkjdfkjldfjlkdjf', 7, 'cover/cover_681721936559b.jpg', 'content/content_68172193a2589.pdf', '2025-05-04 08:13:07', '2025-05-04 08:13:07', 1),
(22, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaalief', 'bambang', 7, 'cover/cover_681aab803e3ec.jpg', 'content/content_681aab80404c1.pdf', '2025-05-07 00:38:24', '2025-05-07 00:38:24', 1);

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
(7, 'Siswa dan Siswi', '2025-05-02 03:52:39', '2025-05-02 03:52:39');

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
  ADD KEY `category_id` (`category_id`),
  ADD KEY `fk_tahun_akademik` (`tahun_akademik_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_tahun_akademik` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `tahun_akademik` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
