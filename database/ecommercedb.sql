-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2025 at 03:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30




/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommercedb`
--

CREATE DATABASE IF NOT EXISTS ecommercedb;
USE ecommercedb;



CREATE TABLE `advertisement` (
  `id` int(11) NOT NULL,
  `company_info` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-09-17 10:52:14', '2025-09-17 10:52:14'),
(2, 3, '2025-09-17 12:14:48', '2025-09-17 12:14:48'),
(3, 10, '2025-09-20 14:57:48', '2025-09-20 14:57:48'),
(4, 12, '2025-09-27 15:14:13', '2025-09-27 15:14:13'),
(5, 13, '2025-09-28 15:36:49', '2025-09-28 15:36:49');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `added_at`) VALUES
(86, 2, 3, 1, '2025-09-26 13:09:56'),
(105, 5, 116, 1, '2025-09-28 15:36:54'),
(108, 4, 116, 2, '2025-09-29 15:33:28');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `product_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `product_type`, `description`, `is_active`, `created_at`) VALUES
(1, 'Điện thoại', 'Điện thoại thông minh \r\n', 1, '2025-09-09 15:09:31'),
(2, 'Laptop', 'Máy tính xách tay', 1, '2025-09-09 15:09:31'),
(3, 'Đồng hồ', 'Đồng hồ thông minh', 1, '2025-09-09 15:09:31'),
(4, 'Phụ kiện Laptop', 'Bàn phím, chuột,...', 1, '2025-09-09 15:09:31'),
(5, 'Tablet\r\n', 'Máy tính bảng\r\n\r\n', 1, '2025-09-23 08:22:05'),
(6, 'Phụ kiện điện thoại\r\n', 'Sạc, pin dự phòng, ốp lưng,...', 1, '2025-09-23 08:22:05'),
(7, 'Âm thanh \r\n', 'Tai nghe, loa bluetooth...', 1, '2025-09-23 08:22:05');

-- --------------------------------------------------------

--
-- Table structure for table `dim_date`
--

CREATE TABLE `dim_date` (
  `date_id` int(11) NOT NULL,
  `full_date` date DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `quarter` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dim_product`
--

CREATE TABLE `dim_product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fact_sales`
--

CREATE TABLE `fact_sales` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `date_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `total_amount` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `attempts` int(11) DEFAULT 0,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp(),
  `locked_until` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `user_id`, `attempts`, `last_attempt`, `locked_until`) VALUES
