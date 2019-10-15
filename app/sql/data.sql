-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 15, 2019 at 06:14 AM
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

--
-- Dumping data for table `bidding_round`
--

INSERT INTO `bidding_round` (`round`, `status`) VALUES
(1, 'Not Started');

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`course`, `school`, `title`, `description`, `examdate`, `examstart`, `examend`) VALUES
('ECON001', 'SOE', 'Microeconomics', 'Microeconomics is about economics in smaller scale (e.g. firm-scale)', '20131101', '15:30:00', '18:45:00'),
('ECON002', 'SOE', 'Macroeconomics', 'You don\'t learn about excel macros here.', '20131101', '08:30:00', '11:45:00'),
('IS100', 'SIS', 'Calculus ', 'The basic objective of Calculus is to relate small-scale (differential) quantities to large-scale (integrated) quantities. This is accomplished by means of the Fundamental Theorem of Calculus. Students should demonstrate an understanding of the integral as a cumulative sum, of the derivative as a rate of change, and of the inverse relationship between integration and differentiation.', '20131119', '08:30:00', '11:45:00'),
('IS101', 'SIS', 'Advanced Calculus', 'This is a second course on calculus. It is more advanced definitely.', '20131118', '12:00:00', '15:15:00'),
('IS102', 'SIS', 'Java programming', 'This course teaches you on Java programming. I love Java definitely.', '20131117', '15:30:00', '18:45:00'),
('IS103', 'SIS', 'Web Programming', 'JSP, Servlets using Tomcat', '20131116', '08:30:00', '11:45:00'),
('IS104', 'SIS', 'Advanced Programming', 'How to write code that nobody can understand', '20131115', '12:00:00', '15:15:00'),
('IS105', 'SIS', 'Data Structures', 'Data structure is a particular way of storing and organizing data in a computer so that it can be used efficiently. Arrays, Lists, Stacks and Trees will be covered.', '20131114', '15:30:00', '18:45:00'),
('IS106', 'SIS', 'Database Modeling & Design', 'Data modeling in software engineering is the process of creating a data model by applying formal data model descriptions using data modeling techniques. ', '20131113', '08:30:00', '11:45:00'),
('IS107', 'SIS', 'IT Outsourcing', 'This course teaches you on how to outsource your programming projects to others.', '20131112', '12:00:00', '15:15:00'),
('IS108', 'SIS', 'Organization Behaviour', 'Organizational Behavior (OB) is the study and application of knowledge about how people, individuals, and groups act in organizations. ', '20131111', '15:30:00', '18:45:00'),
('IS109', 'SIS', 'Cloud Computing', 'Cloud computing is Internet-based computing, whereby shared resources, software and information are provided to computers and other devices on-demand, like the electricity grid.', '20131110', '08:30:00', '11:45:00'),
('IS200', 'SIS', 'Final Touch', 'Learn how eat, dress and talk.', '20131109', '12:00:00', '15:15:00'),
('IS201', 'SIS', 'Fun with Shell Programming', 'Shell scripts are a fundamental part of the UNIX and Linux programming environment.', '20131108', '15:30:00', '18:45:00'),
('IS202', 'SIS', 'Enterprise integration', 'Enterprise integration is a technical field of Enterprise Architecture, which focused on the study of things like system interconnection, electronic data interchange, product data exchange and distributed computing environments, and it\'s possible other solutions.[1', '20131107', '08:30:00', '11:45:00'),
('IS203', 'SIS', 'Software Engineering', 'The Sleepless Era.', '20131106', '12:00:00', '15:15:00'),
('IS204', 'SIS', 'Database System Administration', 'Database administration is a complex, often thankless chore.', '20131105', '15:30:00', '18:45:00'),
('IS205', 'SIS', 'All Talk, No Action', 'The easiest course of all. We will sit around and talk.', '20131104', '08:30:00', '11:45:00'),
('IS206', 'SIS', 'Operation Research', 'Operations research, also known as operational research, is an interdisciplinary branch of applied mathematics and formal science that uses advanced analytical methods such as mathematical modeling, statistical analysis, and mathematical optimization to arrive at optimal or near-optimal solutions to complex decision-making problems.', '20131103', '12:00:00', '15:15:00'),
('IS207', 'SIS', 'GUI Bloopers', 'Common User Interface Design Don\'ts and Dos', '20131103', '15:30:00', '18:45:00'),
('IS208', 'SIS', 'Artifical Intelligence', 'The science and engineering of making intelligent machine', '20131103', '08:30:00', '11:45:00'),
('IS209', 'SIS', 'Information Storage and Management', 'Information storage and management (ISM) - once a relatively straightforward operation -has developed into a highly sophisticated pillar of information technology, requiring proven technical expertise.', '20131102', '12:00:00', '15:15:00'),
('MGMT001', 'SOB', 'Business,Government, and Society', 'learn the interrelation amongst the three', '20131102', '08:30:00', '11:45:00'),
('MGMT002', 'SOB', 'Technology and World Change', 'As technology changes, so does the world', '20131101', '12:00:00', '15:15:00');

