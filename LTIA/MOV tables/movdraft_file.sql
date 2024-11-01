-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 01, 2024 at 11:08 AM
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
-- Table structure for table `movdraft_file`
--

CREATE TABLE `movdraft_file` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `barangay_id` int(11) NOT NULL,
  `IA_1a_pdf_File` varchar(255) DEFAULT NULL,
  `IA_1b_pdf_File` varchar(255) DEFAULT NULL,
  `IA_2a_pdf_File` varchar(255) DEFAULT NULL,
  `IA_2b_pdf_File` varchar(255) DEFAULT NULL,
  `IA_2c_pdf_File` varchar(255) DEFAULT NULL,
  `IA_2d_pdf_File` varchar(255) DEFAULT NULL,
  `IA_2e_pdf_File` varchar(255) DEFAULT NULL,
  `IB_1forcities_pdf_File` varchar(255) DEFAULT NULL,
  `IB_1aformuni_pdf_File` varchar(255) DEFAULT NULL,
  `IB_1bformuni_pdf_File` varchar(255) DEFAULT NULL,
  `IB_2_pdf_File` varchar(255) DEFAULT NULL,
  `IB_3_pdf_File` varchar(255) DEFAULT NULL,
  `IB_4_pdf_File` varchar(255) DEFAULT NULL,
  `IC_1_pdf_File` varchar(255) DEFAULT NULL,
  `IC_2_pdf_File` varchar(255) DEFAULT NULL,
  `ID_1_pdf_File` varchar(255) DEFAULT NULL,
  `ID_2_pdf_File` varchar(255) DEFAULT NULL,
  `IIA_pdf_File` varchar(255) DEFAULT NULL,
  `IIB_1_pdf_File` varchar(255) DEFAULT NULL,
  `IIB_2_pdf_File` varchar(255) DEFAULT NULL,
  `IIC_pdf_File` varchar(255) DEFAULT NULL,
  `IIIA_pdf_File` varchar(255) DEFAULT NULL,
  `IIIB_pdf_File` varchar(255) DEFAULT NULL,
  `IIIC_1forcities_pdf_File` varchar(255) DEFAULT NULL,
  `IIIC_1forcities2_pdf_File` varchar(255) DEFAULT NULL,
  `IIIC_1forcities3_pdf_File` varchar(255) DEFAULT NULL,
  `IIIC_2formuni1_pdf_File` varchar(255) DEFAULT NULL,
  `IIIC_2formuni2_pdf_File` varchar(255) DEFAULT NULL,
  `IIIC_2formuni3_pdf_File` varchar(255) DEFAULT NULL,
  `IIID_pdf_File` varchar(255) DEFAULT NULL,
  `IV_forcities_pdf_File` varchar(255) DEFAULT NULL,
  `IV_muni_pdf_File` varchar(255) DEFAULT NULL,
  `V_1_pdf_File` varchar(255) DEFAULT NULL,
  `threepeoplesorg_File` varchar(255) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `movdraft_file`
--
ALTER TABLE `movdraft_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `movdraft_file`
--
ALTER TABLE `movdraft_file`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `movdraft_file`
--
ALTER TABLE `movdraft_file`
  ADD CONSTRAINT `movdraft_file_ibfk_1` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
