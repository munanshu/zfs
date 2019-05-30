-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 12, 2018 at 03:59 AM
-- Server version: 10.1.28-MariaDB
-- PHP Version: 7.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zf2db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customers`
--

CREATE TABLE `tbl_customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `code` varchar(40) NOT NULL,
  `is_active` enum('1','0') NOT NULL,
  `email` varchar(40) NOT NULL,
  `mobile` bigint(10) NOT NULL,
  `city_id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `state_id` int(11) NOT NULL,
  `addressLine1` text NOT NULL,
  `addressLine2` text NOT NULL,
  `pincode` varchar(20) NOT NULL,
  `created_on` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_ip` varchar(40) NOT NULL,
  `modified_on` date NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_ip` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_customers`
--

INSERT INTO `tbl_customers` (`customer_id`, `first_name`, `last_name`, `code`, `is_active`, `email`, `mobile`, `city_id`, `country_id`, `state_id`, `addressLine1`, `addressLine2`, `pincode`, `created_on`, `created_by`, `created_ip`, `modified_on`, `modified_by`, `modified_ip`) VALUES
(1, 'sfsdf', 'afdad', '0', '1', 'asdasd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(25, 'sfsdf', 'afdad', '0', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(26, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(27, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(28, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(29, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(30, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(31, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(32, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(33, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(34, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(35, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(36, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(37, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(38, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(39, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, ''),
(40, 'sfsdf', 'afdad', 'sd2sdf3ds', '1', 'asdassfdd@gmail.com', 5454545454, 0, 0, 0, '4', 'adasdsad', '4', '0000-00-00', 0, '', '0000-00-00', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `pass_hash` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_Active` enum('1','0') NOT NULL,
  `created_by` int(5) NOT NULL,
  `created_date` date NOT NULL,
  `modified_by` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `username`, `email`, `pass_hash`, `password`, `is_Active`, `created_by`, `created_date`, `modified_by`) VALUES
(1, 'munanshu', 'munanshu.madaank23@gmail.com', 'd6;%QLeB{hQr}cl%`QvIWoktEPrk5!qx6-}fYc*VLZIK?7O~gF', '4c7324bfe462531e0f831bad8ac0d305', '1', 1, '2018-04-26', 1),
(2, 'testuser', 'test@gmail.com', 'UWTX==1f>.?t7i%<8lzs/kem`:)7@Ixt\"MN>j_%)lD|%/#AGn<', '0ae78053196f6bfa5c86f87c8511532e', '0', 1, '2018-04-26', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_tokens`
--

CREATE TABLE `tbl_user_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` text NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '''1'' => ''access'' , ''2'' => ''refresh''',
  `created_on` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_user_tokens`
--

INSERT INTO `tbl_user_tokens` (`token_id`, `user_id`, `token`, `type`, `created_on`) VALUES
(59, 1, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1MjU3MjIyMDAsImlzcyI6ImxvY2FsaG9zdCIsImlkIjoiZjBkMmUwOTkyZjI4ZWY2MjJmMmRjYTA0ZTdkZTM5OTE0YzNjNTBhNWMxZDI3MGVkYzIzMDkxNmIwODI5M2Y4MlBSTWdlOWoyekE3OVJzU09jN0plV0oyYWo2ZU85bWU3KzFIQTJlVTJ2Z1k9IiwiZXhwIjoxNTI1NzM0MjAwLCJUb2tlbnR5cGUiOiJyZWZyZXNoIn0.i1fB6Muhelzxlk8DZcg1wKWCiIGiEXgTIZzhZ35uH0s', 2, '2018-05-07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_customers`
--
ALTER TABLE `tbl_customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tbl_user_tokens`
--
ALTER TABLE `tbl_user_tokens`
  ADD PRIMARY KEY (`token_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_customers`
--
ALTER TABLE `tbl_customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_user_tokens`
--
ALTER TABLE `tbl_user_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
