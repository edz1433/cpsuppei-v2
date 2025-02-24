-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 12, 2023 at 04:15 PM
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
-- Database: `dbcpsuppei`
--

-- --------------------------------------------------------

--
-- Table structure for table `campuses`
--

CREATE TABLE `campuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `campus_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `campus_abbr` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campuses`
--

INSERT INTO `campuses` (`id`, `campus_name`, `campus_abbr`, `created_at`, `updated_at`) VALUES
(1, 'CPSU Main', 'Main', NULL, NULL),
(2, 'CPSU Candoni', 'Candoni', NULL, NULL),
(3, 'CPSU Cauayan', 'Cauayan', NULL, NULL),
(4, 'CPSU Hinigaran', 'Hinigaran', NULL, NULL),
(5, 'CPSU Hinoba-an', 'Hinoba-an', NULL, NULL),
(6, 'CPSU Ilog', 'Ilog', NULL, NULL),
(7, 'CPSU San Carlos', 'San Carlos', NULL, NULL),
(8, 'CPSU Sipalay', 'Sipalay', NULL, NULL),
(9, 'CPSU Victorias', 'Victorias', NULL, NULL),
(10, 'CPSU Murcia', 'Murcias', NULL, NULL),
(11, 'CPSU Valladolid', 'Valladolid', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cat_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cat_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `property_id`, `cat_name`, `cat_code`, `created_at`, `updated_at`) VALUES
(1, '3', 'Land', '01', NULL, NULL),
(2, '3', 'Land Improvements', '02', NULL, NULL),
(3, '3', 'Infrastructure Assets', '03', NULL, NULL),
(4, '3', 'Buildings and Other Structures', '04', NULL, NULL),
(5, '3', 'Machinery and Equipment', '05', NULL, NULL),
(6, '3', 'Transportation Equipment', '06', NULL, NULL),
(7, '3', 'Furniture, Fixtures and Books', '07', NULL, NULL),
(8, '3', 'Leased Assets', '08', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventories`
--

CREATE TABLE `inventories` (
  `id` double NOT NULL,
  `pur_id` int(11) NOT NULL,
  `status` varchar(100) NOT NULL,
  `in_status` varchar(100) NOT NULL,
  `invdate` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `inv_settings`
--

CREATE TABLE `inv_settings` (
  `id` int(11) NOT NULL,
  `switch` varchar(100) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inv_settings`
--

INSERT INTO `inv_settings` (`id`, `switch`, `updated_at`, `created_at`) VALUES
(1, 'Off', '2023-07-27 16:00:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `item_name`, `created_at`, `updated_at`) VALUES
(1, 'Laptop', '2023-08-01 17:27:53', '2023-08-01 17:27:53'),
(2, 'Desktop', '2023-08-01 17:28:56', '2023-08-01 17:28:56');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2023_07_11_052640_create_campus_table', 2),
(6, '2023_07_31_030723_create_settings_table', 3),
(7, '2023_07_31_062333_add_photo_filename_to_settings', 4),
(8, '2023_07_31_062517_add_photo_filename_to_settings', 5),
(9, '2023_08_01_015112_create_unit_table', 6),
(10, '2023_08_01_031325_create_items_table', 7);

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `id` int(10) UNSIGNED NOT NULL,
  `office_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `office_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `office_abbr` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `office_officer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`id`, `office_code`, `office_name`, `office_abbr`, `office_officer`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, '001', 'MAIN CAMPUS', 'MC', 'Aladino', NULL, '2023-08-02 19:20:05', '2023-08-02 19:20:05'),
(2, '002', 'CAUAYAN CAMPUS', 'CC', 'Germa Borres', NULL, '2023-08-02 19:21:23', '2023-08-02 19:25:00');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `property_id`, `category_id`, `account_number`, `account_title`, `code`, `created_at`, `updated_at`) VALUES
(1, '3', '05', '1-07-05-010', 'Machinery', '010', '2023-02-24 13:01:48', '2023-07-14 09:43:40'),
(2, '3', '05', '1-07-05-020', 'Office equipment', '020', '2023-02-24 13:02:55', '2023-07-14 09:45:06'),
(3, '3', '05', '1-07-05-022', 'Accumulated impairment lossess', '022', '2023-02-24 13:04:36', '2023-02-24 13:04:36'),
(4, '3', '06', '1-07-06-010', 'Motor vehicles', '010', '2023-02-24 13:06:02', '2023-02-24 13:06:02'),
(5, '3', '06', '1-07-06-011', 'Accumulated depreciation- motor vehicles', '011', '2023-02-24 13:07:40', '2023-02-24 13:07:40'),
(6, '3', '06', '1-07-06-012', 'Accumulated depreciasion - motorcycle vehicles', '012', '2023-02-24 13:08:43', '2023-02-24 13:08:43'),
(7, '3', '01', '1-07-01-010', 'Lands', '010', '2023-02-26 22:05:47', '2023-08-02 06:53:46'),
(8, '3', '01', '1-07-01-011', 'Accumulated impairment lossess', '011', '2023-02-26 22:06:22', '2023-02-26 22:06:22'),
(9, '3', '02', '1-07-02-010', 'Aquaculture structures', '010', '2023-02-26 22:07:28', '2023-08-02 06:55:24'),
(10, '3', '02', '1-07-02-011', 'Accumulated depreciations', '011', '2023-02-26 22:08:40', '2023-08-02 04:56:03'),
(11, '3', '03', '1-07-03-010', 'Road networks', '010', '2023-02-26 22:09:47', '2023-02-26 22:09:47'),
(12, '3', '03', '1-07-03-011', 'Accumulated Depreciation - Road Networks', '011', '2023-02-26 22:10:56', '2023-02-26 22:10:56'),
(13, '3', '04', '1-07-04-010', 'Buildings', '010', '2023-02-26 22:11:41', '2023-02-26 22:11:41'),
(14, '3', '04', '1-07-04-011', 'Accumulated depreciation - buildings', '011', '2023-02-26 22:12:25', '2023-02-26 22:12:25'),
(15, '3', '07', '1-07-07-010', 'Forniture and fixtures', '010', '2023-02-26 22:16:10', '2023-02-26 22:16:10'),
(16, '3', '07', '1-07-07-011', 'Accumulated depreciation - forniture and fixtures', '011', '2023-02-26 22:16:42', '2023-02-26 22:16:42'),
(18, '3', '08', '1-07-08-011', 'Accumulated impairment losses - leased assets, land', '011', '2023-02-26 22:18:36', '2023-02-26 22:18:36'),
(26, '3', '03', '1-07-03-212', 'Werwer', '212', '2023-08-02 06:44:03', '2023-08-02 06:44:03');