(1, 3, 0, '2025-09-26 14:08:52', NULL),
(2, 1, 0, '2025-09-27 08:47:32', '2025-09-27 03:52:32'),
(3, 7, 0, '2025-09-12 16:52:37', NULL),
(4, 10, 0, '2025-09-20 14:57:42', NULL),
(5, 11, 0, '2025-09-26 10:22:05', NULL),
(6, 8, 1, '2025-09-27 08:24:20', NULL),
(7, 12, 0, '2025-09-30 12:37:16', NULL),
(8, 13, 0, '2025-09-30 03:25:03', NULL),
(9, 15, 0, '2025-09-29 15:40:57', NULL),
(10, 17, 0, '2025-09-30 11:02:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancel_request','cancelled') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(18,2) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_method` enum('cod','momo','paypal') DEFAULT 'cod',
  `paypal_order_id` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `momo_transaction_id` varchar(255) DEFAULT NULL,
  `paypal_transaction_id` varchar(255) DEFAULT NULL,
  `paypal_payer_email` varchar(255) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `fullname`, `email`, `phone`, `address`, `note`, `status`, `order_date`, `total`, `updated_at`, `payment_method`, `paypal_order_id`, `payment_status`, `momo_transaction_id`, `paypal_transaction_id`, `paypal_payer_email`, `payment_date`, `created_at`) VALUES
(102, 3, 'Nguyen Huy Quang', NULL, '0886922226', 'vinh', NULL, 'cancelled', '2025-09-20 08:31:53', 133560000.00, '2025-09-22 18:25:44', 'paypal', '6A158817D3405053W', 'failed', NULL, NULL, NULL, '2025-09-20 08:32:06', '2025-09-20 15:31:53'),
(110, 3, 'Nguyen Huy Quang', NULL, '0886922226', 'vinh', NULL, 'cancelled', '2025-09-20 08:56:53', 33390000.00, '2025-09-22 17:57:25', 'paypal', '2BX17574NG1983445', 'failed', NULL, NULL, NULL, '2025-09-20 08:57:24', '2025-09-20 15:56:53'),
(112, 3, NULL, NULL, NULL, NULL, NULL, 'pending', '2025-09-20 08:58:23', 33390000.00, '2025-09-20 08:58:23', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-20 15:58:23'),
(113, 3, NULL, NULL, NULL, NULL, NULL, 'cancelled', '2025-09-20 09:38:21', 33390000.00, '2025-09-24 11:40:38', 'paypal', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-20 16:38:21'),
(114, 3, 'Nguyen Huy Quang', NULL, '0886922226', 'vinh', NULL, 'cancelled', '2025-09-20 09:56:58', 33390000.00, '2025-09-22 18:28:14', 'paypal', '6DH473653S930610E', 'failed', NULL, NULL, NULL, '2025-09-20 09:57:52', '2025-09-20 16:56:58'),
(115, 3, 'Nguyen Huy Quang', NULL, '0886922226', 'vinh', NULL, 'cancelled', '2025-09-20 10:00:59', 33390000.00, '2025-09-21 17:47:01', 'cod', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-20 17:00:59'),
(116, 3, 'Nguyen Huy Quang', NULL, '0886922226', 'vinh', NULL, 'cancel_request', '2025-09-20 10:01:55', 33390000.00, '2025-09-22 18:23:20', 'momo', NULL, 'paid', 'MOMO_1758362517_116', NULL, NULL, '2025-09-20 10:01:57', '2025-09-20 17:01:55'),
(117, 3, 'Quang Huy Nguyen', NULL, '0886924681', 'Vinh,Nghe An', NULL, 'cancelled', '2025-09-20 14:38:31', 33390000.00, '2025-09-22 18:24:36', 'paypal', '39K59123HC304090B', 'failed', NULL, NULL, NULL, '2025-09-20 14:41:38', '2025-09-20 21:38:31'),
(118, 3, 'quang', NULL, '0886922226', 'vinhfffff', NULL, 'pending', '2025-09-20 14:40:28', 33390000.00, '2025-09-22 13:16:19', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-20 21:40:28'),
(119, 3, NULL, NULL, '0886922226', 'vinhfffff', NULL, 'cancelled', '2025-09-20 14:49:23', 66780000.00, '2025-09-22 13:16:23', 'cod', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-20 21:49:23'),
(120, 3, 'Nguyen Huy Quang', NULL, '0886922226', 'vinh', NULL, 'cancelled', '2025-09-20 14:49:31', 33390000.00, '2025-09-22 18:23:44', 'momo', NULL, 'failed', 'MOMO_1758379773_120', NULL, NULL, '2025-09-20 14:49:33', '2025-09-20 21:49:31'),
(121, 3, 'Nguyen Huy Quang', NULL, '0886924681', 'hà nội', NULL, 'cancelled', '2025-09-20 14:49:40', 33390000.00, '2025-09-22 18:27:11', 'paypal', '3A740319T3420432U', 'failed', NULL, NULL, NULL, NULL, '2025-09-20 21:49:40'),
(122, 10, 'Quang Huy Nguyen', NULL, '0886924681', 'Vinh,Nghe An', NULL, 'confirmed', '2025-09-20 14:57:59', 141340000.00, '2025-09-20 14:58:04', 'cod', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-20 21:57:59'),
(123, 3, 'Nguyen Huy Quang', NULL, '0886922226', 'vinh', NULL, 'cancelled', '2025-09-20 15:54:32', 33390000.00, '2025-09-22 17:27:38', 'paypal', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-20 22:54:32'),
(124, 3, NULL, NULL, NULL, NULL, NULL, 'pending', '2025-09-20 15:54:45', 33390000.00, '2025-09-20 15:54:45', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-20 22:54:45'),
(125, 3, NULL, NULL, NULL, NULL, NULL, 'pending', '2025-09-20 15:54:57', 33390000.00, '2025-09-20 15:54:57', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-20 22:54:57'),
(126, 3, NULL, NULL, NULL, NULL, NULL, 'cancelled', '2025-09-20 15:56:12', 66780000.00, '2025-09-22 18:09:33', 'paypal', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-20 22:56:12'),
(127, 1, 'Super Admin', NULL, 'adasdadad', 'adasdadadasd', NULL, 'pending', '2025-09-20 15:56:27', 33390000.00, '2025-09-20 15:56:34', 'paypal', '21A684846S196312B', 'pending', NULL, NULL, NULL, NULL, '2025-09-20 22:56:27'),
(128, 3, NULL, NULL, NULL, NULL, NULL, 'cancelled', '2025-09-20 15:57:09', 100170000.00, '2025-09-22 18:21:09', 'paypal', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-20 22:57:09'),
(129, 3, 'Quang Huy Nguyen', NULL, '0886924681', 'Vinh,Nghe An', NULL, 'cancel_request', '2025-09-20 15:59:59', 133560000.00, '2025-09-22 11:55:35', 'momo', NULL, 'paid', 'MOMO_1758384002_129', NULL, NULL, '2025-09-20 16:00:02', '2025-09-20 22:59:59'),
(130, 3, NULL, NULL, NULL, NULL, NULL, 'cancel_request', '2025-09-20 16:00:10', 33390000.00, '2025-09-22 16:58:51', 'momo', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-20 23:00:10'),
(131, 3, NULL, NULL, NULL, NULL, NULL, 'cancel_request', '2025-09-20 16:18:16', 66780000.00, '2025-09-22 17:09:54', 'momo', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-20 23:18:16'),
(132, 3, NULL, NULL, NULL, NULL, NULL, 'cancel_request', '2025-09-20 16:18:26', 94770000.00, '2025-09-22 17:11:51', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-20 23:18:26'),
(133, 3, NULL, NULL, NULL, NULL, NULL, 'pending', '2025-09-20 16:45:27', 94770000.00, '2025-09-20 16:45:27', 'cod', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-20 23:45:27'),
(134, 3, NULL, NULL, NULL, NULL, NULL, 'cancelled', '2025-09-21 14:33:02', 66780000.00, '2025-09-22 17:27:27', 'paypal', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-21 21:33:02'),
(135, 3, NULL, NULL, NULL, NULL, NULL, 'cancelled', '2025-09-21 14:33:02', 66780000.00, '2025-09-22 18:02:53', 'paypal', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-21 21:33:02'),
(136, 3, 'Nguyen Huy Quang', NULL, '0886922226', 'vinh', NULL, 'cancel_request', '2025-09-21 14:48:24', 66780000.00, '2025-09-22 12:03:18', 'paypal', '2XW424408C217874W', 'paid', NULL, NULL, NULL, '2025-09-21 14:48:57', '2025-09-21 21:48:24'),
(137, 3, NULL, NULL, NULL, NULL, NULL, 'cancel_request', '2025-09-21 16:11:10', 33390000.00, '2025-09-22 17:09:44', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-21 23:11:10'),
(138, 3, NULL, NULL, NULL, NULL, NULL, 'cancelled', '2025-09-22 12:15:25', 33390000.00, '2025-09-22 17:20:07', 'paypal', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-22 19:15:25'),
(139, 3, NULL, NULL, NULL, NULL, NULL, 'cancelled', '2025-09-22 12:58:56', 33390000.00, '2025-09-22 17:19:49', 'momo', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-22 19:58:56'),
(140, 3, NULL, NULL, NULL, NULL, NULL, 'cancel_request', '2025-09-22 12:59:18', 33390000.00, '2025-09-22 17:01:51', 'cod', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-22 19:59:18'),
(141, 3, NULL, NULL, NULL, NULL, NULL, 'cancel_request', '2025-09-22 12:59:35', 33390000.00, '2025-09-22 16:59:51', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-22 19:59:35'),
(142, 3, 'Nguyen Huy Quang', NULL, '0886922226', 'vinh', NULL, 'cancel_request', '2025-09-22 13:02:16', 33390000.00, '2025-09-22 16:59:47', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-22 20:02:16'),
(143, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancelled', '2025-09-22 13:13:49', 33390000.00, '2025-09-22 18:01:40', 'paypal', '35P46430EH3224704', 'failed', NULL, NULL, NULL, '2025-09-22 13:15:25', '2025-09-22 20:13:49'),
(144, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancelled', '2025-09-22 16:49:40', 33390000.00, '2025-09-22 18:01:48', 'paypal', '5EP55380C73278829', 'failed', NULL, NULL, NULL, '2025-09-22 16:50:22', '2025-09-22 23:49:40'),
(145, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancelled', '2025-09-22 18:18:56', 33390000.00, '2025-09-22 18:23:10', 'cod', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-23 01:18:56'),
(146, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancelled', '2025-09-22 18:19:06', 33390000.00, '2025-09-22 18:20:43', 'momo', NULL, 'failed', 'MOMO_1758565147_146', NULL, NULL, '2025-09-22 18:19:07', '2025-09-23 01:19:06'),
(147, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancelled', '2025-09-22 18:19:13', 33390000.00, '2025-09-22 18:20:41', 'paypal', '00G12229K5861384G', 'failed', NULL, NULL, NULL, '2025-09-22 18:19:41', '2025-09-23 01:19:13'),
(148, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-22 18:27:25', 33390000.00, '2025-09-22 18:27:25', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-23 01:27:25'),
(149, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancelled', '2025-09-22 18:27:42', 100170000.00, '2025-09-22 18:28:05', 'momo', NULL, 'failed', 'MOMO_1758565663_149', NULL, NULL, '2025-09-22 18:27:43', '2025-09-23 01:27:42'),
(150, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 09:56:26', 66780000.00, '2025-09-24 09:56:26', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 16:56:26'),
(151, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 09:58:28', 66780000.00, '2025-09-24 09:58:28', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 16:58:28'),
(152, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 09:59:06', 33390000.00, '2025-09-24 09:59:06', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 16:59:06'),
(153, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 10:00:21', 27990000.00, '2025-09-24 10:00:21', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 17:00:21'),
(154, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 10:00:21', 27990000.00, '2025-09-24 10:00:21', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 17:00:21'),
(155, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 10:03:31', 33390000.00, '2025-09-24 10:03:31', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 17:03:31'),
(156, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 10:03:31', 33390000.00, '2025-09-24 10:03:31', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 17:03:31'),
(157, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 10:05:57', 33390000.00, '2025-09-24 10:05:57', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 17:05:57'),
(158, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 10:07:41', 33390000.00, '2025-09-24 10:07:41', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 17:07:41'),
(159, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 10:08:19', 66780000.00, '2025-09-24 10:08:19', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 17:08:19'),
(160, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 10:11:49', 33390000.00, '2025-09-24 10:11:49', 'momo', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 17:11:49'),
(161, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 10:11:49', 33390000.00, '2025-09-24 10:11:49', 'momo', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 17:11:49'),
(162, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancelled', '2025-09-24 10:11:55', 33390000.00, '2025-09-24 10:32:21', 'paypal', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-24 17:11:55'),
(163, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'confirmed', '2025-09-24 10:16:50', 66780000.00, '2025-09-24 10:24:59', 'paypal', '3C914824BC865632M', 'paid', NULL, NULL, NULL, '2025-09-24 10:24:59', '2025-09-24 17:16:50'),
(164, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancelled', '2025-09-24 10:16:50', 66780000.00, '2025-09-24 10:32:38', 'paypal', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-24 17:16:50'),
(165, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancelled', '2025-09-24 10:26:53', 66780000.00, '2025-09-24 11:40:11', 'paypal', '6Y828940XE770752H', 'failed', NULL, NULL, NULL, '2025-09-24 10:27:05', '2025-09-24 17:26:53'),
(166, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancel_request', '2025-09-24 10:31:25', 66780000.00, '2025-09-24 10:32:24', 'paypal', '7TM79999N5639841X', 'paid', NULL, NULL, NULL, '2025-09-24 10:31:36', '2025-09-24 17:31:25'),
(167, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancelled', '2025-09-24 10:45:55', 27990000.00, '2025-09-24 11:39:50', 'paypal', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-24 17:45:55'),
(168, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancelled', '2025-09-24 10:46:06', 61380000.00, '2025-09-24 11:39:46', 'paypal', '2DS071734K057440M', 'failed', NULL, NULL, NULL, NULL, '2025-09-24 17:46:06'),
(169, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancel_request', '2025-09-24 11:37:46', 61380000.00, '2025-09-24 11:40:48', 'paypal', '8LJ210166N194560U', 'paid', NULL, NULL, NULL, '2025-09-24 11:38:27', '2025-09-24 18:37:46'),
(170, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 11:58:45', 33390000.00, '2025-09-24 11:58:45', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 18:58:45'),
(171, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 11:59:52', 33390000.00, '2025-09-24 11:59:52', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 18:59:52'),
(172, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 12:41:36', 59380000.00, '2025-09-24 12:41:36', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 19:41:36'),
(173, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'confirmed', '2025-09-24 12:41:59', 59380000.00, '2025-09-24 12:42:32', 'paypal', '71A01733YM868332N', 'paid', NULL, NULL, NULL, '2025-09-24 12:42:32', '2025-09-24 19:41:59'),
(174, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'confirmed', '2025-09-24 13:01:14', 33390000.00, '2025-09-24 14:03:59', 'paypal', '78H10521AN082502W', 'paid', NULL, NULL, NULL, '2025-09-24 14:03:59', '2025-09-24 20:01:14'),
(175, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'confirmed', '2025-09-24 15:51:09', 37990000.00, '2025-09-24 15:52:08', 'paypal', '29W305284E866663E', 'paid', NULL, NULL, NULL, '2025-09-24 15:52:08', '2025-09-24 22:51:09'),
(176, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 16:03:50', 333900000.00, '2025-09-24 16:03:50', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 23:03:50'),
(177, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 16:03:50', 333900000.00, '2025-09-24 16:03:50', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 23:03:50'),
(178, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 16:03:50', 333900000.00, '2025-09-24 16:03:50', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 23:03:50'),
(179, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-24 16:03:50', 333900000.00, '2025-09-24 16:03:50', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-24 23:03:50'),
(180, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'confirmed', '2025-09-25 14:53:29', 333900000.00, '2025-09-25 14:53:35', 'paypal', '6S95986776657872U', 'pending', NULL, NULL, NULL, NULL, '2025-09-25 21:53:29'),
(181, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-25 15:57:39', 333900000.00, '2025-09-25 15:57:39', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-25 22:57:39'),
(182, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancel_request', '2025-09-26 09:58:29', 25990000.00, '2025-09-26 09:59:04', 'paypal', '166495006F162781U', 'paid', NULL, NULL, NULL, '2025-09-26 09:58:51', '2025-09-26 16:58:29'),
(183, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'cancelled', '2025-09-26 09:59:15', 25990000.00, '2025-09-26 09:59:30', 'paypal', NULL, 'failed', NULL, NULL, NULL, NULL, '2025-09-26 16:59:15'),
(184, 1, 'Super Admin', '', 'adasdadad', 'adasdadadasd', NULL, 'confirmed', '2025-09-26 11:38:06', 33390000.00, '2025-09-26 11:38:07', 'cod', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-26 18:38:06'),
(185, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-26 13:00:56', 36980000.00, '2025-09-26 13:00:56', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-26 20:00:56'),
(186, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-26 13:02:11', 36980000.00, '2025-09-26 13:02:11', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-26 20:02:11'),
(187, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'confirmed', '2025-09-26 13:02:15', 36980000.00, '2025-09-26 13:02:17', 'momo', NULL, 'paid', 'MOMO_1758891737_187', NULL, NULL, '2025-09-26 13:02:17', '2025-09-26 20:02:15'),
(188, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-26 13:10:04', 25990000.00, '2025-09-26 13:10:04', 'cod', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-26 20:10:04'),
(189, 3, 'Nguyen Huy Quang', '', '0886922226', 'vinh', NULL, 'pending', '2025-09-26 14:03:31', 25990000.00, '2025-09-26 14:03:31', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-26 21:03:31'),
(190, 12, 'Long Ho', '', '0987654321', 'DAKLAK', NULL, 'pending', '2025-09-27 16:50:23', 33390000.00, '2025-09-27 16:50:23', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-27 23:50:23'),
(191, 12, 'Long Ho', '', '0987654321', 'DAKLAK', NULL, 'pending', '2025-09-27 16:50:23', 33390000.00, '2025-09-27 16:50:23', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-27 23:50:23'),
(192, 12, 'Long Ho', '', '0987654321', 'DAKLAK', NULL, 'pending', '2025-09-27 16:50:23', 33390000.00, '2025-09-27 16:50:23', 'paypal', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-27 23:50:23'),
(193, 12, 'Long Ho', '', '0987654321', 'DAKLAK', NULL, 'confirmed', '2025-09-27 17:47:26', 64410000.00, '2025-09-27 17:47:34', 'paypal', '4N743856EM158025X', 'pending', NULL, NULL, NULL, NULL, '2025-09-28 00:47:26'),
(194, 12, 'Long Ho', '', '0987654321', 'DAKLAK', NULL, 'confirmed', '2025-09-27 18:28:26', 322759000.00, '2025-09-27 18:28:31', 'paypal', '9P070352067754347', 'pending', NULL, NULL, NULL, NULL, '2025-09-28 01:28:26'),
(195, 13, 'Long Ho customer', '', '0903222424', '120 TT', NULL, 'confirmed', '2025-09-28 15:36:59', 30000000.00, '2025-09-28 15:37:03', 'paypal', '1WA45720AX3208616', 'pending', NULL, NULL, NULL, NULL, '2025-09-28 22:36:59'),
(196, 13, 'Long Ho customer', '', '0903222424', '120 TT', NULL, 'confirmed', '2025-09-28 15:45:56', 30000000.00, '2025-09-28 15:45:58', 'cod', NULL, 'pending', NULL, NULL, NULL, NULL, '2025-09-28 22:45:56');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `price` decimal(18,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(18,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `price`, `quantity`, `total`) VALUES
(39, 102, 1, 33390000.00, 4, 0.00),
(47, 110, 1, 33390000.00, 1, 0.00),
(49, 112, 1, 33390000.00, 1, 0.00),
(50, 113, 1, 33390000.00, 1, 0.00),
(51, 114, 1, 33390000.00, 1, 0.00),
(52, 115, 1, 33390000.00, 1, 0.00),
(53, 116, 1, 33390000.00, 1, 0.00),
(54, 117, 1, 33390000.00, 1, 0.00),
(55, 118, 1, 33390000.00, 1, 0.00),
(56, 119, 1, 33390000.00, 2, 0.00),
(57, 120, 1, 33390000.00, 1, 0.00),
(58, 121, 1, 33390000.00, 1, 0.00),
(59, 122, 6, 34990000.00, 1, 0.00),
(60, 122, 5, 7990000.00, 1, 0.00),
(61, 122, 4, 10990000.00, 1, 0.00),
(62, 122, 3, 25990000.00, 1, 0.00),
(63, 122, 2, 27990000.00, 1, 0.00),
(64, 122, 1, 33390000.00, 1, 0.00),
(65, 123, 1, 33390000.00, 1, 0.00),
(66, 124, 1, 33390000.00, 1, 0.00),
(67, 125, 1, 33390000.00, 1, 0.00),
(68, 126, 1, 33390000.00, 2, 0.00),
(69, 127, 1, 33390000.00, 1, 0.00),
(70, 128, 1, 33390000.00, 3, 0.00),
(71, 129, 1, 33390000.00, 4, 0.00),
(72, 130, 1, 33390000.00, 1, 0.00),
(73, 131, 1, 33390000.00, 2, 0.00),
(74, 132, 1, 33390000.00, 2, 0.00),
(75, 132, 2, 27990000.00, 1, 0.00),
(76, 133, 1, 33390000.00, 2, 0.00),
(77, 133, 2, 27990000.00, 1, 0.00),
(78, 134, 1, 33390000.00, 2, 0.00),
(79, 135, 1, 33390000.00, 2, 0.00),
(80, 136, 1, 33390000.00, 2, 0.00),
(81, 137, 1, 33390000.00, 1, 0.00),
(82, 138, 1, 33390000.00, 1, 0.00),
(83, 139, 1, 33390000.00, 1, 0.00),
(84, 140, 1, 33390000.00, 1, 0.00),
(85, 141, 1, 33390000.00, 1, 0.00),
(86, 142, 1, 33390000.00, 1, 0.00),
(87, 143, 1, 33390000.00, 1, 0.00),
(88, 144, 1, 33390000.00, 1, 0.00),
(89, 145, 1, 33390000.00, 1, 0.00),
(90, 146, 1, 33390000.00, 1, 0.00),
(91, 147, 1, 33390000.00, 1, 0.00),
(92, 148, 1, 33390000.00, 1, 0.00),
(93, 149, 1, 33390000.00, 3, 0.00),
(94, 150, 1, 33390000.00, 2, 0.00),
(95, 151, 1, 33390000.00, 2, 0.00),
(96, 152, 1, 33390000.00, 1, 0.00),
(97, 153, 2, 27990000.00, 1, 0.00),
(98, 154, 2, 27990000.00, 1, 0.00),
(99, 155, 1, 33390000.00, 1, 0.00),
(100, 156, 1, 33390000.00, 1, 0.00),
(101, 157, 1, 33390000.00, 1, 0.00),
(102, 158, 1, 33390000.00, 1, 0.00),
(103, 159, 1, 33390000.00, 2, 0.00),
(104, 160, 1, 33390000.00, 1, 0.00),
(105, 161, 1, 33390000.00, 1, 0.00),
(106, 162, 1, 33390000.00, 1, 0.00),
(107, 163, 1, 33390000.00, 2, 0.00),
(108, 164, 1, 33390000.00, 2, 0.00),
(109, 165, 1, 33390000.00, 2, 0.00),
(110, 166, 1, 33390000.00, 2, 0.00),
(111, 167, 2, 27990000.00, 1, 0.00),
(112, 168, 2, 27990000.00, 1, 0.00),
(113, 168, 1, 33390000.00, 1, 0.00),
(114, 169, 2, 27990000.00, 1, 0.00),
(115, 169, 1, 33390000.00, 1, 0.00),
(116, 170, 1, 33390000.00, 1, 0.00),
(117, 171, 1, 33390000.00, 1, 0.00),
(118, 172, 1, 33390000.00, 1, 0.00),
(119, 172, 3, 25990000.00, 1, 0.00),
(120, 173, 1, 33390000.00, 1, 0.00),
(121, 173, 3, 25990000.00, 1, 0.00),
(122, 174, 1, 33390000.00, 1, 0.00),
(123, 175, 65, 37990000.00, 1, 0.00),
(124, 176, 1, 33390000.00, 10, 0.00),
(125, 177, 1, 33390000.00, 10, 0.00),
(126, 178, 1, 33390000.00, 10, 0.00),
(127, 179, 1, 33390000.00, 10, 0.00),
(128, 180, 1, 33390000.00, 10, 0.00),
(129, 181, 1, 33390000.00, 10, 0.00),
(130, 182, 3, 25990000.00, 1, 0.00),
(131, 183, 3, 25990000.00, 1, 0.00),
(132, 184, 1, 33390000.00, 1, 0.00),
(133, 185, 4, 10990000.00, 1, 0.00),
(134, 185, 3, 25990000.00, 1, 0.00),
(135, 186, 4, 10990000.00, 1, 0.00),
(136, 186, 3, 25990000.00, 1, 0.00),
(137, 187, 4, 10990000.00, 1, 0.00),
(138, 187, 3, 25990000.00, 1, 0.00),
(139, 188, 3, 25990000.00, 1, 0.00),
(140, 189, 3, 25990000.00, 1, 0.00),
(141, 190, 1, 33390000.00, 1, 0.00),
(142, 191, 1, 33390000.00, 1, 0.00),
(143, 192, 1, 33390000.00, 1, 0.00),
(144, 193, 41, 32990000.00, 1, 0.00),
(145, 193, 71, 8990000.00, 1, 0.00),
(146, 193, 46, 19990000.00, 1, 0.00),
(147, 193, 53, 1350000.00, 1, 0.00),
(148, 193, 51, 1090000.00, 1, 0.00),
(149, 194, 71, 8990000.00, 1, 0.00),
(150, 194, 56, 1899000.00, 1, 0.00),
(151, 194, 60, 890000.00, 1, 0.00),
(152, 194, 64, 2990000.00, 1, 0.00),
(153, 194, 5, 7990000.00, 1, 0.00),
(154, 194, 116, 300000000.00, 1, 0.00),
(155, 195, 116, 30000000.00, 1, 0.00),
(156, 196, 116, 30000000.00, 1, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('cod','momo','paypal') DEFAULT NULL,
  `amount` decimal(18,2) NOT NULL,
  `status` enum('pending','paid','failed') DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `status`, `transaction_id`, `created_at`, `updated_at`) VALUES
(13, 102, 'paypal', 133560000.00, 'paid', '6A158817D3405053W', '2025-09-20 08:32:06', '2025-09-20 08:32:06'),
(15, 110, 'paypal', 33390000.00, 'paid', '2BX17574NG1983445', '2025-09-20 08:57:24', '2025-09-20 08:57:24'),
(17, 114, 'paypal', 33390000.00, 'paid', '6DH473653S930610E', '2025-09-20 09:57:52', '2025-09-20 09:57:52'),
(18, 116, 'momo', 33390000.00, 'paid', 'MOMO_1758362517_116', '2025-09-20 10:01:57', '2025-09-20 10:01:57'),
(19, 117, 'paypal', 33390000.00, 'paid', '39K59123HC304090B', '2025-09-20 14:41:38', '2025-09-20 14:41:38'),
(20, 120, 'momo', 33390000.00, 'paid', 'MOMO_1758379773_120', '2025-09-20 14:49:33', '2025-09-20 14:49:33'),
(21, 129, 'momo', 133560000.00, 'paid', 'MOMO_1758384002_129', '2025-09-20 16:00:02', '2025-09-20 16:00:02'),
(22, 136, 'paypal', 66780000.00, 'paid', '2XW424408C217874W', '2025-09-21 14:48:57', '2025-09-21 14:48:57'),
(23, 143, 'paypal', 33390000.00, 'paid', '35P46430EH3224704', '2025-09-22 13:15:25', '2025-09-22 13:15:25'),
(24, 144, 'paypal', 33390000.00, 'paid', '5EP55380C73278829', '2025-09-22 16:50:22', '2025-09-22 16:50:22'),
(25, 146, 'momo', 33390000.00, 'paid', 'MOMO_1758565147_146', '2025-09-22 18:19:07', '2025-09-22 18:19:07'),
(26, 147, 'paypal', 33390000.00, 'paid', '00G12229K5861384G', '2025-09-22 18:19:41', '2025-09-22 18:19:41'),
(27, 149, 'momo', 100170000.00, 'paid', 'MOMO_1758565663_149', '2025-09-22 18:27:43', '2025-09-22 18:27:43'),
(28, 163, 'paypal', 66780000.00, 'paid', '3C914824BC865632M', '2025-09-24 10:24:59', '2025-09-24 10:24:59'),
(29, 165, 'paypal', 66780000.00, 'paid', '6Y828940XE770752H', '2025-09-24 10:27:05', '2025-09-24 10:27:05'),
(30, 166, 'paypal', 66780000.00, 'paid', '7TM79999N5639841X', '2025-09-24 10:31:36', '2025-09-24 10:31:36'),
(31, 169, 'paypal', 61380000.00, 'paid', '8LJ210166N194560U', '2025-09-24 11:38:27', '2025-09-24 11:38:27'),
(32, 173, 'paypal', 59380000.00, 'paid', '71A01733YM868332N', '2025-09-24 12:42:32', '2025-09-24 12:42:32'),
(33, 174, 'paypal', 33390000.00, 'paid', '78H10521AN082502W', '2025-09-24 14:03:59', '2025-09-24 14:03:59'),
(34, 175, 'paypal', 37990000.00, 'paid', '29W305284E866663E', '2025-09-24 15:52:08', '2025-09-24 15:52:08'),
(35, 182, 'paypal', 25990000.00, 'paid', '166495006F162781U', '2025-09-26 09:58:51', '2025-09-26 09:58:51'),
(36, 187, 'momo', 36980000.00, 'paid', 'MOMO_1758891737_187', '2025-09-26 13:02:17', '2025-09-26 13:02:17');

-- --------------------------------------------------------

--
-- Table structure for table `permission`
--

CREATE TABLE `permission` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `permission_group` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permission`
--

