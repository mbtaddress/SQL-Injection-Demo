-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 20, 2021 at 08:20 PM
-- Server version: 10.4.18-MariaDB
-- PHP Version: 7.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sql_demo`
--

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `age` int(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `account_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `firstname`, `lastname`, `age`, `email`, `phone`, `password`, `account_type`) VALUES
(1, 'admin', 'admin', 32, 'admin@gami.com', '09212321312', 'admin', 'admin'),
(2, 'manage', 'manager', 43, 'manager@gmail.com', '092312423', 'manager', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `secret`
--

CREATE TABLE `secret` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `filepath` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `secret`
--

INSERT INTO `secret` (`id`, `name`, `filepath`) VALUES
(1, 'AWS Production Keys', '/var/secrets/aws_prod.pem'),
(2, 'Database Backup', '/backups/sql_dump_2024.sql'),
(3, 'API Master Key', '/config/api_master_key.txt'),
(4, 'Employee Salary Sheet', '/hr/salaries_2024.xlsx');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstname` varchar(200) DEFAULT NULL,
  `lastname` varchar(200) DEFAULT NULL,
  `age` int(10) NOT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone` varchar(200) DEFAULT NULL,
  `password` varchar(200) DEFAULT NULL,
  `account_type` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `firstname`, `lastname`, `age`, `email`, `phone`, `password`, `account_type`) VALUES
(8, 'tesh', 'besu', 21, 'besu@gmail.com', '0922101673', '123456', 'admin'),
(13, 'abc', 'abc', 45, 'abc@gmail.com', '25', 'Test', 'user'),
(14, 'aaaa', 'aaaaa', 87, 'aaaaaabel@gmail.com', '159875364', '1234', 'user'),
(16, 'sdasdasa', 'aasasa', 25, 'asasa@sasa.c', 'asasas', 'Test', 'user'),
(17, 'hhhh', 'hhhh', 24, 'hhhh@ffff.k', '444444', 'Test', 'user'),
(18, 'nnn', 'nnn', 87, 'abel@gmail.comnnnn', '454', '45\'); DELETE from table admins', 'user'),
(19, 'yado', 'yao', 98, 'yado@asasas.asdas', '121', '987456', 'user'),
(21, 'zzzz', 'zzzzzz', 0, 'zz@zzz.zz', '1245', 'Test', 'user'),
(22, 'ddddd', 'dddd', 0, 'ddd@ddd.ddd', '99999', 'Test', 'user'),
(23, 'abebe', 'abebe', 0, 'abebe@gmail.com', '0922101673', 'Test', 'user'),
(24, 'a', 'a', 0, 'a@gmail.com', '022', 'Test', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `secret`
--
ALTER TABLE `secret`
  ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------

--
-- Table for Second-Order Injection demo
--

CREATE TABLE IF NOT EXISTS `second_order_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(200) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Seed data for second_order_users
--

INSERT INTO `second_order_users` (`email`, `username`, `password`) VALUES
('alice@demo.com', 'alice', 'password1'),
('bob@demo.com', 'bob', 'password2');

-- --------------------------------------------------------

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `secret`
--
ALTER TABLE `secret`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `second_order_users`
--
ALTER TABLE `second_order_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
