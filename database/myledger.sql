-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 16, 2026 at 03:19 PM
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
-- Database: `myledger`
--

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `financial_transaction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `loan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `disk` varchar(40) NOT NULL DEFAULT 'local',
  `path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attachments`
--

INSERT INTO `attachments` (`id`, `user_id`, `financial_transaction_id`, `loan_id`, `disk`, `path`, `original_name`, `mime_type`, `size`, `created_at`, `updated_at`) VALUES
(1, 1, 8, NULL, 'local', 'finance/1/attachments/Rsx6NeN6N0q5HobCrkiSD9dSTbkF9q9APOc5IlPp.jpg', 'images.jpg', 'image/jpeg', 26969, '2026-09-15 11:59:26', '2026-09-15 11:59:26');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `auditable_type` varchar(150) NOT NULL,
  `auditable_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `auditable_type`, `auditable_id`, `action`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 'App\\Models\\FinancialTransaction', 1, 'created', NULL, '{\"type\":\"opening_balance\",\"reference_no\":\"OPEN-20260915-IRBCVC\",\"amount\":150000,\"date\":\"2026-08-31\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:43:04', '2026-09-15 11:43:04'),
(2, 1, 'App\\Models\\FinancialTransaction', 2, 'created', NULL, '{\"type\":\"opening_balance\",\"reference_no\":\"OPEN-20260915-GTAGU2\",\"amount\":80000,\"date\":\"2026-09-15\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:43:20', '2026-09-15 11:43:20'),
(3, 1, 'App\\Models\\FinancialTransaction', 3, 'created', NULL, '{\"type\":\"opening_balance\",\"reference_no\":\"OPEN-20260915-UI4GKW\",\"amount\":20000,\"date\":\"2026-09-15\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:44:06', '2026-09-15 11:44:06'),
(4, 1, 'App\\Models\\FinancialTransaction', 4, 'created', NULL, '{\"type\":\"opening_balance\",\"reference_no\":\"OPEN-20260915-LY0WJ7\",\"amount\":50000,\"date\":\"2026-09-15\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:44:22', '2026-09-15 11:44:22'),
(5, 1, 'App\\Models\\FinancialTransaction', 5, 'created', NULL, '{\"type\":\"income\",\"reference_no\":\"INCO-20260915-BBDA9L\",\"amount\":120000,\"date\":\"2026-09-01\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:45:27', '2026-09-15 11:45:27'),
(6, 1, 'App\\Models\\FinancialTransaction', 6, 'created', NULL, '{\"type\":\"expense\",\"reference_no\":\"EXPE-20260915-Y4XV1S\",\"amount\":30000,\"date\":\"2026-09-02\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:47:21', '2026-09-15 11:47:21'),
(7, 1, 'App\\Models\\FinancialTransaction', 7, 'created', NULL, '{\"type\":\"expense\",\"reference_no\":\"EXPE-20260915-1ZKWLG\",\"amount\":8000,\"date\":\"2026-09-03\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:48:01', '2026-09-15 11:48:01'),
(8, 1, 'App\\Models\\FinancialTransaction', 8, 'created', NULL, '{\"type\":\"expense\",\"reference_no\":\"EXPE-20260915-XMNFIA\",\"amount\":5000,\"date\":\"2026-09-04\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:48:30', '2026-09-15 11:48:30'),
(9, 1, 'App\\Models\\FinancialTransaction', 9, 'created', NULL, '{\"type\":\"transfer\",\"reference_no\":\"TRAN-20260915-AS7AAP\",\"amount\":20000,\"date\":\"2026-09-05\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:49:04', '2026-09-15 11:49:04'),
(10, 1, 'App\\Models\\FinancialTransaction', 10, 'created', NULL, '{\"type\":\"transfer\",\"reference_no\":\"TRAN-20260915-VHVMXY\",\"amount\":15000,\"date\":\"2026-09-06\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:49:37', '2026-09-15 11:49:37'),
(11, 1, 'App\\Models\\FinancialTransaction', 11, 'created', NULL, '{\"type\":\"loan_given\",\"reference_no\":\"LOAN-20260915-NGDFOW\",\"amount\":25000,\"date\":\"2026-09-07\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:52:42', '2026-09-15 11:52:42'),
(12, 1, 'App\\Models\\FinancialTransaction', 12, 'created', NULL, '{\"type\":\"loan_repayment_received\",\"reference_no\":\"LOAN-20260915-IQ8A1Y\",\"amount\":10000,\"date\":\"2026-09-15\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:53:50', '2026-09-15 11:53:50'),
(13, 1, 'App\\Models\\FinancialTransaction', 13, 'created', NULL, '{\"type\":\"loan_taken\",\"reference_no\":\"LOAN-20260915-YNGIWS\",\"amount\":40000,\"date\":\"2026-09-10\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:54:45', '2026-09-15 11:54:45'),
(14, 1, 'App\\Models\\FinancialTransaction', 14, 'created', NULL, '{\"type\":\"loan_repayment_paid\",\"reference_no\":\"LOAN-20260915-DDRSGZ\",\"amount\":5000,\"date\":\"2026-09-15\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 11:55:13', '2026-09-15 11:55:13'),
(15, 1, 'App\\Models\\FinancialTransaction', 15, 'created', NULL, '{\"type\":\"expense\",\"reference_no\":\"EXPE-20260915-GQEEIU\",\"amount\":3500,\"date\":\"2026-09-07\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 12:09:58', '2026-09-15 12:09:58'),
(16, 1, 'App\\Models\\FinancialTransaction', 16, 'created', NULL, '{\"type\":\"reversal\",\"reference_no\":\"REVE-20260915-W8U0UN\",\"amount\":3500,\"date\":\"2026-09-15\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 12:11:27', '2026-09-15 12:11:27'),
(17, 1, 'App\\Models\\FinancialTransaction', 17, 'created', NULL, '{\"type\":\"expense\",\"reference_no\":\"EXPE-20260915-FMQEBR\",\"amount\":3500,\"date\":\"2026-09-07\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 12:12:03', '2026-09-15 12:12:03'),
(18, 1, 'App\\Models\\FinancialTransaction', 18, 'created', NULL, '{\"type\":\"expense\",\"reference_no\":\"EXPE-20260915-T1OOFS\",\"amount\":12000,\"date\":\"2026-09-08\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 12:13:43', '2026-09-15 12:13:43'),
(19, 1, 'App\\Models\\FinancialTransaction', 19, 'created', NULL, '{\"type\":\"expense\",\"reference_no\":\"EXPE-20260916-LQJTHU\",\"amount\":5000,\"date\":\"2026-09-16\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 09:13:12', '2026-09-16 09:13:12');

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `amount` decimal(20,4) NOT NULL,
  `alert_percent` tinyint(3) UNSIGNED NOT NULL DEFAULT 80,
  `last_alert_level` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id`, `user_id`, `category_id`, `currency_id`, `period_start`, `period_end`, `amount`, `alert_percent`, `last_alert_level`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 1, '2026-09-01', '2026-09-30', 20000.0000, 80, 0, 1, '2026-09-15 11:56:17', '2026-09-15 11:56:17'),