-- --------------------------------------------------------

--
-- Table structure for table `property`
--

CREATE TABLE `property` (
  `id` int(10) UNSIGNED NOT NULL,
  `default_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abbreviation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property`
--

INSERT INTO `property` (`id`, `default_code`, `property_code`, `property_name`, `abbreviation`, `created_at`, `updated_at`) VALUES
(1, '', '', 'Semi Expandable Property High Value', 'SPHV', NULL, NULL),
(2, '', '', 'Semi Expandable Property Low Value', 'SPLV', NULL, NULL),
(3, '1', '06', 'Property, Plant & Equipment', 'PPE', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(255) UNSIGNED NOT NULL,
  `office_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_descrip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_acquired` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_cost` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_cost` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `properties_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categories_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_no_generated` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Good Condition',
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N/A',
  `date_stat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `print_stat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `office_id`, `item_id`, `item_descrip`, `serial_number`, `date_acquired`, `unit_id`, `qty`, `item_cost`, `total_cost`, `properties_id`, `categories_id`, `property_id`, `item_number`, `property_no_generated`, `status`, `remarks`, `date_stat`, `print_stat`, `created_at`, `updated_at`) VALUES
(1, '1', '1', 'asus', 'ABCD', '2023-08-09', '1', '1', '50,000', '50,000', '3', '05', '020', '001', '2023-06-05-020-001-001', 'Good Condition', 'N/A', NULL, '1', '2023-08-09 01:10:18', '2023-08-09 01:10:18'),
(2, '1', '1', 'acer', 'DASDA', '2023-08-09', '1', '1', '50,000', '50,000', '3', '05', '020', '002', '2023-06-05-020-002-001', 'Good Condition', 'N/A', NULL, '1', '2023-08-09 01:10:56', '2023-08-09 01:10:56'),
(3, '1', '2', 'acer corei7', 'QWRC123', '2023-08-10', '1', '1', '45,000', '45,000', '2', NULL, NULL, '001', '2023-06---001-001', 'Good Condition', 'N/A', NULL, '1', '2023-08-10 01:04:21', '2023-08-10 01:04:21');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `system_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `system_name`, `photo_filename`, `created_at`, `updated_at`) VALUES
(1, 'INVENTORY MANAGEMENT SYSTEM', '1690789459_CPSU_L.png', '2023-07-30 23:43:57', '2023-07-30 23:44:19');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unit_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `unit_name`, `created_at`, `updated_at`) VALUES
(1, 'Pcs', '2023-08-02 06:31:19', '2023-08-02 07:11:36'),
(2, 'Boxs', '2023-08-02 06:35:17', '2023-08-02 06:51:47'),
(4, 'Set', '2023-08-03 18:23:42', '2023-08-03 18:23:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `campus_id` int(255) DEFAULT NULL,
  `fname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `campus_id`, `fname`, `mname`, `lname`, `username`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'JOSHUA KYLE', 'L', 'DALMACIO', 'admin1', '$2y$10$bwyqEUjkyfxaLAlzDxFbleauxlRa4s9RDTsClnB88XybZBSapiszq', 'Administrator', 'CNpFMD51updIDM8g7CkA3hTP4LTRK8ABsFjoR1JhuXuHYPzdvMxQRL8YNFGh', '2023-07-11 05:18:55', NULL),
(4, 1, 'EDWIN', 'T', 'ABRIL', 'adminbril', '$2y$10$SRUw4zCLpHmNlz8Rhy//A.6FD8BwZGwcMpS0JXCRvSRGpZa42ub1G', 'Administrator', NULL, '2023-08-02 23:49:04', '2023-08-02 23:49:04'),
(5, 1, 'MA. SOCORRO', 'M', 'LLAMAS', 'ching', '$2y$10$x7ACwqYOp75gxMAKmKCcteNdLPk.vGivzhXRL2pi0GYUUXQbUK4vO', 'Supply Officer', 'zmA9jWhQ8iBxsjwFJLmFbT3SirQVPbpM3mX6ovZNTNaAfSm1qtZcV5MXfS7K', '2023-08-09 04:21:10', '2023-08-09 04:21:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `campuses`
--
ALTER TABLE `campuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventories`
--
ALTER TABLE `inventories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inv_settings`
--
ALTER TABLE `inv_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `property`
--
ALTER TABLE `property`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
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
-- AUTO_INCREMENT for table `campuses`
--
ALTER TABLE `campuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventories`
--
ALTER TABLE `inventories`
  MODIFY `id` double NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inv_settings`
--
ALTER TABLE `inv_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `property`
--
ALTER TABLE `property`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(255) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
