-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 07, 2026 at 02:52 PM
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
-- Database: `coursecompass`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `activity` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollees`
--

CREATE TABLE `enrollees` (
  `enrollee_id` int(11) NOT NULL,
  `lrn_no` varchar(50) NOT NULL,
  `application_date` date DEFAULT NULL,
  `pwd_id` varchar(50) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `sex` varchar(20) DEFAULT NULL,
  `disability_type` varchar(255) DEFAULT NULL,
  `disability_cause` varchar(100) DEFAULT NULL,
  `specific_disability` varchar(255) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `educational_attainment` varchar(100) DEFAULT NULL,
  `employment_status` varchar(100) DEFAULT NULL,
  `employment_category` varchar(100) DEFAULT NULL,
  `employment_nature` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `parent_status` varchar(100) DEFAULT NULL,
  `siblings_count` int(11) DEFAULT NULL,
  `working_family_members` int(11) DEFAULT NULL,
  `monthly_income_head` decimal(10,2) DEFAULT NULL,
  `total_family_income` decimal(10,2) DEFAULT NULL,
  `family_type` varchar(100) DEFAULT NULL,
  `comelec_registered` varchar(10) DEFAULT NULL,
  `four_ps_member` varchar(10) DEFAULT NULL,
  `covid_vaccinated` varchar(10) DEFAULT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `guardian_contact` varchar(50) DEFAULT NULL,
  `house_tagging` varchar(100) DEFAULT NULL,
  `teacher_name` varchar(255) DEFAULT NULL,
  `photo` longblob DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `previous_level` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollees`
--