INSERT INTO `permission` (`id`, `permission_name`, `description`, `permission_group`, `created_at`) VALUES
(1, 'user.create', 'Tạo người dùng mới', 'user_management', '2025-09-09 15:06:45'),
(2, 'user.read', 'Xem thông tin người dùng', 'user_management', '2025-09-09 15:06:45'),
(3, 'user.update', 'Cập nhật thông tin người dùng', 'user_management', '2025-09-09 15:06:45'),
(4, 'user.delete', 'Xóa người dùng', 'user_management', '2025-09-09 15:06:45'),
(5, 'user.role.manage', 'Quản lý vai trò người dùng', 'user_management', '2025-09-09 15:06:45'),
(6, 'product.create', 'Tạo sản phẩm mới', 'product_management', '2025-09-09 15:06:45'),
(7, 'product.read', 'Xem thông tin sản phẩm', 'product_management', '2025-09-09 15:06:45'),
(8, 'product.update', 'Cập nhật thông tin sản phẩm', 'product_management', '2025-09-09 15:06:45'),
(9, 'product.delete', 'Xóa sản phẩm', 'product_management', '2025-09-09 15:06:45'),
(10, 'product.inventory.manage', 'Quản lý tồn kho sản phẩm', 'product_management', '2025-09-09 15:06:45'),
(11, 'category.create', 'Tạo danh mục mới', 'category_management', '2025-09-09 15:06:45'),
(12, 'category.read', 'Xem danh mục', 'category_management', '2025-09-09 15:06:45'),
(13, 'category.update', 'Cập nhật danh mục', 'category_management', '2025-09-09 15:06:45'),
(14, 'category.delete', 'Xóa danh mục', 'category_management', '2025-09-09 15:06:45'),
(15, 'order.create', 'Tạo đơn hàng', 'order_management', '2025-09-09 15:06:45'),
(16, 'order.read', 'Xem đơn hàng', 'order_management', '2025-09-09 15:06:45'),
(17, 'order.update', 'Cập nhật đơn hàng', 'order_management', '2025-09-09 15:06:45'),
(18, 'order.delete', 'Xóa đơn hàng', 'order_management', '2025-09-09 15:06:45'),
(19, 'order.status.update', 'Cập nhật trạng thái đơn hàng', 'order_management', '2025-09-09 15:06:45'),
(20, 'cart.manage', 'Quản lý giỏ hàng', 'cart_management', '2025-09-09 15:06:45'),
(21, 'advertisement.create', 'Tạo quảng cáo', 'advertisement_management', '2025-09-09 15:06:45'),
(22, 'advertisement.read', 'Xem quảng cáo', 'advertisement_management', '2025-09-09 15:06:45'),
(23, 'advertisement.update', 'Cập nhật quảng cáo', 'advertisement_management', '2025-09-09 15:06:45'),
(24, 'advertisement.delete', 'Xóa quảng cáo', 'advertisement_management', '2025-09-09 15:06:45'),
(25, 'sales.view', 'Xem báo cáo doanh thu', 'report_management', '2025-09-09 15:06:45'),
(26, 'dashboard.view', 'Xem dashboard thống kê', 'report_management', '2025-09-09 15:06:45');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_type` varchar(100) DEFAULT NULL,
  `price` decimal(18,2) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `category_id`, `product_name`, `product_type`, `price`, `thumbnail`, `description`, `stock_quantity`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Samsung Galaxy S25 Ultra', 'Samsung', 33390000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.8sXrMDzfUzGmgfBHlUTzDQHaHa%3Fpid%3DApi&f=1&ipt=8898ce839b7a3bc21062701bc21fe151a4b6c19c5c6b3f226d09d8ee7d36f588&ipo=images\"]', 'Chip : Snapdragon 8 Elite, RAM 12GB, Bộ nhớ 256GB, Pin 5000mAh, Camera 200MP', 120, 1, '2025-09-09 15:10:34', '2025-09-27 18:20:25'),
(2, 1, 'Samsung Galaxy S25+', 'Samsung', 27990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.dF1MVcmpQuZgqXwQ_b2bjQHaHa%3Fpid%3DApi&f=1&ipt=5a15a7e1f4d1c8065eb9f2ca1bf4e57754ec1aac1fdb9f820d221af3cb49b34c', 'Màn hình Dynamic AMOLED 6.7\", RAM 8GB, bộ nhớ 256GB, hỗ trợ 5G, camera chính 50MP', 135, 1, '2025-09-09 15:10:34', '2025-09-27 15:00:38'),
(3, 1, 'Samsung Galaxy Z Flip6', 'Samsung', 25990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.brUvttGgaQw_Eqm1hofFTgHaHa%3Fpid%3DApi&f=1&ipt=73a03cb765d57960a81450cc29c4d97e87168c570cc006a74beb2ac82131693f&ipo=images', 'Điện thoại màn hình gập 6.7\", RAM 8GB, bộ nhớ 256GB, camera kép 50MP', 145, 1, '2025-09-09 15:10:34', '2025-09-27 15:01:13'),
(4, 1, 'Samsung Galaxy A56 5G', 'Samsung', 10990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fstatic.digit.in%2FSamsung-Galaxy-A56-5G.jpg&f=1&nofb=1&ipt=8521bad01433e67d4103bc7f2f98c85a376d140dbfd62a7e49783c141143105a', 'Điện thoại tầm trung với màn hình 6.6\", Snapdragon 7s Gen 2, camera 50MP, pin 5000mAh', 138, 1, '2025-09-09 15:10:34', '2025-09-27 15:02:37'),
(5, 1, 'Samsung Galaxy A26 5G', 'Samsung', 7990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.0hs1AOiYixZXhEvXBLQ6YQHaHa%3Fpid%3DApi&f=1&ipt=cebea96ca910ae2111d9203549cfbfd2b49595d72601ac45875371b3d3afccd7&ipo=images', 'Màn hình 6.5\", chip Exynos 1330, RAM 6GB, bộ nhớ 128GB, camera kép 50MP, pin 5000mAh', 142, 1, '2025-09-09 15:10:34', '2025-09-27 15:03:01'),
(6, 1, 'iPhone 16 Pro Max', 'Apple', 34990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.5cV6Bxiey16ZNKucbF2r0QHaHa%3Fpid%3DApi&f=1&ipt=bd18c4eb1e5e01379b6df04c0de2757f8252280912845f1a6e350ff789d9da82&ipo=images\"]', 'iPhone cao cấp 2025: chip A18 Pro, màn hình 6.9\", RAM 8GB, camera 48MP, pin 5000mAh', 140, 1, '2025-09-09 15:10:34', '2025-09-27 13:43:12'),
(7, 1, 'iPhone 16 Pro', 'Apple', 30990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.UjRL9K4hrXt4zHzQVG0Q4gHaHa%3Fpid%3DApi&f=1&ipt=6b8431297bba1a6d3c058318aa2595b5b542391ea218b2978a1d0824c0bf5a6c&ipo=images', 'Màn hình 6.3\", chip A18 Pro, RAM 8GB, bộ nhớ 256GB, hỗ trợ 5G', 133, 1, '2025-09-09 15:10:34', '2025-09-27 10:38:16'),
(8, 1, 'iPhone 16 Plus', 'Apple', 26990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.kiGkC1mRT0mXLso5irnYhQHaHa%3Fpid%3DApi&f=1&ipt=b156110262dc0b7b4684aa73a105184c50d45e9edd15ec3cad6a480e663e7cef&ipo=images', 'Chip A17 Bionic, RAM 6GB, camera kép 48MP, pin 4900mAh', 115, 1, '2025-09-09 15:10:34', '2025-09-27 10:38:38'),
(9, 1, 'iPhone 15 Pro Max', 'Apple', 28990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.tz9uisfpGy-dUhoaEXfnYAHaHa%3Fpid%3DApi&f=1&ipt=3f22a25d95b9e302392aa725ade738ec177fb58c0960532864685c8ef0b60ecb&ipo=images', 'Màn hình 6.7\", RAM 8GB, bộ nhớ 256GB, camera tele 5x zoom', 148, 0, '2025-09-09 15:10:34', '2025-09-29 15:36:25'),
(10, 1, 'iPhone 15', 'Apple', 18990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.8g3BYnJflFVP5PsEHRGNawHaHa%3Fpid%3DApi&f=1&ipt=ae2defc2d96e4c4aeeb6432513fb87e7a422792abd7723dc1006fd3678e05532&ipo=images', 'Màn hình 6.1\", RAM 6GB, bộ nhớ 128GB, hỗ trợ 5G, camera kép 48MP', 125, 1, '2025-09-09 15:10:34', '2025-09-27 10:37:54'),
(11, 1, 'Xiaomi 15 Ultra', 'Xiaomi', 24990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.3xzS6kkGkw5vKvTghRowawHaFA%3Fpid%3DApi&f=1&ipt=b9a12d0036f12e2046355a235e61258175cc0ee3777d50b1c514280add0e446b&ipo=images', 'Màn hình AMOLED 6.7\", RAM 16GB, pin 5000mAh, sạc nhanh 120W, camera 200MP', 121, 1, '2025-09-09 15:10:34', '2025-09-27 10:49:11'),
(12, 1, 'Xiaomi 15 Pro', 'Xiaomi', 20990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.vWKKMv8yoa3pTB_d29bAcQHaE0%3Fpid%3DApi&f=1&ipt=5ed8972bf6d489d3d5a7f575c92e4b484a9b1a0d15af1b3f3bef299ce417bc28&ipo=images', 'Flagship Xiaomi với màn hình 6.73\", RAM 12GB, pin 4880mAh, sạc nhanh 120W', 137, 1, '2025-09-09 15:10:34', '2025-09-27 10:39:16'),
(13, 1, 'Xiaomi 14T Pro', 'Xiaomi', 15990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.Qf4JqPZQkEhzGI_3jzk3rgHaHa%3Fpid%3DApi&f=1&ipt=69366681761933130981254113d64781658612817b702d0514a2171180c3f330&ipo=images', 'Màn hình AMOLED 6.7\", RAM 12GB, pin 5000mAh, sạc nhanh 120W', 149, 1, '2025-09-09 15:10:34', '2025-09-27 10:49:30'),
(14, 1, 'Xiaomi Redmi Note 14 Pro+', 'Xiaomi', 9990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.9DGS5xRh02OZ_QjqPxCL2gHaHa%3Fpid%3DApi&f=1&ipt=824d21c81b0a58cb73ce588b38e4b471f27bd7d231f53ddcd244247096e18d0b&ipo=images', 'Màn hình AMOLED 6.67\", Dimensity 8200 Ultra, RAM 8GB, pin 5000mAh', 118, 1, '2025-09-09 15:10:34', '2025-09-27 10:48:49'),
(15, 1, 'Xiaomi Redmi Note 14', 'Xiaomi', 6990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.4VssxC7IH1t5p_qVoFERNgHaHa%3Fpid%3DApi&f=1&ipt=e98682489f1758ba4b63c4dc99083cd575f2a39a9f9dd265b198f5e389eaf018&ipo=images', 'Màn hình AMOLED 6.6\", Snapdragon 7s Gen 2, RAM 6GB, camera 100MP', 132, 1, '2025-09-09 15:10:34', '2025-09-27 10:49:50'),
(16, 1, 'Realme GT 6 Pro', 'Realme', 18990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.5vrARveOex9Hn766LCZyzwHaHa%3Fpid%3DApi&f=1&ipt=4c40b92df9ecb69d1e8acb667f70c04f8716705b59cfa5297e3011b9783c5e5c&ipo=images', 'Flagship Realme với màn hình 6.78\", RAM 16GB, pin 5500mAh, sạc nhanh 120W', 127, 1, '2025-09-09 15:10:34', '2025-09-27 10:48:24'),
(17, 1, 'Realme GT Neo 6', 'Realme', 13990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse2.mm.bing.net%2Fth%2Fid%2FOIP.XWeRqIIopVj_F_yCJZgEzwHaHa%3Fpid%3DApi&f=1&ipt=2996499fbf525ca23dc0cf51b206690c650a1c2bf455353ce236f8e0b4bc40ce&ipo=images', 'Màn hình AMOLED 6.7\", RAM 12GB, pin 5000mAh, camera 50MP', 146, 1, '2025-09-09 15:10:34', '2025-09-27 10:50:35'),
(18, 1, 'Realme 13 Pro+', 'Realme', 10990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.S2CUw_p-6oMaBJl8nTzRcAHaHa%3Fpid%3DApi&f=1&ipt=8a10bf72f7fd298536a73ab018e948887d313cde090bb8fa5f22504a6de18290&ipo=images', 'Màn hình AMOLED 6.7\", Snapdragon 7s Gen 3, RAM 8GB, pin 5000mAh', 141, 1, '2025-09-09 15:10:34', '2025-09-27 10:47:55'),
(19, 1, 'Realme 12+', 'Realme', 7990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.rQi_g_ciBHmO2Y08M2SMVAHaHa%3Fpid%3DApi&f=1&ipt=6487ed2b407d92df1dfa37a39959f792db2fca0575b0a6262992f3d57fa90eec&ipo=images\"]', 'Chip Dimensity 7050, RAM 8GB, bộ nhớ 256GB, camera 50MP OIS', 119, 1, '2025-09-09 15:10:34', '2025-09-27 10:41:09'),
(20, 1, 'Realme C67', 'Realme', 5990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.De4Nf8MZcI5cXusnzHEpMQHaHa%3Fpid%3DApi&f=1&ipt=36d8f8acad451b6967a79a7dc0b22213af489d8061789e6fd8abb69cc44dae3b&ipo=images', 'Màn hình AMOLED 6.6\", RAM 6GB, camera 108MP, pin 5000mAh, sạc 33W', 134, 1, '2025-09-09 15:10:34', '2025-09-27 10:40:25'),
(21, 3, 'Samsung Galaxy Watch 8', 'Samsung', 10449000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.1saLIkoOf_pj6bG-RzKlegHaHa%3Fpid%3DApi&f=1&ipt=43e0ebbb5d3a5786970b40805bc5c923f6eaf3b05133e1d05ac39b629ae19dfc&ipo=images', 'Galaxy Watch 8: màn AMOLED 1.6″, lưu trữ 16 GB, pin 410mAh, GPS, chống nước 5ATM', 129, 1, '2025-09-09 15:10:59', '2025-09-27 15:08:07'),
(22, 3, 'Samsung Galaxy Watch 8 Classic', 'Samsung', 14600000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fwww.techspot.com%2Fimages%2Fproducts%2F2023%2Fwearables%2Forg%2F2023-08-10-product.jpg&f=1&nofb=1&ipt=f867288b092060579b99d4d61db7a526800ee6b7d064e60e9e531fece596db7d\"]', 'Màn 1.5″ AMOLED, viền xoay bằng thép không gỉ, pin 300mAh, LTE tùy chọn', 149, 1, '2025-09-09 15:10:59', '2025-09-27 14:45:43'),
(23, 3, 'Samsung Galaxy Watch Ultra', 'Samsung', 15307000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.fSJZtBIl310Vcubi6VfCPwHaEK%3Fpid%3DApi&f=1&ipt=d77457564667b61c0a80e1c4a70ed3061cf2c665a292c44cb30dfac9467dac9d&ipo=images', 'Màn 1.9″ Super AMOLED, khung titanium, pin lớn 599mAh, GPS độ chính xác cao', 107, 1, '2025-09-09 15:10:59', '2025-09-27 11:32:03'),
(24, 3, 'Samsung Galaxy Watch 7', 'Samsung', 6133000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.7qqAE8L_86rkkC_lRypjtgHaHa%3Fpid%3DApi&f=1&ipt=e2f4228134694495172ef3acc5f1db79f5ff7f8ed15e9647010dd73d831ecb09&ipo=images', 'Thiết kế mỏng nhẹ, GPS, theo dõi giấc ngủ, pin dùng cả ngày', 143, 1, '2025-09-09 15:10:59', '2025-09-27 11:32:32'),
(25, 3, 'Samsung Galaxy Watch FE', 'Samsung', 4717000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse2.mm.bing.net%2Fth%2Fid%2FOIP.dAqbjCLHfX-pQFz-Q603RQHaHa%3Fpid%3DApi&f=1&ipt=a4275e1fd82146d1f27c1883ddeaab05e046a97dc4b92222d6f58a6f1c7db36d&ipo=images', 'Màn AMOLED, GPS, thiết kế đơn giản, pin 300mAh, nhẹ và giá mềm', 140, 1, '2025-09-09 15:10:59', '2025-09-27 11:32:52'),
(26, 3, 'Apple Watch Series 9 (Nhôm)', 'Apple', 10499000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.D9xo-9wqGzDM40vC-HU8IAHaE8%3Fpid%3DApi&f=1&ipt=df16a047b90beee88488c25ec489117aeb1368bc852c898d4d9d0664066a628b&ipo=images', 'Siri chạy trên thiết bị, chip S9 SiP, màn Retina LTPO OLED luôn bật, chống nước 50 m', 120, 1, '2025-09-09 15:10:59', '2025-09-27 11:33:08'),
(27, 3, 'Apple Watch Series 9 (Thép)', 'Apple', 18990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse2.mm.bing.net%2Fth%2Fid%2FOIP.Zhd0iuWgW-Ii42OLXumREAHaHa%3Fpid%3DApi&f=1&ipt=ce7b230aab3846569b6505e5f41d191f9b9d725b701fa175d0736831cd018626&ipo=images', 'Vỏ thép không gỉ, màn sáng 2000 nits, nút Action, phần mềm watchOS 10', 131, 1, '2025-09-09 15:10:59', '2025-09-27 11:33:32'),
(28, 3, 'Apple Watch Series 10', 'Apple', 10799000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.ACqs-9EFhCn6Re3oKvnCVwHaHa%3Fpid%3DApi&f=1&ipt=8d61d52bacc8d397d9a9a88b62f4c172f85630dcb892b25f463f1aca31ab7a6b&ipo=images', 'Phiên bản GPS + Cellular, cảm biến ECG, màn Retina OLED luôn bật, Titan bền', 143, 1, '2025-09-09 15:10:59', '2025-09-27 11:33:52'),
(29, 3, 'Apple Watch Ultra 2', 'Apple', 18990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fwww.apple.com%2Fnewsroom%2Fimages%2F2023%2F09%2Fapple-unveils-apple-watch-ultra-2%2Farticle%2FApple-Watch-Ultra-2-Ocean-Band-Orange-230912_inline.jpg.large_2x.jpg&f=1&nofb=1&ipt=8045d6ace9bf577b9', 'Pin 36 giờ, tính năng thể thao chuyên sâu, nút Tác Vụ tùy chỉnh, chống nước 100 m', 123, 1, '2025-09-09 15:10:59', '2025-09-27 11:38:30'),
(30, 3, 'Apple Watch SE (thế hệ 2)', 'Apple', 6990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.2JyI_wgd3x06IMuiSTD3IgHaHa%3Fpid%3DApi&f=1&ipt=9d378497584c116c413353bdec7f0f7ebd7b4f77cdea75dd80dd5908157f932c&ipo=images', 'Chip S8 giống Watch 8, GPS, theo dõi sức khỏe, giá dễ chịu', 137, 1, '2025-09-09 15:10:59', '2025-09-27 11:39:45'),
(31, 3, 'Xiaomi Redmi Watch 5 Lite', 'Xiaomi', 1270000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.AUb0QSsdiJeDwhRtMsvOxgHaHa%3Fpid%3DApi&f=1&ipt=d358c0517c40c7f14e9320584291327a61d617b42f4e0ecfc4b68a45f1bc12ab&ipo=images', 'Màn 1.55″ TFT, pin dùng 10 ngày, theo dõi nhịp tim & giấc ngủ', 114, 1, '2025-09-09 15:10:59', '2025-09-27 11:40:06'),
(32, 3, 'Xiaomi Redmi Watch 5 Active', 'Xiaomi', 2830000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse2.mm.bing.net%2Fth%2Fid%2FOIP.3aQH3UGWzMB7DHxsNhyH_AHaE0%3Fpid%3DApi&f=1&ipt=c2808de19ff58f7ae130e4bb5adf15ae4e283d8fbd0ede40766764b6bfd78d46&ipo=images', 'Màn AMOLED 1.91″, GPS tích hợp, vỏ nhẹ, pin dùng nhiều ngày', 111, 1, '2025-09-09 15:10:59', '2025-09-27 17:35:46'),
(33, 3, 'Xiaomi Watch S4 Sport', 'Xiaomi', 6455000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.EiTNzG49-3v_K60HAuWO7gHaHa%3Fpid%3DApi&f=1&ipt=95ef4544e899698de5bf72a8980f04718b33697af32ea5830832dd77344aa1fd&ipo=images', 'Màn 1.96″ AMOLED lớn, theo dõi nhịp tim, pin 480mAh, GPS+BT', 112, 1, '2025-09-09 15:10:59', '2025-09-27 11:41:03'),
(34, 3, 'Xiaomi Watch 2 Pro', 'Xiaomi', 6722000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.j_69VziYKAJRs1ycMqp2gAHaHa%3Fpid%3DApi&f=1&ipt=9539d9fe6b56b98e8099932082f7d2b8692020b15113fdb6df26096ca8660c7e&ipo=images', 'Always-on display, Wi-Fi, GPS chính xác, pin 500mAh', 130, 1, '2025-09-09 15:10:59', '2025-09-27 11:40:43'),
(35, 3, 'Xiaomi Watch S1 Active', 'Xiaomi', 4953000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.xizOMLVwoTBQQPoK-2cAiQHaHN%3Fpid%3DApi&f=1&ipt=4cc69161dd91cce18ecfc3de551b67df4df150713903a3cbc9ae8d026269d4b3&ipo=images', 'Màn AMOLED 1.43″, pin 420mAh, vỏ nhẹ, nhiều mặt đồng hồ', 113, 1, '2025-09-09 15:10:59', '2025-09-27 11:41:23'),
(36, 3, 'Realme Watch 3 Pro', 'Realme', 1890000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse2.mm.bing.net%2Fth%2Fid%2FOIP.Gl8_49vZu2WrSCMTcrBrowAAAA%3Fpid%3DApi&f=1&ipt=4d87ca42ab5a18c700ceca3c5830b92d631d96cbf3af41a9301411a1c4fcb265&ipo=images', 'GPS kép, màn 1.78″, pin dùng 14 ngày, theo dõi thể thao', 126, 1, '2025-09-09 15:10:59', '2025-09-27 11:42:39'),
(37, 3, 'Realme Band 3', 'Realme', 1200000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.3u2Q0VnsiSjDCaVJxHVjUwHaHa%3Fpid%3DApi&f=1&ipt=89472c9a1a5af87a917c67b829161fde4e529617300f0afd9eeaeefbeadc7e00&ipo=images\"]', 'Vòng tay nhỏ gọn, theo dõi nhịp tim, SPO2, pin 12 ngày', 140, 1, '2025-09-09 15:10:59', '2025-09-27 17:36:01'),
(38, 3, 'Realme Watch 3', 'Realme', 1690000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.FCB36jtloa5t4IzqjO2dYAHaHa%3Fpid%3DApi&f=1&ipt=bff9fbe385cc54b901a4cf966c9b349320eb21dea4b87832ae62ee27843b9df5&ipo=images', 'Màn 1.8″ IPS, theo dõi sức khỏe, pin 288mAh dùng 10 ngày', 120, 1, '2025-09-09 15:10:59', '2025-09-27 11:43:28'),
(39, 3, 'Realme Watch S100', 'Realme', 2190000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.k_RbqEyW_OuZoqWv-NDWqgHaHa%3Fpid%3DApi&f=1&ipt=fc2597a5061cd792a829c0afece3437090ccc76aa13017772cbd52e71ac8b7ef&ipo=images', 'Khung thép, màn OLED, pin dùng tới 10 ngày, nhiều chế độ tập luyện', 134, 1, '2025-09-09 15:10:59', '2025-09-27 11:44:07'),
(40, 3, 'Realme Watch 2 Pro', 'Realme', 2990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.3u2Q0VnsiSjDCaVJxHVjUwHaHa%3Fpid%3DApi&f=1&ipt=89472c9a1a5af87a917c67b829161fde4e529617300f0afd9eeaeefbeadc7e00&ipo=images', 'GPS hai băng tần, màn 1.75″, pin 390mAh, theo dõi nâng cao', 106, 1, '2025-09-09 15:10:59', '2025-09-27 11:44:28'),
(41, 2, 'MacBook Air M2 2023', 'Laptop', 32990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.G6u4WLh-CWnEVkjIIw7_DwHaFj%3Fpid%3DApi&f=1&ipt=f90501b1e43ce45a4d8201c3f33e7bf9cbeab7759d385e9b36af06dc70760aed&ipo=images', 'Laptop siêu mỏng nhẹ, chip Apple M2.', 10, 1, '2025-09-23 08:22:20', '2025-09-27 09:40:09'),
(42, 2, 'MacBook Pro 14 M3', 'Laptop', 49990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse2.mm.bing.net%2Fth%2Fid%2FOIP.XKP7SbUO8Dm5bTl1Xo3sOwHaET%3Fpid%3DApi&f=1&ipt=7e3e70eb7202f514019a04055dea1beb6a3a59b722bf76526c49bb42d0b2a22a&ipo=images', 'Hiệu năng mạnh mẽ với chip Apple M3.', 8, 1, '2025-09-23 08:22:20', '2025-09-27 09:41:02'),
(43, 2, 'Dell XPS 13 Plus', 'Laptop', 38990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse2.mm.bing.net%2Fth%2Fid%2FOIF.z9PxoUQetx5GVBmP8QBirg%3Fpid%3DApi&f=1&ipt=554f52d361184aa8537abac0625bf13b9b158d0ec833c8887d55c6cd5c6aac5b&ipo=images', 'Ultrabook cao cấp, màn OLED.', 12, 1, '2025-09-23 08:22:20', '2025-09-27 09:41:53'),
(44, 2, 'Asus ROG Strix G16', 'Laptop', 44990000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse2.mm.bing.net%2Fth%2Fid%2FOIP.vqUi86yjCuS-cH7YieO7uwHaHa%3Fpid%3DApi&f=1&ipt=6fdbbbe27912d80efb71111f4a68feb31bf3d1dc535132d54c8a76bb7add7579&ipo=images', 'Laptop gaming, RTX 4070, màn 240Hz.', 7, 1, '2025-09-23 08:22:20', '2025-09-27 09:42:12'),
(45, 5, 'iPad Pro 12.9 2022', 'Apple', 28990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.d0nYFunsrtoas63ZJQWf2QHaHa%3Fpid%3DApi&f=1&ipt=bed66537329a58f9e3856bdfc5b7f32b86f502568eac2df1952f52d4f36d4245&ipo=images\"]', 'Màn hình Liquid Retina XDR, chip M2.', 10, 1, '2025-09-23 08:23:05', '2025-09-29 07:54:15'),
(46, 5, 'Samsung Galaxy Tab S9', 'Samsung', 19990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse2.mm.bing.net%2Fth%2Fid%2FOIP.vuwNJYYfHU1L5C32tMiSUgHaFB%3Fpid%3DApi&f=1&ipt=7c7ddad4438698bfd012785e749f614f2eed130a11b9eb6823cfc29d1928ce40&ipo=images\"]', 'Máy tính bảng AMOLED 120Hz.', 12, 1, '2025-09-23 08:23:05', '2025-09-29 07:54:20'),
(47, 5, 'Xiaomi Pad 6 Pro', 'Xiaomi', 999990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.smMVOhDcv-beQr-etjmrxwHaHa%3Fpid%3DApi&f=1&ipt=3d97d603cf74d82ec91e578f41efcf31aece5c7dfd6864d037ceab67f25d4dd7&ipo=images\"]', 'Máy tính bảng cấu hình mạnh.', 20, 1, '2025-09-23 08:23:05', '2025-09-29 07:54:25'),
(48, 5, 'Lenovo Tab P11 Pro', 'Lenovo', 11990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.P614opziuIzgug1GGvCWpwHaE8%3Fpid%3DApi&f=1&ipt=09b0e838b44920ce6dea0a051ffa22b853c9b14574c81c6a129cef836ebe53e2&ipo=images\"]', 'Màn OLED, pin 8600mAh.', 15, 1, '2025-09-23 08:23:05', '2025-09-29 07:56:05'),
(49, 6, 'Ốp lưng iPhone 15', 'Phụ kiện điện thoại', 1299000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse2.mm.bing.net%2Fth%2Fid%2FOIP.KCBzHFpGuMAd4y7GzYmUZAHaHa%3Fpid%3DApi&f=1&ipt=36ff820bf541f8b0a2980838fe617032fef2c22dfe900b80fdcd855439725b19&ipo=images\"]', 'Ốp lưng chống sốc cho iPhone 15.', 20, 1, '2025-09-23 08:23:05', '2025-09-29 15:27:07'),
(50, 6, 'Sạc nhanh Anker 30W', 'Anker', 1690000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.AxdfUIksZzfY27envRPD2gHaHa%3Fpid%3DApi&f=1&ipt=2bbe7b21f912e99bc7172bb7e79578738eb85759a5531582a02e94f787cabd28&ipo=images\"]', 'Củ sạc nhanh Anker PowerPort, công suất 30W.', 40, 1, '2025-09-23 08:23:05', '2025-09-29 07:56:23'),
(51, 6, 'Pin dự phòng Baseus 20000mAh', 'Baseus', 1090000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.fCKLH3qglOkobq1hVqg4rwHaHa%3Fpid%3DApi&f=1&ipt=280b562af3db15f155ad6a62a96301a8d27a5d4bb68c2d1dd4feca8435696d19&ipo=images\"]', 'Sạc dự phòng dung lượng lớn, hỗ trợ sạc nhanh.', 35, 1, '2025-09-23 08:23:05', '2025-09-29 07:58:54'),
(52, 7, 'Tai nghe có dây Xiaomi', 'Phụ kiện điện thoại', 250000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP._bfyzanzGqpRjed8GSu21QHaHa%3Fpid%3DApi&f=1&ipt=b924a1d104f10f5a68769ecfc6166344a8a0773ad2183fc104a40b9f6d6e836d&ipo=images\"]', 'Tai nghe nhét tai giá rẻ, âm thanh tốt.', 60, 0, '2025-09-23 08:23:05', '2025-09-27 14:44:28'),
(53, 6, 'Cáp sạc Type-C to Lightning', 'Apple', 1350000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.WTq_JRChcg0NVc2Mm-GNTwHaHa%3Fpid%3DApi&f=1&ipt=7a2270a1d6d39fd7d4ce5ddcd3a8a64e08a29076e20e8020f19cce608c71db29&ipo=images\"]', 'Cáp sạc hỗ trợ PD cho iPhone.', 70, 1, '2025-09-23 08:23:05', '2025-09-29 07:59:01'),
(54, 6, 'Kính cường lực iPhone 15', 'Phụ kiện điện thoại', 199000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.54PsncZfDZIxgM7D6FtpoQHaHa%3Fpid%3DApi&f=1&ipt=6b1ca82e5dbc0f050a951f7e393517ea74db7ff7fe436347be0cddcee5dc7632&ipo=images\"]', 'Kính cường lực chống xước, chống vỡ.', 80, 1, '2025-09-23 08:23:05', '2025-09-29 15:25:42'),
(55, 4, 'Chuột Logitech MX Master 3S', 'Logitech', 2499000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.xlW1fySrpIspqUI0tpGsswHaHa%3Fpid%3DApi&f=1&ipt=1d65bcedba84a001a05f3ceaff17e6b427d59ac3f7658bb79166796813285d82&ipo=images\"]', 'Chuột không dây cao cấp, pin sạc lâu.', 20, 1, '2025-09-23 08:23:05', '2025-09-29 07:59:12'),
(56, 4, 'Bàn phím cơ Keychron K2', 'Keychron', 1899000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.BKf7H4DWW9BC10pUilZZ2AHaHa%3Fpid%3DApi&f=1&ipt=f84116fabf9ead2c9ca260a7d622b02ecedd6fb22593d051b3bab012268fc83f&ipo=images\"]', 'Bàn phím cơ bluetooth, layout 75%.', 25, 1, '2025-09-23 08:23:05', '2025-09-29 08:00:00'),
(57, 5, 'Balo Asus TUF Gaming', 'Phụ kiện laptop', 1290000.00, 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.gHObHIro4LbBEIbO4_kA3wHaHa%3Fpid%3DApi&f=1&ipt=d0918734064be899ac9d116cb9c79fc5f82e9c3379808e8c34f218edc2affe33&ipo=images', 'Balo chống sốc, chuyên dụng cho gaming laptop.', 15, 0, '2025-09-23 08:23:05', '2025-09-27 11:01:13'),
(58, 4, 'Đế tản nhiệt Cooler Master', 'Keychron', 990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse2.mm.bing.net%2Fth%2Fid%2FOIP.eH3YRkL3KWwn56NxIgvk0QHaG2%3Fpid%3DApi&f=1&ipt=124ede2aa07b43ab4009d7f91e986b82a1b8e1bb94c0c6388617f68eb733a4e9&ipo=images\"]', 'Đế tản nhiệt với quạt lớn, hiệu quả cao.', 18, 1, '2025-09-23 08:23:05', '2025-09-29 08:00:10'),
(59, 4, 'Ổ cứng di động Samsung T7 1TB', 'Samsung', 3299000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.rCy5jcmdsT_oVzD4UYVyjgHaHa%3Fpid%3DApi&f=1&ipt=7505c27bf062fcd6bc78fcfd776bc6d05327a1254f9c276293891f61a7e8d27a&ipo=images\"]', 'SSD di động tốc độ cao, hỗ trợ USB 3.2.', 12, 1, '2025-09-23 08:23:05', '2025-09-29 08:02:50'),
(60, 4, 'Hub chuyển đổi Ugreen 7-in-1', 'Ugreen', 1100000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.jRx4v2RotKMrxSr7hS7EMwHaHa%3Fpid%3DApi&f=1&ipt=1ed5350c25dbf819c1c941a724bcb8a0f7445369c96a9cb700deaa5bab8609a1&ipo=images\"]', 'Hub USB-C đa năng cho Macbook/Laptop.', 30, 1, '2025-09-23 08:23:05', '2025-09-29 08:13:22'),
(61, 7, 'Sony WH-1000XM5', 'Sony', 9990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.j2XOxqkeFjUMFnCTYe4mcwHaHa%3Fpid%3DApi&f=1&ipt=68b4d06e73a0fbb2a2400d32cd17adeb277c0daea1e4d764b746d9f1ff8662ec&ipo=images\"]', 'Tai nghe chống ồn hàng đầu, pin 30h.', 10, 1, '2025-09-23 08:23:05', '2025-09-29 08:00:26'),
(62, 7, 'Apple AirPods Pro 2', 'Apple', 6490000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.JfiZrRQPI2uxXG2zi0nRCgHaHa%3Fpid%3DApi&f=1&ipt=49355439940fec624e4de760ed0b2426d1c4d28d6c2f154d8437f44a61b8a67b&ipo=images\"]', 'Tai nghe true wireless cao cấp của Apple.', 25, 1, '2025-09-23 08:23:05', '2025-09-29 08:01:06'),
(63, 7, 'Loa JBL Charge 5', 'JBL', 3990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.D-JzpkvWrxWEVWIbpm87rAHaHa%3Fpid%3DApi&f=1&ipt=ab27ed66c7b1756581f90cea74be6cefe510159278a3241c21420f3940484649&ipo=images\"]', 'Loa bluetooth chống nước, pin 20h.', 20, 1, '2025-09-23 08:23:05', '2025-09-29 08:01:12'),
(64, 7, 'Tai nghe Razer BlackShark V2', 'Razer', 2990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.EN4A8WehMYmz5gUd6PX4cAHaE8%3Fpid%3DApi&f=1&ipt=6f1efc2888a6ad19bca6fe75c2a2fbf3983eed6228e6c276cbae406b89f3a721&ipo=images\"]', 'Tai nghe gaming với micro siêu nhạy.', 15, 1, '2025-09-23 08:23:05', '2025-09-29 08:01:22'),
(65, 2, 'HP Spectre x360 14', 'HP', 37990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.JJVbahX59pr0Nxkn9FVHAQHaEK%3Fpid%3DApi&f=1&ipt=aebe704489f5e289da622189e7c7205f633315a24de328f68b4a7df1388e8d04&ipo=images\"]', 'Chip : Intel AI Boost, Card đồ họa : Intel Arc Graphics, RAM : 32GB, Loại RAM : LPDDR5/X 7467MHz, Số khe ram : Onboard, Ổ cứng : 1TB SSD, Kích thước màn hình : 14 inch, Pin : 4 Cell Int (68Wh) 65W (USB-C), Độ phân giải màn hình : 2880 x 1800 pixels, CPU : Intel Core Ultra 7 155H (16 lõi),', 9, 1, '2025-09-23 08:23:20', '2025-09-29 15:12:22'),
(66, 2, 'Lenovo ThinkPad X1 Carbon Gen 11', 'Lenovo', 42990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.Ll3WwhJpIkWVRm7i8gSsQQHaFL%3Fpid%3DApi&f=1&ipt=2060f51a5428a15b33bb13725259e9dbf27f716a8e6e77f663048f267211aa47&ipo=images\"]', 'Doanh nhân cao cấp, siêu bền, pin lâu.', 11, 1, '2025-09-23 08:23:20', '2025-09-29 07:53:41'),
(67, 5, 'iPad Air 5 2022', 'Apple', 16990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.69etAAeWPk63SwjrMrLmrQHaHa%3Fpid%3DApi&f=1&ipt=dc4d078cbb251001ddb82cc82524e00e4c987d36cfa2cb13c5c05acb18b6c79f&ipo=images\"]', 'Kích thước màn hình : 10.9 inch, Chip : Apple M1 8 nhân, RAM : 8 GB, Bộ nhớ trong	: 256 GB, Pin : 7587 mAh, Hệ điều hành : iPadOS 15, Công nghệ màn hình : Liquid Retina, Camera sau : 12MP, Camera trước : 12MP, Độ phân giải màn hình	 : 2360 x 1640 pixel', 14, 1, '2025-09-23 08:23:20', '2025-09-29 15:04:27'),
(68, 5, 'Huawei MatePad 11 2023', 'Huawei', 12990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.PrJg6Q8uT1jyT9nwrtMWTgHaHa%3Fpid%3DApi&f=1&ipt=e7a2fffb39f3b1c76a47c6c2e6382887444ee77f3cd415f7bb1a8dc29813f5ae&ipo=images\"]', 'Màn hình 120Hz, hỗ trợ bút M-Pencil.', 16, 1, '2025-09-23 08:23:20', '2025-09-29 08:01:39'),
(69, 7, 'Loa Bose SoundLink Flex', 'Bose', 4990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse4.mm.bing.net%2Fth%2Fid%2FOIP.3X3VSYZDGNa4J3KzpNymvwHaFS%3Fpid%3DApi&f=1&ipt=ce6143405cd22dab19accccb42d53fc7c2598082ea1d3197b9ea29716aff08c7&ipo=images\"]', 'Loa nhỏ gọn, âm bass mạnh.', 12, 1, '2025-09-23 08:23:20', '2025-09-29 15:36:12'),
(70, 7, 'Tai nghe Sennheiser Momentum 4 Wireless', 'Sennheiser', 8990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.9gr1c9BxH8HoNfcQuy0PqgHaF7%3Fpid%3DApi&f=1&ipt=6e0668fd335a731a6ad367f7a146d8a637ca562da34cd1eff9e9275782ec79be&ipo=images\"]', 'Tai nghe chống ồn, pin 60h.', 8, 1, '2025-09-23 08:23:20', '2025-09-29 07:54:06'),
(71, 3, 'Garmin Forerunner 265', 'Garmin', 8990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse3.mm.bing.net%2Fth%2Fid%2FOIP.2YbRQHlfzTQbDoIzEJ_CggHaHa%3Fpid%3DApi&f=1&ipt=4f8031f812e60eb5c193e925d718ce340a1f61946229b0970ba20881766f6a28&ipo=images\"]', 'Đồng hồ thể thao GPS chuyên nghiệp.', 10, 1, '2025-09-23 08:23:21', '2025-09-29 07:51:40'),
(72, 3, 'Amazfit GTR 4', 'Amazfit', 4990000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftechcart.com.au%2Fwp-content%2Fuploads%2F2023%2F04%2F73007-Amazfit-GTR-4-Global-A2166-Brown.png&f=1&nofb=1&ipt=fbeb5ad152a37e76d2e14b323460749258e20a9fcf19fb0c6e33de14623bdfd9\"]', 'Pin 14 ngày, theo dõi sức khỏe toàn diện.', 20, 1, '2025-09-23 08:23:21', '2025-09-29 07:51:57'),
(73, 6, 'Pin dự phòng Aukey Magfusion Slim 10000mAh 20W PB-MS04', 'Aukey', 1590000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP._TPPNjVqvxW_bT8E2CY8nQAAAA%3Fpid%3DApi&f=1&ipt=9e20985159383cc65faec04112aa94d1b4a0c79f00c203981ec13a8df6f23fb3&ipo=images\"]', 'Dung lượng pin 10000mAh, Công suất sạc 20W, Cổng sạc ra	\n1 x USB-C 1 x Magsafe, Cổng sạc vào, 1 x USB-C, Lõi pin Li-Polymer, Kích thước 104.3 x 67.8 x 19.2 mm, Trọng lượng 208.8 g', 30, 1, '2025-09-27 13:48:36', '2025-09-27 14:43:20'),
(116, 1, 'iPhone 14 Pro', 'Apple', 30000000.00, '[\"https://didongviet.vn/_next/image?url=https%3A%2F%2Fcdn-v2.didongviet.vn%2Ffiles%2Fmedia%2Fcatalog%2Fproduct%2Fi%2Fp%2Fiphone-14-plus-128gb-didongviet_1.jpg&w=640&q=75\"]', 'Chip :  Apple A15 Bionic, RAM 6 GB, Bộ nhớ 256 GB, Màn hình : 6.7 inch, Pin 4325mAh', 20, 1, '2025-09-27 18:05:56', '2025-09-28 11:41:36'),
(117, 3, 'Garmin Forerunner 265', 'Samsung', 30000000.00, '[\"https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%2Fid%2FOIP.69etAAeWPk63SwjrMrLmrQHaHa%3Fpid%3DApi&f=1&ipt=dc4d078cbb251001ddb82cc82524e00e4c987d36cfa2cb13c5c05acb18b6c79f&ipo=images\"]', 'đồng hồ thông minh, Pin : 1000mAh', 20, 1, '2025-09-29 15:35:21', '2025-09-29 15:35:21');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_type` enum('full_admin','limited_admin','customer','guest') DEFAULT 'guest',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `role_type`, `description`, `created_at`) VALUES
(1, 'guest', 'guest', 'Khách chưa đăng nhập', '2025-09-09 15:06:45'),
(2, 'customer', 'customer', 'Khách hàng đã đăng ký', '2025-09-09 15:06:45'),
(3, 'super_admin', 'full_admin', 'Quản trị viên toàn quyền', '2025-09-09 15:06:45'),
(4, 'product_admin', 'limited_admin', 'Quản trị viên sản phẩm', '2025-09-09 15:06:45'),
(5, 'order_admin', 'limited_admin', 'Quản trị viên đơn hàng', '2025-09-09 15:06:45'),
(6, 'content_admin', 'limited_admin', 'Quản trị viên nội dung', '2025-09-09 15:06:45'),
(7, 'report_admin', 'limited_admin', 'Quản trị viên báo cáo', '2025-09-09 15:06:45');

