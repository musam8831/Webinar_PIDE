-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2025 at 09:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webinar_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@example.com', '$2y$10$WGJ6MCoaOn2B.sCtf3jPoeQA3Lj5ZjEletdpOOPMuTorVcjYHAl/i', 'admin', '2025-08-19 07:27:36'),
(2, 'User', 'user@example.com', '$2y$10$Hbieg8VqtFg5OUaAGjaiMuLOum6mGAqZv76antVDIxqE6C/DUqq8m', 'user', '2025-08-19 07:36:17'),
(4, 'Khurram', 'Khuarram@pide.org.pk', '$2y$10$ixzOBVlnIktzGnMB3N4zCuUujRi6e.v1nXwGfEZhJYfj4qXjCLXiW', 'admin', '2025-08-19 07:48:02'),
(9, 'Ahmed Ilyas', 'ahmed@pide.org.pk', '', 'user', '2025-08-22 10:22:18'),
(10, 'Muhammad Usman', 'muhammad.usman@pide.org.pk', '', 'user', '2025-08-25 06:06:13');

-- --------------------------------------------------------

--
-- Table structure for table `webinars`
--

CREATE TABLE `webinars` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `initiated_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `webinars`
--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `webinars`
--
ALTER TABLE `webinars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_webinar_user` (`initiated_by`),
  ADD KEY `idx_time` (`start_at`,`end_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `webinars`
--
ALTER TABLE `webinars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `webinars`
--
ALTER TABLE `webinars`
  ADD CONSTRAINT `fk_webinar_user` FOREIGN KEY (`initiated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