(2, 1, 6, 1, '2026-09-01', '2026-09-30', 15000.0000, 80, 2, 1, '2026-09-15 11:56:29', '2026-09-15 12:13:43'),
(3, 1, 11, 1, '2026-09-01', '2026-09-30', 10000.0000, 80, 0, 1, '2026-09-15 11:56:39', '2026-09-15 11:56:39');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `ledger_account_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `user_id`, `ledger_account_id`, `parent_id`, `type`, `name`, `icon`, `color`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 8, NULL, 'income', 'Salary', 'payments', NULL, 1, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(2, 1, 9, NULL, 'income', 'Freelancing', 'work', NULL, 1, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(3, 1, 10, NULL, 'income', 'Business Income', 'business_center', NULL, 1, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(4, 1, 11, NULL, 'income', 'Other Income', 'add_circle', NULL, 1, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(5, 1, 12, NULL, 'expense', 'Food & Groceries', 'restaurant', NULL, 1, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(6, 1, 13, NULL, 'expense', 'Transport & Fuel', 'directions_car', NULL, 1, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(7, 1, 14, NULL, 'expense', 'Home & Utilities', 'home', NULL, 1, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(8, 1, 15, NULL, 'expense', 'Health', 'medical_services', NULL, 1, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(9, 1, 16, NULL, 'expense', 'Education', 'school', NULL, 1, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(10, 1, 17, NULL, 'expense', 'Shopping', 'shopping_bag', NULL, 1, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(11, 1, 18, NULL, 'expense', 'Entertainment', 'movie', NULL, 1, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(12, 1, 19, NULL, 'expense', 'Other Expense', 'more_horiz', NULL, 1, '2026-09-15 11:42:24', '2026-09-15 11:42:24');

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` char(3) NOT NULL,
  `name` varchar(80) NOT NULL,
  `symbol` varchar(12) NOT NULL,
  `decimal_places` tinyint(3) UNSIGNED NOT NULL DEFAULT 2,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `code`, `name`, `symbol`, `decimal_places`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'PKR', 'Pakistani Rupee', 'Rs', 2, 1, '2026-09-15 11:42:00', '2026-09-15 11:42:00'),
