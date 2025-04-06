-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 12, 2024 at 09:33 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

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
-- Table structure for table `barangays`
--

CREATE TABLE `barangays` (
  `id` int(11) NOT NULL,
  `municipality_id` int(11) NOT NULL,
  `barangay_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangays`
--

INSERT INTO `barangays` (`id`, `municipality_id`, `barangay_name`) VALUES
(41, 27, 'Barangay 1 (Poblacion 1)'),
(42, 27, 'Barangay 3 (Poblacion 3)'),
(44, 27, 'Camaligan'),
(50, 30, 'Bambang'),
(51, 28, 'Alos'),
(52, 28, 'Palamis'),
(53, 28, 'Amangbangan'),
(56, 30, 'San Antonio'),
(61, 30, 'Batong Malake'),
(62, 30, 'Tadlac'),
(63, 30, 'Anos'),
(64, 30, 'Mayondon'),
(66, 33, 'Aplaya'),
(67, 33, 'Labas'),
(70, 38, 'Maitim');

-- --------------------------------------------------------

--
-- Table structure for table `case_progress`
--

CREATE TABLE `case_progress` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `current_hearing` varchar(255) NOT NULL,
  `latest_hearing` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `case_progress`
--

INSERT INTO `case_progress` (`id`, `complaint_id`, `current_hearing`, `latest_hearing`) VALUES
(29, 67, '1st', ''),
(30, 68, '1st', ''),
(31, 69, '0', ''),
(32, 70, '0', ''),
(33, 71, '0', ''),
(34, 72, '1st', ''),
(35, 73, '1st', ''),
(36, 74, '2nd', ''),
(37, 75, '0', ''),
(38, 76, '1st', ''),
(39, 77, '1st', ''),
(40, 78, '2nd', ''),
(41, 79, '2nd', ''),
(42, 80, '0', ''),
(43, 81, '0', ''),
(44, 82, '0', ''),
(45, 83, '0', ''),
(46, 84, '0', ''),
(47, 85, '0', ''),
(48, 86, '0', ''),
(49, 87, '1st', ''),
(50, 88, '1st', ''),
(51, 89, '0', ''),
(52, 90, '0', ''),
(53, 91, '0', ''),
(54, 92, '1st', ''),
(55, 93, '0', ''),
(56, 94, '0', ''),
(57, 95, '1th', '7th'),
(58, 96, '4th', '4th'),
(59, 97, '1st', ''),
(60, 98, '1th', ''),
(61, 99, '0', ''),
(62, 100, '0', ''),
(63, 101, '0', ''),
(64, 102, '1th', ''),
(65, 103, '0', ''),
(66, 104, '0', '');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `BarangayID` int(11) DEFAULT NULL,
  `CNum` varchar(50) DEFAULT NULL,
  `Mdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `RDate` date DEFAULT NULL,
  `CNames` varchar(255) DEFAULT NULL,
  `RspndtNames` varchar(255) DEFAULT NULL,
  `CDesc` varchar(255) DEFAULT NULL,
  `Petition` varchar(255) DEFAULT NULL,
  `ForTitle` varchar(255) DEFAULT NULL,
  `Pangkat` varchar(255) DEFAULT NULL,
  `CType` varchar(50) DEFAULT NULL,
  `CStatus` varchar(50) DEFAULT NULL,
  `CMethod` varchar(50) DEFAULT NULL,
  `IsArchived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `UserID`, `BarangayID`, `CNum`, `Mdate`, `RDate`, `CNames`, `RspndtNames`, `CDesc`, `Petition`, `ForTitle`, `Pangkat`, `CType`, `CStatus`, `CMethod`, `IsArchived`) VALUES
