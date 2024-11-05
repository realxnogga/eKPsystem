-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2024 at 05:42 PM
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
  `barangay` int(11) NOT NULL,
  `mov_id` int(255) NOT NULL,
  `IA_1a_pdf_remark` varchar(255) DEFAULT NULL,
  `IA_1b_pdf_remark` varchar(255) DEFAULT NULL,
  `IA_2a_pdf_remark` varchar(255) DEFAULT NULL,
  `IA_2b_pdf_remark` varchar(255) DEFAULT NULL,
  `IA_2c_pdf_remark` varchar(255) DEFAULT NULL,
  `IA_2d_pdf_remark` varchar(255) DEFAULT NULL,
  `IA_2e_pdf_remark` varchar(255) DEFAULT NULL,
  `IB_1forcities_pdf_remark` varchar(255) DEFAULT NULL,
  `IB_1aformuni_pdf_remark` varchar(255) DEFAULT NULL,
  `IB_1bformuni_pdf_remark` varchar(255) DEFAULT NULL,
  `IB_2_pdf_remark` varchar(255) DEFAULT NULL,
  `IB_3_pdf_remark` varchar(255) DEFAULT NULL,
  `IB_4_pdf_remark` varchar(255) DEFAULT NULL,
  `IC_1_pdf_remark` varchar(255) DEFAULT NULL,
  `IC_2_pdf_remark` varchar(255) DEFAULT NULL,
  `ID_1_pdf_remark` varchar(255) DEFAULT NULL,
  `ID_2_pdf_remark` varchar(255) DEFAULT NULL,
  `IIA_pdf_remark` varchar(255) DEFAULT NULL,
  `IIB_1_pdf_remark` varchar(255) DEFAULT NULL,
  `IIB_2_pdf_remark` varchar(255) DEFAULT NULL,
  `IIC_pdf_remark` varchar(255) DEFAULT NULL,
  `IIIA_pdf_remark` varchar(255) DEFAULT NULL,
  `IIIB_pdf_remark` varchar(255) DEFAULT NULL,
  `IIIC_1forcities_pdf_remark` varchar(255) DEFAULT NULL,
  `IIIC_1forcities2_pdf_remark` varchar(255) DEFAULT NULL,
  `IIIC_1forcities3_pdf_remark` varchar(255) DEFAULT NULL,
  `IIIC_2formuni1_pdf_remark` varchar(255) DEFAULT NULL,
  `IIIC_2formuni2_pdf_remark` varchar(255) DEFAULT NULL,
  `IIIC_2formuni3_pdf_remark` varchar(255) DEFAULT NULL,
  `IIID_pdf_remark` varchar(255) DEFAULT NULL,
  `IV_forcities_pdf_remark` varchar(255) DEFAULT NULL,
  `IV_muni_pdf_remark` varchar(255) DEFAULT NULL,
  `V_1_pdf_remark` varchar(255) DEFAULT NULL,
  `threepeoplesorg_remark` varchar(255) DEFAULT NULL,
  `dateremark` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `movremark`
--
ALTER TABLE `movremark`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mov_id` (`mov_id`),
  ADD KEY `barangay` (`barangay`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `movremark`
--
ALTER TABLE `movremark`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `movremark`
--
ALTER TABLE `movremark`
  ADD CONSTRAINT `movremark_ibfk_2` FOREIGN KEY (`barangay`) REFERENCES `mov` (`barangay_id`),
  ADD CONSTRAINT `movremark_ibfk_3` FOREIGN KEY (`mov_id`) REFERENCES `mov` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