(2, 'USD', 'US Dollar', '$', 2, 1, '2026-09-15 11:42:00', '2026-09-15 11:42:00'),
(3, 'EUR', 'Euro', '€', 2, 1, '2026-09-15 11:42:00', '2026-09-15 11:42:00'),
(4, 'GBP', 'Pound Sterling', '£', 2, 1, '2026-09-15 11:42:00', '2026-09-15 11:42:00'),
(5, 'AED', 'UAE Dirham', 'AED', 2, 1, '2026-09-15 11:42:00', '2026-09-15 11:42:00'),
(6, 'SAR', 'Saudi Riyal', 'SAR', 2, 1, '2026-09-15 11:42:00', '2026-09-15 11:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_transactions`
--

CREATE TABLE `financial_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `source_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `destination_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `person_id` bigint(20) UNSIGNED DEFAULT NULL,
  `loan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `savings_goal_id` bigint(20) UNSIGNED DEFAULT NULL,
  `recurring_transaction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reversal_of_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `reference_no` varchar(60) NOT NULL,
  `transaction_date` date NOT NULL,
  `amount` decimal(20,4) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'posted',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `financial_transactions`
--

INSERT INTO `financial_transactions` (`id`, `user_id`, `currency_id`, `source_account_id`, `destination_account_id`, `category_id`, `person_id`, `loan_id`, `savings_goal_id`, `recurring_transaction_id`, `reversal_of_id`, `type`, `reference_no`, `transaction_date`, `amount`, `description`, `notes`, `status`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 20, NULL, NULL, NULL, NULL, NULL, NULL, 'opening_balance', 'OPEN-20260915-IRBCVC', '2026-08-31', 150000.0000, 'Opening balance - HBL Bank', NULL, 'posted', NULL, '2026-09-15 11:43:04', '2026-09-15 11:43:04'),
(2, 1, 1, NULL, 21, NULL, NULL, NULL, NULL, NULL, NULL, 'opening_balance', 'OPEN-20260915-GTAGU2', '2026-09-15', 80000.0000, 'Opening balance - Meezan Bank', NULL, 'posted', NULL, '2026-09-15 11:43:20', '2026-09-15 11:43:20'),
(3, 1, 1, NULL, 22, NULL, NULL, NULL, NULL, NULL, NULL, 'opening_balance', 'OPEN-20260915-UI4GKW', '2026-09-15', 20000.0000, 'Opening balance - Cash in Hand', NULL, 'posted', NULL, '2026-09-15 11:44:06', '2026-09-15 11:44:06'),
(4, 1, 1, NULL, 23, NULL, NULL, NULL, NULL, NULL, NULL, 'opening_balance', 'OPEN-20260915-LY0WJ7', '2026-09-15', 50000.0000, 'Opening balance - Savings', NULL, 'posted', NULL, '2026-09-15 11:44:22', '2026-09-15 11:44:22'),
(5, 1, 1, NULL, 20, 1, NULL, NULL, NULL, NULL, NULL, 'income', 'INCO-20260915-BBDA9L', '2026-09-01', 120000.0000, 'September Salary', NULL, 'posted', NULL, '2026-09-15 11:45:27', '2026-09-15 11:45:27'),
(6, 1, 1, 20, NULL, 12, NULL, NULL, NULL, NULL, NULL, 'expense', 'EXPE-20260915-Y4XV1S', '2026-09-02', 30000.0000, 'September house rent', NULL, 'posted', NULL, '2026-09-15 11:47:21', '2026-09-15 11:47:21'),
(7, 1, 1, 22, NULL, 12, NULL, NULL, NULL, NULL, NULL, 'expense', 'EXPE-20260915-1ZKWLG', '2026-09-03', 8000.0000, 'Monthly groceries', NULL, 'posted', NULL, '2026-09-15 11:48:01', '2026-09-15 11:48:01'),
(8, 1, 1, 20, NULL, 6, NULL, NULL, NULL, NULL, NULL, 'expense', 'EXPE-20260915-XMNFIA', '2026-09-04', 5000.0000, 'Car fuel', NULL, 'posted', NULL, '2026-09-15 11:48:30', '2026-09-15 11:48:30'),
(9, 1, 1, 20, 21, NULL, NULL, NULL, NULL, NULL, NULL, 'transfer', 'TRAN-20260915-AS7AAP', '2026-09-05', 20000.0000, 'Transfer to Meezan', NULL, 'posted', NULL, '2026-09-15 11:49:04', '2026-09-15 11:49:04'),
(10, 1, 1, 20, 23, NULL, NULL, NULL, NULL, NULL, NULL, 'transfer', 'TRAN-20260915-VHVMXY', '2026-09-06', 15000.0000, NULL, NULL, 'posted', NULL, '2026-09-15 11:49:37', '2026-09-15 11:49:37'),
(11, 1, 1, 20, NULL, NULL, 1, 1, NULL, NULL, NULL, 'loan_given', 'LOAN-20260915-NGDFOW', '2026-09-07', 25000.0000, 'Loan given to Ali Khan', 'Personal loan to Ali', 'posted', NULL, '2026-09-15 11:52:42', '2026-09-15 11:52:42'),
(12, 1, 1, NULL, 20, NULL, 1, 1, NULL, NULL, NULL, 'loan_repayment_received', 'LOAN-20260915-IQ8A1Y', '2026-09-15', 10000.0000, 'Loan repayment - Ali Khan', NULL, 'posted', NULL, '2026-09-15 11:53:50', '2026-09-15 11:53:50'),
(13, 1, 1, NULL, 21, NULL, 2, 2, NULL, NULL, NULL, 'loan_taken', 'LOAN-20260915-YNGIWS', '2026-09-10', 40000.0000, 'Loan taken from Ahmed', NULL, 'posted', NULL, '2026-09-15 11:54:45', '2026-09-15 11:54:45'),
(14, 1, 1, 21, NULL, NULL, 2, 2, NULL, NULL, NULL, 'loan_repayment_paid', 'LOAN-20260915-DDRSGZ', '2026-09-15', 5000.0000, 'Loan repayment - Ahmed', NULL, 'posted', NULL, '2026-09-15 11:55:13', '2026-09-15 11:55:13'),
(15, 1, 1, 20, NULL, 6, NULL, NULL, NULL, NULL, NULL, 'expense', 'EXPE-20260915-GQEEIU', '2026-09-07', 3500.0000, NULL, NULL, 'reversed', NULL, '2026-09-15 12:09:58', '2026-09-15 12:11:27'),
(16, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 15, 'reversal', 'REVE-20260915-W8U0UN', '2026-09-15', 3500.0000, 'Reversal of EXPE-20260915-GQEEIU', NULL, 'posted', '{\"original_type\":\"expense\"}', '2026-09-15 12:11:27', '2026-09-15 12:11:27'),
(17, 1, 1, 20, NULL, 6, NULL, NULL, NULL, NULL, NULL, 'expense', 'EXPE-20260915-FMQEBR', '2026-09-07', 3500.0000, NULL, NULL, 'posted', NULL, '2026-09-15 12:12:03', '2026-09-15 12:12:03'),
(18, 1, 1, 20, NULL, 6, NULL, NULL, NULL, NULL, NULL, 'expense', 'EXPE-20260915-T1OOFS', '2026-09-08', 12000.0000, NULL, NULL, 'posted', NULL, '2026-09-15 12:13:43', '2026-09-15 12:13:43'),
(19, 1, 1, 20, NULL, 6, NULL, NULL, NULL, NULL, NULL, 'expense', 'EXPE-20260916-LQJTHU', '2026-09-16', 5000.0000, NULL, NULL, 'posted', NULL, '2026-09-16 09:13:12', '2026-09-16 09:13:12');

-- --------------------------------------------------------

--
-- Table structure for table `financial_transaction_tag`
--

CREATE TABLE `financial_transaction_tag` (
  `financial_transaction_id` bigint(20) UNSIGNED NOT NULL,
  `tag_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `financial_transaction_tag`
--

INSERT INTO `financial_transaction_tag` (`financial_transaction_id`, `tag_id`) VALUES
(15, 1),
(17, 1),
(18, 1);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ledger_accounts`
--

CREATE TABLE `ledger_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kind` varchar(20) NOT NULL,
  `type` varchar(40) NOT NULL,
  `system_code` varchar(50) DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `institution` varchar(120) DEFAULT NULL,
  `last_four` varchar(4) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `include_in_net_worth` tinyint(1) NOT NULL DEFAULT 1,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ledger_accounts`
--

INSERT INTO `ledger_accounts` (`id`, `user_id`, `currency_id`, `parent_id`, `kind`, `type`, `system_code`, `name`, `institution`, `last_four`, `icon`, `color`, `is_system`, `include_in_net_worth`, `is_archived`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'equity', 'opening_balance_equity', NULL, 'Opening Balance Equity', NULL, NULL, NULL, NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(2, 1, 1, NULL, 'asset', 'loan_receivable', NULL, 'Loans Receivable', NULL, NULL, NULL, NULL, 1, 1, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(3, 1, 1, NULL, 'liability', 'loan_payable', NULL, 'Loans Payable', NULL, NULL, NULL, NULL, 1, 1, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(4, 1, 1, NULL, 'income', 'interest_income', NULL, 'Interest Income', NULL, NULL, NULL, NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(5, 1, 1, NULL, 'expense', 'interest_expense', NULL, 'Interest Expense', NULL, NULL, NULL, NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(6, 1, 1, NULL, 'equity', 'adjustment_equity', NULL, 'Balance Adjustment', NULL, NULL, NULL, NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(7, 1, 1, NULL, 'asset', 'cash', NULL, 'Cash in Hand', NULL, NULL, 'wallet', NULL, 0, 1, 1, NULL, '2026-09-15 11:42:24', '2026-09-15 11:43:54'),
(8, 1, 1, NULL, 'income', 'category', NULL, 'Salary', NULL, NULL, 'payments', NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(9, 1, 1, NULL, 'income', 'category', NULL, 'Freelancing', NULL, NULL, 'work', NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(10, 1, 1, NULL, 'income', 'category', NULL, 'Business Income', NULL, NULL, 'business_center', NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(11, 1, 1, NULL, 'income', 'category', NULL, 'Other Income', NULL, NULL, 'add_circle', NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(12, 1, 1, NULL, 'expense', 'category', NULL, 'Food & Groceries', NULL, NULL, 'restaurant', NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(13, 1, 1, NULL, 'expense', 'category', NULL, 'Transport & Fuel', NULL, NULL, 'directions_car', NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(14, 1, 1, NULL, 'expense', 'category', NULL, 'Home & Utilities', NULL, NULL, 'home', NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(15, 1, 1, NULL, 'expense', 'category', NULL, 'Health', NULL, NULL, 'medical_services', NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(16, 1, 1, NULL, 'expense', 'category', NULL, 'Education', NULL, NULL, 'school', NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(17, 1, 1, NULL, 'expense', 'category', NULL, 'Shopping', NULL, NULL, 'shopping_bag', NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(18, 1, 1, NULL, 'expense', 'category', NULL, 'Entertainment', NULL, NULL, 'movie', NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(19, 1, 1, NULL, 'expense', 'category', NULL, 'Other Expense', NULL, NULL, 'more_horiz', NULL, 1, 0, 0, NULL, '2026-09-15 11:42:24', '2026-09-15 11:42:24'),
(20, 1, 1, NULL, 'asset', 'bank', NULL, 'HBL Bank', NULL, NULL, NULL, NULL, 0, 1, 0, NULL, '2026-09-15 11:43:04', '2026-09-15 11:43:04'),
(21, 1, 1, NULL, 'asset', 'bank', NULL, 'Meezan Bank', NULL, NULL, NULL, NULL, 0, 1, 0, NULL, '2026-09-15 11:43:20', '2026-09-15 11:43:20'),
(22, 1, 1, NULL, 'asset', 'cash', NULL, 'Cash in Hand', NULL, NULL, NULL, NULL, 0, 1, 0, NULL, '2026-09-15 11:44:06', '2026-09-15 11:44:06'),
(23, 1, 1, NULL, 'asset', 'savings', NULL, 'Savings', NULL, NULL, NULL, NULL, 0, 1, 0, NULL, '2026-09-15 11:44:22', '2026-09-15 11:44:22');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `person_id` bigint(20) UNSIGNED NOT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `ledger_account_id` bigint(20) UNSIGNED NOT NULL,
  `direction` varchar(12) NOT NULL,
  `title` varchar(140) DEFAULT NULL,
  `principal` decimal(20,4) NOT NULL,
  `outstanding_principal` decimal(20,4) NOT NULL,
  `interest_rate` decimal(9,4) NOT NULL DEFAULT 0.0000,
  `interest_type` varchar(20) NOT NULL DEFAULT 'none',
  `start_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `user_id`, `person_id`, `currency_id`, `ledger_account_id`, `direction`, `title`, `principal`, `outstanding_principal`, `interest_rate`, `interest_type`, `start_date`, `due_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 2, 'given', NULL, 25000.0000, 15000.0000, 0.0000, 'none', '2026-09-07', '2026-09-12', 'active', 'Personal loan to Ali', '2026-09-15 11:52:42', '2026-09-15 11:53:50'),
(2, 1, 2, 1, 3, 'taken', NULL, 40000.0000, 35000.0000, 0.0000, 'none', '2026-09-10', '2026-10-15', 'active', NULL, '2026-09-15 11:54:45', '2026-09-15 11:55:13');

-- --------------------------------------------------------

--
-- Table structure for table `loan_payments`
--

CREATE TABLE `loan_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `financial_transaction_id` bigint(20) UNSIGNED NOT NULL,
  `principal_amount` decimal(20,4) NOT NULL,
  `interest_amount` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `paid_at` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loan_payments`
--

INSERT INTO `loan_payments` (`id`, `loan_id`, `financial_transaction_id`, `principal_amount`, `interest_amount`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 12, 10000.0000, 0.0000, '2026-09-15', '2026-09-15 11:53:50', '2026-09-15 11:53:50'),
(2, 2, 14, 5000.0000, 0.0000, '2026-09-15', '2026-09-15 11:55:13', '2026-09-15 11:55:13');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_15_000001_create_finance_foundation', 1),
(5, '2026_09_15_000002_create_finance_planning', 1),
(6, '2026_09_15_000003_create_finance_transactions', 1),
(7, '2026_09_15_000004_create_finance_support', 1),
(8, '2026_09_15_161036_add_system_code_to_ledger_accounts_table.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('6a5923b0-8f51-47ad-9cca-afb2dc97f1e9', 'App\\Notifications\\FinanceReminderNotification', 'App\\Models\\User', 1, '{\"title\":\"Budget exceeded\",\"message\":\"You have exceeded your Transport & Fuel budget (137% used).\",\"payload\":{\"budget_id\":2,\"percent\":136.67}}', NULL, '2026-09-15 12:13:43', '2026-09-15 12:13:43');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `people`
--

CREATE TABLE `people` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `people`
--

INSERT INTO `people` (`id`, `user_id`, `name`, `phone`, `email`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ali Khan', '0300xxxxxxx', NULL, NULL, '2026-09-15 11:51:44', '2026-09-15 11:51:44'),
(2, 1, 'Ahmed', NULL, NULL, NULL, '2026-09-15 11:54:15', '2026-09-15 11:54:15');

-- --------------------------------------------------------

--
-- Table structure for table `recurring_transactions`
--

CREATE TABLE `recurring_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `source_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `destination_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `person_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(40) NOT NULL,
  `title` varchar(140) NOT NULL,
  `amount` decimal(20,4) NOT NULL,
  `frequency` varchar(20) NOT NULL,
  `interval` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `next_run_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `mode` varchar(20) NOT NULL DEFAULT 'remind',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recurring_transactions`
--

INSERT INTO `recurring_transactions` (`id`, `user_id`, `currency_id`, `source_account_id`, `destination_account_id`, `category_id`, `person_id`, `type`, `title`, `amount`, `frequency`, `interval`, `next_run_at`, `mode`, `is_active`, `payload`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 20, NULL, 11, NULL, 'expense', 'Internet Bill', 4500.0000, 'monthly', 5, '2026-10-05 07:00:00', 'remind', 1, NULL, '2026-09-15 11:57:57', '2026-09-15 11:57:57'),
(2, 1, 1, NULL, 20, 1, NULL, 'income', 'Salary', 120000.0000, 'monthly', 1, '2026-10-01 12:00:00', 'remind', 1, NULL, '2026-09-15 11:58:35', '2026-09-15 11:58:35');

-- --------------------------------------------------------

--
-- Table structure for table `savings_allocations`
--

CREATE TABLE `savings_allocations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `savings_goal_id` bigint(20) UNSIGNED NOT NULL,
  `financial_transaction_id` bigint(20) UNSIGNED NOT NULL,
  `direction` varchar(20) NOT NULL,
  `amount` decimal(20,4) NOT NULL,
  `allocated_at` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `savings_goals`
--

CREATE TABLE `savings_goals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(120) NOT NULL,
  `target_amount` decimal(20,4) NOT NULL,
  `allocated_amount` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `target_date` date DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `savings_goals`
--

INSERT INTO `savings_goals` (`id`, `user_id`, `account_id`, `currency_id`, `title`, `target_amount`, `allocated_amount`, `target_date`, `icon`, `color`, `is_completed`, `created_at`, `updated_at`) VALUES
(1, 1, 23, 1, 'Emergency Fund', 500000.0000, 0.0000, '2027-12-31', NULL, NULL, 0, '2026-09-15 11:50:17', '2026-09-15 11:50:17');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('XYMzZVIEWdvnVr0C0HQFiusDEgyO6xfXZA3zScDb', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJxUFpGZHJEQ0JLQmF2dkFpaktsb1dXSXBGRlpCSU9lM2lvTVYxd1U1IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjE5OTlcL3JlZ2lzdGVyIiwicm91dGUiOiJyZWdpc3RlciJ9fQ==', 1789550242);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `user_id`, `name`, `color`, `created_at`, `updated_at`) VALUES
(1, 1, 'car', '#3dd680', '2026-09-15 12:08:12', '2026-09-15 12:08:12');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_entries`
--

CREATE TABLE `transaction_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `financial_transaction_id` bigint(20) UNSIGNED NOT NULL,
  `ledger_account_id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `debit` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `credit` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `memo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_entries`
--

INSERT INTO `transaction_entries` (`id`, `financial_transaction_id`, `ledger_account_id`, `loan_id`, `debit`, `credit`, `memo`, `created_at`, `updated_at`) VALUES
(1, 1, 20, NULL, 150000.0000, 0.0000, NULL, '2026-09-15 11:43:04', '2026-09-15 11:43:04'),
(2, 1, 1, NULL, 0.0000, 150000.0000, NULL, '2026-09-15 11:43:04', '2026-09-15 11:43:04'),
(3, 2, 21, NULL, 80000.0000, 0.0000, NULL, '2026-09-15 11:43:20', '2026-09-15 11:43:20'),
(4, 2, 1, NULL, 0.0000, 80000.0000, NULL, '2026-09-15 11:43:20', '2026-09-15 11:43:20'),
(5, 3, 22, NULL, 20000.0000, 0.0000, NULL, '2026-09-15 11:44:06', '2026-09-15 11:44:06'),
(6, 3, 1, NULL, 0.0000, 20000.0000, NULL, '2026-09-15 11:44:06', '2026-09-15 11:44:06'),
(7, 4, 23, NULL, 50000.0000, 0.0000, NULL, '2026-09-15 11:44:22', '2026-09-15 11:44:22'),
(8, 4, 1, NULL, 0.0000, 50000.0000, NULL, '2026-09-15 11:44:22', '2026-09-15 11:44:22'),
(9, 5, 20, NULL, 120000.0000, 0.0000, NULL, '2026-09-15 11:45:27', '2026-09-15 11:45:27'),
(10, 5, 8, NULL, 0.0000, 120000.0000, NULL, '2026-09-15 11:45:27', '2026-09-15 11:45:27'),
(11, 6, 19, NULL, 30000.0000, 0.0000, NULL, '2026-09-15 11:47:21', '2026-09-15 11:47:21'),
(12, 6, 20, NULL, 0.0000, 30000.0000, NULL, '2026-09-15 11:47:21', '2026-09-15 11:47:21'),
(13, 7, 19, NULL, 8000.0000, 0.0000, NULL, '2026-09-15 11:48:01', '2026-09-15 11:48:01'),
(14, 7, 22, NULL, 0.0000, 8000.0000, NULL, '2026-09-15 11:48:01', '2026-09-15 11:48:01'),
(15, 8, 13, NULL, 5000.0000, 0.0000, NULL, '2026-09-15 11:48:30', '2026-09-15 11:48:30'),
(16, 8, 20, NULL, 0.0000, 5000.0000, NULL, '2026-09-15 11:48:30', '2026-09-15 11:48:30'),
(17, 9, 21, NULL, 20000.0000, 0.0000, NULL, '2026-09-15 11:49:04', '2026-09-15 11:49:04'),
(18, 9, 20, NULL, 0.0000, 20000.0000, NULL, '2026-09-15 11:49:04', '2026-09-15 11:49:04'),
(19, 10, 23, NULL, 15000.0000, 0.0000, NULL, '2026-09-15 11:49:37', '2026-09-15 11:49:37'),
(20, 10, 20, NULL, 0.0000, 15000.0000, NULL, '2026-09-15 11:49:37', '2026-09-15 11:49:37'),
(21, 11, 2, 1, 25000.0000, 0.0000, NULL, '2026-09-15 11:52:42', '2026-09-15 11:52:42'),
(22, 11, 20, NULL, 0.0000, 25000.0000, NULL, '2026-09-15 11:52:42', '2026-09-15 11:52:42'),
(23, 12, 20, NULL, 10000.0000, 0.0000, NULL, '2026-09-15 11:53:50', '2026-09-15 11:53:50'),
(24, 12, 2, 1, 0.0000, 10000.0000, NULL, '2026-09-15 11:53:50', '2026-09-15 11:53:50'),
(25, 13, 21, NULL, 40000.0000, 0.0000, NULL, '2026-09-15 11:54:45', '2026-09-15 11:54:45'),
(26, 13, 3, 2, 0.0000, 40000.0000, NULL, '2026-09-15 11:54:45', '2026-09-15 11:54:45'),
(27, 14, 3, 2, 5000.0000, 0.0000, NULL, '2026-09-15 11:55:13', '2026-09-15 11:55:13'),
(28, 14, 21, NULL, 0.0000, 5000.0000, NULL, '2026-09-15 11:55:13', '2026-09-15 11:55:13'),
(29, 15, 13, NULL, 3500.0000, 0.0000, NULL, '2026-09-15 12:09:58', '2026-09-15 12:09:58'),
(30, 15, 20, NULL, 0.0000, 3500.0000, NULL, '2026-09-15 12:09:58', '2026-09-15 12:09:58'),
(31, 16, 13, NULL, 0.0000, 3500.0000, NULL, '2026-09-15 12:11:27', '2026-09-15 12:11:27'),
(32, 16, 20, NULL, 3500.0000, 0.0000, NULL, '2026-09-15 12:11:27', '2026-09-15 12:11:27'),
(33, 17, 13, NULL, 3500.0000, 0.0000, NULL, '2026-09-15 12:12:03', '2026-09-15 12:12:03'),
(34, 17, 20, NULL, 0.0000, 3500.0000, NULL, '2026-09-15 12:12:03', '2026-09-15 12:12:03'),
(35, 18, 13, NULL, 12000.0000, 0.0000, NULL, '2026-09-15 12:13:43', '2026-09-15 12:13:43'),
(36, 18, 20, NULL, 0.0000, 12000.0000, NULL, '2026-09-15 12:13:43', '2026-09-15 12:13:43'),
(37, 19, 13, NULL, 5000.0000, 0.0000, NULL, '2026-09-16 09:13:12', '2026-09-16 09:13:12'),
(38, 19, 20, NULL, 0.0000, 5000.0000, NULL, '2026-09-16 09:13:12', '2026-09-16 09:13:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Shayan Ahmad', 'shayan@gmail.com', NULL, '$2y$12$/nPv/eFyTRGvAozFhKkZt.0OZSNcR/Vx2JAPSThM.qqFF6mRUpRXa', 'f2rvfgInxvUDUmbgBQDWeX3wti4cHUf46hTldXCWwfd6jhMFJ9jD5VQvuwz3', '2026-09-15 11:42:24', '2026-09-15 11:42:24');

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `base_currency_id` bigint(20) UNSIGNED NOT NULL,
  `theme` varchar(20) NOT NULL DEFAULT 'system',
  `locale` varchar(10) NOT NULL DEFAULT 'en',
  `daily_reminder_time` time DEFAULT NULL,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`id`, `user_id`, `base_currency_id`, `theme`, `locale`, `daily_reminder_time`, `preferences`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'system', 'en', NULL, NULL, '2026-09-15 11:42:24', '2026-09-16 09:14:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attachments_user_id_foreign` (`user_id`),
  ADD KEY `attachments_financial_transaction_id_foreign` (`financial_transaction_id`),
  ADD KEY `attachments_loan_id_foreign` (`loan_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  ADD KEY `audit_logs_user_id_created_at_index` (`user_id`,`created_at`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `budgets_user_id_category_id_period_start_period_end_unique` (`user_id`,`category_id`,`period_start`,`period_end`),
  ADD KEY `budgets_category_id_foreign` (`category_id`),
  ADD KEY `budgets_currency_id_foreign` (`currency_id`),
  ADD KEY `budgets_user_id_period_start_period_end_index` (`user_id`,`period_start`,`period_end`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_user_id_type_name_unique` (`user_id`,`type`,`name`),
  ADD KEY `categories_ledger_account_id_foreign` (`ledger_account_id`),
  ADD KEY `categories_parent_id_foreign` (`parent_id`),
  ADD KEY `categories_user_id_type_is_active_index` (`user_id`,`type`,`is_active`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `currencies_code_unique` (`code`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fin_tx_user_reference_unique` (`user_id`,`reference_no`),
  ADD KEY `financial_transactions_currency_id_foreign` (`currency_id`),
  ADD KEY `financial_transactions_source_account_id_foreign` (`source_account_id`),
  ADD KEY `financial_transactions_destination_account_id_foreign` (`destination_account_id`),
  ADD KEY `financial_transactions_category_id_foreign` (`category_id`),
  ADD KEY `financial_transactions_person_id_foreign` (`person_id`),
  ADD KEY `financial_transactions_loan_id_foreign` (`loan_id`),
  ADD KEY `financial_transactions_savings_goal_id_foreign` (`savings_goal_id`),
  ADD KEY `financial_transactions_recurring_transaction_id_foreign` (`recurring_transaction_id`),
  ADD KEY `financial_transactions_reversal_of_id_foreign` (`reversal_of_id`),
  ADD KEY `fin_tx_user_date_idx` (`user_id`,`transaction_date`),
  ADD KEY `fin_tx_user_type_date_idx` (`user_id`,`type`,`transaction_date`),
  ADD KEY `fin_tx_user_status_idx` (`user_id`,`status`);

--
-- Indexes for table `financial_transaction_tag`
--
ALTER TABLE `financial_transaction_tag`
  ADD PRIMARY KEY (`financial_transaction_id`,`tag_id`),
  ADD KEY `financial_transaction_tag_tag_id_foreign` (`tag_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ledger_accounts`
--
ALTER TABLE `ledger_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ledger_accounts_currency_id_foreign` (`currency_id`),
  ADD KEY `ledger_accounts_parent_id_foreign` (`parent_id`),
  ADD KEY `ledger_accounts_user_id_kind_type_index` (`user_id`,`kind`,`type`),
  ADD KEY `ledger_accounts_user_id_is_archived_index` (`user_id`,`is_archived`),
  ADD KEY `ledger_accounts_system_code_index` (`system_code`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loans_person_id_foreign` (`person_id`),
  ADD KEY `loans_currency_id_foreign` (`currency_id`),
  ADD KEY `loans_ledger_account_id_foreign` (`ledger_account_id`),
  ADD KEY `loans_user_id_direction_status_index` (`user_id`,`direction`,`status`),
  ADD KEY `loans_user_id_due_date_index` (`user_id`,`due_date`);

--
-- Indexes for table `loan_payments`
--
ALTER TABLE `loan_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loan_payment_tx_unique` (`financial_transaction_id`),
  ADD KEY `loan_payments_loan_id_foreign` (`loan_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`id`),
  ADD KEY `people_user_id_name_index` (`user_id`,`name`);

--
-- Indexes for table `recurring_transactions`
--
ALTER TABLE `recurring_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recurring_transactions_currency_id_foreign` (`currency_id`),
  ADD KEY `recurring_transactions_source_account_id_foreign` (`source_account_id`),
  ADD KEY `recurring_transactions_destination_account_id_foreign` (`destination_account_id`),
  ADD KEY `recurring_transactions_category_id_foreign` (`category_id`),
  ADD KEY `recurring_transactions_person_id_foreign` (`person_id`),
  ADD KEY `recurring_transactions_is_active_next_run_at_index` (`is_active`,`next_run_at`),
  ADD KEY `recurring_transactions_user_id_is_active_index` (`user_id`,`is_active`);

--
-- Indexes for table `savings_allocations`
--
ALTER TABLE `savings_allocations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `saving_allocation_tx_unique` (`financial_transaction_id`),
  ADD KEY `savings_allocations_savings_goal_id_foreign` (`savings_goal_id`);

--
-- Indexes for table `savings_goals`
--
ALTER TABLE `savings_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `savings_goals_account_id_foreign` (`account_id`),
  ADD KEY `savings_goals_currency_id_foreign` (`currency_id`),
  ADD KEY `savings_goals_user_id_is_completed_index` (`user_id`,`is_completed`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tags_user_id_name_unique` (`user_id`,`name`);

--
-- Indexes for table `transaction_entries`
--
ALTER TABLE `transaction_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_entries_financial_transaction_id_foreign` (`financial_transaction_id`),
  ADD KEY `tx_entry_ledger_tx_idx` (`ledger_account_id`,`financial_transaction_id`),
  ADD KEY `tx_entry_loan_tx_idx` (`loan_id`,`financial_transaction_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_settings_user_id_unique` (`user_id`),
  ADD KEY `user_settings_base_currency_id_foreign` (`base_currency_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ledger_accounts`
--
ALTER TABLE `ledger_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loan_payments`
--
ALTER TABLE `loan_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `people`
--
ALTER TABLE `people`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `recurring_transactions`
--
ALTER TABLE `recurring_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `savings_allocations`
--
ALTER TABLE `savings_allocations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `savings_goals`
--
ALTER TABLE `savings_goals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transaction_entries`
--
ALTER TABLE `transaction_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_financial_transaction_id_foreign` FOREIGN KEY (`financial_transaction_id`) REFERENCES `financial_transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attachments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attachments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budgets_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `budgets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ledger_account_id_foreign` FOREIGN KEY (`ledger_account_id`) REFERENCES `ledger_accounts` (`id`),
  ADD CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD CONSTRAINT `financial_transactions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `financial_transactions_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `financial_transactions_destination_account_id_foreign` FOREIGN KEY (`destination_account_id`) REFERENCES `ledger_accounts` (`id`),
  ADD CONSTRAINT `financial_transactions_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`),
  ADD CONSTRAINT `financial_transactions_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_recurring_transaction_id_foreign` FOREIGN KEY (`recurring_transaction_id`) REFERENCES `recurring_transactions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_reversal_of_id_foreign` FOREIGN KEY (`reversal_of_id`) REFERENCES `financial_transactions` (`id`),
  ADD CONSTRAINT `financial_transactions_savings_goal_id_foreign` FOREIGN KEY (`savings_goal_id`) REFERENCES `savings_goals` (`id`),
  ADD CONSTRAINT `financial_transactions_source_account_id_foreign` FOREIGN KEY (`source_account_id`) REFERENCES `ledger_accounts` (`id`),
  ADD CONSTRAINT `financial_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `financial_transaction_tag`
--
ALTER TABLE `financial_transaction_tag`
  ADD CONSTRAINT `financial_transaction_tag_financial_transaction_id_foreign` FOREIGN KEY (`financial_transaction_id`) REFERENCES `financial_transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `financial_transaction_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ledger_accounts`
--
ALTER TABLE `ledger_accounts`
  ADD CONSTRAINT `ledger_accounts_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `ledger_accounts_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `ledger_accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ledger_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `loans_ledger_account_id_foreign` FOREIGN KEY (`ledger_account_id`) REFERENCES `ledger_accounts` (`id`),
  ADD CONSTRAINT `loans_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`),
  ADD CONSTRAINT `loans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_payments`
--
ALTER TABLE `loan_payments`
  ADD CONSTRAINT `loan_payments_financial_transaction_id_foreign` FOREIGN KEY (`financial_transaction_id`) REFERENCES `financial_transactions` (`id`),
  ADD CONSTRAINT `loan_payments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `people`
--
ALTER TABLE `people`
  ADD CONSTRAINT `people_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recurring_transactions`
--
ALTER TABLE `recurring_transactions`
  ADD CONSTRAINT `recurring_transactions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `recurring_transactions_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `recurring_transactions_destination_account_id_foreign` FOREIGN KEY (`destination_account_id`) REFERENCES `ledger_accounts` (`id`),
  ADD CONSTRAINT `recurring_transactions_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recurring_transactions_source_account_id_foreign` FOREIGN KEY (`source_account_id`) REFERENCES `ledger_accounts` (`id`),
  ADD CONSTRAINT `recurring_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `savings_allocations`
--
ALTER TABLE `savings_allocations`
  ADD CONSTRAINT `savings_allocations_financial_transaction_id_foreign` FOREIGN KEY (`financial_transaction_id`) REFERENCES `financial_transactions` (`id`),
  ADD CONSTRAINT `savings_allocations_savings_goal_id_foreign` FOREIGN KEY (`savings_goal_id`) REFERENCES `savings_goals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `savings_goals`
--
ALTER TABLE `savings_goals`
  ADD CONSTRAINT `savings_goals_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `ledger_accounts` (`id`),
  ADD CONSTRAINT `savings_goals_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `savings_goals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `tags_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaction_entries`
--
ALTER TABLE `transaction_entries`
  ADD CONSTRAINT `transaction_entries_financial_transaction_id_foreign` FOREIGN KEY (`financial_transaction_id`) REFERENCES `financial_transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_entries_ledger_account_id_foreign` FOREIGN KEY (`ledger_account_id`) REFERENCES `ledger_accounts` (`id`),
  ADD CONSTRAINT `transaction_entries_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`);

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_base_currency_id_foreign` FOREIGN KEY (`base_currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `user_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
