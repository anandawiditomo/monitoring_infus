-- phpMyAdmin SQL Dump
-- version 4.9.10
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 04, 2025 at 08:40 AM
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
-- Table structure for table `rsk_infus_monitoring`
--

CREATE TABLE `rsk_infus_monitoring` (
  `no_rawat` varchar(20) NOT NULL,
  `tgl_catat` date NOT NULL,
  `jam_catat` time NOT NULL,
  `tetesan_aktual` int(11) DEFAULT NULL,
  `volume_sisa` double DEFAULT NULL,
  `kd_petugas` varchar(20) DEFAULT NULL,
  `keterangan_temuan` varchar(100) DEFAULT NULL,
  `stts_simrs_alert` enum('NORMAL','PERINGATAN_HABIS','OKLUSI','LAJU_TIDAK_SESUAI','INPUT_TERLAMBAT') DEFAULT 'NORMAL'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rsk_infus_monitoring`
--

INSERT INTO `rsk_infus_monitoring` (`no_rawat`, `tgl_catat`, `jam_catat`, `tetesan_aktual`, `volume_sisa`, `kd_petugas`, `keterangan_temuan`, `stts_simrs_alert`) VALUES
('2025/10/02/000109', '2025-10-04', '07:09:30', 25, 500, 'P001', '', 'NORMAL'),
('2025/10/02/000111', '2025-10-04', '05:43:53', 20, 500, 'P001', 'baru pasang', 'NORMAL'),
('2025/10/02/000111', '2025-10-04', '05:45:21', 25, 300, 'P001', '', 'NORMAL'),
('2025/10/02/000111', '2025-10-04', '07:27:43', 20, 50, 'P001', '', 'NORMAL'),
('2025/10/02/000111', '2025-10-04', '07:28:19', 20, 25, 'P001', '', 'NORMAL'),
('2025/10/02/000111', '2025-10-04', '07:28:54', 20, 15, 'P001', '', 'NORMAL'),
('2025/10/02/000111', '2025-10-04', '07:29:16', 20, 20, 'P001', '', 'NORMAL'),
('2025/10/03/000068', '2025-10-03', '15:56:00', 20, 200, 'P001', '', 'NORMAL'),
('2025/10/03/000068', '2025-10-03', '15:56:38', 20, 480, 'P001', '', 'NORMAL'),
('2025/10/03/000068', '2025-10-03', '15:57:06', 50, 50, 'P001', '', 'NORMAL'),
('2025/10/03/000068', '2025-10-03', '15:57:34', 20, 20, 'P001', '', 'NORMAL'),
('2025/10/03/000068', '2025-10-03', '16:06:59', 44, 25, 'P001', '', 'NORMAL'),
('2025/10/03/000068', '2025-10-03', '16:18:29', 25, 500, 'P001', '', 'NORMAL'),
('2025/10/03/000068', '2025-10-04', '05:46:38', 20, 10, 'P001', '', 'NORMAL'),
('2025/10/03/000068', '2025-10-04', '06:59:19', 20, 500, 'P001', 'ganti baru', 'NORMAL'),
('2025/10/03/000084', '2025-10-03', '15:58:00', 44, 100, 'P001', '', 'NORMAL'),
('2025/10/03/000084', '2025-10-03', '15:58:59', 44, 25, 'P001', '', 'NORMAL'),
('2025/10/03/000084', '2025-10-03', '16:00:35', 44, 25, 'P001', '', 'NORMAL'),
('2025/10/03/000084', '2025-10-03', '16:06:20', 44, 100, 'P001', '', 'NORMAL'),
('2025/10/03/000084', '2025-10-03', '16:18:46', 44, 500, 'P001', '', 'NORMAL'),
('2025/10/03/000084', '2025-10-04', '07:00:33', 20, 500, 'P001', '', 'NORMAL');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rsk_infus_monitoring`
--
ALTER TABLE `rsk_infus_monitoring`
  ADD PRIMARY KEY (`no_rawat`,`tgl_catat`,`jam_catat`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
