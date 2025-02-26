-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2025 at 02:20 AM
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
-- Database: `ejusticesys`
--

-- --------------------------------------------------------

--
-- Table structure for table `movbrgy_officers`
--

CREATE TABLE `movbrgy_officers` (
  `id` int(11) NOT NULL,
  `barangay` int(11) NOT NULL,
  `Punong_Barangay` varchar(100) NOT NULL,
  `Barangay_Secretary` varchar(100) NOT NULL,
  `Barangay_Treasurer` varchar(100) NOT NULL,
  `Kagawad1` varchar(100) NOT NULL,
  `Kagawad2` varchar(100) NOT NULL,
  `Kagawad3` varchar(100) NOT NULL,
  `Kagawad4` varchar(100) NOT NULL,
  `Kagawad5` varchar(100) NOT NULL,
  `Kagawad6` varchar(100) NOT NULL,
  `Kagawad7` varchar(100) NOT NULL,
  `Kagawad8` varchar(100) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `year` year(4) NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `movbrgy_officers`
--
ALTER TABLE `movbrgy_officers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barangay` (`barangay`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `movbrgy_officers`
--
ALTER TABLE `movbrgy_officers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `movbrgy_officers`
--
ALTER TABLE `movbrgy_officers`
  ADD CONSTRAINT `movbrgy_officers_ibfk_1` FOREIGN KEY (`barangay`) REFERENCES `barangays` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
