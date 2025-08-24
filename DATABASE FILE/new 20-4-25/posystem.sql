-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 04:57 PM
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
  `email` text COLLATE utf8_spanish_ci NOT NULL,
  `phone` text COLLATE utf8_spanish_ci NOT NULL,
  `owner` text COLLATE utf8_spanish_ci NOT NULL,
  `Last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `registerDate` date NOT NULL,
  `username` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `active` tinyint(1) DEFAULT 0,
  `del_status` int(2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `cantines`
--

INSERT INTO `cantines` (`id`, `name`, `email`, `phone`, `owner`, `Last_login`, `registerDate`, `username`, `password`, `active`, `del_status`) VALUES
(1, 'cabs', 'jm97@gmail.com', '(345) 345-6345', 'iba zambales', '2025-05-13 13:56:24', '2025-04-18', 'cabs_cantine', 'jmjp1123', 0, 0),
(14, 'ULAM-LAMI', 'ulam13@gmail.com', '09054095587', 'jhon mitchel ', '2025-05-13 01:12:51', '2025-04-18', 'ulam1123', '1123', 0, 0),
(15, 'MINI-STORE', 'mini1123@gmail.com', '0923754782', 'rimuru-sama', '2025-05-13 01:12:53', '2024-04-18', 'mini1123', '1123', 0, 0),
(16, 'Kalameros tuna', 'kala@gmail.com', '09234351214', 'kalamero miguel', '2025-05-13 01:13:53', '2025-04-18', 'kala1123', 'meros1123', 0, 0),
(17, 'RAUL two tower', 'baldeo23@gmail.com', '09267536274', 'baldeo', '2025-05-13 14:00:33', '2025-04-19', 'baldeo', 'baleo23456', 0, 0),
(18, 'kiegororia store', 'kiego23@gmail.com', '4365467578768', 'keigo miguel leuigan', '2025-05-13 01:12:59', '2025-04-19', 'melo', '1123', 0, 0),
(19, 'random', 'mark@gmail.com', '09054095587', 'meme', '2025-05-13 01:13:01', '2025-04-19', 'meme', 'admin1123', 0, 0),
(20, 'korona canteen', 'korona@gmail.com', '096735486263', 'romeo dalioa', '2025-05-13 01:13:03', '2025-04-25', 'korona', 'korona', 0, 0),
(21, 'pilepe jr', 'pelipe@gmail.com', '0946736475', 'pelipe cruz', '2025-05-13 01:13:53', '2025-05-03', 'pelipe', 'pelipe12345678', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `Category` text COLLATE utf8_spanish_ci NOT NULL,
  `type` int(11) NOT NULL DEFAULT 0,
  `Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `del_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `Category`, `type`, `Date`, `del_status`) VALUES
(10, 'cantine(admin)', 0, '2025-05-11 22:15:09', 1),
(11, 'manok', 0, '2025-04-13 07:16:22', 0),
(15, 'ulam', 1, '2025-04-26 09:57:29', 0),
(16, 'turon', 1, '2025-04-26 10:03:04', 0),
(18, 'drinks', 0, '2025-05-01 04:33:30', 0),
(19, 'poypoy jr', 0, '2025-05-11 21:45:20', 1),
(20, 'cans drinks', 0, '2025-05-11 22:47:01', 0),
(21, 'cans', 0, '2025-05-11 23:27:51', 0),
(22, 'bottle drinks', 0, '2025-05-11 23:32:47', 0),
(23, 'souvenir', 0, '2025-05-11 23:34:32', 0),
(24, 'key chain', 0, '2025-05-11 23:34:43', 0),
(25, 'bread', 0, '2025-05-11 23:34:47', 0),
(26, 'drink cans', 0, '2025-05-11 23:36:26', 0),
(27, 'Coffee', 0, '2025-05-11 23:39:36', 0),
(28, 'Hot Chocolate', 0, '2025-05-11 23:39:48', 0),
(29, 'Cold Beverages', 0, '2025-05-11 23:40:04', 0),
(30, 'Milkshakes', 0, '2025-05-11 23:40:17', 0),
(31, 'Specialty Drinks', 0, '2025-05-11 23:40:29', 0),
(32, 'Health-Conscious Options', 0, '2025-05-11 23:40:52', 0),
(33, 'Seasonal Drinks', 0, '2025-05-11 23:41:04', 0),
(34, 'Snacks & Sides', 0, '2025-05-11 23:42:02', 0),
(35, 'Vegetarian/Vegan Options', 0, '2025-05-11 23:42:19', 0),
(36, 'Desserts', 0, '2025-05-11 23:42:33', 0);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `idCategory` int(11) NOT NULL,
  `cantine_id` int(11) NOT NULL,
  `code` text COLLATE utf8_spanish_ci NOT NULL,
  `description` text COLLATE utf8_spanish_ci NOT NULL,
  `image` text COLLATE utf8_spanish_ci NOT NULL,
  `stock` int(11) NOT NULL,
  `buyingPrice` float NOT NULL,
  `sellingPrice` float NOT NULL,
  `sales` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `del_status` int(11) NOT NULL DEFAULT 0,
  `product_type` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `idCategory`, `cantine_id`, `code`, `description`, `image`, `stock`, `buyingPrice`, `sellingPrice`, `sales`, `date`, `del_status`, `product_type`) VALUES
(18, 2, 1, '201', 'Product Sample One 1', 'views/img/products/default/anonymous.png', -458, 56, 78, 20, '2025-05-13 13:38:25', 1, 0),
(25, 3, 0, '301', 'Product Sample Two', 'views/img/products/default/anonymous.png', -4, 144, 185, 23, '2025-05-13 13:36:42', 0, 0),
(36, 4, 1, '401', 'Product Sample Three', 'views/img/products/default/anonymous.png', -2, 98, 125, 22, '2025-05-11 20:17:55', 1, 0),
(44, 5, 1, '501', 'Product Sample Four', 'views/img/products/default/anonymous.png', -232, 350, 490, 21, '2025-05-11 20:18:02', 1, 0),
(61, 7, 0, '518', 'Test Product', 'views/img/products/default/anonymous.png', 675, 20, 28, 41, '2025-05-01 11:08:13', 0, 0),
(62, 4, 1, '519', 'Product Sample Five', 'views/img/products/default/anonymous.png', 654, 120, 156, 0, '2025-05-11 20:18:10', 1, 0),
(63, 7, 0, '520', 'Product Sample Six', 'views/img/products/default/anonymous.png', 678, 70, 98, 0, '2025-04-27 07:24:36', 0, 0),
(64, 1, 0, '521', 'Product Sample Seven', 'views/img/products/default/anonymous.png', 78, 50, 70, 11, '2025-04-27 07:24:31', 0, 0),
(65, 3, 1, '522', 'Product Sample Eight', 'views/img/products/default/anonymous.png', 721, 100, 140, 5, '2025-05-11 20:18:18', 1, 0),
(66, 4, 1, '523', 'Product Sample Nine', 'views/img/products/default/anonymous.png', 0, 25, 35, 52, '2025-05-11 20:18:23', 1, 0),
(67, 5, 1, '524', 'Product Sample Ten', 'views/img/products/default/anonymous.png', -30, 65, 91, 61, '2025-05-11 20:18:30', 1, 0),
(68, 4, 1, '525', 'Product Sample Eleven', 'views/img/products/default/anonymous.png', 7, 121, 168, 26, '2025-05-13 01:16:39', 1, 0),
(73, 5, 0, '', 'kalabaw na inihaw', 'views/img/products/585.jpg', 220, 5, 4, 0, '2025-05-02 16:23:51', 0, 0),
(74, 2, 0, '', 'Product Sample 57', 'views/img/products/586.png', 60, 10, 11, 0, '2025-05-02 16:20:29', 0, 0),
(75, 11, 0, '', 'turon with cream', 'views/img/products/718.png', 10, 125, 344, 0, '2025-05-02 16:14:09', 0, 0),
(76, 11, 0, '', 'car', 'views/img/products/199.jpg', 20, 3000, 4000, 0, '2025-05-02 16:09:04', 0, 0),
(77, 5, 0, '', 'Max level mage', 'views/img/products/300.png', 5600, 45645, 168, 0, '2025-05-02 15:55:07', 0, 0),
(78, 10, 0, '', 'serwrwrwer', 'views/img/products/247.jpeg', 4300, 1000, 2000, 0, '2025-05-02 16:06:55', 0, 0),
(79, 12, 19, '', 'bread 1', 'views/img/products/766.png', 3210, 9, 12, 0, '2025-05-02 04:11:29', 0, 0),
(80, 14, 0, '', 'mask edited', 'views/img/products/827.png', 270, 1235, 4500, 0, '2025-05-02 15:50:11', 0, 0),
(82, 12, 19, '', 'bataan', 'views/img/products/429.png', 10, 23, 324, 0, '2025-05-11 21:08:48', 0, 0),
(83, 15, 18, '', 'cyper', 'views/img/products/493.png', 0, 0, 0, 0, '2025-04-30 13:01:09', 0, 1),
(84, 15, 17, '', 'cyper height', 'views/img/products/532.png', 0, 0, 0, 0, '2025-04-30 13:01:02', 0, 1),
(85, 15, 20, '', 'home sweet', 'views/img/products/784.jpg', 0, 0, 0, 0, '2025-04-30 13:00:56', 0, 1),
(86, 16, 19, '', 'turon with cream', 'views/img/products/945.jpg', 0, 0, 0, 0, '2025-05-11 20:17:38', 1, 1),
(87, 15, 16, '', 'adobo', 'views/img/products/183.jpg', 0, 0, 0, 0, '2025-04-30 13:00:45', 0, 1),
(88, 15, 17, '', 'Sinigang', 'views/img/products/179.jpg', 0, 0, 0, 0, '2025-04-30 12:21:16', 0, 1),
(89, 15, 1, '', 'Menudo', 'views/img/products/175.jpg', -7, 0, 0, 0, '2025-05-03 06:35:20', 0, 1),
(90, 2, 1, '', 'mountain dew', 'views/img/products/default/anonymous.png', 100, 15, 20, 0, '2025-05-11 20:19:37', 1, 0),
(91, 10, 19, '', 'delendelin', 'views/img/products/default/anonymous.png', 0, 0, 0, 0, '2025-05-01 10:40:23', 1, 1),
(92, 15, 18, '', 'litchon', 'views/img/products/104.jpg', 0, 0, 0, 0, '2025-05-11 21:06:48', 1, 1),
(93, 15, 14, '', 'litchot', 'views/img/products/370.jpg', 0, 0, 0, 0, '2025-05-02 04:01:50', 0, 1),
(94, 11, 1, '', 'blablabla', 'views/img/products/911.jpg', 0, 0, 0, 0, '2025-05-11 22:10:15', 0, 1),
(95, 5, 1, '', 'kamatayan lami', 'views/img/products/830.jpg', 0, 0, 0, 0, '2025-05-11 20:19:26', 1, 1),
(96, 15, 1, '', 'Ginataang isda', 'views/img/products/693.jpg', 0, 0, 0, 0, '2025-05-03 10:05:47', 0, 1),
(97, 18, 17, '', 'coca cola', 'views/img/products/221.jpg', 234, 20, 30, 0, '2025-05-13 14:01:00', 0, 0),
(98, 12, 21, '', 'nuts me', 'views/img/products/103.jpg', 5436, 1000, 3000, 0, '2025-05-03 12:17:08', 0, 0),
(99, 15, 21, '', 'longasina jr', 'views/img/products/318.jpg', -1, 0, 0, 0, '2025-05-11 20:19:16', 1, 1),
(100, 15, 21, '', 'blablacanteen jr', 'views/img/products/896.jpg', -1, 0, 0, 0, '2025-05-03 12:18:21', 1, 1),
(101, 2, 1, '', 'blabweeee jr', 'views/img/products/default/anonymous.png', 0, 0, 0, 0, '2025-05-11 20:18:43', 1, 1),
(102, 11, 19, '', '453657', 'views/img/products/default/anonymous.png', 0, 0, 0, 0, '2025-05-03 15:00:23', 1, 1),
(103, 15, 16, '', 'longanisa', 'views/img/products/964.jpg', 0, 0, 0, 0, '2025-05-11 22:46:04', 0, 1),
(104, 15, 1, '', 'Sinigang na Baboy', 'views/img/products/916.jpg', 0, 0, 0, 0, '2025-05-11 22:50:09', 0, 1),
(105, 15, 1, '', 'Kare Kare', 'views/img/products/998.jfif', 0, 0, 0, 0, '2025-05-11 22:51:23', 0, 1),
(106, 15, 1, '', 'Bistek Tagalog', 'views/img/products/710.jpg', -2, 0, 0, 0, '2025-05-13 13:19:00', 0, 1),
(107, 36, 17, '', 'Leche Flan', 'views/img/products/836.jpg', 0, 0, 0, 0, '2025-05-12 07:01:25', 0, 1),
(108, 36, 17, '', 'Halo Halo', 'views/img/products/119.jpg', 185, 30, 40, 0, '2025-05-13 14:13:58', 0, 0),
(109, 36, 17, '', 'Bibingka', 'views/img/products/583.jpg', 0, 0, 0, 0, '2025-05-12 07:04:53', 0, 1),
(110, 36, 17, '', 'Puto', 'views/img/products/702.jpg', 0, 0, 0, 0, '2025-05-12 07:07:00', 0, 1),
(111, 36, 17, '', 'Suman', 'views/img/products/780.jpg', 0, 0, 0, 0, '2025-05-12 07:14:55', 0, 1),
(112, 36, 17, '', 'Ube Halaya', 'views/img/products/735.jpg', 145, 20, 30, 0, '2025-05-13 14:16:34', 0, 0),
(113, 36, 17, '', 'Buko Pandan', 'views/img/products/220.jpg', 0, 0, 0, 0, '2025-05-12 07:20:45', 0, 1),
(114, 22, 1, '', 'summit 500ml', 'views/img/products/416.jpg', 340, 11, 15, 0, '2025-05-13 13:45:04', 0, 0),
(115, 22, 1, '', 'summit 1L', 'views/img/products/525.jpg', 21, 20, 30, 0, '2025-05-13 13:40:29', 0, 0),
(116, 20, 1, '', 'Mountain Dew 330ml', 'views/img/products/821.jpg', 90, 20, 30, 0, '2025-05-13 06:14:52', 0, 0),
(117, 22, 1, '', 'Mogu Mogu 350ml', 'views/img/products/143.jpg', 94, 20, 30, 0, '2025-05-13 13:40:29', 0, 0),
(118, 23, 1, '', 'Neck Tie', 'views/img/products/579.jpg', 185, 40, 50, 0, '2025-05-13 13:40:29', 0, 0),
(119, 36, 1, '', 'Tuna pie', 'views/img/products/745.jpg', 0, 0, 0, 0, '2025-05-13 11:18:23', 0, 1),
(120, 34, 1, '', 'spaghetti', 'views/img/products/659.jpg', 0, 0, 0, 0, '2025-05-13 11:52:22', 0, 1),
(121, 34, 1, '', 'Shumai', 'views/img/products/210.jpg', 0, 0, 0, 0, '2025-05-13 12:29:59', 0, 1),
(122, 34, 1, '', 'Shumai with rice', 'views/img/products/877.jpg', 0, 0, 0, 0, '2025-05-13 12:31:16', 0, 1),
(123, 15, 1, '', 'Hatdog with rice', 'views/img/products/415.jpg', 0, 0, 0, 0, '2025-05-13 12:34:05', 0, 1),
(124, 15, 1, '', 'chicken afritada', 'views/img/products/354.jpg', 0, 0, 0, 0, '2025-05-13 12:36:49', 0, 1),
(125, 34, 1, '', 'fish fillet', 'views/img/products/255.jpg', 0, 0, 0, 0, '2025-05-13 12:38:11', 0, 1),
(126, 34, 1, '', 'chicken torta', 'views/img/products/778.jpg', 0, 0, 0, 0, '2025-05-13 12:40:10', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_stocks`
--