INSERT INTO `enrollees` (`enrollee_id`, `lrn_no`, `application_date`, `pwd_id`, `last_name`, `first_name`, `middle_name`, `age`, `birthday`, `sex`, `disability_type`, `disability_cause`, `specific_disability`, `civil_status`, `address`, `barangay`, `contact_no`, `educational_attainment`, `employment_status`, `employment_category`, `employment_nature`, `occupation`, `father_name`, `mother_name`, `parent_status`, `siblings_count`, `working_family_members`, `monthly_income_head`, `total_family_income`, `family_type`, `comelec_registered`, `four_ps_member`, `covid_vaccinated`, `guardian_name`, `guardian_contact`, `house_tagging`, `teacher_name`, `photo`, `created_at`, `previous_level`) VALUES
(2913, '198912225902', '2023-03-24', 'PWD-14165', 'Pascual', 'Juan', 'Mendoza', 14, '2009-08-27', 'Male', 'Intellectual Disability', 'Yes', 'Severe', 'Single', '575 Mabini St.', 'Barangay 9', '09550455977', 'JUNIOR HIGH SCHOOL', 'Student', '', '', '', 'Ronel Pascual', 'Lorna Pascual', 'Single Parent', 0, 1, 0.00, 15000.00, 'Extended', 'No', 'No', 'Yes', 'Cristina Pascual', '09919795579', 'Not Tagged', 'Ms. Dela Cruz, R.', '', '2026-02-27 10:27:39', 'Grade 1'),
(2914, '516278088865', '2022-06-11', 'PWD-80284', 'Garcia', 'Felix', 'Torres', 25, '1999-07-20', 'Male', 'Down Syndrome', 'Yes', 'Moderate', 'Married', '592 Mabini St.', 'Barangay 2', '09149203558', 'CARER PROGRAM', 'Student', '', '', '', 'Angelo Garcia', 'Luz Garcia', 'Both Living', 1, 3, 0.00, 15000.00, 'Extended', 'No', 'No', 'Yes', 'Rowena Garcia', '09481469012', 'Tagged', 'Ms. Reyes, M.', NULL, '2026-02-27 10:27:39', 'HOME PROGRAM'),
(2915, '459422681391', '2022-04-25', 'PWD-38785', 'Dela Torre', 'Henry', 'Cruz', 18, '2005-08-23', 'Male', 'Cerebral Palsy', 'No', 'Mild', 'Married', '411 Bonifacio Ave.', 'Barangay 2', '09326541099', 'CARER PROGRAM', 'Unemployed', '', '', '', 'Mark Dela Torre', 'Norma Dela Torre', 'Single Parent', 7, 1, 0.00, 20000.00, 'Extended', 'Yes', 'Yes', 'No', 'Lorna Dela Torre', '09560027313', 'Not Tagged', 'Ms. Reyes, M.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM II'),
(2916, '754648192232', '2022-05-11', 'PWD-99192', 'Lim', 'Antonio', 'Aquino', 18, '2006-01-30', 'Male', 'Intellectual Disability', 'Yes', 'Moderate', 'Married', '542 Bonifacio Ave.', 'Barangay 9', '09112327652', 'CARER PROGRAM', 'Student', '', '', '', 'Francis Lim', 'Grace Lim', 'Orphan', 5, 0, 0.00, 8000.00, 'Extended', 'No', 'Yes', 'Yes', 'Maria Lim', '09875340444', 'Not Tagged', 'Ms. Flores, C.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM IV'),
(2917, '220342736258', '2024-01-14', 'PWD-74042', 'Espiritu', 'Emmanuel', 'Reyes', 29, '1994-11-11', 'Male', 'Learning Disability', 'Yes', 'Mild', 'Single', '247 Aguinaldo St.', 'Barangay 2', '09191969690', 'ALTERNATIVE LEARNING SCHOOL (ALS)', 'Employed', 'Self-employed', 'Full-time', 'Store Helper', 'Miguel Espiritu', 'Wilma Espiritu', 'Both Living', 2, 3, 10000.00, 20000.00, 'Nuclear', 'No', 'No', 'No', 'Wilma Espiritu', '09910959828', 'Tagged', 'Ms. Reyes, M.', NULL, '2026-02-27 10:27:39', ''),
(2918, '351487177987', '2023-03-28', 'PWD-87110', 'Morales', 'Rowena', 'Bautista', 21, '2002-01-08', 'Female', 'Autism Spectrum Disorder', 'Yes', 'Mild', 'Single', '235 Rizal St.', 'Barangay 1', '09454794895', 'SENIOR HIGH SCHOOL', 'Unemployed', '', '', '', 'Emmanuel Morales', 'Marjorie Morales', 'Single Parent', 7, 1, 0.00, 5000.00, 'Nuclear', 'No', 'Yes', 'Yes', 'Aileen Morales', '09304451095', 'Tagged', 'Ms. Dela Cruz, R.', NULL, '2026-02-27 10:27:39', 'Grade 11'),
(2919, '311521368324', '2023-01-25', 'PWD-24322', 'Castillo', 'Rowena', 'Flores', 19, '2005-07-03', 'Female', 'Intellectual Disability', 'No', 'Moderate', 'Single', '433 Mabini St.', 'Barangay 5', '09596743109', 'CARER PROGRAM', 'Student', '', '', '', 'Miguel Castillo', 'Glenda Castillo', 'Orphan', 1, 0, 0.00, 10000.00, 'Nuclear', 'Yes', 'Yes', 'Yes', 'Aileen Castillo', '09621453189', 'Not Tagged', 'Mr. Santos, J.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM II'),
(2920, '635661484746', '2022-11-14', 'PWD-96752', 'Torres', 'Ana', 'Flores', 9, '2014-07-14', 'Female', 'Cerebral Palsy', 'No', 'Moderate', 'Single', '992 Rizal St.', 'Barangay 10', '09889991777', 'COLLEGE', 'Unemployed', '', '', '', 'Ramon Torres', 'Ana Torres', 'Both Living', 7, 4, 0.00, 20000.00, 'Nuclear', 'Yes', 'Yes', 'Yes', 'Luz Torres', '09738914080', 'Tagged', 'Mr. Santos, J.', NULL, '2026-02-27 10:27:39', '1ST YEAR'),
(2921, '445842718357', '2023-06-19', 'PWD-84085', 'Rivera', 'Rosario', 'Villanueva', 12, '2011-07-29', 'Female', 'Learning Disability', 'Yes', 'Severe', 'Married', '245 Bonifacio Ave.', 'Barangay 7', '09240529481', 'CARER PROGRAM', 'Unemployed', '', '', '', 'Ronel Rivera', 'Marites Rivera', 'Both Living', 0, 3, 0.00, 20000.00, 'Nuclear', 'Yes', 'Yes', 'No', 'Carla Rivera', '09474745382', 'Tagged', 'Mr. Santos, J.', NULL, '2026-02-27 10:27:39', 'EARLY INTERVENTION (by 2\'s)'),
(2922, '829851334750', '2022-08-01', 'PWD-49240', 'Torres', 'Lovely', 'Aquino', 25, '1998-05-18', 'Female', 'Learning Disability', 'Yes', 'Moderate', 'Single', '912 Rizal St.', 'Barangay 9', '09266910922', 'CARER PROGRAM', 'Student', '', '', '', 'Angelo Torres', 'Erlinda Torres', 'Both Living', 5, 1, 0.00, 20000.00, 'Extended', 'No', 'No', 'No', 'Luz Torres', '09781057736', 'Not Tagged', 'Ms. Reyes, M.', NULL, '2026-02-27 10:27:39', 'GROUP TUTORIAL'),
(2923, '859707157168', '2022-11-02', 'PWD-24663', 'Villanueva', 'Juan', 'Cruz', 18, '2006-07-13', 'Male', 'Cerebral Palsy', 'No', 'Mild', 'Married', '597 Aguinaldo St.', 'Barangay 3', '09561479973', 'CARER PROGRAM', 'Student', '', '', '', 'Jose Villanueva', 'Lovely Villanueva', 'Single Parent', 0, 2, 0.00, 5000.00, 'Nuclear', 'Yes', 'Yes', 'Yes', 'Wilma Villanueva', '09536344399', 'Tagged', 'Mr. Santos, J.', NULL, '2026-02-27 10:27:39', 'DEPED SPED/SNED'),
(2924, '963972176017', '2022-08-10', 'PWD-44970', 'Rivera', 'Eduardo', 'Flores', 16, '2008-01-27', 'Male', 'Down Syndrome', 'No', 'Mild', 'Married', '228 Mabini St.', 'Barangay 8', '09475442872', 'CARER PROGRAM', 'Student', '', '', '', 'John Rivera', 'Marjorie Rivera', 'Both Living', 3, 3, 0.00, 8000.00, 'Extended', 'No', 'Yes', 'No', 'Rowena Rivera', '09788785773', 'Not Tagged', 'Ms. Flores, C.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM IV'),
(2925, '966417503705', '2023-10-05', 'PWD-55309', 'Garcia', 'Maria', 'Mendoza', 16, '2008-03-22', 'Female', 'Intellectual Disability', 'Yes', 'Severe', 'Single', '395 Aguinaldo St.', 'Barangay 4', '09373506211', 'CARER PROGRAM', 'Student', '', '', '', 'Ricardo Garcia', 'Maria Garcia', 'Orphan', 3, 2, 0.00, 15000.00, 'Extended', 'Yes', 'No', 'Yes', 'Rosario Garcia', '09872830248', 'Not Tagged', 'Ms. Flores, C.', NULL, '2026-02-27 10:27:39', 'GROUP TUTORIAL'),
(2926, '775057307204', '2023-09-09', 'PWD-98777', 'Villanueva', 'Aileen', 'Flores', 24, '2000-02-20', 'Female', 'Autism Spectrum Disorder', 'Yes', 'Severe', 'Single', '312 Bonifacio Ave.', 'Barangay 4', '09561588882', 'SENIOR HIGH SCHOOL', 'Unemployed', '', '', '', 'Ronel Villanueva', 'Glenda Villanueva', 'Single Parent', 3, 4, 0.00, 15000.00, 'Extended', 'Yes', 'Yes', 'No', 'Tessie Villanueva', '09812807629', 'Not Tagged', 'Ms. Dela Cruz, R.', NULL, '2026-02-27 10:27:39', 'Grade 12'),
(2927, '734065014236', '2023-02-03', 'PWD-69692', 'Aquino', 'Angelo', 'Garcia', 12, '2012-12-14', 'Male', 'Autism Spectrum Disorder', 'No', 'Moderate', 'Married', '410 Mabini St.', 'Barangay 3', '09804436922', 'CARER PROGRAM', 'Student', '', '', '', 'Carlos Aquino', 'Aileen Aquino', 'Both Living', 2, 4, 0.00, 5000.00, 'Extended', 'Yes', 'Yes', 'Yes', 'Glenda Aquino', '09243171768', 'Not Tagged', 'Ms. Flores, C.', NULL, '2026-02-27 10:27:39', 'ON THE JOB TRAINING (OJT)'),
(2928, '943004633186', '2024-12-03', 'PWD-93579', 'Uy', 'Glenda', 'Bautista', 20, '2004-05-11', 'Female', 'Cerebral Palsy', 'No', 'Severe', 'Single', '282 Quezon Blvd.', 'Barangay 2', '09866162966', 'COLLEGE', 'Unemployed', '', '', '', 'John Uy', 'Grace Uy', 'Single Parent', 5, 4, 0.00, 8000.00, 'Nuclear', 'Yes', 'Yes', 'No', 'Sheryl Uy', '09845122940', 'Tagged', 'Mr. Santos, J.', NULL, '2026-02-27 10:27:39', '1ST YEAR'),
(2929, '868567484183', '2022-02-10', 'PWD-86556', 'Castillo', 'Ricardo', 'Torres', 22, '2002-08-13', 'Male', 'Intellectual Disability', 'Yes', 'Moderate', 'Married', '7 Bonifacio Ave.', 'Barangay 5', '09909037759', 'CARER PROGRAM', 'Unemployed', '', '', '', 'Ricardo Castillo', 'Wilma Castillo', 'Orphan', 3, 3, 0.00, 15000.00, 'Nuclear', 'No', 'No', 'No', 'Maria Castillo', '09517532243', 'Not Tagged', 'Mr. Garcia, A.', NULL, '2026-02-27 10:27:39', 'EARLY INTERVENTION (by 2\'s)'),
(2930, '458110524275', '2023-03-10', 'PWD-44099', 'Espiritu', 'Ronel', 'Cruz', 30, '1994-04-11', 'Male', 'Cerebral Palsy', 'Yes', 'Moderate', 'Married', '780 Quezon Blvd.', 'Barangay 5', '09907464398', 'CARER PROGRAM', 'Employed', '', 'Full-time', '', 'Paolo Espiritu', 'Luz Espiritu', 'Single Parent', 0, 4, 3000.00, 8000.00, 'Nuclear', 'No', 'Yes', 'No', 'Ana Espiritu', '09909851739', 'Tagged', 'Mr. Santos, J.', NULL, '2026-02-27 10:27:39', 'EARLY INTERVENTION (by 2\'s)'),
(2931, '766440579767', '2022-08-23', 'PWD-58351', 'Uy', 'Juan', 'Cruz', 19, '2004-06-16', 'Male', 'Intellectual Disability', 'Yes', 'Moderate', 'Single', '593 Rizal St.', 'Barangay 5', '09718257024', 'CARER PROGRAM', 'Unemployed', '', '', '', 'Felix Uy', 'Cristina Uy', 'Both Living', 3, 0, 0.00, 10000.00, 'Extended', 'Yes', 'Yes', 'No', 'Wilma Uy', '09559967386', 'Not Tagged', 'Ms. Dela Cruz, R.', NULL, '2026-02-27 10:27:39', 'TUTORIAL PROGRAM'),
(2932, '295143931370', '2024-12-04', 'PWD-30052', 'Perez', 'Maria', 'Flores', 17, '2007-10-29', 'Female', 'Cerebral Palsy', 'No', 'Moderate', 'Married', '477 Quezon Blvd.', 'Barangay 10', '09388187517', 'CARER PROGRAM', 'Student', '', '', '', 'John Perez', 'Luz Perez', 'Single Parent', 7, 1, 0.00, 15000.00, 'Extended', 'No', 'No', 'No', 'Norma Perez', '09448981305', 'Tagged', 'Mr. Garcia, A.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM IV'),
(2933, '891310598800', '2024-04-12', 'PWD-21221', 'Soriano', 'Luis', 'Mendoza', 20, '2004-05-15', 'Male', 'Down Syndrome', 'Yes', 'Severe', 'Single', '708 Quezon Blvd.', 'Barangay 8', '09581223755', 'COLLEGE', 'Student', '', '', '', 'Miguel Soriano', 'Lovely Soriano', 'Both Living', 6, 1, 0.00, 8000.00, 'Extended', 'No', 'No', 'Yes', 'Aileen Soriano', '09900942073', 'Not Tagged', 'Ms. Reyes, M.', NULL, '2026-02-27 10:27:39', '1ST YEAR'),
(2934, '337045863496', '2024-09-18', 'PWD-34267', 'Dela Cruz', 'Grace', 'Mendoza', 15, '2008-10-10', 'Female', 'Down Syndrome', 'Yes', 'Severe', 'Married', '103 Del Pilar St.', 'Barangay 4', '09418092217', 'CARER PROGRAM', 'Student', '', '', '', 'Luis Dela Cruz', 'Jennifer Dela Cruz', 'Single Parent', 0, 4, 0.00, 20000.00, 'Nuclear', 'No', 'Yes', 'Yes', 'Wilma Dela Cruz', '09413679331', 'Tagged', 'Mr. Garcia, A.', NULL, '2026-02-27 10:27:39', 'DEPED SPED/SNED'),
(2935, '731678373015', '2022-04-20', 'PWD-74454', 'Sy', 'Juan', 'Mendoza', 25, '1998-06-05', 'Male', 'Down Syndrome', 'Yes', 'Mild', 'Married', '88 Mabini St.', 'Barangay 2', '09699226355', 'CARER PROGRAM', 'Employed', 'Self-employed', 'Part-time', 'Store Helper', 'Henry Sy', 'Erlinda Sy', 'Orphan', 3, 4, 0.00, 15000.00, 'Extended', 'No', 'No', 'No', 'Lorna Sy', '09560452529', 'Not Tagged', 'Ms. Flores, C.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM II'),
(2936, '290008774121', '2022-06-03', 'PWD-30585', 'Pascual', 'Henry', 'Reyes', 26, '1997-06-10', 'Male', 'Autism Spectrum Disorder', 'No', 'Mild', 'Married', '462 Luna St.', 'Barangay 10', '09604564682', 'CARER PROGRAM', 'Student', '', '', '', 'Jose Pascual', 'Marjorie Pascual', 'Single Parent', 4, 3, 0.00, 5000.00, 'Nuclear', 'Yes', 'No', 'No', 'Aileen Pascual', '09223240112', 'Tagged', 'Mr. Santos, J.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM III'),
(2937, '400401507048', '2024-10-21', 'PWD-62757', 'Cruz', 'Carla', 'Santos', 16, '2008-07-06', 'Female', 'Cerebral Palsy', 'Yes', 'Moderate', 'Married', '83 Aguinaldo St.', 'Barangay 1', '09563887247', 'ALTERNATIVE LEARNING SCHOOL (ALS)', 'Unemployed', '', '', '', 'Henry Cruz', 'Grace Cruz', 'Both Living', 1, 1, 0.00, 10000.00, 'Nuclear', 'No', 'Yes', 'No', 'Norma Cruz', '09657233178', 'Not Tagged', 'Ms. Reyes, M.', NULL, '2026-02-27 10:27:39', ''),
(2938, '552388062042', '2024-10-11', 'PWD-31079', 'Castillo', 'Gerald', 'Aquino', 10, '2013-10-23', 'Male', 'Cerebral Palsy', 'No', 'Severe', 'Married', '834 Del Pilar St.', 'Barangay 9', '09139403247', 'ALTERNATIVE LEARNING SCHOOL (ALS)', 'Unemployed', '', '', '', 'Miguel Castillo', 'Marites Castillo', 'Single Parent', 5, 0, 0.00, 5000.00, 'Extended', 'Yes', 'No', 'No', 'Ana Castillo', '09301415220', 'Not Tagged', 'Ms. Flores, C.', NULL, '2026-02-27 10:27:39', ''),
(2939, '768425855938', '2023-05-06', 'PWD-25915', 'Domingo', 'Glenda', 'Santos', 20, '2004-12-27', 'Female', 'Autism Spectrum Disorder', 'No', 'Mild', 'Married', '565 Rizal St.', 'Barangay 9', '09538076670', 'CARER PROGRAM', 'Unemployed', '', '', '', 'John Domingo', 'Rosario Domingo', 'Single Parent', 1, 1, 0.00, 15000.00, 'Extended', 'No', 'No', 'Yes', 'Norma Domingo', '09607042130', 'Tagged', 'Mr. Garcia, A.', NULL, '2026-02-27 10:27:39', 'EARLY INTERVENTION (1 on 1)'),
(2940, '409248663017', '2023-09-04', 'PWD-76550', 'Flores', 'Felix', 'Villanueva', 29, '1995-08-25', 'Male', 'Intellectual Disability', 'No', 'Severe', 'Married', '887 Mabini St.', 'Barangay 8', '09678296492', 'CARER PROGRAM', 'Unemployed', '', '', '', 'Luis Flores', 'Marites Flores', 'Orphan', 6, 3, 0.00, 10000.00, 'Extended', 'Yes', 'Yes', 'No', 'Marjorie Flores', '09932734539', 'Not Tagged', 'Ms. Dela Cruz, R.', NULL, '2026-02-27 10:27:39', 'HOME PROGRAM'),
(2941, '211109516823', '2024-06-20', 'PWD-53515', 'Navarro', 'Norma', 'Flores', 25, '1998-04-13', 'Female', 'Intellectual Disability', 'Yes', 'Severe', 'Married', '16 Luna St.', 'Barangay 3', '09540211187', 'CARER PROGRAM', 'Student', '', '', '', 'Miguel Navarro', 'Norma Navarro', 'Single Parent', 5, 4, 0.00, 20000.00, 'Extended', 'Yes', 'No', 'Yes', 'Marites Navarro', '09773020489', 'Not Tagged', 'Ms. Flores, C.', NULL, '2026-02-27 10:27:39', 'ON THE JOB TRAINING (OJT)'),
(2942, '456679815270', '2022-04-25', 'PWD-13794', 'Cruz', 'Henry', 'Garcia', 19, '2004-10-27', 'Male', 'Intellectual Disability', 'No', 'Moderate', 'Married', '442 Mabini St.', 'Barangay 4', '09670335494', 'CARER PROGRAM', 'Employed', 'Private', 'Part-time', 'Janitor', 'Gerald Cruz', 'Jennifer Cruz', 'Both Living', 2, 0, 15000.00, 25000.00, 'Extended', 'Yes', 'No', 'No', 'Marjorie Cruz', '09595155950', 'Not Tagged', 'Mr. Garcia, A.', NULL, '2026-02-27 10:27:39', 'EARLY INTERVENTION (1 on 1)'),
(2943, '567988351366', '2023-05-28', 'PWD-93746', 'Rivera', 'Maria', 'Aquino', 16, '2008-04-12', 'Female', 'Cerebral Palsy', 'No', 'Moderate', 'Single', '394 Del Pilar St.', 'Barangay 8', '09214518079', 'CARER PROGRAM', 'Student', '', '', '', 'Felix Rivera', 'Lorna Rivera', 'Single Parent', 4, 2, 0.00, 10000.00, 'Nuclear', 'No', 'No', 'Yes', 'Lorna Rivera', '09836371599', 'Tagged', 'Ms. Flores, C.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM II'),
(2944, '811364515897', '2022-03-22', 'PWD-92345', 'Domingo', 'Lovely', 'Garcia', 19, '2004-11-12', 'Female', 'Autism Spectrum Disorder', 'No', 'Moderate', 'Single', '594 Bonifacio Ave.', 'Barangay 3', '09196779973', 'CARER PROGRAM', 'Student', '', '', '', 'Ramon Domingo', 'Aileen Domingo', 'Both Living', 3, 1, 0.00, 8000.00, 'Extended', 'No', 'Yes', 'No', 'Norma Domingo', '09965960427', 'Not Tagged', 'Ms. Reyes, M.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM II'),
(2945, '840317438269', '2023-06-21', 'PWD-69609', 'Cruz', 'Ronel', 'Cruz', 29, '1995-08-22', 'Male', 'Cerebral Palsy', 'No', 'Moderate', 'Married', '111 Luna St.', 'Barangay 4', '09606244434', 'CARER PROGRAM', 'Employed', '', 'Full-time', 'Vendor', 'Henry Cruz', 'Wilma Cruz', 'Single Parent', 3, 0, 10000.00, 15000.00, 'Extended', 'No', 'No', 'Yes', 'Carla Cruz', '09148690765', 'Tagged', 'Ms. Reyes, M.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM I'),
(2946, '918566393823', '2022-11-13', 'PWD-64935', 'Garcia', 'Rosario', 'Garcia', 22, '2002-03-01', 'Female', 'Cerebral Palsy', 'No', 'Severe', 'Single', '854 Quezon Blvd.', 'Barangay 10', '09538211092', 'CARER PROGRAM', 'Student', '', '', '', 'Jose Garcia', 'Rowena Garcia', 'Both Living', 7, 3, 0.00, 20000.00, 'Nuclear', 'No', 'Yes', 'No', 'Wilma Garcia', '09792377512', 'Not Tagged', 'Ms. Dela Cruz, R.', NULL, '2026-02-27 10:27:39', 'DEPED SPED/SNED'),
(2947, '941082663172', '2023-03-01', 'PWD-18992', 'Flores', 'Grace', 'Reyes', 17, '2007-08-24', 'Female', 'Autism Spectrum Disorder', 'Yes', 'Mild', 'Single', '337 Del Pilar St.', 'Barangay 3', '09946799779', 'ALTERNATIVE LEARNING SCHOOL (ALS)', 'Employed', 'Self-employed', 'Full-time', 'Janitor', 'Paolo Flores', 'Carla Flores', 'Both Living', 4, 1, 10000.00, 18000.00, 'Nuclear', 'Yes', 'Yes', 'Yes', 'Rowena Flores', '09948239575', 'Tagged', 'Ms. Flores, C.', NULL, '2026-02-27 10:27:39', ''),
(2948, '956623819286', '2024-01-12', 'PWD-72422', 'Torres', 'Maria', 'Mendoza', 16, '2008-06-09', 'Female', 'Cerebral Palsy', 'Yes', 'Severe', 'Single', '463 Aguinaldo St.', 'Barangay 4', '09760465943', 'JUNIOR HIGH SCHOOL', 'Student', '', '', '', 'Emmanuel Torres', 'Lovely Torres', 'Single Parent', 0, 0, 0.00, 5000.00, 'Extended', 'No', 'No', 'Yes', 'Norma Torres', '09864935082', 'Not Tagged', 'Ms. Dela Cruz, R.', NULL, '2026-02-27 10:27:39', 'Grade 6'),
(2949, '599482782524', '2024-11-01', 'PWD-79541', 'Uy', 'Ramon', 'Cruz', 9, '2015-09-12', 'Male', 'Learning Disability', 'No', 'Moderate', 'Single', '813 Luna St.', 'Barangay 2', '09802992685', 'CARER PROGRAM', 'Unemployed', '', '', '', 'Ricardo Uy', 'Glenda Uy', 'Both Living', 6, 2, 0.00, 20000.00, 'Extended', 'No', 'No', 'Yes', 'Marites Uy', '09558234365', 'Not Tagged', 'Ms. Reyes, M.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM IV'),
(2950, '245220443325', '2022-05-03', 'PWD-58843', 'Fernandez', 'Carla', 'Aquino', 18, '2006-08-24', 'Female', 'Intellectual Disability', 'Yes', 'Severe', 'Married', '687 Rizal St.', 'Barangay 7', '09479673044', 'CARER PROGRAM', 'Unemployed', '', '', '', 'Jose Fernandez', 'Lovely Fernandez', 'Orphan', 4, 2, 0.00, 5000.00, 'Nuclear', 'Yes', 'Yes', 'No', 'Marjorie Fernandez', '09216198604', 'Not Tagged', 'Ms. Flores, C.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM I'),
(2951, '397127624567', '2023-09-25', 'PWD-13794', 'Domingo', 'Rosario', 'Mendoza', 28, '1995-12-14', 'Female', 'Cerebral Palsy', 'Yes', 'Moderate', 'Single', '186 Del Pilar St.', 'Barangay 3', '09708082645', 'ALTERNATIVE LEARNING SCHOOL (ALS)', 'Student', '', '', '', 'Miguel Domingo', 'Carla Domingo', 'Orphan', 0, 0, 0.00, 10000.00, 'Nuclear', 'No', 'No', 'No', 'Marites Domingo', '09269016920', 'Not Tagged', 'Ms. Reyes, M.', NULL, '2026-02-27 10:27:39', ''),
(2952, '827752628746', '2024-05-18', 'PWD-45665', 'Uy', 'Lorna', 'Reyes', 7, '2017-05-12', 'Female', 'Learning Disability', 'Yes', 'Severe', 'Married', '425 Bonifacio Ave.', 'Barangay 4', '09910614025', 'COLLEGE', 'Student', '', '', '', 'Luis Uy', 'Aileen Uy', 'Both Living', 4, 4, 0.00, 5000.00, 'Extended', 'No', 'Yes', 'Yes', 'Sheryl Uy', '09743520276', 'Tagged', 'Ms. Dela Cruz, R.', NULL, '2026-02-27 10:27:39', '3RD YEAR'),
(2953, '875381537047', '2023-04-17', 'PWD-59834', 'Flores', 'Angelo', 'Cruz', 20, '2004-05-27', 'Male', 'Down Syndrome', 'Yes', 'Severe', 'Married', '74 Quezon Blvd.', 'Barangay 1', '09568387228', 'CARER PROGRAM', 'Employed', '', '', '', 'Ronel Flores', 'Luz Flores', 'Single Parent', 6, 4, 3000.00, 11000.00, 'Extended', 'No', 'No', 'Yes', 'Sheryl Flores', '09122394554', 'Not Tagged', 'Mr. Santos, J.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM I'),
(2954, '443041290279', '2023-11-27', 'PWD-61873', 'Cruz', 'Rowena', 'Flores', 19, '2004-08-14', 'Female', 'Cerebral Palsy', 'No', 'Moderate', 'Single', '79 Aguinaldo St.', 'Barangay 2', '09669732591', 'CARER PROGRAM', 'Student', '', '', '', 'Luis Cruz', 'Rowena Cruz', 'Orphan', 2, 1, 0.00, 5000.00, 'Nuclear', 'Yes', 'No', 'Yes', 'Jennifer Cruz', '09746820987', 'Tagged', 'Ms. Dela Cruz, R.', NULL, '2026-02-27 10:27:39', 'EARLY INTERVENTION (1 on 1)'),
(2955, '174903210971', '2024-08-18', 'PWD-29786', 'Morales', 'Danilo', 'Villanueva', 26, '1998-03-29', 'Male', 'Learning Disability', 'No', 'Severe', 'Married', '816 Bonifacio Ave.', 'Barangay 10', '09160314355', 'PALIGAWAN SATELLITE', 'Student', '', '', '', 'Emmanuel Morales', 'Luz Morales', 'Single Parent', 7, 3, 0.00, 10000.00, 'Nuclear', 'Yes', 'No', 'No', 'Luz Morales', '09792205349', 'Tagged', 'Ms. Flores, C.', NULL, '2026-02-27 10:27:39', ''),
(2956, '265362113427', '2022-05-07', 'PWD-72341', 'Sy', 'Glenda', 'Bautista', 23, '2001-04-06', 'Female', 'Learning Disability', 'Yes', 'Mild', 'Married', '732 Rizal St.', 'Barangay 9', '09793773681', 'CARER PROGRAM', 'Unemployed', '', '', '', 'Jose Sy', 'Marjorie Sy', 'Orphan', 7, 3, 0.00, 20000.00, 'Nuclear', 'No', 'No', 'Yes', 'Sheryl Sy', '09538912794', 'Not Tagged', 'Ms. Flores, C.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM IV'),
(2957, '187331124984', '2022-10-17', 'PWD-29709', 'Cruz', 'Ramon', 'Torres', 15, '2008-12-24', 'Male', 'Cerebral Palsy', 'No', 'Moderate', 'Married', '133 Aguinaldo St.', 'Barangay 2', '09432411019', 'CARER PROGRAM', 'Unemployed', '', '', '', 'Ramon Cruz', 'Carla Cruz', 'Orphan', 1, 3, 0.00, 20000.00, 'Extended', 'Yes', 'No', 'No', 'Jennifer Cruz', '09329987621', 'Not Tagged', 'Mr. Garcia, A.', NULL, '2026-02-27 10:27:39', 'HOME PROGRAM'),
(2958, '753397880348', '2024-02-11', 'PWD-91006', 'Mendoza', 'John', 'Cruz', 18, '2005-06-07', 'Male', 'Intellectual Disability', 'Yes', 'Mild', 'Single', '852 Luna St.', 'Barangay 10', '09968704078', 'CARER PROGRAM', 'Student', '', '', '', 'Ronel Mendoza', 'Ana Mendoza', 'Single Parent', 5, 1, 0.00, 10000.00, 'Extended', 'No', 'No', 'Yes', 'Wilma Mendoza', '09356805216', 'Not Tagged', 'Mr. Garcia, A.', NULL, '2026-02-27 10:27:39', 'TUTORIAL PROGRAM'),
(2959, '707184732581', '2022-07-30', 'PWD-94040', 'Fernandez', 'Luis', 'Villanueva', 22, '2002-12-30', 'Male', 'Cerebral Palsy', 'No', 'Severe', 'Single', '292 Rizal St.', 'Barangay 3', '09392907427', 'CARER PROGRAM', 'Employed', 'Government', '', 'Janitor', 'Emmanuel Fernandez', 'Carla Fernandez', 'Single Parent', 1, 1, 5000.00, 10000.00, 'Extended', 'No', 'Yes', 'No', 'Ana Fernandez', '09525689202', 'Not Tagged', 'Mr. Santos, J.', NULL, '2026-02-27 10:27:39', 'ADAPTIVE SKILLS PROGRAM III'),
(2960, '776236594141', '2022-01-11', 'PWD-71487', 'Ramos', 'Luz', 'Garcia', 6, '2017-03-23', 'Female', 'Down Syndrome', 'Yes', 'Mild', 'Married', '221 Del Pilar St.', 'Barangay 3', '09689274064', 'COLLEGE', 'Employed', 'Private', 'Part-time', '', 'Carlos Ramos', 'Lovely Ramos', 'Both Living', 4, 0, 15000.00, 23000.00, 'Nuclear', 'Yes', 'No', 'No', 'Luz Ramos', '09219032752', 'Not Tagged', 'Ms. Flores, C.', 0x696d616765732f313737323738333638345f47656d696e695f47656e6572617465645f496d6167655f31726b77703131726b77703131726b772e706e67, '2026-02-27 10:27:39', '4TH YEAR'),
(2962, '989841108290', '2022-10-04', 'PWD-92802', 'Reyes', 'Carla', 'Torres', 29, '1994-03-06', 'Female', 'Autism Spectrum Disorder', 'No', 'Severe', 'Married', '38 Bonifacio Ave.', 'Barangay 4', '09147036629', 'CARER PROGRAM', 'Student', '', '', '', 'Jose Reyes', 'Marites Reyes', 'Single Parent', 6, 4, 0.00, 15000.00, 'Extended', 'No', 'Yes', 'Yes', 'Lovely Reyes', '09483298103', 'Tagged', 'Ms. Reyes, M.', 0x696d616765732f313737323731313833365f6973746f636b70686f746f2d3633393435343431382d363132783631322e6a7067, '2026-02-27 10:27:39', 'EARLY INTERVENTION (1 on 1)');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(20) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `schedule_id` int(11) NOT NULL,
  `admin_id` int(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `age` int(255) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `role` int(255) NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `age`, `gender`, `role`, `is_approved`, `created_at`) VALUES
(17, 'John', 'admin@admin.com', '$2y$10$dD.B3ykgOkN/OdW4TRzNyuoJrRVi0/IXjem1c6HXGZP4xN5VeBzJG', 21, 'male', 0, 1, '2026-03-05 09:47:59'),
(18, 'jane', 'pixelastra5@gmail.com', '$2y$10$bGOG7ZX/G9tdr56BHzTvu.dO.kSen19YW6WnsHXQvnHnrMIPp9NtC', 21, 'male', 2, 1, '2026-03-05 10:13:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`);

--
-- Indexes for table `enrollees`
--
ALTER TABLE `enrollees`
  ADD PRIMARY KEY (`enrollee_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollees`
--
ALTER TABLE `enrollees`
  MODIFY `enrollee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2963;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
