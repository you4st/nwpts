-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Jan 12, 2016 at 02:46 PM
-- Server version: 5.5.47-cll
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `nwpts_admin`
--
CREATE DATABASE IF NOT EXISTS `nwpts_admin` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `nwpts_admin`;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `email` varchar(60) NOT NULL,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `passcode` varchar(45) DEFAULT NULL,
  `user_type` tinyint(4) NOT NULL DEFAULT 1,
  `rel_id` int(11) DEFAULT NULL,
  `last_updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
--
-- Table structure for table `course`
--

DROP TABLE IF EXISTS `course`;
CREATE TABLE IF NOT EXISTS `course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` varchar(16) NOT NULL,
  `name` varchar(128) NOT NULL,
  `kor_name` varchar(128) NOT NULL,
  `description` varchar(512) NOT NULL,
  `credit` int(11) NOT NULL,
  `degree` varchar(10) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1, 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
CREATE TABLE IF NOT EXISTS `student` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) NOT NULL,
  `email` varchar(64) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `street` varchar(128) DEFAULT NULL,
  `city` varchar(64) DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `zip` varchar(5) DEFAULT NULL,
  `birth_date` varchar(10) NOT NULL,
  `major` tinyint(4) NOT NULL,
  `start_year` year(4) NOT NULL,
  `start_semester` varchar(10) NOT NULL,
  `grad_year` year(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `faculty`
--

DROP TABLE IF EXISTS `faculty`;
CREATE TABLE IF NOT EXISTS `faculty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `faculty_id` int(11) NOT NULL,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) NOT NULL,
  `email` varchar(128) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `birth_date` varchar(10) NOT NULL,
  `start_year` year(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `faculty_id` (`faculty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `takes`
--

DROP TABLE IF EXISTS `takes`;
CREATE TABLE IF NOT EXISTS `takes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` varchar(16) NOT NULL,
  `year` year(4) NOT NULL,
  `semester` varchar(10) NOT NULL,
  `grade` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `payment_type`
--

DROP TABLE IF EXISTS `payment_type`;
CREATE TABLE IF NOT EXISTS `payment_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO payment_type (type) VALUES ('charged'), ('paid'), ('CR');

--
-- Table structure for table `payment_reason_code`
--

DROP TABLE IF EXISTS `payment_reason_code`;
CREATE TABLE IF NOT EXISTS `payment_reason_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reason_code` varchar(10) NOT NULL,
  `reason` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO payment_reason_code (reason_code, reason) VALUES ('CC','Course Charge'),('TF','Technology Fee'),('SP','Student Payment'),('FA','Financial Aid'),('CR','Credits'),('RF','Refund');

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(6,2) NOT NULL,
  `date` DATE NOT NULL,
  `reason_code` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
