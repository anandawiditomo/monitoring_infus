-- phpMyAdmin SQL Dump
-- version 4.9.10
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 04, 2025 at 08:39 AM
-- Server version: 5.7.42-0ubuntu0.18.04.1-log
-- PHP Version: 7.2.24-0ubuntu0.18.04.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sik`
--

-- --------------------------------------------------------

--
-- Table structure for table `rsk_infus_order`
--

CREATE TABLE `rsk_infus_order` (
  `no_rawat` varchar(20) NOT NULL,
  `kd_obat_infus` varchar(10) DEFAULT NULL,
  `volume_total` double DEFAULT NULL,
  `target_tetesan` int(11) DEFAULT NULL,
  `tgl_mulai` date DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `kd_petugas` varchar(20) DEFAULT NULL,
  `status_order` enum('Aktif','Selesai','Dihentikan') NOT NULL DEFAULT 'Aktif',
  `tgl_jam_selesai` datetime DEFAULT NULL,
  `faktor_tetes` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rsk_infus_order`
--

INSERT INTO `rsk_infus_order` (`no_rawat`, `kd_obat_infus`, `volume_total`, `target_tetesan`, `tgl_mulai`, `jam_mulai`, `kd_petugas`, `status_order`, `tgl_jam_selesai`, `faktor_tetes`) VALUES
('2025/10/02/000109', 'IV001', 500, 25, '2025-10-04', '07:08:59', 'P001', 'Aktif', NULL, 20),
('2025/10/02/000111', 'IV001', 500, 20, '2025-10-04', '05:43:29', 'P001', 'Selesai', '2025-10-04 08:21:00', 20),
('2025/10/03/000068', 'IV001', 500, 20, '2025-10-03', '15:58:21', 'P001', 'Aktif', NULL, 20),
('2025/10/03/000084', 'IV001', 500, 44, '2025-10-03', '15:55:16', 'P001', 'Aktif', NULL, 20);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rsk_infus_order`
--
ALTER TABLE `rsk_infus_order`
  ADD PRIMARY KEY (`no_rawat`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
