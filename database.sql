-- phpMyAdmin SQL Dump
-- version 5.2.1-1.el8
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 14, 2025 at 01:37 PM
-- Server version: 8.0.41
-- PHP Version: 7.2.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `animac_bot`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `user_id` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `animes`
--

CREATE TABLE `animes` (
  `id` int NOT NULL,
  `title` text NOT NULL,
  `season` varchar(255) NOT NULL,
  `genre` text NOT NULL,
  `voter` text NOT NULL,
  `episodes` text NOT NULL,
  `films` text NOT NULL,
  `lang` text NOT NULL,
  `tegs` text NOT NULL,
  `status` text NOT NULL,
  `about` text NOT NULL,
  `views` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anime_views`
--

CREATE TABLE `anime_views` (
  `view_id` int NOT NULL,
  `user_id` text NOT NULL,
  `anime_id` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `channels`
--

CREATE TABLE `channels` (
  `id` int NOT NULL,
  `channelID` varchar(255) DEFAULT NULL,
  `name` varchar(1000) DEFAULT NULL,
  `link` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `send`
--

CREATE TABLE `send` (
  `send_id` int NOT NULL,
  `time1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `time2` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `start_id` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `stop_id` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `admin_id` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `message_id` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `reply_markup` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `step` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `time3` text NOT NULL,
  `time4` text NOT NULL,
  `time5` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trailers`
--

CREATE TABLE `trailers` (
  `id` int NOT NULL,
  `anime_id` varchar(255) NOT NULL,
  `video_id` varchar(255) NOT NULL,
  `type` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `id` text NOT NULL,
  `ban` text NOT NULL,
  `step` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int NOT NULL,
  `anime_id` text NOT NULL,
  `episode` text NOT NULL,
  `video_id` text NOT NULL,
  `date` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `animes`
--
ALTER TABLE `animes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `anime_views`
--
ALTER TABLE `anime_views`
  ADD PRIMARY KEY (`view_id`);

--
-- Indexes for table `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `send`
--
ALTER TABLE `send`
  ADD PRIMARY KEY (`send_id`);

--
-- Indexes for table `trailers`
--
ALTER TABLE `trailers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `animes`
--
ALTER TABLE `animes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `anime_views`
--
ALTER TABLE `anime_views`
  MODIFY `view_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `channels`
--
ALTER TABLE `channels`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `send`
--
ALTER TABLE `send`
  MODIFY `send_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trailers`
--
ALTER TABLE `trailers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