CREATE TABLE `product_stocks` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_admin` varchar(225) NOT NULL,
  `update_stock` int(11) NOT NULL,
  `stock_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `product_stocks`
--

INSERT INTO `product_stocks` (`id`, `product_id`, `user_id`, `user_admin`, `update_stock`, `stock_date`) VALUES
(21, 97, 0, 'john mark d. cabreros', 10, '2025-05-03 19:38:35'),
(22, 97, 17, '', 40, '2025-05-03 19:40:01'),
(23, 90, 1, '', 80, '2025-05-03 19:40:33'),
(24, 68, 1, '', 25, '2025-05-03 19:40:51'),
(25, 67, 1, '', -30, '2025-05-03 19:41:04'),
(26, 98, 0, 'john mark d. cabreros', 0, '2025-05-03 20:09:28'),
(27, 99, 0, 'john mark d. cabreros', 0, '2025-05-03 20:10:54'),
(28, 100, 21, '', 0, '2025-05-03 20:15:23'),
(29, 101, 1, '', 0, '2025-05-03 23:06:52'),
(30, 103, 0, 'john mark d. cabreros', 0, '2025-05-03 23:18:51'),
(31, 103, 0, 'john mark d. cabreros', 0, '2025-05-11 21:19:55'),
(32, 82, 0, 'john mark d. cabreros', -240, '2025-05-11 22:08:48'),
(33, 94, 1, '', 0, '2025-05-11 23:10:15'),
(34, 103, 0, 'john mark d. cabreros', -345, '2025-05-11 23:46:04'),
(35, 104, 0, 'john mark d. cabreros', 0, '2025-05-11 23:50:09'),
(36, 112, 0, 'john mark d. cabreros', 20, '2025-05-12 08:31:25'),
(37, 114, 0, 'john mark d. cabreros', 264, '2025-05-13 16:05:40'),
(38, 118, 1, '', 0, '2025-05-13 19:13:47'),
(39, 118, 1, '', -50, '2025-05-13 19:14:04'),
(40, 114, 1, '', 330, '2025-05-13 21:21:44'),
(41, 114, 0, 'john mark d. cabreros', 3204, '2025-05-13 21:45:04'),
(42, 112, 17, '', 770, '2025-05-13 22:00:46'),
(43, 108, 17, '', 411, '2025-05-13 22:00:54'),
(44, 97, 17, '', 4674, '2025-05-13 22:01:00');