--
-- Dumping data for table `course_completed`
--

INSERT INTO `course_completed` (`userid`, `code`) VALUES
('amy.ng.2009', 'IS100'),
('gary.ng.2009', 'IS100'),
('ben.ng.2009', 'IS102'),
('ben.ng.2009', 'IS103');

--
-- Dumping data for table `prerequisite`
--

INSERT INTO `prerequisite` (`parent_course`, `child_course`) VALUES
('IS101', 'IS100'),
('IS103', 'IS102'),
('IS109', 'IS102'),
('IS104', 'IS103'),
('IS203', 'IS103'),
('IS203', 'IS106'),
('IS204', 'IS106'),
('IS209', 'IS106');

--
-- Dumping data for table `round1_bid`
--

INSERT INTO `round1_bid` (`userid`, `amount`, `code`, `section`) VALUES
('ben.ng.2009', 11.00, 'IS100', 'S1'),
('calvin.ng.2009', 12.00, 'IS100', 'S1'),
('dawn.ng.2009', 13.00, 'IS100', 'S1'),
('eddy.ng.2009', 14.00, 'IS100', 'S1'),
('fred.ng.2009', 15.00, 'IS100', 'S1'),
('gary.ng.2009', 16.00, 'IS100', 'S1'),
('harry.ng.2009', 17.00, 'IS100', 'S1'),
('ian.ng.2009', 18.00, 'IS100', 'S1'),
('larry.ng.2009', 19.00, 'IS100', 'S1'),
('maggie.ng.2009', 20.00, 'IS100', 'S1'),
('neilson.ng.2009', 21.00, 'IS100', 'S1'),
('olivia.ng.2009', 22.00, 'IS100', 'S1'),
('parker.ng.2009', 24.00, 'IS100', 'S1'),
('quiten.ng.2009', 24.00, 'IS100', 'S1'),
('ricky.ng.2009', 24.00, 'IS100', 'S1'),
('steven.ng.2009', 26.00, 'IS100', 'S1'),
('timothy.ng.2009', 27.00, 'IS100', 'S1'),
('ursala.ng.2009', 28.00, 'IS100', 'S1'),
('valarie.ng.2009', 29.00, 'IS100', 'S1'),
('winston.ng.2009', 30.00, 'IS100', 'S1'),
('xavier.ng.2009', 31.00, 'IS100', 'S1'),
('yasir.ng.2009', 32.00, 'IS100', 'S1'),
('zac.ng.2009', 33.00, 'IS100', 'S1');

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`course`, `section`, `day`, `start`, `end`, `instructor`, `venue`, `size`) VALUES
('ECON001', 'S1', 4, '08:30:00', '11:45:00', 'John KHOO', 'Seminar Rm 2-34', 10),
('ECON002', 'S1', 5, '15:30:00', '18:45:00', 'Andy KHOO', 'Seminar Rm 2-35', 10),
('IS100', 'S1', 1, '08:30:00', '11:45:00', 'Albert KHOO', 'Seminar Rm 2-1', 10),
('IS100', 'S2', 2, '12:00:00', '15:15:00', 'Billy KHOO', 'Seminar Rm 2-2', 10),
('IS101', 'S1', 3, '15:30:00', '18:45:00', 'Cheri KHOO', 'Seminar Rm 2-3', 10),
('IS101', 'S2', 4, '08:30:00', '11:45:00', 'Daniel KHOO', 'Seminar Rm 2-4', 10),
('IS101', 'S3', 5, '12:00:00', '15:15:00', 'Ernest KHOO', 'Seminar Rm 2-5', 10),
('IS102', 'S1', 1, '15:30:00', '18:45:00', 'Felicia KHOO', 'Seminar Rm 2-6', 10),
('IS102', 'S2', 2, '08:30:00', '11:45:00', 'Gerald KHOO', 'Seminar Rm 2-7', 10),
('IS102', 'S3', 3, '12:00:00', '15:15:00', 'Henry KHOO', 'Seminar Rm 2-8', 10),
('IS103', 'S1', 4, '15:30:00', '18:45:00', 'Ivy KHOO', 'Seminar Rm 2-9', 10),
('IS103', 'S2', 5, '08:30:00', '11:45:00', 'Jason KHOO', 'Seminar Rm 2-10', 10),
('IS103', 'S3', 1, '12:00:00', '15:15:00', 'Kat KHOO', 'Seminar Rm 2-11', 10),
('IS104', 'S1', 2, '15:30:00', '18:45:00', 'Linn KHOO', 'Seminar Rm 2-12', 10),
('IS104', 'S2', 3, '08:30:00', '11:45:00', 'Michael KHOO', 'Seminar Rm 2-13', 10),
('IS105', 'S1', 4, '12:00:00', '15:15:00', 'Nathaniel KHOO', 'Seminar Rm 2-14', 10),
('IS105', 'S2', 5, '15:30:00', '18:45:00', 'Oreilly KHOO', 'Seminar Rm 2-15', 10),
('IS106', 'S1', 1, '08:30:00', '11:45:00', 'Peter KHOO', 'Seminar Rm 2-16', 10),
('IS106', 'S2', 2, '12:00:00', '15:15:00', 'Queen KHOO', 'Seminar Rm 2-17', 10),
('IS107', 'S1', 3, '15:30:00', '18:45:00', 'Ray KHOO', 'Seminar Rm 2-18', 10),
('IS107', 'S2', 4, '08:30:00', '11:45:00', 'Simon KHOO', 'Seminar Rm 2-19', 10),
('IS108', 'S1', 5, '12:00:00', '15:15:00', 'Tim KHOO', 'Seminar Rm 2-20', 10),
('IS109', 'S1', 2, '08:30:00', '11:45:00', 'Vincent KHOO', 'Seminar Rm 2-22', 10),
('IS109', 'S2', 3, '12:00:00', '15:15:00', 'Winnie KHOO', 'Seminar Rm 2-23', 10),
('IS200', 'S1', 4, '15:30:00', '18:45:00', 'Xtra KHOO', 'Seminar Rm 2-24', 10),
('IS201', 'S1', 5, '08:30:00', '11:45:00', 'Yale KHOO', 'Seminar Rm 2-25', 10),
('IS202', 'S1', 1, '12:00:00', '15:15:00', 'Zen KHOO', 'Seminar Rm 2-26', 10),
('IS203', 'S1', 2, '15:30:00', '18:45:00', 'Anderson KHOO', 'Seminar Rm 2-27', 10),
('IS204', 'S1', 3, '08:30:00', '11:45:00', 'Bing KHOO', 'Seminar Rm 2-28', 10),
('IS205', 'S1', 4, '12:00:00', '15:15:00', 'Carlo KHOO', 'Seminar Rm 2-29', 10),
('IS206', 'S1', 5, '15:30:00', '18:45:00', 'Dickson KHOO', 'Seminar Rm 2-30', 10),
('IS207', 'S1', 1, '08:30:00', '11:45:00', 'Edmund KHOO', 'Seminar Rm 2-31', 10),
('IS208', 'S1', 2, '12:00:00', '15:15:00', 'Febrice KHOO', 'Seminar Rm 2-32', 10),
('MGMT001', 'S1', 3, '08:30:00', '11:45:00', 'Gavin KHOO', 'Seminar Rm 2-33', 10),
('MGMT002', 'S1', 3, '15:30:00', '18:45:00', 'Bob KHOO', 'Seminar Rm 2-37', 10);

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`userid`, `password`, `name`, `school`, `edollar`) VALUES
('amy.ng.2009', 'qwerty128', 'Amy NG', 'SIS', 200.00),
('ben.ng.2009', 'qwerty129', 'Ben NG', 'SIS', 200.00),
('calvin.ng.2009', 'qwerty130', 'Calvin NG', 'SIS', 200.00),
('dawn.ng.2009', 'qwerty131', 'Dawn NG', 'SIS', 200.00),
('eddy.ng.2009', 'qwerty132', 'Eddy NG', 'SIS', 200.00),
('fred.ng.2009', 'qwerty133', 'Fred NG', 'SIS', 200.00),
('gary.ng.2009', 'qwerty134', 'Gary NG', 'SIS', 200.00),
('harry.ng.2009', 'qwerty135', 'Harry NG', 'SIS', 200.00),
('ian.ng.2009', 'qwerty136', 'Ian NG', 'SIS', 200.00),
('jerry.ng.2009', 'qwerty137', 'Jerry NG', 'SIS', 200.00),
('kelly.ng.2009', 'qwerty138', 'Kelly NG', 'SIS', 200.00),
('larry.ng.2009', 'qwerty139', 'Larry NG', 'SIS', 200.00),
('maggie.ng.2009', 'qwerty140', 'Maggie NG', 'SIS', 200.00),
('neilson.ng.2009', 'qwerty141', 'Neilson NG', 'SIS', 200.00),
('olivia.ng.2009', 'qwerty142', 'Olivia NG', 'SIS', 200.00),
('parker.ng.2009', 'qwerty143', 'Parker NG', 'SOE', 200.00),
('quiten.ng.2009', 'qwerty144', 'Quiten NG', 'SOE', 200.00),
('ricky.ng.2009', 'qwerty145', 'Ricky NG', 'SOE', 200.00),
('steven.ng.2009', 'qwerty146', 'Steven NG', 'SOE', 200.00),
('timothy.ng.2009', 'qwerty147', 'Timothy NG', 'SOE', 200.00),
('ursala.ng.2009', 'qwerty148', 'Ursala NG', 'SOE', 200.00),
('valarie.ng.2009', 'qwerty149', 'Valarie NG', 'SOB', 200.00),
('winston.ng.2009', 'qwerty150', 'Winston NG', 'SOB', 200.00),
('xavier.ng.2009', 'qwerty151', 'Xavier NG', 'SOB', 200.00),
('yasir.ng.2009', 'qwerty152', 'Yasir NG', 'SOB', 200.00),
('zac.ng.2009', 'qwerty153', 'Zac NG', 'SOB', 200.00);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
