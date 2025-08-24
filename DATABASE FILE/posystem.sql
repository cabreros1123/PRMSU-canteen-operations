-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2025 at 08:56 AM
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
-- Database: `posystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `cantines`
--

CREATE TABLE `cantines` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `idDocument` int(11) NOT NULL,
  `email` text NOT NULL,
  `phone` text NOT NULL,
  `address` text NOT NULL,
  `birthdate` date NOT NULL,
  `purchases` int(11) NOT NULL,
  `lastPurchase` datetime NOT NULL,
  `registerDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `cantines`
--

INSERT INTO `cantines` (`id`, `name`, `idDocument`, `email`, `phone`, `address`, `birthdate`, `purchases`, `lastPurchase`, `registerDate`, `username`, `password`) VALUES
(1, 'David Cullison', 123456, 'davidc@mail.com', '(555)567-9999', '27 Joseph Street', '1986-01-05', 15, '2018-12-03 00:01:21', '2022-12-10 13:41:42', '', ''),
(2, 'Mary Yaeger', 121212, 'maryy@mail.com', '(555) 789-9045', '71 Highland Drive', '1983-06-22', 31, '2025-03-23 03:26:49', '2025-03-23 08:26:49', '', ''),
(3, 'Robert Zimmerman', 122458, 'robert@mail.com', '(305) 455-6677', '27 Joseph Street', '1989-04-12', 2, '2025-03-21 23:29:33', '2025-03-22 04:29:33', '', ''),
(4, 'Randall Williams', 103698, 'randalw@mail.com', '(305) 256-6541', '31 Romines Mill Road', '1989-08-15', 5, '2022-12-10 08:42:36', '2022-12-10 13:42:36', '', ''),
(6, 'Christine Moore', 852100, 'christine@mail.com', '(785) 458-7888', '44 Down Lane', '1990-10-16', 36, '2022-12-07 13:17:31', '2022-12-08 18:11:56', '', ''),
(7, 'Nicole Young', 100254, 'nicole@mail.com', '(101) 222-1145', '44 Sycamore Fork Road', '1989-12-12', 4, '2022-12-10 08:38:47', '2022-12-10 13:38:47', '', ''),
(8, 'Grace Moore', 178500, 'gracem@mail.com', '(100) 124-5896', '39 Cambridge Drive', '1990-12-07', 8, '2025-03-23 03:27:30', '2025-03-23 08:27:30', '', ''),
(9, 'Reed Campbell', 178500, 'reedc@mail.com', '(100) 245-7866', '87 Lang Avenue', '1988-04-16', 18, '2022-12-10 08:43:42', '2022-12-10 13:43:42', '', ''),
(10, 'Lynn', 101014, 'lynn@mail.com', '(100) 145-8966', '90 Roosevelt Road', '1992-02-22', 0, '0000-00-00 00:00:00', '2022-12-10 17:12:55', '', ''),
(11, 'Will Williams', 100147, 'williams@mail.com', '(774) 145-8888', '114 Test Address', '1985-04-19', 13, '2022-12-10 12:35:52', '2022-12-10 17:35:52', '', ''),
(12, 'cabs', 11234, 'jm97@gmail.com', '(345) 345-6345', 'iba zambales', '0000-00-00', 72, '2025-03-22 02:12:19', '2025-03-22 07:12:19', 'cabs_cantine', 'jmjp1123');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `Category` text NOT NULL,
  `Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `Category`, `Date`) VALUES
(1, 'Category One', '2022-12-07 18:04:16'),
(2, 'Category Two', '2022-12-07 18:04:20'),
(3, 'Category Three', '2022-12-07 18:04:24'),
(4, 'Category Four', '2022-12-07 18:04:27'),
(5, 'Category Five', '2022-12-07 18:04:31'),
(6, 'Category Six', '2022-12-07 18:04:36'),
(7, 'Category seven eleven', '2025-03-23 10:38:09');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `idCategory` int(11) NOT NULL,
  `code` text NOT NULL,
  `description` text NOT NULL,
  `image` text NOT NULL,
  `stock` int(11) NOT NULL,
  `buyingPrice` float NOT NULL,
  `sellingPrice` float NOT NULL,
  `sales` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `idCategory`, `code`, `description`, `image`, `stock`, `buyingPrice`, `sellingPrice`, `sales`, `date`) VALUES
(18, 2, '201', 'Product Sample One', 'views/img/products/default/anonymous.png', 10, 56, 78, 20, '2022-12-08 17:23:41'),
(25, 3, '301', 'Product Sample Two', 'views/img/products/default/anonymous.png', 18, 144, 185, 23, '2022-12-08 17:20:28'),
(36, 4, '401', 'Product Sample Three', 'views/img/products/default/anonymous.png', 55, 98, 125, 22, '2022-12-10 13:42:36'),
(44, 5, '501', 'Product Sample Four', 'views/img/products/default/anonymous.png', 8, 350, 490, 21, '2022-12-08 17:23:27'),
(61, 7, '518', 'Test Product', 'views/img/products/518/204.jpg', 19, 20, 28, 41, '2022-12-07 18:19:13'),
(62, 4, '519', 'Product Sample Five', 'views/img/products/default/anonymous.png', 95, 120, 156, 0, '2022-12-10 17:12:55'),
(63, 7, '520', 'Product Sample Six', 'views/img/products/default/anonymous.png', 53, 70, 98, 0, '2022-12-10 17:12:55'),
(64, 1, '521', 'Product Sample Seven', 'views/img/products/default/anonymous.png', 21, 50, 70, 11, '2025-03-22 07:12:19'),
(65, 3, '522', 'Product Sample Eight', 'views/img/products/default/anonymous.png', 5, 100, 140, 5, '2022-12-10 16:53:02'),
(66, 4, '523', 'Product Sample Nine', 'views/img/products/default/anonymous.png', 11, 25, 35, 52, '2025-03-23 08:26:49'),
(67, 5, '524', 'Product Sample Ten', 'views/img/products/default/anonymous.png', 13, 65, 91, 61, '2025-03-23 08:26:49'),
(68, 4, '525', 'Product Sample Eleven', 'views/img/products/default/anonymous.png', 0, 120, 168, 26, '2025-03-23 08:27:30');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `code` int(11) NOT NULL,
  `idCustomer` int(11) NOT NULL,
  `idSeller` int(11) NOT NULL,
  `products` text NOT NULL,
  `tax` int(11) NOT NULL,
  `netPrice` float NOT NULL,
  `totalPrice` float NOT NULL,
  `paymentMethod` text NOT NULL,
  `saledate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=COMPACT;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `code`, `idCustomer`, `idSeller`, `products`, `tax`, `netPrice`, `totalPrice`, `paymentMethod`, `saledate`) VALUES
