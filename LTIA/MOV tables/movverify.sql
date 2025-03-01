-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2025 at 02:19 AM
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
-- Table structure for table `movverify`
--

CREATE TABLE `movverify` (
  `id` int(11) NOT NULL,
  `mov_id` int(11) NOT NULL,
  `barangay_id` int(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_modified_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `IA_1a_pdf_verify` tinyint(1) DEFAULT 0,
  `IA_1b_pdf_verify` tinyint(1) DEFAULT 0,
  `IA_2a_pdf_verify` tinyint(1) DEFAULT 0,
  `IA_2b_pdf_verify` tinyint(1) DEFAULT 0,
  `IA_2c_pdf_verify` tinyint(1) DEFAULT 0,
  `IA_2d_pdf_verify` tinyint(1) DEFAULT 0,
  `IA_2e_pdf_verify` tinyint(1) DEFAULT 0,
  `IB_1forcities_pdf_verify` tinyint(1) DEFAULT 0,
  `IB_1aformuni_pdf_verify` tinyint(1) DEFAULT 0,
  `IB_1bformuni_pdf_verify` tinyint(1) DEFAULT 0,
  `IB_2_pdf_verify` tinyint(1) DEFAULT 0,
  `IB_3_pdf_verify` tinyint(1) DEFAULT 0,
  `IB_4_pdf_verify` tinyint(1) DEFAULT 0,
  `IC_1_pdf_verify` tinyint(1) DEFAULT 0,
  `IC_2_pdf_verify` tinyint(1) DEFAULT 0,
  `ID_1_pdf_verify` tinyint(1) DEFAULT 0,
  `ID_2_pdf_verify` tinyint(1) DEFAULT 0,
  `IIA_pdf_verify` tinyint(1) DEFAULT 0,
  `IIB_1_pdf_verify` tinyint(1) DEFAULT 0,
  `IIB_2_pdf_verify` tinyint(1) DEFAULT 0,
  `IIC_pdf_verify` tinyint(1) DEFAULT 0,
  `IIIA_pdf_verify` tinyint(1) DEFAULT 0,
  `IIIB_pdf_verify` tinyint(1) DEFAULT 0,
  `IIIC_1forcities_pdf_verify` tinyint(1) DEFAULT 0,
  `IIIC_1forcities2_pdf_verify` tinyint(1) DEFAULT 0,
  `IIIC_1forcities3_pdf_verify` tinyint(1) DEFAULT 0,
  `IIIC_2formuni1_pdf_verify` tinyint(1) DEFAULT 0,
  `IIIC_2formuni2_pdf_verify` tinyint(1) DEFAULT 0,
  `IIIC_2formuni3_pdf_verify` tinyint(1) DEFAULT 0,
  `IIID_pdf_verify` tinyint(1) DEFAULT 0,
  `IV_forcities_pdf_verify` tinyint(1) DEFAULT 0,
  `IV_muni_pdf_verify` tinyint(1) DEFAULT 0,
  `V_1_pdf_verify` tinyint(1) DEFAULT 0,
  `threepeoplesorg_verify` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `movverify`
--
ALTER TABLE `movverify`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mov_id` (`mov_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `movverify`
--
ALTER TABLE `movverify`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `movverify`
--
ALTER TABLE `movverify`
  ADD CONSTRAINT `movverify_ibfk_1` FOREIGN KEY (`mov_id`) REFERENCES `mov` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