-- --------------------------------------------------------

--
-- Table structure for table `role_permission`
--

CREATE TABLE `role_permission` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permission`
--

INSERT INTO `role_permission` (`role_id`, `permission_id`) VALUES
(1, 1),
(2, 2),
(2, 3),
(2, 7),
(2, 12),
(2, 15),
(2, 16),
(2, 17),
(2, 20),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(3, 15),
(3, 16),
(3, 17),
(3, 18),
(3, 19),
(3, 20),
(3, 21),
(3, 22),
(3, 23),
(3, 24),
(3, 25),
(3, 26),
(4, 6),
(4, 7),
(4, 8),
(4, 9),
(4, 10),
(4, 11),
(4, 12),
(4, 13),
(4, 14),
(5, 15),
(5, 16),
(5, 17),
(5, 18),
(5, 19),
(5, 20),
(6, 21),
(6, 22),
(6, 23),
(6, 24),
(7, 25),
(7, 26);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `card` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_token_expiry` datetime DEFAULT NULL,
  `password_changed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `reset_token`, `address`, `card`, `is_active`, `created_at`, `updated_at`, `reset_token_expiry`, `password_changed`) VALUES
(1, 'Super Admin', 'admin@example.com', 'adasdadad', '$2y$10$dz.9Sjy6t/zNmc4b3S5g8OBOv/r3zZg0VeDqp/zRhqmdyPySPWZMS', NULL, 'adasdadadasd', '', 0, '2025-09-09 15:06:46', '2025-09-30 11:43:14', NULL, 0),
(2, 'Nguyen Van A', 'nguyenvana@example.com', '0886924444', '$2y$10$OzGyXikFUDm8Sh2QDHYopO20kS9sicBo/KlWduTLpNGurxbnvRgye', NULL, NULL, NULL, 1, '2025-09-09 15:24:24', '2025-09-09 15:24:24', NULL, 0),
(3, 'Nguyen Huy Quang', 'huyquang@gmail.com', '0886922226', '$2y$10$KFOdndCeV184iBdJpcQ6xet13mpLaTZLXf/rk/EplB1zISvkabjki', NULL, 'vinh', NULL, 1, '2025-09-09 15:33:29', '2025-09-21 16:45:48', NULL, 0),
(7, 'Nguyen Huy Quang', 'test1@gmail.com', '0886922226', '$2y$10$mEQDnpn8bWNUdksMQLa2W.HQnL4cBMI1N/f4oCzboeP6wbLi/hcSC', NULL, 'Tan binh', '', 0, '2025-09-12 16:52:22', '2025-09-29 08:17:31', NULL, 0),
(8, 'Nguyen Huy Quang', 'huyquang1@gmail.com', '0886922226', '$2y$10$V40V08ea9cKAZNn2/Iemc.p.OldkgAo1Z5bqMPg/7ig22fiiOeQm2', NULL, 'Tan binh', '', 0, '2025-09-13 09:09:16', '2025-09-29 08:56:00', NULL, 0),
(9, 'Nguyen quangn', 'test3@gmail.com', '0886922226', '$2y$10$tGvoX6thqd2QVCORoKcJE.VOFj5mypMdOjemIc7YwUfqU9m1K9Nmi', NULL, 'Tbinh', NULL, 1, '2025-09-16 16:47:30', '2025-09-16 16:47:30', NULL, 0),
(10, 'Quang Huy Nguyennnnnn', 'test5@gmail.com', '0886924681', '$2y$10$HKSE45ynh5nKpuXbmqI.geg0xbDftMk3on7DYSXEwbyWVgJs7AERS', NULL, 'Vinh,Nghe Annnnnn', NULL, 1, '2025-09-20 14:57:24', '2025-09-20 14:57:24', NULL, 0),
(11, 'Quang Huy Nguyen', 'hquang6504@gmail.com', '0886924681', '$2y$10$Ho47VGBZViREKUhA.tjRHuR0nKwyANfkb5J/rmCcgQ/Ditvuu1hXi', NULL, 'Vinh,Nghe An', NULL, 1, '2025-09-25 15:52:20', '2025-09-26 10:22:05', NULL, 0),
(12, 'Long Ho', 'thanhlong@gmail.com', '0987654321', '$2y$10$bDeuYL.BdB4XAZZ2FTZtAOBMV4ExvEzH64hQPIaCf2V6iVDGeHUH2', NULL, 'DAKLAK', 'non', 1, '2025-09-27 08:24:40', '2025-09-30 13:46:41', NULL, 0),
(13, 'Long Ho customer', 'customer@gmail.com', '0903222424', '$2y$10$enE5Xn.oERHOk07pRQnJ7ern8FjjW/IK/nvdgdWdLrZw5cIiqaN6K', NULL, '120 TT', '', 1, '2025-09-28 08:42:15', '2025-09-29 15:19:19', NULL, 0),
(15, 'Quản trị sản viên sản phẩm', 'adminproducts@gmail.com', '0886922226', '$2y$10$Lg5iHrdMitC7TayhV5oJ4OhT3UzlX2a2LpHL4XCvFFZr6WGLL9BAq', NULL, '120 TT', '', 1, '2025-09-29 15:21:23', '2025-09-30 13:46:37', NULL, 0),
(16, 'Long Ho', 'thanhlongzz@gmail.com', '0886922226', '$2y$10$FqVmZ1VNh2qICXYDZqXKCu7HAd/HScZUE3S2AR2g5w2QPPCQbpVxC', NULL, '120 TT', NULL, 0, '2025-09-29 15:37:53', '2025-09-29 15:38:15', NULL, 0),
(17, 'admin fake', 'adminfake@gmail.com', '0886922226', '$2y$10$07RH5PhS7B1ujkfIUSGAH.45s0XKgu6o/37lGFhayUhY9xDqTlVoS', NULL, '120 TT', NULL, 0, '2025-09-30 11:02:51', '2025-09-30 11:03:27', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE `user_role` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`user_id`, `role_id`) VALUES
(1, 3),
(2, 2),
(3, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 3),
(13, 2),
(15, 3),
(16, 2),
(17, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `advertisement`
--
ALTER TABLE `advertisement`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dim_date`
--
ALTER TABLE `dim_date`
  ADD PRIMARY KEY (`date_id`);

--
-- Indexes for table `dim_product`
--
ALTER TABLE `dim_product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `fact_sales`
--
ALTER TABLE `fact_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `date_id` (`date_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `permission`
--
ALTER TABLE `permission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_role`
--
ALTER TABLE `user_role`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advertisement`
--
ALTER TABLE `advertisement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `fact_sales`
--
ALTER TABLE `fact_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `permission`
--
ALTER TABLE `permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `dim_product`
--
ALTER TABLE `dim_product`
  ADD CONSTRAINT `dim_product_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `dim_product_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Constraints for table `fact_sales`
--
ALTER TABLE `fact_sales`
  ADD CONSTRAINT `fact_sales_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `fact_sales_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `dim_product` (`product_id`),
  ADD CONSTRAINT `fact_sales_ibfk_3` FOREIGN KEY (`date_id`) REFERENCES `dim_date` (`date_id`);

--
-- Constraints for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD CONSTRAINT `login_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Constraints for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `role_permission_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permission_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `user_role_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_role_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
