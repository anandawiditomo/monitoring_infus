-- phpMyAdmin SQL Dump
-- version 4.9.10
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 04, 2025 at 09:59 PM
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
-- Table structure for table `rsk_infus_master`
--

CREATE TABLE `rsk_infus_master` (
  `kd_obat_infus` varchar(10) NOT NULL,
  `nama_obat_infus` varchar(50) NOT NULL,
  `volume_standar` double DEFAULT NULL,
  `faktor_tetes` int(11) NOT NULL DEFAULT '20'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rsk_infus_master`
--

INSERT INTO `rsk_infus_master` (`kd_obat_infus`, `nama_obat_infus`, `volume_standar`, `faktor_tetes`) VALUES
('2009', 'Water For Inj 1000 cc', 500, 20),
('2061', 'Dextrose 40% ', 500, 20),
('3501', 'Ciprofloxacin Infus ', 500, 20),
('3506', 'Asering infus  ', 500, 20),
('3535', ' Fluconazole Infus 100 ml  ', 100, 20),
('3555', 'Tutosol infus ', 500, 20),
('3568', 'Dextrose 10%|WIDA D10 TM', 500, 20),
('3585', 'Sanmol Infus', 500, 20),
('3604', 'Kidmin infus  ', 500, 20),
('3745', ' NS 1 Liter|Na.Klor.0,9%| OGB ', 500, 20),
('3801', 'Piggy Bag NS 100 ML  ', 100, 20),
('3802', 'NS|Sodium clorit 500ml|WIDA NS  	 ', 500, 20),
('3821', 'Ringer Lactat | WIDA RL ', 500, 20),
('IV001', 'NaCl 0.9% (Normal Saline)', 500, 20),
('IV002', 'Infus Apa ya ? silakan tulis dan edit juga', 500, 20);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rsk_infus_master`
--
ALTER TABLE `rsk_infus_master`
  ADD PRIMARY KEY (`kd_obat_infus`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