-- --------------------------------------------------------

--
-- Table structure for table `recent_sales`
--

CREATE TABLE `recent_sales` (
  `id` int(11) NOT NULL,
  `cantine_id` int(11) NOT NULL,
  `products` varchar(255) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `recent_sales`
--

INSERT INTO `recent_sales` (`id`, `cantine_id`, `products`, `date`) VALUES
(1, 1, '[{\"id\":\"65\",\"description\":\"Product Sample Eight\",\"quantity\":34,\"cost\":3400,\"sell\":4760,\"profit\":1360},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":23,\"cost\":1495,\"sell\":2093,\"profit\":598},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantit', '2025-05-02 12:23:04'),
(4, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":200,\"cost\":11200,\"sell\":15600,\"profit\":4400},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":49,\"cost\":7056,\"sell\":9065,\"profit\":2009},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quan', '2025-05-03 08:17:21'),
(5, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":50,\"cost\":17500,\"sell\":24500,\"profit\":7000}]', '2025-05-03 08:18:35'),
(6, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":150,\"cost\":8400,\"sell\":11700,\"profit\":3300},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":49,\"cost\":7056,\"sell\":9065,\"profit\":2009},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quant', '2025-05-03 08:21:13'),
(7, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":50,\"cost\":17500,\"sell\":24500,\"profit\":7000},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cost\"', '2025-05-03 08:27:52'),
(8, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":100,\"cost\":35000,\"sell\":49000,\"profit\":14000},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cos', '2025-05-03 08:28:47'),
(9, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":150,\"cost\":52500,\"sell\":73500,\"profit\":21000},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cos', '2025-05-03 08:29:26'),
(10, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":150,\"cost\":52500,\"sell\":73500,\"profit\":21000},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cos', '2025-05-03 08:30:01'),
(11, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":152,\"cost\":53200,\"sell\":74480,\"profit\":21280},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cos', '2025-05-03 08:30:44'),
(12, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":150,\"cost\":52500,\"sell\":73500,\"profit\":21000},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cos', '2025-05-03 08:31:34'),
(13, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":152,\"cost\":53200,\"sell\":74480,\"profit\":21280},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cos', '2025-05-03 08:33:32'),
(14, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":152,\"cost\":53200,\"sell\":74480,\"profit\":21280},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cos', '2025-05-03 08:33:46'),
(15, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":152,\"cost\":53200,\"sell\":74480,\"profit\":21280},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cos', '2025-05-03 08:35:20'),
(16, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":150,\"cost\":52500,\"sell\":73500,\"profit\":21000},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cos', '2025-05-03 08:35:42'),
(17, 21, '[{\"id\":\"98\",\"description\":\"nuts me\",\"quantity\":4,\"cost\":4000,\"sell\":12000,\"profit\":8000},{\"id\":\"99\",\"description\":\"longasina jr\",\"quantity\":1,\"cost\":345,\"sell\":400,\"profit\":55},{\"id\":\"100\",\"description\":\"blablacanteen jr\",\"quantity\":1,\"cost\":345,\"sell\":34', '2025-05-03 14:16:33'),
(18, 21, '[{\"id\":\"98\",\"description\":\"nuts me\",\"quantity\":5,\"cost\":5000,\"sell\":15000,\"profit\":10000},{\"id\":\"99\",\"description\":\"longasina jr\",\"quantity\":1,\"cost\":345,\"sell\":400,\"profit\":55},{\"id\":\"100\",\"description\":\"blablacanteen jr\",\"quantity\":1,\"cost\":345,\"sell\":3', '2025-05-03 14:16:54'),
(19, 21, '[{\"id\":\"98\",\"description\":\"nuts me\",\"quantity\":34,\"cost\":34000,\"sell\":102000,\"profit\":68000},{\"id\":\"99\",\"description\":\"longasina jr\",\"quantity\":1,\"cost\":345,\"sell\":400,\"profit\":55},{\"id\":\"100\",\"description\":\"blablacanteen jr\",\"quantity\":1,\"cost\":400,\"sell', '2025-05-03 14:17:08'),
(20, 1, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":16,\"cost\":1936,\"sell\":2688,\"profit\":752},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":50,\"cost\":3250,\"sell\":4550,\"profit\":1300},{\"id\":\"90\",\"description\":\"mountain dew\",\"quantity\":10,', '2025-05-13 03:15:47'),
(21, 1, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":20,\"cost\":2420,\"sell\":3360,\"profit\":940},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":50,\"cost\":3250,\"sell\":4550,\"profit\":1300},{\"id\":\"90\",\"description\":\"mountain dew\",\"quantity\":10,', '2025-05-13 03:16:15'),
(22, 1, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":28,\"cost\":3388,\"sell\":4704,\"profit\":1316},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":50,\"cost\":3250,\"sell\":4550,\"profit\":1300},{\"id\":\"90\",\"description\":\"mountain dew\",\"quantity\":10', '2025-05-13 03:16:39'),
(23, 1, '[{\"id\":\"116\",\"description\":\"Mountain Dew 330ml\",\"quantity\":100,\"cost\":20,\"sell\":30,\"profit\":10}]', '2025-05-13 04:07:12'),
(24, 1, '[{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cost\":324,\"sell\":32,\"profit\":-292}]', '2025-05-13 05:48:22'),
(25, 1, '[{\"id\":\"114\",\"description\":\"summit 500ml\",\"quantity\":230,\"cost\":2530,\"sell\":3450,\"profit\":920},{\"id\":\"116\",\"description\":\"Mountain Dew 330ml\",\"quantity\":10,\"cost\":200,\"sell\":300,\"profit\":100},{\"id\":\"106\",\"description\":\"Bistek Tagalog\",\"quantity\":1,\"cost\":', '2025-05-13 08:14:52'),
(26, 1, '[{\"id\":\"114\",\"description\":\"summit 500ml\",\"quantity\":200,\"cost\":2200,\"sell\":3000,\"profit\":800},{\"id\":\"106\",\"description\":\"Bistek Tagalog\",\"quantity\":1,\"cost\":600,\"sell\":20,\"profit\":-580}]', '2025-05-13 15:18:59'),
(27, 17, '[{\"id\":\"97\",\"description\":\"coca cola\",\"quantity\":2345,\"cost\":46900,\"sell\":70350,\"profit\":23450},{\"id\":\"108\",\"description\":\"Halo Halo\",\"quantity\":234,\"cost\":7020,\"sell\":9360,\"profit\":2340},{\"id\":\"112\",\"description\":\"Ube Halaya\",\"quantity\":344,\"cost\":6880,\"', '2025-05-13 15:49:47'),
(28, 17, '[{\"id\":\"97\",\"description\":\"coca cola\",\"quantity\":2345,\"cost\":46900,\"sell\":70350,\"profit\":23450},{\"id\":\"108\",\"description\":\"Halo Halo\",\"quantity\":234,\"cost\":7020,\"sell\":9360,\"profit\":2340},{\"id\":\"112\",\"description\":\"Ube Halaya\",\"quantity\":344,\"cost\":6880,\"', '2025-05-13 15:49:48'),
(29, 17, '[{\"id\":\"112\",\"description\":\"Ube Halaya\",\"quantity\":34,\"cost\":680,\"sell\":1020,\"profit\":340},{\"id\":\"108\",\"description\":\"Halo Halo\",\"quantity\":23,\"cost\":690,\"sell\":920,\"profit\":230},{\"id\":\"113\",\"description\":\"Buko Pandan\",\"quantity\":1,\"cost\":2000,\"sell\":4000', '2025-05-13 16:04:27'),
(30, 17, '[{\"id\":\"108\",\"description\":\"Halo Halo\",\"quantity\":35,\"cost\":1050,\"sell\":1400,\"profit\":350},{\"id\":\"112\",\"description\":\"Ube Halaya\",\"quantity\":23,\"cost\":460,\"sell\":690,\"profit\":230},{\"id\":\"110\",\"description\":\"Puto\",\"quantity\":1,\"cost\":345,\"sell\":500,\"profit', '2025-05-13 16:13:58'),
(31, 17, '[{\"id\":\"112\",\"description\":\"Ube Halaya\",\"quantity\":100,\"cost\":2000,\"sell\":3000,\"profit\":1000},{\"id\":\"107\",\"description\":\"Leche Flan\",\"quantity\":1,\"cost\":2432,\"sell\":23443,\"profit\":21011},{\"id\":\"88\",\"description\":\"Sinigang\",\"quantity\":1,\"cost\":324,\"sell\":2', '2025-05-13 16:16:33');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `total_product` int(255) NOT NULL,
  `id_cantine` int(11) NOT NULL,
  `products` text COLLATE utf8_spanish_ci NOT NULL,
  `product_cost` int(255) NOT NULL,
  `product_sale` int(255) NOT NULL,
  `total_profit` float NOT NULL,
  `saledate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `del_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=COMPACT;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `total_product`, `id_cantine`, `products`, `product_cost`, `product_sale`, `total_profit`, `saledate`, `del_status`) VALUES
(30, 0, 14, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":0,\"cost\":288,\"sell\":370,\"profit\":82},{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":0,\"cost\":504,\"sell\":702,\"profit\":198},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":0,\"cost\":392,\"sell\":500,\"profit\":108},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":0,\"cost\":5250,\"sell\":7350,\"profit\":2100}]', 6434, 8922, 2488, '2025-04-26 15:28:25', 1),
(73, 36, 14, '[{\"id\":\"63\",\"description\":\"Product Sample Six\",\"quantity\":8,\"cost\":560,\"sell\":784,\"profit\":224},{\"id\":\"18\",\"description\":\"Product Sample One 1\",\"quantity\":14,\"cost\":784,\"sell\":1092,\"profit\":308},{\"id\":\"62\",\"description\":\"Product Sample Five\",\"quantity\":14,\"cost\":152.73,\"sell\":198.55,\"profit\":45.82}]', 1497, 2075, 577.82, '2025-05-13 13:25:12', 1),
(74, 9, 1, '[{\"id\":\"73\",\"description\":\"kalabaw na inihaw\",\"quantity\":9,\"cost\":45,\"sell\":36,\"profit\":-9}]', 45, 36, -9, '2025-05-13 13:25:27', 1),
(75, 26, 14, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":3,\"cost\":432,\"sell\":555,\"profit\":123},{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":10,\"cost\":560,\"sell\":780,\"profit\":220},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":8,\"cost\":784,\"sell\":1000,\"profit\":216},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":5,\"cost\":1750,\"sell\":2450,\"profit\":700}]', 3526, 4785, 1259, '2025-05-13 13:25:40', 1),
(76, 33, 14, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":10,\"cost\":1440,\"sell\":1850,\"profit\":410},{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":10,\"cost\":560,\"sell\":780,\"profit\":220},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":8,\"cost\":784,\"sell\":1000,\"profit\":216},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":5,\"cost\":1750,\"sell\":2450,\"profit\":700}]', 4534, 6080, 1546, '2025-05-13 13:25:43', 1),
(77, 43, 14, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":20,\"cost\":2880,\"sell\":3700,\"profit\":820},{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":10,\"cost\":560,\"sell\":780,\"profit\":220},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":8,\"cost\":784,\"sell\":1000,\"profit\":216},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":5,\"cost\":1750,\"sell\":2450,\"profit\":700}]', 5974, 7930, 1956, '2025-05-13 13:25:38', 1),
(78, 69, 14, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":23,\"cost\":3312,\"sell\":4255,\"profit\":943},{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":33,\"cost\":1848,\"sell\":2574,\"profit\":726},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":8,\"cost\":784,\"sell\":1000,\"profit\":216},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":5,\"cost\":1750,\"sell\":2450,\"profit\":700}]', 7694, 10279, 2585, '2025-05-13 13:38:39', 1),
(79, 610, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":150,\"cost\":8400,\"sell\":11700,\"profit\":3300},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":49,\"cost\":7056,\"sell\":9065,\"profit\":2009},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":100,\"cost\":9800,\"sell\":12500,\"profit\":2700},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":100,\"cost\":35000,\"sell\":49000,\"profit\":14000},{\"id\":\"61\",\"description\":\"Test Product\",\"quantity\":1,\"cost\":20,\"sell\":28,\"profit\":8},{\"id\":\"62\",\"description\":\"Product Sample Five\",\"quantity\":50,\"cost\":6000,\"sell\":7800,\"profit\":1800},{\"id\":\"63\",\"description\":\"Product Sample Six\",\"quantity\":40,\"cost\":2800,\"sell\":3920,\"profit\":1120},{\"id\":\"64\",\"description\":\"Product Sample Seven\",\"quantity\":20,\"cost\":1000,\"sell\":1400,\"profit\":400},{\"id\":\"65\",\"description\":\"Product Sample Eight\",\"quantity\":1,\"cost\":100,\"sell\":140,\"profit\":40},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":5,\"cost\":125,\"sell\":175,\"profit\":50},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":1,\"cost\":65,\"sell\":91,\"profit\":26},{\"id\":\"73\",\"description\":\"kalabaw na inihaw\",\"quantity\":20,\"cost\":100,\"sell\":80,\"profit\":-20},{\"id\":\"74\",\"description\":\"Product Sample 57\",\"quantity\":10,\"cost\":100,\"sell\":110,\"profit\":10},{\"id\":\"75\",\"description\":\"turon with cream 1\",\"quantity\":40,\"cost\":5000,\"sell\":13760,\"profit\":8760},{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":23,\"cost\":2760,\"sell\":3864,\"profit\":1104}]', 78326, 113633, 35307, '2025-05-13 13:25:02', 1),
(80, 900, 16, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":400,\"cost\":22400,\"sell\":31200,\"profit\":8800},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":200,\"cost\":28800,\"sell\":37000,\"profit\":8200},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":300,\"cost\":29400,\"sell\":37500,\"profit\":8100}]', 80600, 105700, 25100, '2025-05-13 13:25:41', 1),
(81, 70, 19, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":50,\"cost\":7200,\"sell\":9250,\"profit\":2050},{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":20,\"cost\":1120,\"sell\":1560,\"profit\":440}]', 8320, 10810, 2490, '2025-05-13 13:25:36', 1),
(82, 359, 17, '[{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":300,\"cost\":105000,\"sell\":147000,\"profit\":42000},{\"id\":\"76\",\"description\":\"car\",\"quantity\":9,\"cost\":27000,\"sell\":36000,\"profit\":9000},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":50,\"cost\":4900,\"sell\":6250,\"profit\":1350}]', 136900, 189250, 52350, '2025-05-13 13:25:26', 1),
(83, 40, 19, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":10,\"cost\":560,\"sell\":780,\"profit\":220},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":30,\"cost\":4320,\"sell\":5550,\"profit\":1230}]', 4880, 6330, 1450, '2025-05-13 13:25:17', 1),
(84, 90, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":40,\"cost\":2240,\"sell\":3120,\"profit\":880},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":50,\"cost\":17500,\"sell\":24500,\"profit\":7000}]', 19740, 27620, 7880, '2025-05-13 13:25:35', 1),
(85, 1, 1, '[{\"id\":\"77\",\"description\":\"Max level\",\"quantity\":1,\"cost\":45645,\"sell\":168,\"profit\":-45477}]', 45645, 168, -45477, '2025-05-13 13:25:33', 1),
(86, 2, 1, '[{\"id\":\"77\",\"description\":\"Max level\",\"quantity\":2,\"cost\":91290,\"sell\":336,\"profit\":-90954}]', 91290, 336, -90954, '2025-05-13 13:25:32', 1),
(87, 2, 18, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":1,\"cost\":56,\"sell\":78,\"profit\":22},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":1,\"cost\":98,\"sell\":125,\"profit\":27}]', 154, 203, 49, '2025-05-13 13:25:31', 1),
(88, 2, 18, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":1,\"cost\":144,\"sell\":185,\"profit\":41},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":1,\"cost\":98,\"sell\":125,\"profit\":27}]', 242, 310, 68, '2025-05-13 13:24:09', 1),
(89, 115, 17, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":5,\"cost\":605,\"sell\":840,\"profit\":235},{\"id\":\"75\",\"description\":\"turon with cream 1\",\"quantity\":10,\"cost\":1250,\"sell\":3440,\"profit\":2190},{\"id\":\"73\",\"description\":\"kalabaw na inihaw\",\"quantity\":100,\"cost\":500,\"sell\":400,\"profit\":-100}]', 2355, 4680, 2325, '2025-05-13 14:21:44', 1),
(90, 80, 15, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":30,\"cost\":1680,\"sell\":2340,\"profit\":660},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":40,\"cost\":3920,\"sell\":5000,\"profit\":1080},{\"id\":\"63\",\"description\":\"Product Sample Six\",\"quantity\":10,\"cost\":700,\"sell\":980,\"profit\":280}]', 6300, 8320, 2020, '2025-05-13 13:25:30', 1),
(91, 50, 19, '[{\"id\":\"78\",\"description\":\"serwrwrwer\",\"quantity\":50,\"cost\":50000,\"sell\":100000,\"profit\":50000}]', 50000, 100000, 50000, '2025-05-13 13:25:37', 1),
(93, 9, 20, '[{\"id\":\"80\",\"description\":\"mask edited\",\"quantity\":9,\"cost\":11115,\"sell\":40500,\"profit\":29385}]', 11115, 40500, 29385, '2025-05-13 13:25:29', 1),
(94, 7, 16, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":1,\"cost\":144,\"sell\":185,\"profit\":41},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":5,\"cost\":490,\"sell\":625,\"profit\":135},{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":1,\"cost\":121,\"sell\":168,\"profit\":47}]', 755, 978, 223, '2025-05-13 13:25:25', 1),
(95, 12, 15, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":10,\"cost\":1440,\"sell\":1850,\"profit\":410},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":1,\"cost\":98,\"sell\":125,\"profit\":27},{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":1,\"cost\":121,\"sell\":168,\"profit\":47}]', 1659, 2143, 484, '2025-05-13 13:25:23', 1),
(96, 1, 19, '[{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":1,\"cost\":98,\"sell\":125,\"profit\":27}]', 98, 125, 27, '2025-05-13 13:25:22', 1),
(97, 8, 18, '[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":3,\"cost\":195,\"sell\":273,\"profit\":78},{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":3,\"cost\":363,\"sell\":504,\"profit\":141},{\"id\":\"83\",\"description\":\"cyper\",\"quantity\":1,\"cost\":23,\"sell\":234,\"profit\":211},{\"id\":\"84\",\"description\":\"cyper height\",\"quantity\":1,\"cost\":343,\"sell\":2345,\"profit\":2002}]', 924, 3356, 2432, '2025-05-13 13:25:21', 1),
(98, 6, 1, '[{\"id\":\"64\",\"description\":\"Product Sample Seven\",\"quantity\":1,\"cost\":50,\"sell\":70,\"profit\":20},{\"id\":\"63\",\"description\":\"Product Sample Six\",\"quantity\":3,\"cost\":210,\"sell\":294,\"profit\":84},{\"id\":\"84\",\"description\":\"cyper height\",\"quantity\":1,\"cost\":45667,\"sell\":76897,\"profit\":31230},{\"id\":\"83\",\"description\":\"cyper\",\"quantity\":1,\"cost\":657,\"sell\":568,\"profit\":-89}]', 46584, 77829, 31245, '2025-05-13 13:25:20', 1),
(99, 2, 20, '[{\"id\":\"80\",\"description\":\"mask edited\",\"quantity\":1,\"cost\":1235,\"sell\":4500,\"profit\":3265},{\"id\":\"83\",\"description\":\"cyper\",\"quantity\":1,\"cost\":234,\"sell\":235,\"profit\":1}]', 1469, 4735, 3266, '2025-05-13 13:25:18', 1),
(100, 30, 16, '[{\"id\":\"63\",\"description\":\"Product Sample Six\",\"quantity\":4,\"cost\":70,\"sell\":98,\"profit\":28},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":8,\"cost\":25,\"sell\":35,\"profit\":10},{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":1,\"cost\":121,\"sell\":168,\"profit\":47},{\"id\":\"18\",\"description\":\"Product Sample One 1\",\"quantity\":15,\"cost\":840,\"sell\":1170,\"profit\":330},{\"id\":\"85\",\"description\":\"home sweet\",\"quantity\":1,\"cost\":2342,\"sell\":35265,\"profit\":32923},{\"id\":\"84\",\"description\":\"cyper height\",\"quantity\":1,\"cost\":3523,\"sell\":345432,\"profit\":341909}]', 6921, 382168, 375247, '2025-05-13 13:25:16', 1),
(101, 105, 14, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":46,\"cost\":144,\"sell\":185,\"profit\":41},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":58,\"cost\":369.09,\"sell\":516.73,\"profit\":147.64},{\"id\":\"83\",\"description\":\"cyper\",\"quantity\":1,\"cost\":234,\"sell\":235,\"profit\":1}]', 747, 937, 189.64, '2025-05-13 13:25:15', 1),
(102, 101, 1, '[{\"id\":\"79\",\"description\":\"bread\",\"quantity\":100,\"cost\":900,\"sell\":1200,\"profit\":300},{\"id\":\"84\",\"description\":\"cyper height\",\"quantity\":1,\"cost\":1000,\"sell\":2000,\"profit\":1000}]', 1900, 3200, 1300, '2025-05-13 13:25:14', 1),
(103, 25, 15, '[{\"id\":\"76\",\"description\":\"car\",\"quantity\":5,\"cost\":15000,\"sell\":20000,\"profit\":5000},{\"id\":\"75\",\"description\":\"turon with cream\",\"quantity\":4,\"cost\":500,\"sell\":1376,\"profit\":876},{\"id\":\"74\",\"description\":\"Product Sample 57\",\"quantity\":4,\"cost\":40,\"sell\":44,\"profit\":4},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":3,\"cost\":432,\"sell\":555,\"profit\":123},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":7,\"cost\":686,\"sell\":875,\"profit\":189},{\"id\":\"88\",\"description\":\"Sinigang\",\"quantity\":1,\"cost\":75,\"sell\":75,\"profit\":0},{\"id\":\"87\",\"description\":\"adobo\",\"quantity\":1,\"cost\":986,\"sell\":875,\"profit\":-111}]', 17719, 23800, 6081, '2025-05-13 13:25:10', 1),
(104, 1077, 14, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":59,\"cost\":144,\"sell\":185,\"profit\":41},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":7,\"cost\":2450,\"sell\":3430,\"profit\":980},{\"id\":\"61\",\"description\":\"Test Product\",\"quantity\":4,\"cost\":80,\"sell\":112,\"profit\":32},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":4,\"cost\":392,\"sell\":500,\"profit\":108},{\"id\":\"62\",\"description\":\"Product Sample Five\",\"quantity\":25,\"cost\":3000,\"sell\":3900,\"profit\":900},{\"id\":\"79\",\"description\":\"bread 1\",\"quantity\":156,\"cost\":1404,\"sell\":1872,\"profit\":468},{\"id\":\"78\",\"description\":\"serwrwrwer\",\"quantity\":165,\"cost\":165000,\"sell\":330000,\"profit\":165000},{\"id\":\"90\",\"description\":\"mountain dew\",\"quantity\":650,\"cost\":9750,\"sell\":13000,\"profit\":3250},{\"id\":\"84\",\"description\":\"cyper height\",\"quantity\":1,\"cost\":45,\"sell\":34,\"profit\":-11},{\"id\":\"83\",\"description\":\"cyper\",\"quantity\":1,\"cost\":345,\"sell\":345,\"profit\":0},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cost\":453,\"sell\":345,\"profit\":-108},{\"id\":\"88\",\"description\":\"Sinigang\",\"quantity\":1,\"cost\":4366,\"sell\":345,\"profit\":-4021},{\"id\":\"87\",\"description\":\"adobo\",\"quantity\":1,\"cost\":345,\"sell\":45656,\"profit\":45311},{\"id\":\"92\",\"description\":\"litchon\",\"quantity\":1,\"cost\":345,\"sell\":324,\"profit\":-21},{\"id\":\"86\",\"description\":\"turon with cream\",\"quantity\":1,\"cost\":43534,\"sell\":435,\"profit\":-43099}]', 231653, 400483, 168830, '2025-05-13 13:25:11', 1),
(105, 1, 15, '[{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":1,\"cost\":144,\"sell\":185,\"profit\":41}]', 144, 185, 41, '2025-05-13 13:25:09', 1),
(106, 42, 19, '[{\"id\":\"79\",\"description\":\"bread 1\",\"quantity\":40,\"cost\":360,\"sell\":480,\"profit\":120},{\"id\":\"88\",\"description\":\"Sinigang\",\"quantity\":1,\"cost\":400,\"sell\":600,\"profit\":200},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cost\":500,\"sell\":453,\"profit\":-47}]', 1260, 1533, 273, '2025-05-02 04:14:24', 1),
(107, 77, 1, '[{\"id\":\"65\",\"description\":\"Product Sample Eight\",\"quantity\":34,\"cost\":3400,\"sell\":4760,\"profit\":1360},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":23,\"cost\":1495,\"sell\":2093,\"profit\":598},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":20,\"cost\":25,\"sell\":35,\"profit\":10}]', 4920, 6888, 1968, '2025-05-13 13:25:08', 1),
(108, 79, 1, '[{\"id\":\"65\",\"description\":\"Product Sample Eight\",\"quantity\":34,\"cost\":3400,\"sell\":4760,\"profit\":1360},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":45,\"cost\":2925,\"sell\":4095,\"profit\":1170}]', 6325, 8855, 2530, '2025-05-13 13:25:06', 1),
(109, 2, 1, '[{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":2,\"cost\":65,\"sell\":91,\"profit\":26}]', 65, 91, 26, '2025-05-13 13:25:05', 1),
(110, 660, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":200,\"cost\":11200,\"sell\":15600,\"profit\":4400},{\"id\":\"25\",\"description\":\"Product Sample Two\",\"quantity\":49,\"cost\":7056,\"sell\":9065,\"profit\":2009},{\"id\":\"36\",\"description\":\"Product Sample Three\",\"quantity\":100,\"cost\":9800,\"sell\":12500,\"profit\":2700},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":100,\"cost\":35000,\"sell\":49000,\"profit\":14000},{\"id\":\"61\",\"description\":\"Test Product\",\"quantity\":1,\"cost\":20,\"sell\":28,\"profit\":8},{\"id\":\"62\",\"description\":\"Product Sample Five\",\"quantity\":50,\"cost\":6000,\"sell\":7800,\"profit\":1800},{\"id\":\"63\",\"description\":\"Product Sample Six\",\"quantity\":40,\"cost\":2800,\"sell\":3920,\"profit\":1120},{\"id\":\"64\",\"description\":\"Product Sample Seven\",\"quantity\":20,\"cost\":1000,\"sell\":1400,\"profit\":400},{\"id\":\"65\",\"description\":\"Product Sample Eight\",\"quantity\":1,\"cost\":100,\"sell\":140,\"profit\":40},{\"id\":\"66\",\"description\":\"Product Sample Nine\",\"quantity\":5,\"cost\":125,\"sell\":175,\"profit\":50},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":1,\"cost\":65,\"sell\":91,\"profit\":26},{\"id\":\"73\",\"description\":\"kalabaw na inihaw\",\"quantity\":20,\"cost\":100,\"sell\":80,\"profit\":-20},{\"id\":\"74\",\"description\":\"Product Sample 57\",\"quantity\":10,\"cost\":100,\"sell\":110,\"profit\":10},{\"id\":\"75\",\"description\":\"turon with cream 1\",\"quantity\":40,\"cost\":5000,\"sell\":13760,\"profit\":8760},{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":23,\"cost\":2760,\"sell\":3864,\"profit\":1104}]', 81126, 117533, 36407, '2025-05-13 13:25:04', 1),
(111, 100, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":50,\"cost\":17500,\"sell\":24500,\"profit\":7000}]', 20300, 28400, 8100, '2025-05-13 13:25:03', 1),
(112, 201, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":150,\"cost\":52500,\"sell\":73500,\"profit\":21000},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cost\":345,\"sell\":345,\"profit\":0}]', 55645, 77745, 22100, '2025-05-13 13:25:00', 1),
(113, 203, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":152,\"cost\":53200,\"sell\":74480,\"profit\":21280},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cost\":345,\"sell\":345,\"profit\":0}]', 56345, 78725, 22380, '2025-05-13 13:24:59', 1),
(114, 203, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":152,\"cost\":53200,\"sell\":74480,\"profit\":21280},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cost\":345,\"sell\":345,\"profit\":0}]', 56345, 78725, 22380, '2025-05-13 13:24:58', 1),
(115, 201, 1, '[{\"id\":\"18\",\"description\":\"Product Sample One\",\"quantity\":50,\"cost\":2800,\"sell\":3900,\"profit\":1100},{\"id\":\"44\",\"description\":\"Product Sample Four\",\"quantity\":150,\"cost\":52500,\"sell\":73500,\"profit\":21000},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cost\":345,\"sell\":345,\"profit\":0}]', 55645, 77745, 22100, '2025-05-13 13:24:56', 1),
(116, 90, 1, '[{\"id\":\"68\",\"description\":\"Product Sample Eleven\",\"quantity\":28,\"cost\":3388,\"sell\":4704,\"profit\":1316},{\"id\":\"67\",\"description\":\"Product Sample Ten\",\"quantity\":50,\"cost\":3250,\"sell\":4550,\"profit\":1300},{\"id\":\"90\",\"description\":\"mountain dew\",\"quantity\":10,\"cost\":150,\"sell\":200,\"profit\":50},{\"id\":\"95\",\"description\":\"kamatayan lami\",\"quantity\":1,\"cost\":546,\"sell\":600,\"profit\":54},{\"id\":\"94\",\"description\":\"blablabla\",\"quantity\":1,\"cost\":456,\"sell\":500,\"profit\":44}]', 7790, 10554, 2764, '2025-05-13 13:24:52', 1),
(117, 6, 21, '[{\"id\":\"98\",\"description\":\"nuts me\",\"quantity\":5,\"cost\":5000,\"sell\":15000,\"profit\":10000},{\"id\":\"99\",\"description\":\"longasina jr\",\"quantity\":1,\"cost\":345,\"sell\":2535,\"profit\":2190}]', 5345, 17535, 12190, '2025-05-13 13:24:55', 1),
(118, 7, 21, '[{\"id\":\"98\",\"description\":\"nuts me\",\"quantity\":5,\"cost\":5000,\"sell\":15000,\"profit\":10000},{\"id\":\"99\",\"description\":\"longasina jr\",\"quantity\":1,\"cost\":345,\"sell\":400,\"profit\":55},{\"id\":\"100\",\"description\":\"blablacanteen jr\",\"quantity\":1,\"cost\":345,\"sell\":34,\"profit\":-311}]', 5690, 15434, 9744, '2025-05-13 13:24:54', 1),
(119, 36, 21, '[{\"id\":\"98\",\"description\":\"nuts me\",\"quantity\":34,\"cost\":34000,\"sell\":102000,\"profit\":68000},{\"id\":\"99\",\"description\":\"longasina jr\",\"quantity\":1,\"cost\":345,\"sell\":400,\"profit\":55},{\"id\":\"100\",\"description\":\"blablacanteen jr\",\"quantity\":1,\"cost\":400,\"sell\":34,\"profit\":-366}]', 34745, 102434, 67689, '2025-05-13 13:24:53', 1),
(120, 0, 1, '[{\"id\":\"116\",\"description\":\"Mountain Dew 330ml\",\"quantity\":0,\"cost\":0,\"sell\":0,\"profit\":0}]', 0, 0, 0, '2025-05-13 13:24:48', 1),
(121, 1, 1, '[{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cost\":324,\"sell\":32,\"profit\":-292}]', 324, 32, -292, '2025-05-13 13:24:45', 1),
(122, 369, 1, '[{\"id\":\"114\",\"description\":\"summit 500ml\",\"quantity\":234,\"cost\":2574,\"sell\":3510,\"profit\":936},{\"id\":\"115\",\"description\":\"summit 1L\",\"quantity\":134,\"cost\":2680,\"sell\":4020,\"profit\":1340},{\"id\":\"106\",\"description\":\"Bistek Tagalog\",\"quantity\":1,\"cost\":234,\"sell\":300,\"profit\":66}]', 5488, 7830, 2342, '2025-04-23 00:12:25', 0),
(123, 241, 1, '[{\"id\":\"114\",\"description\":\"summit 500ml\",\"quantity\":230,\"cost\":2530,\"sell\":3450,\"profit\":920},{\"id\":\"116\",\"description\":\"Mountain Dew 330ml\",\"quantity\":10,\"cost\":200,\"sell\":300,\"profit\":100},{\"id\":\"106\",\"description\":\"Bistek Tagalog\",\"quantity\":1,\"cost\":234,\"sell\":600,\"profit\":366}]', 2964, 4350, 1386, '2025-05-01 00:14:52', 0),
(124, 3241, 1, '[{\"id\":\"114\",\"description\":\"summit 500ml\",\"quantity\":3240,\"cost\":35640,\"sell\":48600,\"profit\":12960},{\"id\":\"106\",\"description\":\"Bistek Tagalog\",\"quantity\":1,\"cost\":2344,\"sell\":3423,\"profit\":1079}]', 37984, 52023, 14039, '2025-05-06 07:41:35', 0),
(125, 228, 1, '[{\"id\":\"114\",\"description\":\"summit 500ml\",\"quantity\":54,\"cost\":594,\"sell\":810,\"profit\":216},{\"id\":\"115\",\"description\":\"summit 1L\",\"quantity\":45,\"cost\":900,\"sell\":1350,\"profit\":450},{\"id\":\"118\",\"description\":\"Neck Tie\",\"quantity\":65,\"cost\":2600,\"sell\":3250,\"profit\":650},{\"id\":\"117\",\"description\":\"Mogu Mogu 350ml\",\"quantity\":56,\"cost\":1120,\"sell\":1680,\"profit\":560},{\"id\":\"94\",\"description\":\"blablabla\",\"quantity\":1,\"cost\":5754,\"sell\":34536,\"profit\":28782},{\"id\":\"89\",\"description\":\"Menudo\",\"quantity\":1,\"cost\":435,\"sell\":346,\"profit\":-89},{\"id\":\"96\",\"description\":\"Ginataang isda\",\"quantity\":1,\"cost\":346,\"sell\":400,\"profit\":54},{\"id\":\"121\",\"description\":\"Shumai\",\"quantity\":1,\"cost\":34536,\"sell\":34666,\"profit\":130},{\"id\":\"120\",\"description\":\"spaghetti\",\"quantity\":1,\"cost\":3465,\"sell\":4355,\"profit\":890},{\"id\":\"123\",\"description\":\"Hatdog with rice\",\"quantity\":1,\"cost\":2325,\"sell\":4000,\"profit\":1675},{\"id\":\"125\",\"description\":\"fish fillet\",\"quantity\":1,\"cost\":345,\"sell\":50,\"profit\":-295},{\"id\":\"124\",\"description\":\"chicken afritada\",\"quantity\":1,\"cost\":456,\"sell\":456,\"profit\":0}]', 52876, 85899, 33023, '2025-05-10 07:41:11', 0),
(127, 2926, 17, '[{\"id\":\"97\",\"description\":\"coca cola\",\"quantity\":2345,\"cost\":46900,\"sell\":70350,\"profit\":23450},{\"id\":\"108\",\"description\":\"Halo Halo\",\"quantity\":234,\"cost\":7020,\"sell\":9360,\"profit\":2340},{\"id\":\"112\",\"description\":\"Ube Halaya\",\"quantity\":344,\"cost\":6880,\"sell\":10320,\"profit\":3440},{\"id\":\"109\",\"description\":\"Bibingka\",\"quantity\":1,\"cost\":234,\"sell\":324,\"profit\":90},{\"id\":\"111\",\"description\":\"Suman\",\"quantity\":1,\"cost\":3425,\"sell\":234,\"profit\":-3191},{\"id\":\"110\",\"description\":\"Puto\",\"quantity\":1,\"cost\":234,\"sell\":2342,\"profit\":2108}]', 64693, 92930, 28237, '2025-04-06 07:49:48', 0),
(128, 61, 17, '[{\"id\":\"112\",\"description\":\"Ube Halaya\",\"quantity\":34,\"cost\":680,\"sell\":1020,\"profit\":340},{\"id\":\"108\",\"description\":\"Halo Halo\",\"quantity\":23,\"cost\":690,\"sell\":920,\"profit\":230},{\"id\":\"113\",\"description\":\"Buko Pandan\",\"quantity\":1,\"cost\":2000,\"sell\":4000,\"profit\":2000},{\"id\":\"107\",\"description\":\"Leche Flan\",\"quantity\":1,\"cost\":3456,\"sell\":4366,\"profit\":910},{\"id\":\"88\",\"description\":\"Sinigang\",\"quantity\":1,\"cost\":345,\"sell\":3456,\"profit\":3111},{\"id\":\"84\",\"description\":\"cyper height\",\"quantity\":1,\"cost\":345,\"sell\":2367,\"profit\":2022}]', 7516, 16129, 8613, '2025-04-11 08:04:27', 0),
(129, 61, 17, '[{\"id\":\"108\",\"description\":\"Halo Halo\",\"quantity\":35,\"cost\":1050,\"sell\":1400,\"profit\":350},{\"id\":\"112\",\"description\":\"Ube Halaya\",\"quantity\":23,\"cost\":460,\"sell\":690,\"profit\":230},{\"id\":\"110\",\"description\":\"Puto\",\"quantity\":1,\"cost\":345,\"sell\":500,\"profit\":155},{\"id\":\"109\",\"description\":\"Bibingka\",\"quantity\":1,\"cost\":465,\"sell\":5678,\"profit\":5213},{\"id\":\"107\",\"description\":\"Leche Flan\",\"quantity\":1,\"cost\":567,\"sell\":5678,\"profit\":5111}]', 2887, 13946, 11059, '2025-04-01 08:13:58', 0),
(130, 104, 17, '[{\"id\":\"112\",\"description\":\"Ube Halaya\",\"quantity\":100,\"cost\":2000,\"sell\":3000,\"profit\":1000},{\"id\":\"107\",\"description\":\"Leche Flan\",\"quantity\":1,\"cost\":2432,\"sell\":23443,\"profit\":21011},{\"id\":\"88\",\"description\":\"Sinigang\",\"quantity\":1,\"cost\":324,\"sell\":2344,\"profit\":2020},{\"id\":\"110\",\"description\":\"Puto\",\"quantity\":1,\"cost\":2344,\"sell\":2344,\"profit\":0},{\"id\":\"111\",\"description\":\"Suman\",\"quantity\":1,\"cost\":24567,\"sell\":37778,\"profit\":13211}]', 31667, 68909, 37242, '2025-04-25 08:16:33', 0),
(131, 1, 14, '[{\"id\":\"93\",\"description\":\"litchot\",\"quantity\":1,\"cost\":2345,\"sell\":23425,\"profit\":21080}]', 2345, 23425, 21080, '2025-05-13 08:17:51', 0),
(132, 1, 20, '[{\"id\":\"85\",\"description\":\"home sweet\",\"quantity\":1,\"cost\":2353,\"sell\":34564,\"profit\":32211}]', 2353, 34564, 32211, '2025-05-13 08:24:33', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` text COLLATE utf8_spanish_ci NOT NULL,
  `user` text COLLATE utf8_spanish_ci NOT NULL,
  `password` text COLLATE utf8_spanish_ci NOT NULL,
  `profile` text COLLATE utf8_spanish_ci NOT NULL,
  `photo` text COLLATE utf8_spanish_ci NOT NULL,
  `status` int(1) NOT NULL,
  `lastLogin` datetime NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `del_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=COMPACT;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `user`, `password`, `profile`, `photo`, `status`, `lastLogin`, `date`, `del_status`) VALUES
(1, 'Administrator', 'admin', 'admin1123', 'Administrator', 'views/img/users/admin/admin-icn.png', 1, '2025-04-04 06:51:27', '2025-04-20 06:52:59', 0),
(3, 'Carmen McLeod', 'carmen', '$2a$07$asxx54ahjppf45sd87a5au8uJqn2VoaOMw86zRUoDH6inuYomGLDq', 'Special', 'views/img/users/carmen/215.jpg', 0, '2022-12-10 12:17:55', '2025-04-11 13:43:00', 0),
(4, 'john mark d. cabreros', 'cabs', 'jm1123', 'administrator', 'views/img/users/admin/admin-icn.png', 1, '2025-04-11 12:19:27', '2025-05-13 14:56:20', 0),
(6, 'raul baldeo', 'raul', '1123', 'administrator', '', 1, '2025-03-22 08:26:10', '2025-04-13 13:29:19', 0);

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
-- Indexes for table `product_stocks`
--
ALTER TABLE `product_stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recent_sales`
--
ALTER TABLE `recent_sales`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `product_stocks`
--
ALTER TABLE `product_stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `recent_sales`
--
ALTER TABLE `recent_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
