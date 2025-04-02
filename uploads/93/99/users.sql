-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 29, 2024 at 07:03 AM
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
-- Database: `u620962682_ejusticesys`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(11) DEFAULT NULL,
  `user_type` enum('superadmin','admin','user') NOT NULL,
  `municipality_id` int(11) DEFAULT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `attempt_count` int(11) DEFAULT 0,
  `restrict_end` timestamp NULL DEFAULT NULL,
  `lgu_logo` varchar(255) DEFAULT NULL,
  `city_logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `password`, `email`, `contact_number`, `user_type`, `municipality_id`, `barangay_id`, `registration_date`, `profile_picture`, `verified`, `attempt_count`, `restrict_end`, `lgu_logo`, `city_logo`) VALUES
(1, 'SuperAdmins', 'DILGs', 'Head', '$2y$10$TrBHp44V3RHaAaVwmtAreeDmUU5iSlSWXmXv6vmocjS6NtT.K8KF6', 'superadmin@gmail.com', '09212342546', 'superadmin', NULL, NULL, '2023-08-04 01:05:56', '1.png', 0, 0, NULL, 'angel.png', 'angel.png'),
(71, 'Janzell121', 'Carl Janzell', 'Oropesa', '$2y$10$TrBHp44V3RHaAaVwmtAreeDmUU5iSlSWXmXv6vmocjS6NtT.K8KF6', 'carl.oropesa11@gmail.com', '09455545531', 'admin', 27, NULL, '2023-08-10 07:00:56', '71.png', 0, 0, NULL, NULL, NULL),
(73, 'BrgySec.Pedro', 'Pedro', 'Penduko', '$2y$10$CgAczE03TLM94dhcw4FjWeCus3AejqLxrnobTdGckZ5UKrc.KbUqq', 'pedro@gmail.com', '09218361337', 'user', 27, 41, '2023-08-11 01:52:44', NULL, 1, 4, '2023-12-09 17:50:24', NULL, NULL),
(74, 'BrgySec.Bautista', 'Mary Gracee', 'Bautista', '$2y$10$NmcQscaP9.melNSUDZEAou/oLGpn3s3UQNB1HdMjmNDSyO5vTVfoi', 'megabyte@gmail.com', '09218361337', 'user', 27, 42, '2023-08-11 01:53:24', NULL, 0, 0, NULL, NULL, NULL),
(76, 'AdminAlaminos', 'Joe', 'Dohn', '$2y$10$iO2sQiP6Es4/fohKgMyIrutsE4NIn7hxn0Gp10a1WAbE65SM9a1tm', 'respectmyonion@gmail.com', '09218361337', 'admin', 28, NULL, '2023-08-11 02:46:28', NULL, 0, 0, NULL, NULL, NULL),
(78, 'BrgySecAntoine', 'Antoine', 'De Villa', '$2y$10$nlknd8Lp0koz9GWGUygdbePG0CGb7CvrpU0i6IEJnUeTNCdqKTlS6', 'antoinedev@gmail.com', '09218361337', 'user', 27, 44, '2023-09-11 10:11:37', NULL, 0, 0, NULL, NULL, NULL),
(79, 'AdminLosBaños', 'Admin', 'Los Baños', '$2y$10$Het5RsywKBTZ1OhiARs/5.qwCBxpqatYdrdTO8v1x4ZApntNxJ1Jm', 'adminlosbanos@gmail.com', '09210001234', 'admin', 30, NULL, '2023-09-14 09:24:28', '79.jpg', 0, 0, NULL, NULL, NULL),
(80, 'SecManalo', 'Cristina', 'Manalo', '$2y$10$qi6Vn3yFFAVZbtIl3d7WNO/895/cogf6wZROR6Ls2xlvPWMgUMGqG', 'cristinamanalo@gmail.com', '09981234567', 'user', 30, 50, '2023-09-14 09:26:11', '80.png', 1, 0, NULL, 'angel.png', 'grace.png'),
(81, 'BrgySecAlos', 'Jealous', 'Jealousy', '$2y$10$N0kCK5aSNjkvRJFsw13EouCnyNZcPKPMcoK0u/ejHlfwuacwTYrta', 'oliviarodrigo@gmail.com', '09211231231', 'user', 28, 51, '2023-09-14 10:33:29', NULL, 1, 0, NULL, NULL, NULL),
(82, 'BrgySecPalamis', 'Palawan', 'Bulawan', '$2y$10$599TaTeuLjWC5wbyc2.zNO29XYPn9kK1f3gWxiBMq5BtC76iM/ijG', 'palamisbulawan@gmail.com', '09123451234', 'user', 28, 52, '2023-09-14 10:34:37', NULL, 0, 0, NULL, NULL, NULL),
(83, 'kuya', 'Kinm', 'Atienza', '$2y$10$USE1JPcQ8lLHuMLjbs.PvOTxLfexc6jT1dE9a1I22yc5VGBuKgO5e', 'kuyakim@gmail.com', '09218361337', 'user', 28, 53, '2023-10-12 14:07:57', NULL, 0, 0, NULL, NULL, NULL),
(84, 'CalauanAdmin', 'Jose Mari', 'Rizal', '$2y$10$olbCUC7VVkdX739Ayimu9.7izoabvoBm8CqmcDIG4t6JE3YqcS3ja', 'rizal@gmail.com', '09218361337', 'admin', 31, NULL, '2023-10-12 14:08:36', NULL, 0, 0, NULL, NULL, NULL),
(85, 'AdminRosellePablo', 'Roselle', 'Colander', '$2y$10$/478yNeXasiy1sPWSjDFYu3sgDEuB1JSMmjT3KVYgIqqqYkCcUcSS', 'roselle@gmail.com', '09218361337', 'admin', 34, NULL, '2023-11-27 08:18:58', NULL, 0, 0, NULL, NULL, NULL),
(86, 'sanpedero', 'san', 'pedro', '$2y$10$b8s3ObFLPWwDvcTUxgNtdOETwFcj/KD8J8R0LxZLXW6uJcNRqM2g.', 'sanpedro@gmail.com', '09218361337', 'admin', 35, NULL, '2024-02-07 07:18:50', NULL, 0, 0, NULL, NULL, NULL),
(88, 'b ajfoafowbipaw', 'Juan', 'Luna', '$2y$10$BVSvAzAxaZw7IYWlfgUur.PuJmDpUWkMo9f7o0RCZD0Yo5SEfqoaa', 'juanluna@gmail.com', '09218361337', 'user', 30, 56, '2024-02-12 09:20:59', NULL, 1, 0, NULL, NULL, NULL),
(89, 'AdminBiñan', 'Admin', 'Biñan', '$2y$10$zJAiVP7m8/v2TiSQN/qggeVSU35heXMRkPX9NjHyWuDBrmpJxOIXK', 'adminbinan@gmail.com', '09218361337', 'admin', 36, NULL, '2024-02-12 14:57:47', NULL, 0, 0, NULL, NULL, NULL),
(93, 'brgybatongmalake', 'Barangay', 'Batong Malake', '$2y$10$qxPi/KZ7XkI43nssvidTjum.ixAGio5sNvkOCVmH6wh2foDpCrR0W', 'clustera_batongmalake_ekp@gmail.com', '09218361337', 'user', 30, 61, '2024-02-13 01:48:58', '93.jpg', 1, 0, NULL, 'LB.png', 'dilg.ico'),
(94, 'User_Tadlac', 'Jaime', 'Buendia', '$2y$10$jazRUw7/txzalJ0/k0w98u173gYUUp96KRzAuQt6.1dh1CvWANA6m', 'clustera_tadlac_ekp@gmail.com', '09605762440', 'user', 30, 62, '2024-02-14 20:40:56', '94.jpg', 1, 0, NULL, NULL, NULL),
(95, 'brgyanos', 'Benito', 'Elec', '$2y$10$r.2viOy43Ev/89EoPaYnXuT1QCGOv5oQi50UQIzTfgkOe6/Q878FO', 'clustera_anos_ekp@gmail.com', '09196057623', 'user', 30, 63, '2024-02-14 20:47:56', NULL, 1, 0, NULL, NULL, NULL),
(96, 'brgymayondon', 'Rommel', 'Eusebio', '$2y$10$j66vwD/QrigeipIaARAoPuiZEj7dT6XFtHr.yoqalEnTFuC9sDf96', 'clustera_mayondon_ekp@gmail.com', '09234567891', 'user', 30, 64, '2024-02-15 00:14:48', NULL, 1, 0, NULL, NULL, NULL),
(99, 'Admin_Bay_EKPSys', 'Bay', 'Admin', '$2y$10$dfXpzO7ICKHhq0LuPkc9sewOC.MCwpEa1j7yfSeSKMdYqPTVazPKm', 'clusterA_adminbay_ekpsys@gmail.com', '09469998765', 'admin', 38, NULL, '2024-02-21 20:26:16', NULL, 0, 0, NULL, NULL, NULL),
(100, 'BarangayMaitim', 'Barangay', 'Maitim', '$2y$10$hlSy2Nfnh5S4WY7yARNF9ef8ky0sboYaMKS7kKbk7K3qs7UX/lJby', 'clustera_brgymacabling_ekpsys@gmail.com', '09228371336', 'user', 38, 70, '2024-02-21 20:31:10', NULL, 0, 0, NULL, NULL, NULL),
(101, 'CLGOO Santa.Rosa', 'Santa.Rosa', 'City', '$2y$10$2.RMBwIUo5ttdzkbOuZupOFgFLsORAkREq0zNX6BAp9usxKJ8sqfq', 'adminstarosa@gmail.com', '09104807052', 'admin', 39, NULL, '2024-02-22 02:35:04', NULL, 0, 0, NULL, NULL, NULL),
(102, 'secretary', 'aplaya', 'santa.rosa', '$2y$10$fyzxqlFmnqq.z40jGvui0.Pb2Kt.hnk1aUJjNuj1oKTziUf.tXC5K', 'clustera_aplaya_ekp@gmail.com', '09104907052', 'user', 39, 0, '2024-02-22 02:38:27', NULL, 1, 0, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `municipality_id` (`municipality_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
