-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2025
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET @NAMES utf8mb4 */;

--
-- Database: `webinar_app`
--
CREATE DATABASE IF NOT EXISTS `webinar_app` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `webinar_app`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `users`
--

TRUNCATE TABLE `users`;
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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_by` int(11),
  `modified_on` timestamp NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `title`, `description`, `is_active`, `created_by`, `created_on`) VALUES
(1, 'Technical', 'Technical webinars and workshops', 1, 1, '2025-11-25 10:00:00'),
(2, 'Research', 'Research-focused webinars', 1, 1, '2025-11-25 10:00:00'),
(3, 'Training', 'Training and development sessions', 1, 1, '2025-11-25 10:00:00'),
(4, 'Awareness', 'Awareness and information sessions', 1, 1, '2025-11-25 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `webinars`
--

DROP TABLE IF EXISTS `webinars`;
CREATE TABLE `webinars` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `initiated_by` int(11) NOT NULL,
  `category_id` int(11),
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by` int(11),
  `approved_on` timestamp NULL,
  `rejection_reason` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `webinars`
--

TRUNCATE TABLE `webinars`;
--
-- Dumping data for table `webinars`
--

INSERT INTO `webinars` (`id`, `title`, `start_at`, `end_at`, `initiated_by`, `category_id`, `is_approved`, `approved_by`, `approved_on`, `created_at`) VALUES
(1, 'Team Intro', '2025-01-06 09:00:00', '2025-01-06 10:00:00', 1, 1, 1, 1, '2025-08-19 07:37:01', '2025-08-19 07:37:01'),
(2, 'Q1 Planning', '2025-02-10 13:00:00', '2025-02-10 14:00:00', 1, 2, 1, 1, '2025-08-19 07:37:01', '2025-08-19 07:37:01'),
(3, 'Customer Webinar - Updated', '2025-03-12 15:00:00', '2025-03-12 18:00:00', 2, 1, 1, 1, '2025-08-19 07:37:01', '2025-08-19 07:37:01');

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
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `webinars`
--
ALTER TABLE `webinars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_webinar_user` (`initiated_by`),
  ADD KEY `fk_webinar_category` (`category_id`),
  ADD KEY `idx_approved` (`is_approved`),
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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `webinars`
--
ALTER TABLE `webinars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_category_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `webinars`
--
ALTER TABLE `webinars`
  ADD CONSTRAINT `fk_webinar_user` FOREIGN KEY (`initiated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_webinar_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