(9, 10001, 2, 2, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":\"3\",\"stock\":\"29\",\"price\":\"185\",\"totalPrice\":\"555\"}]', 17, 555, 572, 'cash', '2018-12-04 00:53:28'),
(11, 10002, 3, 1, '[{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":\"4\",\"stock\":\"16\",\"price\":\"490\",\"totalPrice\":\"1960\"},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":\"6\",\"stock\":\"14\",\"price\":\"125\",\"totalPrice\":\"750\"}]', 0, 2710, 2710, 'cash', '2018-12-05 06:30:28'),
(12, 10003, 3, 1, '[{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":\"1\",\"stock\":\"2\",\"price\":\"490\",\"totalPrice\":\"490\"},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":\"1\",\"stock\":\"8\",\"price\":\"125\",\"totalPrice\":\"125\"},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":\"1\",\"stock\":\"23\",\"price\":\"185\",\"totalPrice\":\"185\"},{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":\"2\",\"stock\":\"114\",\"price\":\"78\",\"totalPrice\":\"156\"}]', 48, 956, 1004, 'cash', '2019-04-09 22:59:10'),
(14, 10005, 6, 1, '[{\"id\":\"61\",\"description\":\"Test Product\",\"quantity\":\"9\",\"stock\":\"31\",\"price\":\"28\",\"totalPrice\":\"252\"},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":\"3\",\"stock\":\"3\",\"price\":\"490\",\"totalPrice\":\"1470\"},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":\"5\",\"stock\":\"3\",\"price\":\"125\",\"totalPrice\":\"625\"}]', 117, 2347, 2464, 'cash', '2020-02-26 05:34:45'),
(15, 10006, 6, 1, '[{\"id\":\"61\",\"description\":\"Test Product\",\"quantity\":\"17\",\"stock\":\"19\",\"price\":\"28\",\"totalPrice\":\"476\"},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":\"2\",\"stock\":\"1\",\"price\":\"185\",\"totalPrice\":\"370\"}]', 25, 846, 871, 'cash', '2021-01-05 15:36:20'),
(17, 10008, 4, 1, '[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"2\",\"stock\":\"69\",\"price\":\"91\",\"totalPrice\":\"182\"}]', 0, 182, 182, 'cash', '2021-09-28 05:18:53'),
(18, 10009, 7, 1, '[{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":\"3\",\"stock\":\"57\",\"price\":\"35\",\"totalPrice\":\"105\"},{\"id\":\"65\",\"description\":\"Product Sample Eight\",\"quantity\":\"1\",\"stock\":\"40\",\"price\":\"140\",\"totalPrice\":\"140\"}]', 5, 245, 250, 'cash', '2022-02-13 23:58:09'),
(19, 10010, 4, 1, '[{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":\"3\",\"stock\":\"55\",\"price\":\"125\",\"totalPrice\":\"375\"}]', 4, 375, 379, 'cash', '2022-06-29 03:42:37'),
(20, 10011, 9, 1, '[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"4\",\"stock\":\"65\",\"price\":\"91\",\"totalPrice\":\"364\"},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":\"10\",\"stock\":\"47\",\"price\":\"35\",\"totalPrice\":\"350\"},{\"id\":\"65\",\"description\":\"Product Sample Eight\",\"quantity\":\"4\",\"stock\":\"36\",\"price\":\"140\",\"totalPrice\":\"560\"}]', 64, 1274, 1338, 'CC-110101458966', '2022-09-20 13:43:42'),
(21, 10012, 11, 1, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"3\",\"stock\":\"23\",\"price\":\"168\",\"totalPrice\":\"504\"},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":\"10\",\"stock\":\"37\",\"price\":\"35\",\"totalPrice\":\"350\"}]', 68, 854, 922, 'CC-100000147850', '2022-12-10 17:35:52'),
(22, 10013, 8, 2, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"7\",\"stock\":\"16\",\"price\":\"168\",\"totalPrice\":\"1176\"}]', 0, 1176, 1176, 'cash', '2022-12-10 17:40:02'),
(23, 10014, 2, 4, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"14\",\"stock\":\"2\",\"price\":\"168\",\"totalPrice\":\"2352\"},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"12\",\"stock\":\"53\",\"price\":\"91\",\"totalPrice\":\"1092\"}]', 69, 3444, 3513, '', '2025-03-15 06:34:43'),
(24, 10015, 3, 6, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"1\",\"stock\":\"1\",\"price\":\"168\",\"totalPrice\":\"168\"},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"1\",\"stock\":\"52\",\"price\":\"91\",\"totalPrice\":\"91\"}]', 3, 259, 262, '', '2025-03-22 04:29:33'),
(25, 10016, 12, 4, '[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"1\",\"stock\":\"50\",\"price\":\"91\",\"totalPrice\":\"91\"},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":\"1\",\"stock\":\"35\",\"price\":\"35\",\"totalPrice\":\"35\"}]', 1, 126, 127, '', '2025-03-22 06:47:38'),
(26, 10017, 12, 4, '[{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":\"23\",\"stock\":\"12\",\"price\":\"35\",\"totalPrice\":\"805\"},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"13\",\"stock\":\"37\",\"price\":\"91\",\"totalPrice\":\"1183\"}]', 20, 1988, 2008, '', '2025-03-22 06:47:55'),
(27, 10018, 12, 4, '[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"23\",\"stock\":\"14\",\"price\":\"91\",\"totalPrice\":\"2093\"},{\"id\":\"64\",\"description\":\"Product Sample Seven\",\"quantity\":\"11\",\"stock\":\"21\",\"price\":\"70\",\"totalPrice\":\"770\"}]', 29, 2863, 2892, '', '2025-03-22 07:12:19'),
(28, 10019, 2, 4, '[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":\"1\",\"stock\":\"13\",\"price\":\"91\",\"totalPrice\":\"91\"},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":\"1\",\"stock\":\"11\",\"price\":\"35\",\"totalPrice\":\"35\"}]', 0, 126, 126, '', '2025-03-23 08:26:49'),
(29, 10020, 8, 4, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":\"1\",\"stock\":\"0\",\"price\":\"168\",\"totalPrice\":\"168\"}]', 0, 168, 168, '', '2025-03-23 08:27:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `user` text NOT NULL,
  `password` text NOT NULL,
  `profile` text NOT NULL,
  `photo` text NOT NULL,
  `status` int(1) NOT NULL,
  `lastLogin` datetime NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=COMPACT;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `user`, `password`, `profile`, `photo`, `status`, `lastLogin`, `date`) VALUES
(1, 'Administrator', 'admin', 'admin1123', 'Administrator', 'views/img/users/admin/admin-icn.png', 1, '2025-03-22 08:30:34', '2025-03-22 13:30:34'),
(3, 'Carmen McLeod', 'carmen', '$2a$07$asxx54ahjppf45sd87a5au8uJqn2VoaOMw86zRUoDH6inuYomGLDq', 'Special', 'views/img/users/carmen/215.jpg', 0, '2022-12-10 12:17:55', '2025-03-16 06:57:39'),
(4, 'john mark d. cabreros', 'cabs', 'jm1123', 'administrator', 'views/img/users/admin/admin-icn.png', 1, '2025-03-23 06:32:55', '2025-03-23 11:32:55'),
(6, 'raul baldeo', 'raul', '1123', 'administrator', '', 1, '2025-03-22 08:26:10', '2025-03-22 13:26:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cantines`
--
ALTER TABLE `cantines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cantines`
--
ALTER TABLE `cantines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