(67, 80, 50, '0124-01', '2024-02-11 01:58:00', NULL, 'Roberto Asuncion', 'Johsna M. Katimbangs', 'Di pagbayad ng utang sa takdang oras', 'Ibalik ang pera at mag usap ng tama', 'Loan', 'Ryan Magno, Julius Caligba, Luzviminda Dela Cruz', 'Civil', 'Settled', 'Mediation', 0),
(68, 80, 50, '0224-01', '2024-02-12 05:58:01', '0000-00-00', 'test', 'test', 'testq', '1t1', 'test', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(69, 73, 41, '0224-01', '2024-02-12 00:29:00', '0000-00-00', 'yesy', 'ea', 'rwrw', 'r', 'test', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(70, 73, 41, '0224-02', '2024-02-12 00:30:00', '0000-00-00', 'wfa', 'awfw', 'faw', 'wfawaw', 'eafaa', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(71, 72, 40, '0224-01', '2024-02-19 07:40:00', NULL, 'Prince Zydrick R. Salazar', 'Phil Bojo Repotente ', 'He eat my hotdog.', 'Hotdog den kain ko.', 'Utang', '', 'Civil', 'Unsettled', 'Pending', 0),
(72, 91, 59, '0224-01', '2024-02-12 15:23:37', '0000-00-00', 'test', 'test', 'setes', 'tsets', 'test', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(73, 93, 61, '0224-01', '2024-02-13 02:17:00', '2024-02-13', 'Pedro Penduko', 'Kuya Badang', 'Di pagbayad ng utang sa takdang oras', 'Gusto kong makaharap siya at magbayad siya ng utang', 'Alarms and Scandal', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(74, 80, 50, '0224-02', '2024-02-14 07:39:00', '2024-02-24', 'yown', 'yawn', 'hwhwhw', 'heheheh', 'testing', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(75, 94, 62, '0224-01', '2024-02-15 00:30:00', '0000-00-00', 'Erning Diamante', 'Popoy Kaloy', 'Isang taon na hindi nakakapagbayad sa napagusapan na utang', 'magbayad ng utang ayon sa napagkasunduang araw, nais kong singilin na may dagdag na tubo si Mang Erning sapagkat ang due ng kanyang utang ay lagpas lagpas sa napagkasunduang araw. Ang napagkasunduan na bayad ay 1k ngunit nais ko siyang tubuan pa ng 500 pa', 'Pagkakautang sa Baboy ni Mareng', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(76, 94, 62, '0224-02', '2024-02-15 00:32:00', '2024-02-15', 'Lisa Samson', 'Gina Gomez', 'Hindi niya inaalagaan ang kanyang anak. Palagi niyang iniiwan sa aming bahay ang anak niya, hindi binibigyang pangkain o bihisan lamang. ', 'Bigyan niyang sapat na atensyon ang anak niya at ito ay alagaain pa.', 'Abondoning a minor', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(77, 93, 61, '0224-02', '2024-02-15 01:00:00', NULL, 'Lory Cruz', 'John Mark Yubo', 'Light threats', 'Light threats', 'Light threats', '', 'Civil', 'Settled', 'Conciliation', 0),
(78, 93, 61, '0224-03', '2024-02-15 00:59:00', '2024-02-15', 'Lisa Samson', 'Gina Gomez', 'Iniiwan palagi ang anak niya sa amin, ni hindi niya manlang pinakakain muna o iniiwanan ng pera.', 'Bigyan niyang sapat na atensyon ang anak niya at ito ay alagaain pa.', 'Abondoning a minor', '', 'Civil', 'Unsettled', 'Dismissed', 0),
(79, 93, 61, '0224-03', '2024-02-19 08:42:50', NULL, 'Prince Zydrick R. Salazar', 'Phil Bojo Repotente', 'Bigla na lamang niya akong sinuntok habang kami ay naglalaro ng basketball.', 'Gusto ko siya makausap at makaharap upang malaman ko ang dahilan kung bakit niya iyon ginawa.', 'Panununtok', '', 'Civil', 'Settled', 'Mediation', 0),
(80, 93, 61, '01-000-0224', '2024-02-21 00:53:43', '0002-02-02', '2', '2', '2', '2', '2', NULL, 'Civil', 'Unsettled', 'Pending', 1),
(81, 93, 61, '01-000-0224', '2024-02-21 00:53:46', '0002-02-02', '2', '2', '2', '2', '2', NULL, 'Civil', 'Unsettled', 'Pending', 1),
(82, 93, 61, '01-000-0224', '2024-02-21 00:53:38', '0001-01-01', '1', '1', '1', '1', '1', NULL, 'Others', 'Unsettled', 'Pending', 1),
(83, 93, 61, '01-232-0224', '2024-02-21 00:53:41', '0022-02-22', '2', '2', '2', '2', '2', NULL, 'Civil', 'Unsettled', 'Pending', 1),
(84, 93, 61, '01-565-0224', '2024-02-21 00:52:15', '0002-02-02', '2', '2', '22', '2', '2', NULL, 'Civil', 'Unsettled', 'Pending', 1),
(85, 80, 50, '01-000-0224', '2024-02-20 05:29:00', '0000-00-00', 'test2', 'test3', 'test4', 'test5', 'test1', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(86, 80, 50, '02-100-0224', '2024-02-20 05:30:00', '0000-00-00', 'test2', 'test3', 'test4', 'test5', 'test1', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(87, 80, 50, '03-200-0224', '2024-02-20 05:36:00', '0000-00-00', 'test2', 'test3', 'test4', 'test5', 'test1', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(88, 80, 50, '04-001-0224', '2024-02-20 05:36:00', '0000-00-00', 'test2', 'test3', 'test4', 'test5', 'test1', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(89, 80, 50, '05-020-0224', '2024-02-20 05:36:00', '0000-00-00', 'test2', 'test3', 'test4', 'test5', 'test1', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(90, 80, 50, '06-001-0224', '2024-02-20 05:36:00', '0000-00-00', 'test2', 'test3', 'test4', 'test5', 'test1', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(91, 80, 50, '07-101-0224', '2024-02-20 05:36:00', '0000-00-00', 'test2', 'test3', 'test4', 'test5', 'test1', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(92, 93, 61, '02-565-0224', '2024-02-20 05:56:00', '2024-02-20', 'Phil Bojo Repotente', 'Prince Zydrick Salazar', 'pagkatapos nya kong utangan bigla nya nalang akong tinaguan', 'bayaran nya utang nya sakin', 'hit and run', NULL, 'Criminal', 'Unsettled', 'Pending', 0),
(93, 93, 61, '03-565-0224', '2024-02-21 00:46:18', '0022-02-02', '2', '2', '2', '2', '2', NULL, 'Civil', 'Unsettled', 'Pending', 1),
(94, 93, 61, '03-123-0224', '2024-02-21 03:52:03', '2024-02-20', 'Phil Bojo Repotente', 'mang kanor', 'inagawan ako ng lollipop awit guys', 'AY DAPAT MAGBAYAD KA SA NAPAGUSAPAN TUMAL MO E', 'fraud', NULL, 'Criminal', 'Unsettled', 'Pending', 0),
(95, 93, 61, '03-565-0224', '2024-02-21 10:31:00', NULL, 'Phil Bojojo', 'Zydrick ', 'hindi nagbayad ng utang', 'magbayad ng ayon sa napagkasunduang araw. ', 'Hindi nagbayad', '', 'Civil', 'Unsettled', 'Pending', 0),
(96, 80, 50, '08-101-0224', '2024-02-21 09:46:39', '0000-00-00', 'test2', 'test3', 'test4', 'test5', 'test1', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(97, 93, 61, '02-000-0224', '2024-02-21 02:09:00', '0000-00-00', 'Jayson Cuason', 'Gina Gomez', 'Nanuntok ng walang dahilan, dumaan  ako sa harap niya at bigla nalamang niya akong sinuntok.', 'Huwag idamay ang mga anak', 'Panununtok', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(98, 93, 61, '04-565-0224', '2024-02-22 03:11:01', '2024-02-22', 'Angel May De Guzman', 'Mary Grace Bautista', 'Using false Certificates', 'Seeking redress and justice for the events that have transpired.', 'Alarms and Scandals (Art.155)', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(99, 93, 61, '16-565-0224', '2024-02-22 03:33:00', '0000-00-00', 'Irah Dela Cruz', 'John Mike Suare', 'Using false Certificates', 'Seeking redress and justice for the events that have transpired.', 'Tumults and other disturbances of public order; Tumltuous disturbances or interruption liable to cause disturbance (Art. 153)', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(100, 102, 0, '01-000-0324', '2024-02-29 20:40:00', '0000-00-00', 'Erning Diamonddd', 'Popoy Kaloy', 'Isang taon na hindi nakakapagbayad sa napagusapan na utang. Noong January 2023 nangutang siya ng limang libo sa akin, ipinangako niya na sa isang linggo pagkarating ng sahpd ng asawa ay magbabayad siya. Subalit ngayon ay taong 2024 na perohindi parin siya', 'Bigyan niyang sapat na atensyon ang anak niya at ito ay alagaain pa.', 'Using false certificates (Art. 175)', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(101, 102, 0, '01-000-0324', '2024-02-29 20:40:00', '0000-00-00', 'Angel May L. De Guzman', 'Aaron Banaag', 'Isang taon na hindi nakakapagbayad sa napagusapan na utang. Noong January 2023 nangutang siya ng limang libo sa akin, ipinangako niya na sa isang linggo pagkarating ng sahpd ng asawa ay magbabayad siya. Subalit ngayon ay taong 2024 na perohindi parin siya', 'magbayad ng utang ayon sa napagkasunduang araw, nais kong singilin na may dagdag na tub si Mang Erning sapagkat ang due ng kanyang utang ay lagpas lagpas sa napagkasunduang araw. Ang napagkasunduan na bayad ay 15k ngunit nais ko siyang tubuan pa ng 10k pa', 'Unlawful use of means of publication and unlawful utterances (Art. 154)', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(102, 93, 61, '01-565-0324', '2024-03-03 17:05:00', '0000-00-00', 'Erning Diamante', 'Popoy Kaloy', 'Isang taon na hindi nakakapagbayad sa napagusapan na utang. Noong January 2023 nangutang siya ng limang libo sa akin, ipinangako niya na sa isang linggo pagkarating ng sahpd ng asawa ay magbabayad siya. Subalit ngayon ay taong 2024 na perohindi parin siya', 'magbayad ng utang ayon sa napagkasunduang araw, nais kong singilin na may dagdag na tubo si Mang Erning sapagkat ang due ng kanyang utang ay lagpas lagpas sa napagkasunduang araw. Ang napagkasunduan na bayad ay 1k ngunit nais ko siyang tubuan pa ng 500 pa', 'Responsibility of participants in a duel (Art. 260)', NULL, 'Civil', 'Unsettled', 'Pending', 0),
(103, 93, 61, '02-565-0324', '2024-03-12 00:54:00', NULL, 'Lisa Samson', 'Popoy Kaloy', 'Isang taon na hindi nakakapagbayad sa napagusapan na utang. Noong January 2023 nangutang siya ng limang libo sa akin, ipinangako niya na sa isang linggo pagkarating ng sahpd ng asawa ay magbabayad siya. Subalit ngayon ay taong 2024 na perohindi parin siya', 'Sinasaktan ang mga anak namin at binubugbog ako', 'nanuntok lang bigla', '', 'Civil', 'Unsettled', 'Pending', 0),
(104, 93, 61, '03-565-0324', '2024-03-12 00:57:00', '0000-00-00', 'Prince Zydrick Salazar', 'Angel May ', NULL, NULL, 'nagnakaw ng mangga sa aming bakuran', NULL, 'Civil', 'Unsettled', 'Pending', 0);

-- --------------------------------------------------------

--
-- Table structure for table `hearings`
--

CREATE TABLE `hearings` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `hearing_number` varchar(255) NOT NULL,
  `form_used` varchar(50) NOT NULL,
  `made_date` date DEFAULT NULL,
  `received_date` date NOT NULL,
  `appear_date` timestamp NULL DEFAULT NULL,
  `resp_date` date DEFAULT NULL,
  `scenario` int(11) DEFAULT NULL,
  `scenario_info` varchar(255) DEFAULT NULL,
  `officer` varchar(50) NOT NULL,
  `settlement` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `fraud_check` tinyint(1) DEFAULT 0,
  `fraud_text` text DEFAULT NULL,
  `violence_check` tinyint(1) DEFAULT 0,
  `violence_text` text DEFAULT NULL,
  `intimidation_check` tinyint(1) DEFAULT 0,
  `intimidation_text` text DEFAULT NULL,
  `fourth_check` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hearings`
--

INSERT INTO `hearings` (`id`, `complaint_id`, `hearing_number`, `form_used`, `made_date`, `received_date`, `appear_date`, `resp_date`, `scenario`, `scenario_info`, `officer`, `settlement`, `created`, `fraud_check`, `fraud_text`, `violence_check`, `violence_text`, `intimidation_check`, `intimidation_text`, `fourth_check`) VALUES
(254, 67, '1st', '11', NULL, '2021-06-06', NULL, NULL, NULL, NULL, 'Ephraim Villanueva', NULL, '2024-02-11 09:47:45', 0, NULL, 0, NULL, 0, NULL, 0),
(255, 67, '1st', '7', '2024-02-12', '2024-02-12', NULL, NULL, NULL, NULL, '', NULL, '2024-02-12 05:02:15', 0, NULL, 0, NULL, 0, NULL, 0),
(256, 67, '1st', '11', NULL, '2024-02-11', NULL, NULL, NULL, NULL, 'Timothy Magdalena', NULL, '2024-02-12 05:19:31', 0, NULL, 0, NULL, 0, NULL, 0),
(257, 67, '1st', '11', NULL, '2022-12-16', NULL, NULL, NULL, NULL, 'Ephraim Villanueva', NULL, '2024-02-12 05:26:48', 0, NULL, 0, NULL, 0, NULL, 0),
(260, 68, '1st', '8', '2024-02-01', '2024-02-01', '2024-02-01 03:11:00', NULL, NULL, NULL, '', NULL, '2024-02-12 05:55:57', 0, NULL, 0, NULL, 0, NULL, 0),
(261, 68, '1st', '8', '2024-02-01', '2024-02-01', '2024-02-01 03:11:00', NULL, NULL, NULL, '', NULL, '2024-02-12 05:56:02', 0, NULL, 0, NULL, 0, NULL, 0),
(262, 72, '1st', '7', '2024-02-12', '2024-02-11', NULL, NULL, NULL, NULL, '', NULL, '2024-02-12 15:13:00', 0, NULL, 0, NULL, 0, NULL, 0),
(263, 72, '1st', '8', '2023-05-12', '2024-06-13', '2022-04-11 03:11:00', NULL, NULL, NULL, '', NULL, '2024-02-12 15:13:27', 0, NULL, 0, NULL, 0, NULL, 0),
(264, 67, '1st', '8', '2022-05-12', '2023-05-13', '2021-03-11 03:11:00', NULL, NULL, NULL, '', NULL, '2024-02-14 06:35:34', 0, NULL, 0, NULL, 0, NULL, 0),
(265, 74, '1st', '7', '2024-02-14', '2024-02-25', NULL, NULL, NULL, NULL, '', NULL, '2024-02-14 07:40:13', 0, NULL, 0, NULL, 0, NULL, 0),
(266, 74, '1st', '8', '2022-03-01', '2024-02-15', '2021-05-11 12:59:00', NULL, NULL, NULL, '', NULL, '2024-02-15 00:28:15', 0, NULL, 0, NULL, 0, NULL, 0),
(267, 74, '1st', '9', '2018-06-05', '2021-09-05', '2024-01-05 11:57:00', '2018-04-04', 3, 'John Doe', 'CHRISTOPHER VILLANUEVA', NULL, '2024-02-15 00:30:35', 0, NULL, 0, NULL, 0, NULL, 0),
(268, 76, '1st', '7', '2024-02-15', '2024-02-15', NULL, NULL, NULL, NULL, '', NULL, '2024-02-15 00:41:04', 0, NULL, 0, NULL, 0, NULL, 0),
(269, 79, '1st', '7', '2024-02-15', '2024-02-15', NULL, NULL, NULL, NULL, '', NULL, '2024-02-15 01:29:53', 0, NULL, 0, NULL, 0, NULL, 0),
(270, 79, '2nd', '8', '2024-02-15', '2024-02-15', '2024-02-15 09:30:00', NULL, NULL, NULL, '', NULL, '2024-02-15 01:34:14', 0, NULL, 0, NULL, 0, NULL, 0),
(271, 79, '2nd', '9', '2024-02-15', '2024-02-15', '2024-02-15 09:30:00', '2024-02-15', 1, '', 'Phil Bojo', NULL, '2024-02-15 01:36:22', 0, NULL, 0, NULL, 0, NULL, 0),
(272, 87, '', '17', '2024-02-11', '2024-02-21', NULL, '2024-02-24', NULL, NULL, '', NULL, '2024-02-20 05:45:09', 0, NULL, 0, NULL, 0, NULL, 0),
(273, 87, '1st', '17', '2024-02-07', '2024-02-09', NULL, '2024-02-08', NULL, NULL, '', NULL, '2024-02-20 05:45:41', 0, NULL, 0, NULL, 0, NULL, 0),
(274, 95, '1st', '8', '2024-02-20', '2024-02-20', '2024-02-20 00:12:00', NULL, NULL, NULL, '', NULL, '2024-02-20 06:59:49', 0, NULL, 0, NULL, 0, NULL, 0),
(275, 95, '1st', '10', '2024-02-20', '2024-02-12', '2024-02-12 15:13:00', NULL, NULL, NULL, '', NULL, '2024-02-20 07:13:07', 0, NULL, 0, NULL, 0, NULL, 0),
(276, 92, '1st', '8', '2024-02-21', '2024-02-20', '2024-02-20 12:37:00', NULL, NULL, NULL, '', NULL, '2024-02-21 01:38:02', 0, NULL, 0, NULL, 0, NULL, 0),
(277, 95, '1st', '7', '2024-02-21', '2024-02-21', NULL, NULL, NULL, NULL, '', NULL, '2024-02-21 10:33:07', 0, NULL, 0, NULL, 0, NULL, 0),
(278, 95, '1th', '8', '2024-02-11', '2024-02-11', '2024-02-11 11:11:00', NULL, NULL, NULL, '', NULL, '2024-02-22 02:20:01', 0, NULL, 0, NULL, 0, NULL, 0),
(279, 98, '1th', '7', '2024-02-22', '2024-02-22', NULL, NULL, NULL, NULL, '', NULL, '2024-02-22 03:08:41', 0, NULL, 0, NULL, 0, NULL, 0),
(280, 102, '0', '8', '2024-03-09', '2024-03-09', '2024-01-12 00:41:00', NULL, NULL, NULL, '', NULL, '2024-03-05 00:41:50', 0, NULL, 0, NULL, 0, NULL, 0),
(282, 102, '0', '26', '2024-03-08', '0000-00-00', NULL, NULL, NULL, NULL, '   ', '', '2024-03-09 07:25:59', 1, NULL, 0, NULL, 0, NULL, 0),
(283, 102, '1th', '26', '2024-03-09', '0000-00-00', NULL, NULL, NULL, NULL, '   ', '', '2024-03-09 07:29:10', 1, NULL, 0, NULL, 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `lupons`
--

CREATE TABLE `lupons` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name1` varchar(255) DEFAULT NULL,
  `name2` varchar(255) DEFAULT NULL,
  `name3` varchar(255) DEFAULT NULL,
  `name4` varchar(255) DEFAULT NULL,
  `name5` varchar(255) DEFAULT NULL,
  `name6` varchar(255) DEFAULT NULL,
  `name7` varchar(255) DEFAULT NULL,
  `name8` varchar(255) DEFAULT NULL,
  `name9` varchar(255) DEFAULT NULL,
  `name10` varchar(255) DEFAULT NULL,
  `name11` varchar(255) DEFAULT NULL,
  `name12` varchar(255) DEFAULT NULL,
  `name13` varchar(255) DEFAULT NULL,
  `name14` varchar(255) DEFAULT NULL,
  `name15` varchar(255) DEFAULT NULL,
  `name16` varchar(255) DEFAULT NULL,
  `name17` varchar(255) DEFAULT NULL,
  `name18` varchar(255) DEFAULT NULL,
  `name19` varchar(255) DEFAULT NULL,
  `name20` varchar(255) DEFAULT NULL,
  `punong_barangay` varchar(255) DEFAULT NULL,
  `lupon_chairman` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `appoint` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lupons`
--

INSERT INTO `lupons` (`id`, `user_id`, `name1`, `name2`, `name3`, `name4`, `name5`, `name6`, `name7`, `name8`, `name9`, `name10`, `name11`, `name12`, `name13`, `name14`, `name15`, `name16`, `name17`, `name18`, `name19`, `name20`, `punong_barangay`, `lupon_chairman`, `created_at`, `appoint`) VALUES
(69, 72, 'Prince Zydrick R. Salazar', 'Mary Grace Bautista', 'Angel May De Guzman', 'Carl Janzell Oropesa', 'Kisha Abrenilla', 'Phil Bojo Repotente', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KIM ATIENZA', 'KORINA SANCHEZ', '2024-01-01 14:51:08', 0),
(70, 72, 'Prince Zydrick R. Salazar', 'Mary Grace Bautista', 'Angel May De Guzman', 'Carl Janzell Oropesa', 'Kisha Abrenilla', 'Phil Bojo Repotente', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KIM ATIENZA', 'KORINA SANCHEZ', '2024-01-01 14:52:56', 1),
(71, 80, 'John', 'Mary Grace Bautista', 'Gon', 'Bang Ryan', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KIM ATIENZA', 'GORDON RAMSAY', '2024-01-02 00:48:39', 0),
(72, 80, 'John', 'Mary Grace Bautista', 'Gon', 'Bang Ryan', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KIM ATIENZA', 'GORDON RAMSAY', '2024-01-02 00:51:36', 1),
(73, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-02-08 08:02:35', 0),
(74, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-02-08 08:02:44', 0),
(75, 73, 'Name 1', 'Name 2', 'Name 3', 'Name 4', 'Name 5', 'Name 6', 'Name 7', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NAME BRGY', 'NAME CHAIR ', '2024-02-12 07:28:44', 0),
(76, 73, 'Name 1', 'Name 2', 'Name 3', 'Name 4', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '2024-02-12 07:29:26', 1),
(77, 88, 'hello', 'hi', 'ho', 'wawa', 'afwa', 'adaw', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'YES', 'NO', '2024-02-12 09:21:30', 0),
(78, 88, 'hello', 'hi', 'ho', 'wawa', 'afwa', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '2024-02-12 09:25:18', 1),
(79, 91, 'Jose ', 'Pedro', 'Jinny', 'Jojo', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'JOHNNY', 'JOHNNA', '2024-02-12 15:07:25', 0),
(80, 91, 'Jose ', 'Pedro', 'Marites', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '2024-02-12 15:08:08', 1),
(81, 93, 'Angel May DeGuzman', 'Kisha', 'Prince Salazar', 'Phil Bojo', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'IAN NORA KALAW', 'JAIME BERON', '2024-02-13 01:54:10', 0),
(82, 93, 'Angel May DeGuzman', 'Kisha', 'Prince Salazar', 'Phil Bojo', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'IAN NORA KALAW', 'JAIME BERON', '2024-02-13 01:56:27', 1),
(83, 94, 'Prince Salazar', 'Kisha Abrenilla', 'Angle De Guzman', 'Carl Oropeza', 'Phil Bojo', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'JAIME BUENDIA', 'JAIME BERON', '2024-02-15 00:41:34', 0),
(84, 94, 'Prince Salazar', 'Kisha Abrenilla', 'Angle De Guzman', 'Carl Oropeza', 'Phil Bojo', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'JAIME BUENDIA', 'JAIME BERON', '2024-02-15 00:41:49', 1),
(85, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-02-21 23:25:56', 0),
(86, 102, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-03-01 05:26:53', 0);

-- --------------------------------------------------------

--
-- Table structure for table `municipalities`
--

CREATE TABLE `municipalities` (
  `id` int(11) NOT NULL,
  `municipality_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `municipalities`
--

INSERT INTO `municipalities` (`id`, `municipality_name`) VALUES
(27, 'Calamba'),
(28, 'Alaminos'),
(30, 'Los Baños'),
(31, 'Calauan'),
(34, 'San Pablo'),
(35, 'San Pedro'),
(36, 'Binan'),
(38, 'Bay'),
(39, 'Santa Rosa');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `report_date` date DEFAULT curdate(),
  `mayor` varchar(100) DEFAULT NULL,
  `region` varchar(4) DEFAULT NULL,
  `budget` varchar(20) DEFAULT NULL,
  `population` varchar(10) DEFAULT NULL,
  `totalcase` int(11) DEFAULT NULL,
  `numlupon` int(11) DEFAULT NULL,
  `male` int(11) DEFAULT NULL,
  `female` int(11) DEFAULT NULL,
  `landarea` varchar(25) DEFAULT NULL,
  `criminal` int(11) DEFAULT NULL,
  `civil` int(11) DEFAULT NULL,
  `others` int(11) DEFAULT NULL,
  `totalNature` int(11) DEFAULT NULL,
  `media` int(11) DEFAULT NULL,
  `concil` int(11) DEFAULT NULL,
  `arbit` int(11) DEFAULT NULL,
  `totalSet` int(11) DEFAULT NULL,
  `pending` int(11) DEFAULT NULL,
  `dismissed` int(11) DEFAULT NULL,
  `repudiated` int(11) DEFAULT NULL,
  `certcourt` int(11) DEFAULT NULL,
  `dropped` int(11) DEFAULT NULL,
  `totalUnset` int(11) DEFAULT NULL,
  `outsideBrgy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `user_id`, `barangay_id`, `report_date`, `mayor`, `region`, `budget`, `population`, `totalcase`, `numlupon`, `male`, `female`, `landarea`, `criminal`, `civil`, `others`, `totalNature`, `media`, `concil`, `arbit`, `totalSet`, `pending`, `dismissed`, `repudiated`, `certcourt`, `dropped`, `totalUnset`, `outsideBrgy`) VALUES
(26, 80, 50, '2024-02-01', 'HON. IAN NORA KALAW', 'IV-A', '12,442 pesos', '12,124', 11, 0, 6, 6, '1,7369 HECTARES', 0, 11, 0, 11, 1, 0, 0, 1, 10, 0, 0, 0, 0, 10, 0),
(28, 73, 41, '2024-02-12', 'Janzell Oropesa', 'Iv-b', '12,442 pesos', '12312', 2, 7, 0, 0, '12121', 0, 2, 0, 2, 0, 0, 0, 0, 2, 0, 0, 0, 0, 2, 0),
(29, 73, 41, '2023-02-12', 'Carl Janzell Oropesa', 'IV-B', '12,442 pesos', '12312', 4, 13, 8, 5, '12121hectares', 2, 2, 0, 4, 4, 0, 0, 4, 0, 0, 0, 0, 0, 0, 0),
(30, 72, 40, '2024-02-12', 'Joaquin Chipeco', 'IV-C', '112,123PHP', '1212', 1, 4, 2, 2, '4411', 0, 1, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0),
(31, 80, 50, '2023-02-01', 'HON. IAN NORA KALAW', 'IV-A', '12,442 pesos', '12,124', 2, 12, 11, 1, '1,7369 HECTARES', 0, 2, 0, 2, 1, 0, 0, 1, 1, 0, 0, 0, 0, 1, 0),
(32, 91, 59, '2024-02-12', 'Joaquin Chipeco', 'awa', '12112', '121', 1, 4, 12, 11, '11', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(33, 94, 62, '2024-02-15', 'Anthony Ton Genuino', 'IV-A', '1,000,000', '115, 353', 2, 5, 0, 0, '6505 has.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(34, 93, 61, '2024-02-15', 'Anthony Ton Genuino', 'IV-A', '238,123,000.00', '123,000', 9, 9, 4, 5, '54.22 km²', 2, 7, 0, 9, 1, 1, 0, 2, 6, 1, 0, 0, 0, 7, 0),
(35, 93, 61, '2024-03-04', 'jksaaksjd', '', '', '', 1, 4, 0, 0, '', 0, 1, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `security`
--

CREATE TABLE `security` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `question1` varchar(255) DEFAULT NULL,
  `answer1` varchar(255) DEFAULT NULL,
  `question2` varchar(255) DEFAULT NULL,
  `answer2` varchar(255) DEFAULT NULL,
  `question3` varchar(255) DEFAULT NULL,
  `answer3` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `security`
--

INSERT INTO `security` (`id`, `user_id`, `question1`, `answer1`, `question2`, `answer2`, `question3`, `answer3`) VALUES
(7, 72, '1', '$2y$10$lkK4GgCfQnYcJRpAttBvzeeihiEgORs4kVSwVJC3Mf7DUNHRNfRxm', '2', '$2y$10$NI8FErkufNuIYBfbTTv8SOvFiZULBk64C8SRCrpwtM29t/NGheRvq', '3', '$2y$10$gNnFJO5jvsRQevwmAazL2ODH/HbOGNR1dykhuXMEfKyw8YJcFz5MK'),
(8, 73, '1', '$2y$10$I9x/y2thUSyEknIIu89b1OZxJZnlC5cs4AhS8/H9ei.kDsAQ/HpbO', '1', '$2y$10$o1zGHJ0FFMf1TH19yhpVue8wl6lvOpF2Hwfu5h5sCDr5kjnzToINW', '4', '$2y$10$TyDV8BIdLp5mNxqur.UeH.l8XOj5cixm1FV7wsjjVpiV5H4Awzcta'),
(9, 71, '1', '$2y$10$iaUwWglTya8ftAnhyzSXB.jz7ekijvhsMUitKQzQr9kX/jBoaSSH.', '2', '$2y$10$//hHr4.66hob/cysuzupWO2yY1xNCsgut6pjHy1QGp.JJMD3qxQa2', '3', '$2y$10$qMUf.nFTW.93TAE.Av6heuXu03Im5l1gb1/UBblSWb3OGEExhwZru'),
(10, 80, '1', '$2y$10$nuCz1GB4zLP82sk6UYh1CuIReG5jafYZphOCRvxRp7Vr4Y7Xrkiq6', '2', '$2y$10$zaAz08BX3Y1tL1lKQXVykeXZ863D/kSz0e1PdeBp5XF463efxAidq', '3', '$2y$10$5UTIIKx3OujN./ZMweoaweaRTwOW6cZKFf1AQzS3XgzIvhYUP20hi'),
(11, 93, '1', '$2y$10$IHpkB9zuWYEjAL7pnpU2kOqaZlrGngMZF14Fd17C23Jf6b.h5PA3u', '3', '$2y$10$WeM/JgXL85WUM9wUg8mQW.O4UFlDx8udlUkrVbyXVFl9SXCX3FfOW', '4', '$2y$10$TVfWct3csDdfo0avyodeT.N4762VrINkbwYEVvT/3SwHL9DkWsPIq');

-- --------------------------------------------------------

--
-- Table structure for table `upload_files`
--

CREATE TABLE `upload_files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `barangay_id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `upload_files`
--

INSERT INTO `upload_files` (`id`, `user_id`, `barangay_id`, `case_id`, `file_name`, `file_path`, `upload_date`) VALUES
(2, 93, 61, 98, 'download (1).jpg', 'uploads/93/98/download (1).jpg', '2024-02-22 03:12:07');

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
(74, 'brgybatongmalake', 'Barangay', 'Batong Malake', '$2y$10$TrBHp44V3RHaAaVwmtAreeDmUU5iSlSWXmXv6vmocjS6NtT.K8KF6', '', '09099762440', 'user', 27, 42, '2023-08-11 01:53:24', '74.jpg', 1, 0, NULL, NULL, NULL),
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
(101, 'CLGOO Santa.Rosa', 'Santa.Rosa', 'City', '$2y$10$TrBHp44V3RHaAaVwmtAreeDmUU5iSlSWXmXv6vmocjS6NtT.K8KF6', 'adminstarosa@gmail.com', '09104807052', 'admin', 39, NULL, '2024-02-22 02:35:04', NULL, 0, 0, NULL, NULL, NULL),
(102, 'secretary', 'aplaya', 'santa.rosa', '$2y$10$TrBHp44V3RHaAaVwmtAreeDmUU5iSlSWXmXv6vmocjS6NtT.K8KF6', 'clustera_aplaya_ekp@gmail.com', '09104907052', 'user', 39, 0, '2024-02-22 02:38:27', '102.jpeg', 1, 0, NULL, 'starosa.png', 'logoo.png');

-- --------------------------------------------------------

--
-- Table structure for table `user_files`
--

CREATE TABLE `user_files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `barangay_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_files`
--

INSERT INTO `user_files` (`id`, `user_id`, `file_name`, `file_path`, `uploaded_at`, `barangay_id`) VALUES
(10, 93, 'narrative-ni-jear.pdf', 'uploadsLP/narrative-ni-jear.pdf', '2024-03-01 04:59:39', 61),
(11, 93, 'kp_form3_01-000-0324.pdf', 'C:\\xampppp\\htdocs\\eKPsys\\uploadsLP\\kp_form3_01-000-0324.pdf', '2024-03-04 00:42:57', 61),
(12, 93, 'OJT-Narrative-Report-Format.docx', 'C:\\xampppp\\htdocs\\eKPsys\\uploadsLP\\OJT-Narrative-Report-Format.docx', '2024-03-04 00:43:08', 61),
(13, 93, 'template-conso-report-KP (1).xlsx - Sheet1 (2).pdf', 'C:\\xampppp\\htdocs\\eKPsys\\uploadsLP\\template-conso-report-KP (1).xlsx - Sheet1 (2).pdf', '2024-03-04 00:45:58', 61),
(14, 93, 'template-conso-report-KP (1).xlsx - Sheet1 (2).pdf', 'uploadsLP/template-conso-report-KP (1).xlsx - Sheet1 (2).pdf', '2024-03-04 00:46:30', 61),
(15, 93, 'starosa.png', 'uploadsLP/starosa.png', '2024-03-04 00:46:52', 61),
(16, 93, 'tuesdays of march.docx', 'uploadsLP/tuesdays of march.docx', '2024-03-05 00:54:04', 61);

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `activity` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`log_id`, `user_id`, `timestamp`, `activity`) VALUES
(1, 71, '2024-02-12 05:59:29', 'User logged in'),
(2, 80, '2024-02-12 06:02:08', 'User logged in'),
(3, 80, '2024-02-12 06:03:42', 'User logged in'),
(4, 71, '2024-02-12 06:04:27', 'User logged in'),
(5, 79, '2024-02-12 08:43:48', 'User logged in'),
(6, 80, '2024-02-12 09:19:16', 'User logged in'),
(7, 79, '2024-02-12 09:21:10', 'User logged in'),
(8, 88, '2024-02-12 09:21:22', 'User logged in'),
(9, 88, '2024-02-12 09:24:48', 'User logged in'),
(10, 89, '2024-02-12 14:59:48', 'User logged in'),
(11, 89, '2024-02-12 15:00:03', 'User logged in'),
(12, 89, '2024-02-12 15:00:21', 'User logged in'),
(13, 89, '2024-02-12 15:03:55', 'User logged in'),
(14, 89, '2024-02-12 15:04:11', 'User logged in'),
(15, 89, '2024-02-12 15:05:09', 'User logged in'),
(16, 91, '2024-02-12 15:05:50', 'User logged in'),
(17, 71, '2024-02-12 15:28:15', 'User logged in'),
(18, 80, '2024-02-12 15:53:36', 'User logged in'),
(19, 80, '2024-02-12 16:32:44', 'User logged in'),
(20, 91, '2024-02-13 00:12:14', 'User logged in'),
(21, 89, '2024-02-13 00:12:25', 'User logged in'),
(22, 89, '2024-02-13 00:14:26', 'User logged in'),
(23, 80, '2024-02-13 00:20:30', 'User logged in'),
(24, 89, '2024-02-13 00:26:34', 'User logged in'),
(25, 89, '2024-02-13 00:27:49', 'User logged in'),
(26, 80, '2024-02-13 00:29:24', 'User logged in'),
(27, 89, '2024-02-13 00:32:04', 'User logged in'),
(28, 80, '2024-02-13 00:33:09', 'User logged in'),
(29, 89, '2024-02-13 01:13:15', 'User logged in'),
(30, 79, '2024-02-13 01:13:41', 'User logged in'),
(31, 79, '2024-02-13 01:14:33', 'User logged in'),
(32, 80, '2024-02-13 01:15:50', 'User logged in'),
(33, 89, '2024-02-13 01:20:29', 'User logged in'),
(34, 79, '2024-02-13 01:20:39', 'User logged in'),
(35, 79, '2024-02-13 01:52:07', 'User logged in'),
(36, 93, '2024-02-13 01:53:51', 'User logged in'),
(37, 89, '2024-02-13 03:32:32', 'User logged in'),
(38, 93, '2024-02-13 03:44:27', 'User logged in'),
(39, 80, '2024-02-14 06:40:17', 'User logged in'),
(40, 80, '2024-02-14 07:33:31', 'User logged in'),
(41, 80, '2024-02-14 07:34:13', 'User logged in'),
(42, 79, '2024-02-14 07:35:05', 'User logged in'),
(43, 89, '2024-02-14 07:35:31', 'User logged in'),
(44, 79, '2024-02-14 07:36:05', 'User logged in'),
(45, 79, '2024-02-14 07:36:27', 'User logged in'),
(46, 79, '2024-02-14 07:36:28', 'User logged in'),
(47, 80, '2024-02-14 07:37:47', 'User logged in'),
(48, 79, '2024-02-14 07:38:40', 'User logged in'),
(49, 80, '2024-02-14 07:39:10', 'User logged in'),
(50, 79, '2024-02-14 07:40:14', 'User logged in'),
(51, 72, '2024-02-14 07:41:40', 'User logged in'),
(52, 1, '2024-02-14 07:41:55', 'User logged in'),
(53, 79, '2024-02-14 09:31:36', 'User logged in'),
(54, 79, '2024-02-14 20:38:15', 'User logged in'),
(55, 79, '2024-02-14 20:41:10', 'User logged in'),
(56, 79, '2024-02-14 20:45:58', 'User logged in'),
(57, 79, '2024-02-14 21:29:27', 'User logged in'),
(58, 79, '2024-02-14 22:29:27', 'User logged in'),
(59, 80, '2024-02-15 00:05:28', 'User logged in'),
(60, 80, '2024-02-15 00:07:26', 'User logged in'),
(61, 79, '2024-02-15 00:09:14', 'User logged in'),
(62, 71, '2024-02-15 00:09:34', 'User logged in'),
(63, 79, '2024-02-15 00:10:02', 'User logged in'),
(64, 79, '2024-02-15 00:11:58', 'User logged in'),
(65, 79, '2024-02-15 00:15:17', 'User logged in'),
(66, 80, '2024-02-15 00:27:09', 'User logged in'),
(67, 94, '2024-02-15 00:27:45', 'User logged in'),
(68, 79, '2024-02-15 00:43:37', 'User logged in'),
(69, 79, '2024-02-15 00:52:05', 'User logged in'),
(70, 79, '2024-02-15 00:52:06', 'User logged in'),
(71, 71, '2024-02-15 00:52:58', 'User logged in'),
(72, 94, '2024-02-15 00:53:17', 'User logged in'),
(73, 79, '2024-02-15 00:54:42', 'User logged in'),
(74, 79, '2024-02-15 00:55:10', 'User logged in'),
(75, 79, '2024-02-15 00:56:05', 'User logged in'),
(76, 79, '2024-02-15 00:56:33', 'User logged in'),
(77, 93, '2024-02-15 00:57:34', 'User logged in'),
(78, 93, '2024-02-15 00:57:44', 'User logged in'),
(79, 93, '2024-02-15 00:57:52', 'User logged in'),
(80, 79, '2024-02-15 00:59:36', 'User logged in'),
(81, 71, '2024-02-15 00:59:51', 'User logged in'),
(82, 79, '2024-02-15 01:00:04', 'User logged in'),
(83, 93, '2024-02-15 01:00:21', 'User logged in'),
(84, 79, '2024-02-15 01:01:03', 'User logged in'),
(85, 79, '2024-02-15 01:01:48', 'User logged in'),
(86, 79, '2024-02-15 01:04:15', 'User logged in'),
(87, 93, '2024-02-15 01:05:47', 'User logged in'),
(88, 79, '2024-02-15 01:07:30', 'User logged in'),
(89, 79, '2024-02-15 01:11:09', 'User logged in'),
(90, 93, '2024-02-15 01:15:40', 'User logged in'),
(91, 79, '2024-02-15 01:21:43', 'User logged in'),
(92, 93, '2024-02-15 01:23:41', 'User logged in'),
(93, 79, '2024-02-15 01:41:42', 'User logged in'),
(94, 79, '2024-02-15 01:43:09', 'User logged in'),
(95, 79, '2024-02-15 01:44:23', 'User logged in'),
(96, 93, '2024-02-15 01:51:23', 'User logged in'),
(97, 79, '2024-02-15 01:57:18', 'User logged in'),
(98, 79, '2024-02-15 05:54:01', 'User logged in'),
(99, 93, '2024-02-15 05:54:34', 'User logged in'),
(100, 93, '2024-02-15 06:54:00', 'User logged in'),
(101, 93, '2024-02-15 07:07:01', 'User logged in'),
(102, 93, '2024-02-16 00:49:27', 'User logged in'),
(103, 93, '2024-02-16 00:56:27', 'User logged in'),
(104, 93, '2024-02-16 01:02:02', 'User logged in'),
(105, 93, '2024-02-16 01:18:38', 'User logged in'),
(106, 79, '2024-02-16 01:19:47', 'User logged in'),
(107, 93, '2024-02-16 03:12:13', 'User logged in'),
(108, 93, '2024-02-16 04:51:06', 'User logged in'),
(109, 93, '2024-02-16 05:12:10', 'User logged in'),
(110, 93, '2024-02-16 05:23:17', 'User logged in'),
(111, 93, '2024-02-16 08:05:58', 'User logged in'),
(112, 93, '2024-02-19 00:36:37', 'User logged in'),
(113, 93, '2024-02-19 01:43:37', 'User logged in'),
(114, 93, '2024-02-19 04:55:32', 'User logged in'),
(115, 79, '2024-02-19 04:57:14', 'User logged in'),
(116, 93, '2024-02-19 05:22:22', 'User logged in'),
(117, 93, '2024-02-19 05:27:02', 'User logged in'),
(118, 93, '2024-02-19 05:27:03', 'User logged in'),
(119, 93, '2024-02-19 05:27:05', 'User logged in'),
(120, 93, '2024-02-19 05:36:45', 'User logged in'),
(121, 93, '2024-02-19 05:46:06', 'User logged in'),
(122, 93, '2024-02-19 06:24:15', 'User logged in'),
(123, 72, '2024-02-19 07:24:52', 'User logged in'),
(124, 93, '2024-02-19 07:28:48', 'User logged in'),
(125, 93, '2024-02-19 08:19:00', 'User logged in'),
(126, 93, '2024-02-19 08:35:30', 'User logged in'),
(127, 93, '2024-02-19 08:35:40', 'User logged in'),
(128, 93, '2024-02-19 08:52:06', 'User logged in'),
(129, 93, '2024-02-19 08:53:16', 'User logged in'),
(130, 93, '2024-02-19 08:54:10', 'User logged in'),
(131, 93, '2024-02-19 08:57:52', 'User logged in'),
(132, 93, '2024-02-20 01:00:52', 'User logged in'),
(133, 93, '2024-02-20 01:01:03', 'User logged in'),
(134, 93, '2024-02-20 01:18:38', 'User logged in'),
(135, 93, '2024-02-20 02:35:18', 'User logged in'),
(136, 93, '2024-02-20 02:53:14', 'User logged in'),
(137, 93, '2024-02-20 02:56:00', 'User logged in'),
(138, 93, '2024-02-20 03:04:38', 'User logged in'),
(139, 93, '2024-02-20 05:08:28', 'User logged in'),
(140, 93, '2024-02-20 05:09:46', 'User logged in'),
(141, 80, '2024-02-20 05:29:47', 'User logged in'),
(142, 93, '2024-02-20 05:32:44', 'User logged in'),
(143, 93, '2024-02-20 05:47:32', 'User logged in'),
(144, 93, '2024-02-20 05:47:51', 'User logged in'),
(145, 80, '2024-02-20 05:52:21', 'User logged in'),
(146, 80, '2024-02-20 05:53:05', 'User logged in'),
(147, 93, '2024-02-20 07:11:59', 'User logged in'),
(148, 93, '2024-02-20 07:30:47', 'User logged in'),
(149, 93, '2024-02-20 08:08:09', 'User logged in'),
(150, 93, '2024-02-20 08:09:50', 'User logged in'),
(151, 93, '2024-02-20 08:14:04', 'User logged in'),
(152, 93, '2024-02-20 08:16:20', 'User logged in'),
(153, 93, '2024-02-20 08:17:20', 'User logged in'),
(154, 93, '2024-02-20 08:56:05', 'User logged in'),
(155, 80, '2024-02-20 08:57:37', 'User logged in'),
(156, 93, '2024-02-20 09:03:14', 'User logged in'),
(157, 93, '2024-02-20 10:26:23', 'User logged in'),
(158, 93, '2024-02-20 13:33:38', 'User logged in'),
(159, 93, '2024-02-20 15:06:29', 'User logged in'),
(160, 93, '2024-02-21 00:26:56', 'User logged in'),
(161, 93, '2024-02-21 00:37:00', 'User logged in'),
(162, 93, '2024-02-21 00:38:51', 'User logged in'),
(163, 93, '2024-02-21 00:39:32', 'User logged in'),
(164, 93, '2024-02-21 00:40:38', 'User logged in'),
(165, 93, '2024-02-21 02:00:22', 'User logged in'),
(166, 93, '2024-02-21 04:01:44', 'User logged in'),
(167, 93, '2024-02-21 05:01:22', 'User logged in'),
(168, 93, '2024-02-21 05:07:30', 'User logged in'),
(169, 93, '2024-02-21 05:12:40', 'User logged in'),
(170, 93, '2024-02-21 05:15:12', 'User logged in'),
(171, 93, '2024-02-21 05:15:13', 'User logged in'),
(172, 93, '2024-02-21 05:21:37', 'User logged in'),
(173, 93, '2024-02-21 05:21:38', 'User logged in'),
(174, 93, '2024-02-21 05:21:49', 'User logged in'),
(175, 93, '2024-02-21 05:24:23', 'User logged in'),
(176, 93, '2024-02-21 05:26:43', 'User logged in'),
(177, 93, '2024-02-21 05:28:16', 'User logged in'),
(178, 93, '2024-02-21 08:08:38', 'User logged in'),
(179, 93, '2024-02-21 08:47:49', 'User logged in'),
(180, 80, '2024-02-21 09:05:07', 'User logged in'),
(181, 79, '2024-02-21 09:08:14', 'User logged in'),
(182, 79, '2024-02-21 09:08:54', 'User logged in'),
(183, 93, '2024-02-21 09:09:57', 'User logged in'),
(184, 93, '2024-02-21 09:21:23', 'User logged in'),
(185, 93, '2024-02-21 09:27:13', 'User logged in'),
(186, 93, '2024-02-21 09:29:14', 'User logged in'),
(187, 1, '2024-02-21 09:29:25', 'User logged in'),
(188, 1, '2024-02-21 09:34:40', 'User logged in'),
(189, 93, '2024-02-21 09:36:25', 'User logged in'),
(190, 80, '2024-02-21 09:39:42', 'User logged in'),
(191, 93, '2024-02-21 09:40:34', 'User logged in'),
(192, 93, '2024-02-21 09:41:24', 'User logged in'),
(193, 93, '2024-02-21 09:41:51', 'User logged in'),
(194, 80, '2024-02-21 09:43:13', 'User logged in'),
(195, 80, '2024-02-21 09:45:43', 'User logged in'),
(196, 93, '2024-02-21 09:46:28', 'User logged in'),
(197, 93, '2024-02-21 10:05:15', 'User logged in'),
(198, 80, '2024-02-21 10:21:14', 'User logged in'),
(199, 79, '2024-02-21 10:21:36', 'User logged in'),
(200, 1, '2024-02-21 10:21:59', 'User logged in'),
(201, 93, '2024-02-21 10:40:02', 'User logged in'),
(202, 1, '2024-02-21 10:44:19', 'User logged in'),
(203, 1, '2024-02-21 10:44:20', 'User logged in'),
(204, 1, '2024-02-21 10:50:24', 'User logged in'),
(205, 79, '2024-02-21 10:50:50', 'User logged in'),
(206, 93, '2024-02-21 10:51:54', 'User logged in'),
(207, 93, '2024-02-21 10:52:16', 'User logged in'),
(208, 98, '2024-02-21 10:55:30', 'User logged in'),
(209, 71, '2024-02-21 10:59:00', 'User logged in'),
(210, 1, '2024-02-21 11:00:54', 'User logged in'),
(211, 79, '2024-02-21 11:02:23', 'User logged in'),
(212, 80, '2024-02-21 11:03:16', 'User logged in'),
(213, 1, '2024-02-21 11:06:23', 'User logged in'),
(214, 80, '2024-02-21 11:09:27', 'User logged in'),
(215, 80, '2024-02-21 11:14:05', 'User logged in'),
(216, 80, '2024-02-21 13:43:17', 'User logged in'),
(217, 80, '2024-02-21 13:57:27', 'User logged in'),
(218, 93, '2024-02-21 13:58:24', 'User logged in'),
(219, 93, '2024-02-21 14:05:32', 'User logged in'),
(220, 93, '2024-02-21 15:01:32', 'User logged in'),
(221, 93, '2024-02-21 15:24:42', 'User logged in'),
(222, 80, '2024-02-21 15:57:55', 'User logged in'),
(223, 93, '2024-02-21 16:07:24', 'User logged in'),
(224, 93, '2024-02-21 16:19:31', 'User logged in'),
(225, 93, '2024-02-21 16:32:12', 'User logged in'),
(226, 93, '2024-02-21 16:32:21', 'User logged in'),
(227, 80, '2024-02-21 16:50:17', 'User logged in'),
(228, 93, '2024-02-21 17:05:07', 'User logged in'),
(229, 93, '2024-02-21 17:15:40', 'User logged in'),
(230, 93, '2024-02-21 17:18:37', 'User logged in'),
(231, 80, '2024-02-21 17:25:03', 'User logged in'),
(232, 93, '2024-02-21 18:34:40', 'User logged in'),
(233, 80, '2024-02-21 18:59:51', 'User logged in'),
(234, 79, '2024-02-21 19:16:38', 'User logged in'),
(235, 93, '2024-02-21 19:18:36', 'User logged in'),
(236, 1, '2024-02-21 19:42:42', 'User logged in'),
(237, 1, '2024-02-21 19:45:31', 'User logged in'),
(238, 99, '2024-02-21 20:29:03', 'User logged in'),
(239, 93, '2024-02-21 20:34:13', 'User logged in'),
(240, 79, '2024-02-21 20:40:08', 'User logged in'),
(241, 1, '2024-02-21 22:34:52', 'User logged in'),
(242, 99, '2024-02-21 22:35:09', 'User logged in'),
(243, 93, '2024-02-21 22:56:19', 'User logged in'),
(244, 80, '2024-02-21 23:04:03', 'User logged in'),
(245, 1, '2024-02-22 01:07:33', 'User logged in'),
(246, 93, '2024-02-22 01:11:23', 'User logged in'),
(247, 1, '2024-02-22 01:13:14', 'User logged in'),
(248, 1, '2024-02-22 01:18:23', 'User logged in'),
(249, 80, '2024-02-22 02:15:03', 'User logged in'),
(250, 93, '2024-02-22 02:18:15', 'User logged in'),
(251, 93, '2024-02-22 02:35:47', 'User logged in'),
(252, 101, '2024-02-22 02:36:27', 'User logged in'),
(253, 101, '2024-02-22 02:36:27', 'User logged in'),
(254, 101, '2024-02-22 02:39:20', 'User logged in'),
(255, 102, '2024-02-22 02:40:10', 'User logged in'),
(256, 93, '2024-02-22 02:45:53', 'User logged in'),
(257, 93, '2024-02-23 01:20:19', 'User logged in'),
(258, 93, '2024-02-23 01:29:25', 'User logged in'),
(259, 93, '2024-02-23 03:06:25', 'User logged in'),
(260, 93, '2024-02-23 03:06:39', 'User logged in'),
(261, 93, '2024-02-23 07:20:52', 'User logged in'),
(262, 93, '2024-02-24 00:38:23', 'User logged in'),
(263, 93, '2024-02-24 05:23:26', 'User logged in'),
(264, 93, '2024-02-24 11:52:07', 'User logged in'),
(265, 93, '2024-02-26 00:17:49', 'User logged in'),
(266, 93, '2024-02-26 00:56:00', 'User logged in'),
(267, 93, '2024-02-26 00:56:06', 'User logged in'),
(268, 101, '2024-03-01 04:38:56', 'User logged in'),
(269, 102, '2024-03-01 04:40:16', 'User logged in'),
(270, 93, '2024-03-01 04:42:44', 'User logged in'),
(271, 71, '2024-03-01 04:43:13', 'User logged in'),
(272, 73, '2024-03-01 04:43:27', 'User logged in'),
(273, 102, '2024-03-01 05:41:26', 'User logged in'),
(274, 102, '2024-03-01 05:53:35', 'User logged in'),
(275, 102, '2024-03-01 06:23:53', 'User logged in'),
(276, 93, '2024-03-03 23:57:10', 'User logged in'),
(277, 93, '2024-03-04 00:23:53', 'User logged in'),
(278, 93, '2024-03-04 08:47:09', 'User logged in'),
(279, 93, '2024-03-05 00:31:32', 'User logged in'),
(280, 93, '2024-03-05 00:37:36', 'User logged in'),
(281, 93, '2024-03-05 00:38:18', 'User logged in'),
(282, 93, '2024-03-05 00:48:15', 'User logged in'),
(283, 93, '2024-03-07 02:43:31', 'User logged in'),
(284, 93, '2024-03-08 00:31:21', 'User logged in'),
(285, 93, '2024-03-08 00:45:31', 'User logged in'),
(286, 71, '2024-03-08 01:08:26', 'User logged in'),
(287, 74, '2024-03-08 01:08:41', 'User logged in'),
(288, 93, '2024-03-08 01:10:29', 'User logged in'),
(289, 93, '2024-03-09 06:52:16', 'User logged in'),
(290, 79, '2024-03-10 21:02:17', 'User logged in'),
(291, 93, '2024-03-10 21:02:46', 'User logged in'),
(292, 93, '2024-03-10 22:26:25', 'User logged in'),
(293, 93, '2024-03-12 00:30:46', 'User logged in'),
(294, 93, '2024-03-12 00:48:42', 'User logged in'),
(295, 93, '2024-03-12 02:29:14', 'User logged in');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barangays`
--
ALTER TABLE `barangays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `municipality_id` (`municipality_id`);

--
-- Indexes for table `case_progress`
--
ALTER TABLE `case_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_id` (`complaint_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `BarangayID` (`BarangayID`);

--
-- Indexes for table `hearings`
--
ALTER TABLE `hearings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_id` (`complaint_id`);

--
-- Indexes for table `lupons`
--
ALTER TABLE `lupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `municipalities`
--
ALTER TABLE `municipalities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `security`
--
ALTER TABLE `security`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `upload_files`
--
ALTER TABLE `upload_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `municipality_id` (`municipality_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `user_files`
--
ALTER TABLE `user_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barangays`
--
ALTER TABLE `barangays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `case_progress`
--
ALTER TABLE `case_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `hearings`
--
ALTER TABLE `hearings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=284;

--
-- AUTO_INCREMENT for table `lupons`
--
ALTER TABLE `lupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `municipalities`
--
ALTER TABLE `municipalities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `security`
--
ALTER TABLE `security`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `upload_files`
--
ALTER TABLE `upload_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `user_files`
--
ALTER TABLE `user_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=296;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `case_progress`
--
ALTER TABLE `case_progress`
  ADD CONSTRAINT `case_progress_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`);

--
-- Constraints for table `user_files`
--
ALTER TABLE `user_files`
  ADD CONSTRAINT `user_files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
