-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2025 at 02:36 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `posystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `cantines`
--

CREATE TABLE `cantines` (
  `id` int(11) NOT NULL,
  `name` text COLLATE utf8_spanish_ci NOT NULL,
  `idDocument` int(11) NOT NULL,
  `email` text COLLATE utf8_spanish_ci NOT NULL,
  `phone` text COLLATE utf8_spanish_ci NOT NULL,
  `address` text COLLATE utf8_spanish_ci NOT NULL,
  `birthdate` date NOT NULL,
  `purchases` int(11) NOT NULL,
  `lastPurchase` datetime NOT NULL,
  `registerDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `cantines`
--

INSERT INTO `cantines` (`id`, `name`, `idDocument`, `email`, `phone`, `address`, `birthdate`, `purchases`, `lastPurchase`, `registerDate`, `username`, `password`, `active`) VALUES
(1, 'David Cullison', 123456, 'davidc@mail.com', '(555)567-9999', '27 Joseph Street', '1986-01-05', 15, '2018-12-03 00:01:21', '2022-12-10 13:41:42', '', '', 0),
(2, 'Mary Yaeger', 121212, 'maryy@mail.com', '(555) 789-9045', '71 Highland Drive', '1983-06-22', 31, '2025-03-23 03:26:49', '2025-03-23 08:26:49', '', '', 0),
(3, 'Robert Zimmerman', 122458, 'robert@mail.com', '(305) 455-6677', '27 Joseph Street', '1989-04-12', 2, '2025-03-21 23:29:33', '2025-03-22 04:29:33', '', '', 0),
(4, 'Randall Williams', 103698, 'randalw@mail.com', '(305) 256-6541', '31 Romines Mill Road', '1989-08-15', 5, '2022-12-10 08:42:36', '2022-12-10 13:42:36', '', '', 0),
(6, 'Christine Moore', 852100, 'christine@mail.com', '(785) 458-7888', '44 Down Lane', '1990-10-16', 36, '2022-12-07 13:17:31', '2022-12-08 18:11:56', '', '', 0),
(7, 'Nicole Young', 100254, 'nicole@mail.com', '(101) 222-1145', '44 Sycamore Fork Road', '1989-12-12', 4, '2022-12-10 08:38:47', '2022-12-10 13:38:47', '', '', 0),
(8, 'Grace Moore', 178500, 'gracem@mail.com', '(100) 124-5896', '39 Cambridge Drive', '1990-12-07', 8, '2025-03-23 03:27:30', '2025-03-23 08:27:30', '', '', 0),
(9, 'Reed Campbell', 178500, 'reedc@mail.com', '(100) 245-7866', '87 Lang Avenue', '1988-04-16', 18, '2022-12-10 08:43:42', '2022-12-10 13:43:42', '', '', 0),
(10, 'Lynn', 101014, 'lynn@mail.com', '(100) 145-8966', '90 Roosevelt Road', '1992-02-22', 0, '0000-00-00 00:00:00', '2022-12-10 17:12:55', '', '', 0),
(11, 'Will Williams', 100147, 'williams@mail.com', '(774) 145-8888', '114 Test Address', '1985-04-19', 13, '2022-12-10 12:35:52', '2022-12-10 17:35:52', '', '', 0),
(12, 'cabs', 11234, 'jm97@gmail.com', '(345) 345-6345', 'iba zambales', '0000-00-00', 72, '2025-03-22 02:12:19', '2025-03-22 07:12:19', 'cabs_cantine', 'jmjp1123', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cantines`
--
ALTER TABLE `cantines`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cantines`
--
ALTER TABLE `cantines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
