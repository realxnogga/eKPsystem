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
-- Table structure for table `movremark`
--

CREATE TABLE `movremark` (
  `id` int(11) NOT NULL,
  `mov_id` int(11) NOT NULL,
  `barangay` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `last_modified_at` datetime DEFAULT NULL,
  `IA_1a_pdf_remark` text DEFAULT NULL,
  `IA_1b_pdf_remark` text DEFAULT NULL,
  `IA_2a_pdf_remark` text DEFAULT NULL,
  `IA_2b_pdf_remark` text DEFAULT NULL,
  `IA_2c_pdf_remark` text DEFAULT NULL,
  `IA_2d_pdf_remark` text DEFAULT NULL,
  `IA_2e_pdf_remark` text DEFAULT NULL,
  `IB_1forcities_pdf_remark` text DEFAULT NULL,
  `IB_1aformuni_pdf_remark` text DEFAULT NULL,
  `IB_1bformuni_pdf_remark` text DEFAULT NULL,
  `IB_2_pdf_remark` text DEFAULT NULL,
  `IB_3_pdf_remark` text DEFAULT NULL,
  `IB_4_pdf_remark` text DEFAULT NULL,
  `IC_1_pdf_remark` text DEFAULT NULL,
  `IC_2_pdf_remark` text DEFAULT NULL,
  `ID_1_pdf_remark` text DEFAULT NULL,
  `ID_2_pdf_remark` text DEFAULT NULL,
  `IIA_pdf_remark` text DEFAULT NULL,
  `IIB_1_pdf_remark` text DEFAULT NULL,
  `IIB_2_pdf_remark` text DEFAULT NULL,
  `IIC_pdf_remark` text DEFAULT NULL,
  `IIIA_pdf_remark` text DEFAULT NULL,
  `IIIB_pdf_remark` text DEFAULT NULL,
  `IIIC_1forcities_pdf_remark` text DEFAULT NULL,
  `IIIC_1forcities2_pdf_remark` text DEFAULT NULL,
  `IIIC_1forcities3_pdf_remark` text DEFAULT NULL,
  `IIIC_2formuni1_pdf_remark` text DEFAULT NULL,
  `IIIC_2formuni2_pdf_remark` text DEFAULT NULL,
  `IIIC_2formuni3_pdf_remark` text DEFAULT NULL,
  `IIID_pdf_remark` text DEFAULT NULL,
  `IV_forcities_pdf_remark` text DEFAULT NULL,
  `IV_muni_pdf_remark` text DEFAULT NULL,
  `V_1_pdf_remark` text DEFAULT NULL,
  `threepeoplesorg_remark` text DEFAULT NULL,
  `year` year(4) NOT NULL DEFAULT year(curdate())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `movremark`
--
ALTER TABLE `movremark`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_remark` (`mov_id`,`barangay`,`user_id`,`user_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `movremark`
--
ALTER TABLE `movremark`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
