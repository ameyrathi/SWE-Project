-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 15, 2019 at 06:13 AM
-- Server version: 5.7.23
-- PHP Version: 7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spm`
--

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

DROP TABLE IF EXISTS `course`;
CREATE TABLE IF NOT EXISTS `course` (
  `course` varchar(255) NOT NULL,
  `school` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `examdate` varchar(255) NOT NULL,
  `examstart` time NOT NULL,
  `examend` time NOT NULL,
  PRIMARY KEY (`course`,`examdate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `prerequisite`
--

DROP TABLE IF EXISTS `prerequisite`;
CREATE TABLE IF NOT EXISTS `prerequisite` (
  `parent_course` varchar(255) NOT NULL,
  `child_course` varchar(255) NOT NULL,
  PRIMARY KEY (`parent_course`,`child_course`),
  KEY `FK2_prerequisite` (`child_course`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
CREATE TABLE IF NOT EXISTS `student` (
  `userid` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `school` varchar(255) NOT NULL,
  `edollar` double(8,2) NOT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `section`
--

DROP TABLE IF EXISTS `section`;
CREATE TABLE IF NOT EXISTS `section` (
  `course` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `day` int(1) NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  `instructor` varchar(255) NOT NULL,
  `venue` varchar(255) NOT NULL,
  `size` int(3) NOT NULL,
  PRIMARY KEY (`course`,`section`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `course_completed`
--

DROP TABLE IF EXISTS `course_completed`;
CREATE TABLE IF NOT EXISTS `course_completed` (
  `userid` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  PRIMARY KEY (`userid`,`code`),
  KEY `FK2_course_completed` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `round1_bid`
--

DROP TABLE IF EXISTS `round1_bid`;
CREATE TABLE IF NOT EXISTS `round1_bid` (
  `userid` varchar(255) NOT NULL,
  `amount` double(8,2) NOT NULL,
  `code` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  PRIMARY KEY (`userid`,`code`,`section`),
  KEY `FK2_round1_bid` (`code`,`section`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `round2_bid`
--

DROP TABLE IF EXISTS `round2_bid`;
CREATE TABLE IF NOT EXISTS `round2_bid` (
  `userid` varchar(255) NOT NULL,
  `amount` double(8,2) NOT NULL,
  `code` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`userid`,`code`,`section`),
  KEY `FK2_round2_bid` (`code`,`section`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `section_results`
--

DROP TABLE IF EXISTS `section_results`;
CREATE TABLE IF NOT EXISTS `section_results` (
  `course` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `min_bid` double(8,2) NOT NULL,
  `vacancies` int(11) NOT NULL,
  PRIMARY KEY (`course`,`section`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bidding_round`
--

DROP TABLE IF EXISTS `bidding_round`;
CREATE TABLE IF NOT EXISTS `bidding_round` (
  `round` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`round`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `round1_successful`
--

DROP TABLE IF EXISTS `round1_successful`;
CREATE TABLE IF NOT EXISTS `round1_successful` (
  `userid` varchar(255) NOT NULL,
  `amount` double(8,2) NOT NULL,
  `code` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  PRIMARY KEY (`userid`,`code`,`section`),
  KEY `FK2_round1_successful` (`code`,`section`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `round2_successful`
--

DROP TABLE IF EXISTS `round2_successful`;
CREATE TABLE IF NOT EXISTS `round2_successful` (
  `userid` varchar(255) NOT NULL,
  `amount` double(8,2) NOT NULL,
  `code` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  PRIMARY KEY (`userid`,`code`,`section`),
  KEY `FK2_round2_successful` (`code`,`section`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `round1_unsuccessful`
--

DROP TABLE IF EXISTS `round1_unsuccessful`;
CREATE TABLE IF NOT EXISTS `round1_unsuccessful` (
  `userid` varchar(255) NOT NULL,
  `amount` double(8,2) NOT NULL,
  `code` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  PRIMARY KEY (`userid`,`code`,`section`),
  KEY `FK2_round1_unsuccessful` (`code`,`section`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



-- --------------------------------------------------------

--
-- Table structure for table `round2_unsuccessful`
--

DROP TABLE IF EXISTS `round2_unsuccessful`;
CREATE TABLE IF NOT EXISTS `round2_unsuccessful` (
  `userid` varchar(255) NOT NULL,
  `amount` double(8,2) NOT NULL,
  `code` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  PRIMARY KEY (`userid`,`code`,`section`),
  KEY `FK2_round2_unsuccessful` (`code`,`section`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course_completed`
--
ALTER TABLE `course_completed`
  ADD CONSTRAINT `FK1_course_completed` FOREIGN KEY (`userid`) REFERENCES `student` (`userid`),
  ADD CONSTRAINT `FK2_course_completed` FOREIGN KEY (`code`) REFERENCES `course` (`course`);

--
-- Constraints for table `prerequisite`
--
ALTER TABLE `prerequisite`
  ADD CONSTRAINT `FK1_prerequisite` FOREIGN KEY (`parent_course`) REFERENCES `course` (`course`),
  ADD CONSTRAINT `FK2_prerequisite` FOREIGN KEY (`child_course`) REFERENCES `course` (`course`);

--
-- Constraints for table `round1_bid`
--
ALTER TABLE `round1_bid`
  ADD CONSTRAINT `FK1_round1_bid` FOREIGN KEY (`userid`) REFERENCES `student` (`userid`),
  ADD CONSTRAINT `FK2_round1_bid` FOREIGN KEY (`code`,`section`) REFERENCES `section` (`course`, `section`);

--
-- Constraints for table `round1_successful`
--
ALTER TABLE `round1_successful`
  ADD CONSTRAINT `FK1_round1_successful` FOREIGN KEY (`userid`) REFERENCES `student` (`userid`),
  ADD CONSTRAINT `FK2_round1_successful` FOREIGN KEY (`code`,`section`) REFERENCES `section` (`course`, `section`);

--
-- Constraints for table `round1_unsuccessful`
--
ALTER TABLE `round1_unsuccessful`
  ADD CONSTRAINT `FK1_round1_unsuccessful` FOREIGN KEY (`userid`) REFERENCES `student` (`userid`),
  ADD CONSTRAINT `FK2_round1_unsuccessful` FOREIGN KEY (`code`,`section`) REFERENCES `section` (`course`, `section`);

--
-- Constraints for table `round2_bid`
--
ALTER TABLE `round2_bid`
  ADD CONSTRAINT `FK1_round2_bid` FOREIGN KEY (`userid`) REFERENCES `student` (`userid`),
  ADD CONSTRAINT `FK2_round2_bid` FOREIGN KEY (`code`,`section`) REFERENCES `section` (`course`, `section`);

--
-- Constraints for table `round2_successful`
--
ALTER TABLE `round2_successful`
  ADD CONSTRAINT `FK1_round2_successful` FOREIGN KEY (`userid`) REFERENCES `student` (`userid`),
  ADD CONSTRAINT `FK2_round2_successful` FOREIGN KEY (`code`,`section`) REFERENCES `section` (`course`, `section`);

--
-- Constraints for table `round2_unsuccessful`
--
ALTER TABLE `round2_unsuccessful`
  ADD CONSTRAINT `FK1_round2_unsuccessful` FOREIGN KEY (`userid`) REFERENCES `student` (`userid`),
  ADD CONSTRAINT `FK2_round2_unsuccessful` FOREIGN KEY (`code`,`section`) REFERENCES `section` (`course`, `section`);

--
-- Constraints for table `section`
--
ALTER TABLE `section`
  ADD CONSTRAINT `FK_section` FOREIGN KEY (`course`) REFERENCES `course` (`course`);

--
-- Constraints for table `section_results`
--
ALTER TABLE `section_results`
  ADD CONSTRAINT `FK1_section_results` FOREIGN KEY (`course`,`section`) REFERENCES `section` (`course`, `section`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
