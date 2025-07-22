-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2025 at 08:48 PM
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
-- Database: `emis`
--

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `device_name` varchar(100) NOT NULL,
  `device_type` varchar(50) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `manufacturer` varchar(100) NOT NULL,
  `purchase_date` date NOT NULL,
  `warranty_end` date NOT NULL,
  `status` enum('active','maintenance','repair','disposed') NOT NULL DEFAULT 'active',
  `location` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `last_maintenance` date DEFAULT NULL,
  `next_maintenance` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `recommendation` varchar(255) DEFAULT NULL,
  `reliability_score` int(11) DEFAULT NULL,
  `cost_benefit` varchar(255) DEFAULT NULL,
  `health` int(11) DEFAULT NULL,
  `alert` varchar(255) DEFAULT NULL,
  `disposal_reason` varchar(255) DEFAULT NULL,
  `disposal_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `device_name`, `device_type`, `serial_number`, `model`, `manufacturer`, `purchase_date`, `warranty_end`, `status`, `location`, `department`, `assigned_to`, `last_maintenance`, `next_maintenance`, `created_at`, `deleted_at`, `recommendation`, `reliability_score`, `cost_benefit`, `health`, `alert`, `disposal_reason`, `disposal_date`) VALUES
(249, 'PC-IT-001', 'Desktop', 'SN-DESK-2024-001', 'OptiPlex 7090', 'Dell', '2024-01-15', '2027-01-15', 'active', 'IT Office - Floor 1', 'IT', NULL, '2024-06-15', '2024-12-15', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(250, 'PC-IT-002', 'Desktop', 'SN-DESK-2024-002', 'OptiPlex 7090', 'Dell', '2024-01-20', '2027-01-20', 'active', 'IT Office - Floor 1', 'IT', NULL, '2024-06-20', '2024-12-20', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(251, 'PC-HR-001', 'Desktop', 'SN-DESK-2024-003', 'ThinkCentre M90t', 'Lenovo', '2024-02-01', '2027-02-01', 'active', 'HR Department', 'Human Resources', NULL, '2024-07-01', '2025-01-01', '2025-07-12 20:03:20', NULL, 'Keep', 80, 'Good', 85, '', '', NULL),
(252, 'PC-HR-002', 'Desktop', 'SN-DESK-2024-004', 'ThinkCentre M90t', 'Lenovo', '2024-02-05', '2027-02-05', 'active', 'HR Department', 'Human Resources', NULL, '2024-07-05', '2025-01-05', '2025-07-12 20:03:20', NULL, 'Keep', 80, 'Good', 85, '', '', NULL),
(253, 'PC-FIN-001', 'Desktop', 'SN-DESK-2024-005', 'ProDesk 600 G7', 'HP', '2024-02-10', '2027-02-10', 'active', 'Finance Office', 'Finance', NULL, '2024-07-10', '2025-01-10', '2025-07-12 20:03:20', NULL, 'Keep', 82, 'Good', 88, '', '', NULL),
(254, 'PC-FIN-002', 'Desktop', 'SN-DESK-2024-006', 'ProDesk 600 G7', 'HP', '2024-02-15', '2027-02-15', 'active', 'Finance Office', 'Finance', NULL, '2024-07-15', '2025-01-15', '2025-07-12 20:03:20', NULL, 'Keep', 82, 'Good', 88, '', '', NULL),
(255, 'PC-MKT-001', 'Desktop', 'SN-DESK-2024-007', 'iMac 24\"', 'Apple', '2024-03-01', '2027-03-01', 'active', 'Marketing Suite', 'Marketing', NULL, '2024-08-01', '2025-02-01', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(256, 'PC-MKT-002', 'Desktop', 'SN-DESK-2024-008', 'iMac 24', 'Apple', '2024-03-05', '2027-03-05', 'active', 'Marketing Suite', 'Marketing', 4, '2024-08-05', '2025-02-05', '2025-07-12 20:03:20', NULL, '', 0, '', 0, '', '', '0000-00-00'),
(257, 'PC-SALES-001', 'Desktop', 'SN-DESK-2024-009', 'OptiPlex 7090', 'Dell', '2024-03-10', '2027-03-10', 'active', 'Sales Floor', 'Sales', NULL, '2024-08-10', '2025-02-10', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(258, 'PC-SALES-002', 'Desktop', 'SN-DESK-2024-010', 'OptiPlex 7090', 'Dell', '2024-03-15', '2027-03-15', 'active', 'Sales Floor', 'Sales', NULL, '2024-08-15', '2025-02-15', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(259, 'PC-OPS-001', 'Desktop', 'SN-DESK-2024-011', 'ThinkCentre M90t', 'Lenovo', '2024-03-20', '2027-03-20', 'active', 'Operations Center', 'Operations', NULL, '2024-08-20', '2025-02-20', '2025-07-12 20:03:20', NULL, 'Keep', 80, 'Good', 85, '', '', NULL),
(260, 'PC-OPS-002', 'Desktop', 'SN-DESK-2024-012', 'ThinkCentre M90t', 'Lenovo', '2024-03-25', '2027-03-25', 'active', 'Operations Center', 'Operations', NULL, '2024-08-25', '2025-02-25', '2025-07-12 20:03:20', NULL, 'Keep', 80, 'Good', 85, '', '', NULL),
(261, 'PC-LEGAL-001', 'Desktop', 'SN-DESK-2024-013', 'ProDesk 600 G7', 'HP', '2024-04-01', '2027-04-01', 'active', 'Legal Department', 'Legal', NULL, '2024-09-01', '2025-03-01', '2025-07-12 20:03:20', NULL, 'Keep', 82, 'Good', 88, '', '', NULL),
(262, 'PC-LEGAL-002', 'Desktop', 'SN-DESK-2024-014', 'ProDesk 600 G7', 'HP', '2024-04-05', '2027-04-05', 'active', 'Legal Department', 'Legal', NULL, '2024-09-05', '2025-03-05', '2025-07-12 20:03:20', NULL, 'Keep', 82, 'Good', 88, '', '', NULL),
(263, 'PC-REC-001', 'Desktop', 'SN-DESK-2024-015', 'OptiPlex 7090', 'Dell', '2024-04-10', '2027-04-10', 'active', 'Reception Desk', 'Reception', NULL, '2024-09-10', '2025-03-10', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(264, 'PC-REC-002', 'Desktop', 'SN-DESK-2024-016', 'OptiPlex 7090', 'Dell', '2024-04-15', '2027-04-15', 'active', 'Reception Desk', 'Reception', NULL, '2024-09-15', '2025-03-15', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(265, 'PC-ADMIN-001', 'Desktop', 'SN-DESK-2024-017', 'iMac 24\"', 'Apple', '2024-04-20', '2027-04-20', 'active', 'Executive Office', 'Administration', NULL, '2024-09-20', '2025-03-20', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(266, 'PC-ADMIN-002', 'Desktop', 'SN-DESK-2024-018', 'iMac 24\"', 'Apple', '2024-04-25', '2027-04-25', 'active', 'Executive Office', 'Administration', NULL, '2024-09-25', '2025-03-25', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(267, 'PC-IT-003', 'Desktop', 'SN-DESK-2024-019', 'OptiPlex 7090', 'Dell', '2024-05-01', '2027-05-01', 'maintenance', 'IT Office - Floor 2', 'IT', NULL, '2024-10-01', '2025-04-01', '2025-07-12 20:03:20', NULL, 'Maintain', 75, 'Fair', 70, 'Maintenance Required', '', NULL),
(268, 'PC-IT-004', 'Desktop', 'SN-DESK-2024-020', 'OptiPlex 7090', 'Dell', '2024-05-05', '2027-05-05', 'active', 'IT Office - Floor 2', 'IT', NULL, '2024-10-05', '2025-04-05', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(269, 'PC-RES-001', 'Desktop', 'SN-DESK-2024-021', 'ThinkCentre M90t', 'Lenovo', '2024-05-10', '2027-05-10', 'active', 'Research Lab', 'Research', NULL, '2024-10-10', '2025-04-10', '2025-07-12 20:03:20', NULL, 'Keep', 80, 'Good', 85, '', '', NULL),
(270, 'PC-RES-002', 'Desktop', 'SN-DESK-2024-022', 'ThinkCentre M90t', 'Lenovo', '2024-05-15', '2027-05-15', 'active', 'Research Lab', 'Research', NULL, '2024-10-15', '2025-04-15', '2025-07-12 20:03:20', NULL, 'Keep', 80, 'Good', 85, '', '', NULL),
(271, 'PC-IT-005', 'Desktop', 'SN-DESK-2024-023', 'OptiPlex 7090', 'Dell', '2024-05-20', '2027-05-20', 'active', 'IT Office - Floor 3', 'IT', NULL, '2024-10-20', '2025-04-20', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(272, 'PC-IT-006', 'Desktop', 'SN-DESK-2024-024', 'OptiPlex 7090', 'Dell', '2024-05-25', '2027-05-25', 'active', 'IT Office - Floor 3', 'IT', NULL, '2024-10-25', '2025-04-25', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(273, 'PC-IT-007', 'Desktop', 'SN-DESK-2024-025', 'OptiPlex 7090', 'Dell', '2024-06-01', '2027-06-01', 'active', 'IT Office - Floor 3', 'IT', NULL, '2024-11-01', '2025-05-01', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(274, 'LAP-IT-001', 'Laptop', 'SN-LAP-2024-001', 'Latitude 5520', 'Dell', '2024-01-10', '2027-01-10', 'active', 'IT Office', 'IT', NULL, '2024-06-10', '2024-12-10', '2025-07-12 20:03:20', NULL, 'Keep', 88, 'Good', 92, '', '', NULL),
(275, 'LAP-IT-002', 'Laptop', 'SN-LAP-2024-002', 'Latitude 5520', 'Dell', '2024-01-12', '2027-01-12', 'active', 'IT Office', 'IT', NULL, '2024-06-12', '2024-12-12', '2025-07-12 20:03:20', NULL, 'Keep', 88, 'Good', 92, '', '', NULL),
(276, 'LAP-SALES-001', 'Laptop', 'SN-LAP-2024-003', 'ThinkPad T14', 'Lenovo', '2024-02-01', '2027-02-01', 'active', 'Sales Department', 'Sales', NULL, '2024-07-01', '2025-01-01', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(277, 'LAP-SALES-002', 'Laptop', 'SN-LAP-2024-004', 'ThinkPad T14', 'Lenovo', '2024-02-05', '2027-02-05', 'active', 'Sales Department', 'Sales', NULL, '2024-07-05', '2025-01-05', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(278, 'LAP-MKT-001', 'Laptop', 'SN-LAP-2024-005', 'MacBook Pro 14\"', 'Apple', '2024-02-10', '2027-02-10', 'active', 'Marketing Department', 'Marketing', NULL, '2024-07-10', '2025-01-10', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(279, 'LAP-MKT-002', 'Laptop', 'SN-LAP-2024-006', 'MacBook Pro 14\"', 'Apple', '2024-02-15', '2027-02-15', 'active', 'Marketing Department', 'Marketing', NULL, '2024-07-15', '2025-01-15', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(280, 'LAP-ADMIN-001', 'Laptop', 'SN-LAP-2024-007', 'EliteBook 840 G8', 'HP', '2024-03-01', '2027-03-01', 'active', 'Executive Office', 'Administration', NULL, '2024-08-01', '2025-02-01', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(281, 'LAP-ADMIN-002', 'Laptop', 'SN-LAP-2024-008', 'EliteBook 840 G8', 'HP', '2024-03-05', '2027-03-05', 'active', 'Executive Office', 'Administration', NULL, '2024-08-05', '2025-02-05', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(282, 'LAP-FIN-001', 'Laptop', 'SN-LAP-2024-009', 'ThinkPad X1 Carbon', 'Lenovo', '2024-03-10', '2027-03-10', 'active', 'Finance Department', 'Finance', NULL, '2024-08-10', '2025-02-10', '2025-07-12 20:03:20', NULL, 'Keep', 92, 'Excellent', 96, '', '', NULL),
(283, 'LAP-FIN-002', 'Laptop', 'SN-LAP-2024-010', 'ThinkPad X1 Carbon', 'Lenovo', '2024-03-15', '2027-03-15', 'active', 'Finance Department', 'Finance', NULL, '2024-08-15', '2025-02-15', '2025-07-12 20:03:20', NULL, 'Keep', 92, 'Excellent', 96, '', '', NULL),
(284, 'LAP-HR-001', 'Laptop', 'SN-LAP-2024-011', 'Latitude 5420', 'Dell', '2024-03-20', '2027-03-20', 'active', 'HR Department', 'Human Resources', NULL, '2024-08-20', '2025-02-20', '2025-07-12 20:03:20', NULL, 'Keep', 88, 'Good', 92, '', '', NULL),
(285, 'LAP-HR-002', 'Laptop', 'SN-LAP-2024-012', 'Latitude 5420', 'Dell', '2024-03-25', '2027-03-25', 'active', 'HR Department', 'Human Resources', NULL, '2024-08-25', '2025-02-25', '2025-07-12 20:03:20', NULL, 'Keep', 88, 'Good', 92, '', '', NULL),
(286, 'LAP-LEGAL-001', 'Laptop', 'SN-LAP-2024-013', 'MacBook Air 13\"', 'Apple', '2024-04-01', '2027-04-01', 'active', 'Legal Department', 'Legal', NULL, '2024-09-01', '2025-03-01', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(287, 'LAP-LEGAL-002', 'Laptop', 'SN-LAP-2024-014', 'MacBook Air 13\"', 'Apple', '2024-04-05', '2027-04-05', 'active', 'Legal Department', 'Legal', NULL, '2024-09-05', '2025-03-05', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(288, 'LAP-OPS-001', 'Laptop', 'SN-LAP-2024-015', 'EliteBook 840 G8', 'HP', '2024-04-10', '2027-04-10', 'repair', 'Operations Department', 'Operations', NULL, '2024-09-10', '2025-03-10', '2025-07-12 20:03:20', NULL, 'Repair', 65, 'Poor', 60, 'Repair Required', '', NULL),
(289, 'LAP-RES-001', 'Laptop', 'SN-LAP-2024-016', 'ThinkPad T14', 'Lenovo', '2024-04-15', '2027-04-15', 'active', 'Research Department', 'Research', NULL, '2024-09-15', '2025-03-15', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(290, 'LAP-RES-002', 'Laptop', 'SN-LAP-2024-017', 'ThinkPad T14', 'Lenovo', '2024-04-20', '2027-04-20', 'active', 'Research Department', 'Research', NULL, '2024-09-20', '2025-03-20', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(291, 'LAP-IT-003', 'Laptop', 'SN-LAP-2024-018', 'Latitude 5520', 'Dell', '2024-04-25', '2027-04-25', 'active', 'IT Office', 'IT', NULL, '2024-09-25', '2025-03-25', '2025-07-12 20:03:20', NULL, 'Keep', 88, 'Good', 92, '', '', NULL),
(292, 'LAP-IT-004', 'Laptop', 'SN-LAP-2024-019', 'Latitude 5520', 'Dell', '2024-05-01', '2027-05-01', 'active', 'IT Office', 'IT', NULL, '2024-10-01', '2025-04-01', '2025-07-12 20:03:20', NULL, 'Keep', 88, 'Good', 92, '', '', NULL),
(293, 'LAP-IT-005', 'Laptop', 'SN-LAP-2024-020', 'Latitude 5520', 'Dell', '2024-05-05', '2027-05-05', 'active', 'IT Office', 'IT', NULL, '2024-10-05', '2025-04-05', '2025-07-12 20:03:20', NULL, 'Keep', 88, 'Good', 92, '', '', NULL),
(294, 'PRN-IT-001', 'Printer', 'SN-PRN-2024-001', 'LaserJet Pro M404n', 'HP', '2024-01-05', '2027-01-05', 'active', 'IT Office', 'IT', NULL, '2024-06-05', '2024-12-05', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(295, 'PRN-IT-002', 'Printer', 'SN-PRN-2024-002', 'LaserJet Pro M404n', 'HP', '2024-01-10', '2027-01-10', 'active', 'IT Office', 'IT', NULL, '2024-06-10', '2024-12-10', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(296, 'PRN-HR-001', 'Printer', 'SN-PRN-2024-003', 'WorkForce Pro WF-4740', 'Epson', '2024-01-15', '2027-01-15', 'active', 'HR Department', 'Human Resources', NULL, '2024-06-15', '2024-12-15', '2025-07-12 20:03:20', NULL, 'Keep', 80, 'Good', 85, '', '', NULL),
(297, 'PRN-HR-002', 'Printer', 'SN-PRN-2024-004', 'WorkForce Pro WF-4740', 'Epson', '2024-01-20', '2027-01-20', 'active', 'HR Department', 'Human Resources', NULL, '2024-06-20', '2024-12-20', '2025-07-12 20:03:20', NULL, 'Keep', 80, 'Good', 85, '', '', NULL),
(298, 'PRN-FIN-001', 'Printer', 'SN-PRN-2024-005', 'LaserJet Pro M404n', 'HP', '2024-01-25', '2027-01-25', 'active', 'Finance Office', 'Finance', NULL, '2024-06-25', '2024-12-25', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(299, 'PRN-FIN-002', 'Printer', 'SN-PRN-2024-006', 'LaserJet Pro M404n', 'HP', '2024-02-01', '2027-02-01', 'active', 'Finance Office', 'Finance', NULL, '2024-07-01', '2025-01-01', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(300, 'PRN-MKT-001', 'Printer', 'SN-PRN-2024-007', 'Pixma PRO-100', 'Canon', '2024-02-05', '2027-02-05', 'active', 'Marketing Suite', 'Marketing', NULL, '2024-07-05', '2025-01-05', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(301, 'PRN-MKT-002', 'Printer', 'SN-PRN-2024-008', 'Pixma PRO-100', 'Canon', '2024-02-10', '2027-02-10', 'active', 'Marketing Suite', 'Marketing', NULL, '2024-07-10', '2025-01-10', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(302, 'PRN-SALES-001', 'Printer', 'SN-PRN-2024-009', 'LaserJet Pro M404n', 'HP', '2024-02-15', '2027-02-15', 'active', 'Sales Floor', 'Sales', NULL, '2024-07-15', '2025-01-15', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(303, 'PRN-SALES-002', 'Printer', 'SN-PRN-2024-010', 'LaserJet Pro M404n', 'HP', '2024-02-20', '2027-02-20', 'active', 'Sales Floor', 'Sales', NULL, '2024-07-20', '2025-01-20', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(304, 'PRN-OPS-001', 'Printer', 'SN-PRN-2024-011', 'WorkForce Pro WF-4740', 'Epson', '2024-02-25', '2027-02-25', 'active', 'Operations Center', 'Operations', NULL, '2024-07-25', '2025-01-25', '2025-07-12 20:03:20', NULL, 'Keep', 80, 'Good', 85, '', '', NULL),
(305, 'PRN-OPS-002', 'Printer', 'SN-PRN-2024-012', 'WorkForce Pro WF-4740', 'Epson', '2024-03-01', '2027-03-01', 'active', 'Operations Center', 'Operations', NULL, '2024-08-01', '2025-02-01', '2025-07-12 20:03:20', NULL, 'Keep', 80, 'Good', 85, '', '', NULL),
(306, 'PRN-LEGAL-001', 'Printer', 'SN-PRN-2024-013', 'LaserJet Pro M404n', 'HP', '2024-03-05', '2027-03-05', 'active', 'Legal Department', 'Legal', NULL, '2024-08-05', '2025-02-05', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(307, 'PRN-LEGAL-002', 'Printer', 'SN-PRN-2024-014', 'LaserJet Pro M404n', 'HP', '2024-03-10', '2027-03-10', 'active', 'Legal Department', 'Legal', NULL, '2024-08-10', '2025-02-10', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(308, 'PRN-REC-001', 'Printer', 'SN-PRN-2024-015', 'WorkForce Pro WF-4740', 'Epson', '2024-03-15', '2027-03-15', 'active', 'Reception Desk', 'Reception', NULL, '2024-08-15', '2025-02-15', '2025-07-12 20:03:20', NULL, 'Keep', 80, 'Good', 85, '', '', NULL),
(309, 'PRN-REC-002', 'Printer', 'SN-PRN-2024-016', 'WorkForce Pro WF-4740', 'Epson', '2024-03-20', '2027-03-20', 'active', 'Reception Desk', 'Reception', NULL, '2024-08-20', '2025-02-20', '2025-07-12 20:03:20', NULL, 'Keep', 80, 'Good', 85, '', '', NULL),
(310, 'PRN-ADMIN-001', 'Printer', 'SN-PRN-2024-017', 'LaserJet Pro M404n', 'HP', '2024-03-25', '2027-03-25', 'active', 'Executive Office', 'Administration', NULL, '2024-08-25', '2025-02-25', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(311, 'PRN-ADMIN-002', 'Printer', 'SN-PRN-2024-018', 'LaserJet Pro M404n', 'HP', '2024-04-01', '2027-04-01', 'active', 'Executive Office', 'Administration', NULL, '2024-09-01', '2025-03-01', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(312, 'PRN-IT-003', 'Printer', 'SN-PRN-2024-019', 'LaserJet Pro M404n', 'HP', '2024-04-05', '2027-04-05', 'maintenance', 'IT Office - Floor 2', 'IT', NULL, '2024-09-05', '2025-03-05', '2025-07-12 20:03:20', NULL, 'Maintain', 75, 'Fair', 70, 'Maintenance Required', '', NULL),
(313, 'PRN-IT-004', 'Printer', 'SN-PRN-2024-020', 'LaserJet Pro M404n', 'HP', '2024-04-10', '2027-04-10', 'active', 'IT Office - Floor 2', 'IT', NULL, '2024-09-10', '2025-03-10', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(314, 'SW-IT-001', 'Network Switch', 'SN-SW-2024-001', 'Catalyst 2960-X', 'Cisco', '2024-01-01', '2027-01-01', 'active', 'Server Room', 'IT', NULL, '2024-06-01', '2024-12-01', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(315, 'SW-IT-002', 'Network Switch', 'SN-SW-2024-002', 'Catalyst 2960-X', 'Cisco', '2024-01-05', '2027-01-05', 'active', 'Server Room', 'IT', NULL, '2024-06-05', '2024-12-05', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(316, 'SW-IT-003', 'Network Switch', 'SN-SW-2024-003', 'Catalyst 2960-X', 'Cisco', '2024-01-10', '2027-01-10', 'active', 'Server Room', 'IT', NULL, '2024-06-10', '2024-12-10', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(317, 'RT-IT-001', 'Router', 'SN-RT-2024-001', 'ISR 4321', 'Cisco', '2024-01-15', '2027-01-15', 'active', 'Server Room', 'IT', NULL, '2024-06-15', '2024-12-15', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(318, 'RT-IT-002', 'Router', 'SN-RT-2024-002', 'ISR 4321', 'Cisco', '2024-01-20', '2027-01-20', 'active', 'Server Room', 'IT', NULL, '2024-06-20', '2024-12-20', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(319, 'AP-IT-001', 'Wireless Access Point', 'SN-AP-2024-001', 'Aironet 1815i', 'Cisco', '2024-02-01', '2027-02-01', 'active', 'Floor 1 - Central', 'IT', NULL, '2024-07-01', '2025-01-01', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(320, 'AP-IT-002', 'Wireless Access Point', 'SN-AP-2024-002', 'Aironet 1815i', 'Cisco', '2024-02-05', '2027-02-05', 'active', 'Floor 1 - East', 'IT', NULL, '2024-07-05', '2025-01-05', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(321, 'AP-IT-003', 'Wireless Access Point', 'SN-AP-2024-003', 'Aironet 1815i', 'Cisco', '2024-02-10', '2027-02-10', 'active', 'Floor 1 - West', 'IT', NULL, '2024-07-10', '2025-01-10', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(322, 'AP-IT-004', 'Wireless Access Point', 'SN-AP-2024-004', 'Aironet 1815i', 'Cisco', '2024-02-15', '2027-02-15', 'active', 'Floor 2 - Central', 'IT', NULL, '2024-07-15', '2025-01-15', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(323, 'AP-IT-005', 'Wireless Access Point', 'SN-AP-2024-005', 'Aironet 1815i', 'Cisco', '2024-02-20', '2027-02-20', 'active', 'Floor 2 - East', 'IT', NULL, '2024-07-20', '2025-01-20', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(324, 'AP-IT-006', 'Wireless Access Point', 'SN-AP-2024-006', 'Aironet 1815i', 'Cisco', '2024-02-25', '2027-02-25', 'active', 'Floor 2 - West', 'IT', NULL, '2024-07-25', '2025-01-25', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(325, 'FW-IT-001', 'Firewall', 'SN-FW-2024-001', 'ASA 5506-X', 'Cisco', '2024-03-01', '2027-03-01', 'active', 'Server Room', 'IT', NULL, '2024-08-01', '2025-02-01', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(326, 'FW-IT-002', 'Firewall', 'SN-FW-2024-002', 'ASA 5506-X', 'Cisco', '2024-03-05', '2027-03-05', 'active', 'Server Room', 'IT', NULL, '2024-08-05', '2025-02-05', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(327, 'UPS-IT-001', 'UPS', 'SN-UPS-2024-001', 'Smart-UPS 1500VA', 'APC', '2024-03-10', '2027-03-10', 'active', 'Server Room', 'IT', NULL, '2024-08-10', '2025-02-10', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(328, 'UPS-IT-002', 'UPS', 'SN-UPS-2024-002', 'Smart-UPS 1500VA', 'APC', '2024-03-15', '2027-03-15', 'active', 'Server Room', 'IT', NULL, '2024-08-15', '2025-02-15', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(329, 'UPS-IT-003', 'UPS', 'SN-UPS-2024-003', 'Smart-UPS 1500VA', 'APC', '2024-03-20', '2027-03-20', 'active', 'Server Room', 'IT', NULL, '2024-08-20', '2025-02-20', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(330, 'SRV-IT-001', 'Server', 'SN-SRV-2024-001', 'PowerEdge R740', 'Dell', '2024-01-01', '2027-01-01', 'active', 'Server Room', 'IT', NULL, '2024-06-01', '2024-12-01', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(331, 'SRV-IT-002', 'Server', 'SN-SRV-2024-002', 'PowerEdge R740', 'Dell', '2024-01-05', '2027-01-05', 'active', 'Server Room', 'IT', NULL, '2024-06-05', '2024-12-05', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(332, 'SRV-IT-003', 'Server', 'SN-SRV-2024-003', 'PowerEdge R740', 'Dell', '2024-01-10', '2027-01-10', 'active', 'Server Room', 'IT', NULL, '2024-06-10', '2024-12-10', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(333, 'SRV-IT-004', 'Server', 'SN-SRV-2024-004', 'PowerEdge R740', 'Dell', '2024-01-15', '2027-01-15', 'active', 'Server Room', 'IT', NULL, '2024-06-15', '2024-12-15', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(334, 'SRV-IT-005', 'Server', 'SN-SRV-2024-005', 'PowerEdge R740', 'Dell', '2024-01-20', '2027-01-20', 'active', 'Server Room', 'IT', NULL, '2024-06-20', '2024-12-20', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(335, 'SRV-IT-006', 'Server', 'SN-SRV-2024-006', 'PowerEdge R740', 'Dell', '2024-01-25', '2027-01-25', 'active', 'Server Room', 'IT', NULL, '2024-06-25', '2024-12-25', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(336, 'SRV-IT-007', 'Server', 'SN-SRV-2024-007', 'PowerEdge R740', 'Dell', '2024-02-01', '2027-02-01', 'active', 'Server Room', 'IT', NULL, '2024-07-01', '2025-01-01', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(337, 'SRV-IT-008', 'Server', 'SN-SRV-2024-008', 'PowerEdge R740', 'Dell', '2024-02-05', '2027-02-05', 'active', 'Server Room', 'IT', NULL, '2024-07-05', '2025-01-05', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(338, 'SRV-IT-009', 'Server', 'SN-SRV-2024-009', 'PowerEdge R740', 'Dell', '2024-02-10', '2027-02-10', 'maintenance', 'Server Room', 'IT', NULL, '2024-07-10', '2025-01-10', '2025-07-12 20:03:20', NULL, 'Maintain', 85, 'Good', 80, 'Maintenance Required', '', NULL),
(339, 'SRV-IT-010', 'Server', 'SN-SRV-2024-010', 'PowerEdge R740', 'Dell', '2024-02-15', '2027-02-15', 'active', 'Server Room', 'IT', NULL, '2024-07-15', '2025-01-15', '2025-07-12 20:03:20', NULL, 'Keep', 95, 'Excellent', 98, '', '', NULL),
(340, 'TAB-IT-001', 'Tablet', 'SN-TAB-2024-001', 'iPad Pro 12.9\"', 'Apple', '2024-01-10', '2027-01-10', 'active', 'IT Office', 'IT', NULL, '2024-06-10', '2024-12-10', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(341, 'TAB-IT-002', 'Tablet', 'SN-TAB-2024-002', 'iPad Pro 12.9\"', 'Apple', '2024-01-15', '2027-01-15', 'active', 'IT Office', 'IT', NULL, '2024-06-15', '2024-12-15', '2025-07-12 20:03:20', NULL, 'Keep', 90, 'Excellent', 95, '', '', NULL),
(342, 'TAB-SALES-001', 'Tablet', 'SN-TAB-2024-003', 'Galaxy Tab S8', 'Samsung', '2024-02-01', '2027-02-01', 'active', 'Sales Department', 'Sales', NULL, '2024-07-01', '2025-01-01', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(343, 'TAB-SALES-002', 'Tablet', 'SN-TAB-2024-004', 'Galaxy Tab S8', 'Samsung', '2024-02-05', '2027-02-05', 'active', 'Sales Department', 'Sales', NULL, '2024-07-05', '2025-01-05', '2025-07-12 20:03:20', NULL, 'Keep', 85, 'Good', 90, '', '', NULL),
(344, 'TAB-MKT-001', 'Tablet', 'SN-TAB-2024-005', 'iPad Air', 'Apple', '2024-02-10', '2027-02-10', 'active', 'Marketing Department', 'Marketing', NULL, '2024-07-10', '2025-01-10', '2025-07-12 20:03:20', NULL, 'Keep', 88, 'Good', 92, '', '', NULL),
(345, 'TAB-MKT-002', 'Tablet', 'SN-TAB-2024-006', 'iPad Air', 'Apple', '2024-02-15', '2027-02-15', 'active', 'Marketing Department', 'Marketing', NULL, '2024-07-15', '2025-01-15', '2025-07-12 20:03:20', NULL, 'Keep', 88, 'Good', 92, '', '', NULL),
(346, 'PHN-IT-001', 'Smartphone', 'SN-PHN-2024-001', 'iPhone 15 Pro', 'Apple', '2024-03-01', '2027-03-01', 'active', 'IT Office', 'IT', NULL, '2024-08-01', '2025-02-01', '2025-07-12 20:03:20', NULL, 'Keep', 92, 'Excellent', 96, '', '', NULL),
(347, 'PHN-IT-002', 'Smartphone', 'SN-PHN-2024-002', 'iPhone 15 Pro', 'Apple', '2024-03-05', '2027-03-05', 'active', 'IT Office', 'IT', NULL, '2024-08-05', '2025-02-05', '2025-07-12 20:03:20', NULL, 'Keep', 92, 'Excellent', 96, '', '', NULL),
(348, 'PHN-SALES-001', 'Smartphone', 'SN-PHN-2024-003', 'Galaxy S24', 'Samsung', '2024-03-10', '2027-03-10', 'active', 'Sales Department', 'Sales', NULL, '2024-08-10', '2025-02-10', '2025-07-12 20:03:20', NULL, 'Keep', 88, 'Good', 92, '', '', NULL),
(349, 'PHN-SALES-002', 'Smartphone', 'SN-PHN-2024-004', 'Galaxy S24', 'Samsung', '2024-03-15', '2027-03-15', 'active', 'Sales Department', 'Sales', NULL, '2024-08-15', '2025-02-15', '2025-07-12 20:03:20', NULL, 'Keep', 88, 'Good', 92, '', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `device_history`
--

CREATE TABLE `device_history` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `performed_by` int(11) NOT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `device_history`
--

INSERT INTO `device_history` (`id`, `device_id`, `action`, `description`, `performed_by`, `performed_at`) VALUES
(420, 249, 'Installation', 'Initial setup and configuration of PC-IT-001', 1, '2024-01-15 07:00:00'),
(421, 249, 'Software Installation', 'Installed Windows 11 and office applications', 1, '2024-01-16 11:30:00'),
(422, 249, 'Maintenance', 'Routine system cleanup and optimization', 1, '2024-02-15 06:00:00'),
(423, 249, 'Repair', 'Fixed network connectivity issues', 1, '2024-03-10 08:20:00'),
(424, 249, 'Update', 'Updated antivirus software and system patches', 1, '2024-04-05 12:45:00'),
(425, 250, 'Installation', 'Initial setup and configuration of PC-IT-002', 1, '2024-01-20 11:30:00'),
(426, 250, 'Software Installation', 'Installed development tools and IDEs', 1, '2024-01-21 13:45:00'),
(427, 250, 'Maintenance', 'Cleaned keyboard and updated drivers', 1, '2024-02-20 07:15:00'),
(428, 250, 'Repair', 'Replaced faulty RAM module', 1, '2024-03-15 09:30:00'),
(429, 250, 'Update', 'Updated system packages and security patches', 1, '2024-04-10 06:45:00'),
(430, 251, 'Installation', 'Initial setup and configuration of PC-HR-001', 1, '2024-02-01 06:15:00'),
(431, 251, 'Software Installation', 'Installed HR management software and Office 365', 1, '2024-02-02 08:30:00'),
(432, 251, 'Maintenance', 'System optimization and disk cleanup', 1, '2024-03-01 11:20:00'),
(433, 251, 'Repair', 'Fixed display driver issues', 1, '2024-04-15 07:45:00'),
(434, 251, 'Update', 'Updated Windows and application software', 1, '2024-05-20 10:10:00'),
(435, 252, 'Installation', 'Initial setup and configuration of PC-HR-002', 1, '2024-02-05 08:45:00'),
(436, 252, 'Software Installation', 'Installed HR management software and Office 365', 1, '2024-02-06 10:20:00'),
(437, 252, 'Maintenance', 'Routine system maintenance and updates', 1, '2024-03-05 07:30:00'),
(438, 252, 'Repair', 'Fixed printer connectivity issues', 1, '2024-04-20 09:15:00'),
(439, 252, 'Update', 'Updated Windows and application software', 1, '2024-05-25 11:45:00'),
(440, 253, 'Installation', 'Initial setup and configuration of PC-FIN-001', 1, '2024-02-10 10:20:00'),
(441, 253, 'Software Installation', 'Installed accounting software and Office 365', 1, '2024-02-11 12:45:00'),
(442, 253, 'Maintenance', 'System optimization and disk cleanup', 1, '2024-03-10 09:30:00'),
(443, 253, 'Repair', 'Fixed network connectivity issues', 1, '2024-04-25 07:15:00'),
(444, 253, 'Update', 'Updated Windows and application software', 1, '2024-05-30 11:20:00'),
(445, 254, 'Installation', 'Initial setup and configuration of PC-FIN-002', 1, '2024-02-15 12:00:00'),
(446, 254, 'Software Installation', 'Installed accounting software and Office 365', 1, '2024-02-16 13:30:00'),
(447, 254, 'Maintenance', 'Routine system maintenance and updates', 1, '2024-03-15 08:45:00'),
(448, 254, 'Repair', 'Fixed display driver issues', 1, '2024-04-30 10:20:00'),
(449, 254, 'Update', 'Updated Windows and application software', 1, '2024-06-05 12:30:00'),
(450, 255, 'Installation', 'Initial setup and configuration of PC-MKT-001', 1, '2024-03-01 05:30:00'),
(451, 255, 'Software Installation', 'Installed Adobe Creative Suite and design tools', 1, '2024-03-02 07:15:00'),
(452, 255, 'Maintenance', 'System optimization and disk cleanup', 1, '2024-04-01 11:30:00'),
(453, 255, 'Repair', 'Fixed graphics card driver issues', 1, '2024-05-15 09:45:00'),
(454, 255, 'Update', 'Updated Windows and application software', 1, '2024-06-10 13:20:00'),
(455, 256, 'Installation', 'Initial setup and configuration of PC-MKT-002', 1, '2024-03-05 07:15:00'),
(456, 256, 'Software Installation', 'Installed Adobe Creative Suite and design tools', 1, '2024-03-06 09:00:00'),
(457, 256, 'Maintenance', 'Routine system maintenance and updates', 1, '2024-04-05 12:45:00'),
(458, 256, 'Repair', 'Fixed network connectivity issues', 1, '2024-05-20 08:30:00'),
(459, 256, 'Update', 'Updated Windows and application software', 1, '2024-06-15 10:45:00'),
(460, 274, 'Installation', 'Initial setup and configuration of LAP-IT-001', 1, '2024-01-25 06:00:00'),
(461, 274, 'Software Installation', 'Installed Windows 11 and development tools', 1, '2024-01-26 08:30:00'),
(462, 274, 'Maintenance', 'Cleaned keyboard and updated drivers', 1, '2024-02-25 11:15:00'),
(463, 274, 'Repair', 'Replaced faulty battery', 1, '2024-03-20 13:45:00'),
(464, 274, 'Update', 'Updated system packages and security patches', 1, '2024-04-15 07:30:00'),
(465, 275, 'Installation', 'Initial setup and configuration of LAP-IT-002', 1, '2024-01-30 10:45:00'),
(466, 275, 'Software Installation', 'Installed Ubuntu Linux and development tools', 1, '2024-01-31 12:20:00'),
(467, 275, 'Maintenance', 'System optimization and disk cleanup', 1, '2024-03-01 09:00:00'),
(468, 275, 'Repair', 'Fixed display issues', 1, '2024-03-25 11:30:00'),
(469, 275, 'Update', 'Updated system packages and security patches', 1, '2024-04-20 13:15:00'),
(470, 294, 'Installation', 'Initial setup and configuration of PRN-IT-001', 1, '2024-02-20 07:30:00'),
(471, 294, 'Configuration', 'Configured network printing and user access', 1, '2024-02-21 09:15:00'),
(472, 294, 'Maintenance', 'Cleaned print heads and replaced toner', 1, '2024-03-20 12:00:00'),
(473, 294, 'Repair', 'Fixed paper jam and sensor issues', 1, '2024-04-25 08:45:00'),
(474, 294, 'Update', 'Updated printer firmware', 1, '2024-05-30 10:30:00'),
(475, 295, 'Installation', 'Initial setup and configuration of PRN-IT-002', 1, '2024-02-25 11:00:00'),
(476, 295, 'Configuration', 'Configured network printing and user access', 1, '2024-02-26 13:30:00'),
(477, 295, 'Maintenance', 'Routine maintenance and parts replacement', 1, '2024-03-25 06:15:00'),
(478, 295, 'Repair', 'Fixed network connectivity issues', 1, '2024-04-30 09:00:00'),
(479, 295, 'Update', 'Updated printer firmware', 1, '2024-06-05 11:45:00'),
(480, 330, 'Installation', 'Initial setup and configuration of SRV-IT-001', 1, '2024-01-10 05:00:00'),
(481, 330, 'Configuration', 'Configured network services and security', 1, '2024-01-11 07:30:00'),
(482, 330, 'Maintenance', 'System optimization and log cleanup', 1, '2024-02-10 10:15:00'),
(483, 330, 'Repair', 'Fixed disk space issues', 1, '2024-03-15 12:45:00'),
(484, 330, 'Update', 'Updated server operating system and patches', 1, '2024-04-20 08:30:00'),
(485, 331, 'Installation', 'Initial setup and configuration of SRV-IT-002', 1, '2024-01-15 06:30:00'),
(486, 331, 'Configuration', 'Configured database services and backup', 1, '2024-01-16 08:45:00'),
(487, 331, 'Maintenance', 'Database optimization and maintenance', 1, '2024-02-15 11:00:00'),
(488, 331, 'Repair', 'Fixed memory allocation issues', 1, '2024-03-20 13:30:00'),
(489, 331, 'Update', 'Updated database software and security patches', 1, '2024-04-25 09:15:00'),
(490, 340, 'Installation', 'Initial setup and configuration of TAB-IT-001', 1, '2024-03-10 09:00:00'),
(491, 340, 'Software Installation', 'Installed Android apps and security software', 1, '2024-03-11 11:30:00'),
(492, 340, 'Maintenance', 'System optimization and cache clearing', 1, '2024-04-10 13:45:00'),
(493, 340, 'Repair', 'Fixed touch screen calibration', 1, '2024-05-15 07:20:00'),
(494, 340, 'Update', 'Updated Android OS and applications', 1, '2024-06-20 10:00:00'),
(495, 341, 'Installation', 'Initial setup and configuration of TAB-IT-002', 1, '2024-03-15 10:30:00'),
(496, 341, 'Software Installation', 'Installed iOS apps and security software', 1, '2024-03-16 12:45:00'),
(497, 341, 'Maintenance', 'System optimization and cache clearing', 1, '2024-04-15 08:00:00'),
(498, 341, 'Repair', 'Fixed charging port issues', 1, '2024-05-20 11:30:00'),
(499, 341, 'Update', 'Updated iOS and applications', 1, '2024-06-25 13:15:00'),
(500, 346, 'Installation', 'Initial setup and configuration of PHN-IT-001', 1, '2024-03-20 11:00:00'),
(501, 346, 'Software Installation', 'Installed business apps and security software', 1, '2024-03-21 13:30:00'),
(502, 346, 'Maintenance', 'Battery optimization and app updates', 1, '2024-04-20 09:45:00'),
(503, 346, 'Repair', 'Fixed screen display issues', 1, '2024-05-25 12:00:00'),
(504, 346, 'Update', 'Updated Android OS and applications', 1, '2024-06-30 07:30:00'),
(505, 347, 'Installation', 'Initial setup and configuration of PHN-IT-002', 1, '2024-03-25 12:30:00'),
(506, 347, 'Software Installation', 'Installed business apps and security software', 1, '2024-03-26 14:45:00'),
(507, 347, 'Maintenance', 'Battery optimization and app updates', 1, '2024-04-25 10:15:00'),
(508, 347, 'Repair', 'Fixed charging port issues', 1, '2024-05-30 13:00:00'),
(509, 347, 'Update', 'Updated iOS and applications', 1, '2024-07-05 08:45:00'),
(510, 256, 'updated', 'Device information updated', 1, '2025-07-12 20:31:50');

-- --------------------------------------------------------

--
-- Table structure for table `issues`
--

CREATE TABLE `issues` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `issue_title` varchar(100) NOT NULL,
  `issue_description` text NOT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issues`
--

INSERT INTO `issues` (`id`, `device_id`, `reported_by`, `issue_title`, `issue_description`, `priority`, `status`, `reported_at`, `resolved_at`, `resolution_notes`) VALUES
(53, 249, 1, 'Blue Screen Error', 'Computer shows blue screen error when starting up applications', 'high', 'resolved', '2024-06-15 06:30:00', '2024-06-16 14:20:00', 'Updated graphics drivers and ran system diagnostics. Issue resolved.'),
(54, 249, 1, 'Slow Performance', 'Computer running very slowly, takes long time to open applications', 'medium', 'resolved', '2024-06-20 08:15:00', '2024-06-21 10:45:00', 'Cleaned temporary files and optimized startup programs. Performance improved.'),
(55, 249, 1, 'Network Connectivity Issues', 'Cannot connect to network printer and shared drives', 'high', 'open', '2024-07-10 05:45:00', NULL, NULL),
(56, 250, 1, 'RAM Module Failure', 'System crashes randomly, suspect faulty RAM', 'critical', 'resolved', '2024-06-18 11:20:00', '2024-06-19 16:30:00', 'Replaced faulty RAM module with new one. System stable now.'),
(57, 250, 1, 'Development Tools Not Working', 'Visual Studio and other IDEs not launching properly', 'high', 'resolved', '2024-06-25 07:30:00', '2024-06-26 12:15:00', 'Reinstalled development tools and updated .NET framework.'),
(58, 250, 1, 'USB Ports Not Responding', 'External devices not detected when plugged in', 'medium', 'in_progress', '2024-07-05 10:45:00', NULL, NULL),
(59, 251, 1, 'HR Software Crashes', 'HR management software crashes when generating reports', 'high', 'resolved', '2024-06-22 12:10:00', '2024-06-23 11:20:00', 'Updated HR software to latest version and cleared cache.'),
(60, 251, 1, 'Printer Not Working', 'Cannot print documents from HR applications', 'medium', 'resolved', '2024-06-28 06:15:00', '2024-06-29 14:30:00', 'Reconfigured printer settings and updated drivers.'),
(61, 251, 1, 'Slow Excel Performance', 'Excel files with large datasets taking too long to load', 'medium', 'open', '2024-07-08 13:20:00', NULL, NULL),
(62, 252, 1, 'Display Issues', 'Screen flickering and showing distorted colors', 'high', 'resolved', '2024-06-30 09:45:00', '2024-07-01 10:15:00', 'Replaced display cable and updated graphics drivers.'),
(63, 252, 1, 'Office 365 Login Problems', 'Cannot sign in to Office 365 applications', 'high', 'resolved', '2024-07-02 05:30:00', '2024-07-03 11:45:00', 'Cleared browser cache and reset Office credentials.'),
(64, 252, 1, 'Keyboard Not Responding', 'Some keys on keyboard not working properly', 'medium', 'in_progress', '2024-07-12 11:20:00', NULL, NULL),
(65, 253, 1, 'Accounting Software Error', 'Financial software showing calculation errors', 'critical', 'resolved', '2024-06-12 07:20:00', '2024-06-13 15:30:00', 'Updated accounting software and verified database integrity.'),
(66, 253, 1, 'Network Connection Lost', 'Frequent disconnections from network during work', 'high', 'resolved', '2024-06-19 10:15:00', '2024-06-20 09:45:00', 'Replaced network cable and updated network drivers.'),
(67, 253, 1, 'Backup Software Issues', 'Automatic backup not working properly', 'medium', 'open', '2024-07-01 13:30:00', NULL, NULL),
(68, 254, 1, 'Dual Monitor Setup Problem', 'Second monitor not displaying correctly', 'medium', 'resolved', '2024-06-14 08:45:00', '2024-06-15 13:20:00', 'Reconfigured display settings and updated graphics drivers.'),
(69, 254, 1, 'Excel File Corruption', 'Important financial spreadsheet showing corrupted data', 'critical', 'resolved', '2024-06-21 11:30:00', '2024-06-22 12:15:00', 'Recovered file from backup and restored data integrity.'),
(70, 254, 1, 'Slow System Boot', 'Computer taking too long to start up', 'low', 'open', '2024-07-03 06:00:00', NULL, NULL),
(71, 255, 1, 'Adobe Creative Suite Crashes', 'Photoshop and Illustrator crashing frequently', 'high', 'resolved', '2024-06-16 12:45:00', '2024-06-17 11:30:00', 'Reinstalled Adobe Creative Suite and cleared preferences.'),
(72, 255, 1, 'Graphics Card Overheating', 'Graphics card getting too hot during design work', 'high', 'resolved', '2024-06-24 09:20:00', '2024-06-25 14:45:00', 'Cleaned graphics card and improved ventilation.'),
(73, 255, 1, 'Large File Processing Issues', 'Cannot process large design files efficiently', 'medium', 'in_progress', '2024-07-06 07:15:00', NULL, NULL),
(74, 256, 1, 'Color Calibration Problems', 'Monitor colors not displaying accurately for design work', 'high', 'resolved', '2024-06-26 06:30:00', '2024-06-27 16:20:00', 'Recalibrated monitor and updated color profiles.'),
(75, 256, 1, 'Design Software Performance', 'Design applications running slowly with large files', 'medium', 'resolved', '2024-07-04 10:45:00', '2024-07-05 11:15:00', 'Optimized system settings and increased virtual memory.'),
(76, 256, 1, 'External Hard Drive Issues', 'External storage device not recognized', 'medium', 'open', '2024-07-09 12:30:00', NULL, NULL),
(77, 274, 1, 'Battery Not Charging', 'Laptop battery not charging when plugged in', 'high', 'resolved', '2024-06-17 07:45:00', '2024-06-18 12:30:00', 'Replaced faulty battery with new one.'),
(78, 274, 1, 'WiFi Connection Problems', 'Cannot connect to WiFi networks', 'high', 'resolved', '2024-06-23 11:15:00', '2024-06-24 09:45:00', 'Updated WiFi drivers and reset network settings.'),
(79, 274, 1, 'Overheating Issues', 'Laptop getting very hot during use', 'medium', 'in_progress', '2024-07-07 08:20:00', NULL, NULL),
(80, 275, 1, 'Linux System Update Failed', 'Ubuntu system update causing boot problems', 'critical', 'resolved', '2024-06-19 13:30:00', '2024-06-20 13:45:00', 'Booted in recovery mode and fixed broken packages.'),
(81, 275, 1, 'Development Environment Issues', 'Docker containers not running properly', 'high', 'resolved', '2024-06-27 09:00:00', '2024-06-28 15:30:00', 'Reinstalled Docker and updated container configurations.'),
(82, 275, 1, 'Display Brightness Problems', 'Screen brightness not adjusting properly', 'low', 'open', '2024-07-11 11:45:00', NULL, NULL),
(83, 294, 1, 'Paper Jam Error', 'Printer showing paper jam error but no paper stuck', 'medium', 'resolved', '2024-06-13 05:30:00', '2024-06-14 10:15:00', 'Cleaned paper sensors and reset printer.'),
(84, 294, 1, 'Print Quality Issues', 'Printed documents showing streaks and poor quality', 'medium', 'resolved', '2024-06-29 08:45:00', '2024-06-30 13:20:00', 'Replaced toner cartridge and cleaned print heads.'),
(85, 294, 1, 'Network Printing Problems', 'Cannot print from network computers', 'high', 'open', '2024-07-02 12:15:00', NULL, NULL),
(86, 295, 1, 'Printer Offline', 'Printer showing offline status in network', 'high', 'resolved', '2024-06-25 06:20:00', '2024-06-26 11:30:00', 'Reset network settings and updated printer firmware.'),
(87, 295, 1, 'Print Queue Stuck', 'Print jobs stuck in queue and not printing', 'medium', 'resolved', '2024-07-01 11:30:00', '2024-07-02 10:45:00', 'Cleared print queue and restarted print spooler service.'),
(88, 295, 1, 'Toner Low Warning', 'Toner cartridge needs replacement soon', 'low', 'open', '2024-07-10 09:00:00', NULL, NULL),
(89, 330, 1, 'High CPU Usage', 'Server CPU usage at 95% causing slow response', 'critical', 'resolved', '2024-06-10 23:30:00', '2024-06-12 08:15:00', 'Identified and terminated resource-intensive processes.'),
(90, 330, 1, 'Disk Space Warning', 'Server disk space running low', 'high', 'resolved', '2024-06-28 03:45:00', '2024-06-29 12:30:00', 'Cleaned log files and archived old data.'),
(91, 330, 1, 'Service Crashes', 'Critical services crashing frequently', 'critical', 'in_progress', '2024-07-04 00:20:00', NULL, NULL),
(92, 331, 1, 'Database Connection Errors', 'Database connections timing out', 'critical', 'resolved', '2024-06-15 01:15:00', '2024-06-16 10:30:00', 'Optimized database queries and increased connection pool.'),
(93, 331, 1, 'Backup Failure', 'Automated backup process failing', 'high', 'resolved', '2024-06-29 22:45:00', '2024-07-01 07:20:00', 'Fixed backup script and verified storage space.'),
(94, 331, 1, 'Memory Leak Issues', 'Server memory usage increasing over time', 'high', 'open', '2024-07-08 02:30:00', NULL, NULL),
(95, 340, 1, 'Touch Screen Not Responding', 'Touch screen not responding to touch input', 'high', 'resolved', '2024-06-20 10:20:00', '2024-06-21 15:45:00', 'Calibrated touch screen and updated drivers.'),
(96, 340, 1, 'App Crashes', 'Business applications crashing frequently', 'medium', 'resolved', '2024-07-03 07:30:00', '2024-07-04 12:15:00', 'Updated apps and cleared cache data.'),
(97, 340, 1, 'Battery Draining Fast', 'Tablet battery draining much faster than usual', 'medium', 'open', '2024-07-13 13:45:00', NULL, NULL),
(98, 341, 1, 'WiFi Connection Issues', 'Cannot connect to WiFi networks', 'high', 'resolved', '2024-06-22 08:15:00', '2024-06-23 13:30:00', 'Reset network settings and updated WiFi drivers.'),
(99, 341, 1, 'iOS Update Problems', 'iOS update stuck and not completing', 'medium', 'resolved', '2024-07-05 11:20:00', '2024-07-06 09:45:00', 'Forced restart and completed update via iTunes.'),
(100, 341, 1, 'App Store Not Working', 'Cannot download or update apps', 'medium', 'open', '2024-07-14 09:30:00', NULL, NULL),
(101, 346, 1, 'Screen Cracked', 'Phone screen cracked after accidental drop', 'medium', 'resolved', '2024-06-24 13:45:00', '2024-06-25 14:20:00', 'Replaced screen with new one.'),
(102, 346, 1, 'Camera Not Working', 'Phone camera not focusing properly', 'low', 'resolved', '2024-07-06 07:15:00', '2024-07-07 11:30:00', 'Cleaned camera lens and updated camera app.'),
(103, 346, 1, 'Battery Swelling', 'Phone battery showing signs of swelling', 'critical', 'open', '2024-07-15 10:45:00', NULL, NULL),
(104, 347, 1, 'Charging Port Damaged', 'Phone not charging due to damaged charging port', 'high', 'resolved', '2024-06-26 09:30:00', '2024-06-27 15:15:00', 'Replaced charging port assembly.'),
(105, 347, 1, 'Speaker Issues', 'Phone speaker producing distorted sound', 'low', 'resolved', '2024-07-08 06:20:00', '2024-07-09 10:45:00', 'Cleaned speaker grills and updated audio drivers.'),
(106, 347, 1, 'GPS Not Working', 'GPS navigation not functioning properly', 'medium', 'open', '2024-07-16 11:30:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

CREATE TABLE `maintenance` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `maintenance_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `scheduled_date` date NOT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `performed_by` int(11) DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `maintenance_cost` decimal(10,2) DEFAULT NULL,
  `parts_replaced` tinyint(1) DEFAULT 0,
  `parts_details` text DEFAULT NULL,
  `issues_found` text DEFAULT NULL,
  `resolution` text DEFAULT NULL,
  `device_health_impact` enum('significant_improvement','minor_improvement','no_change','deterioration') DEFAULT 'no_change',
  `next_recommended_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance`
--

INSERT INTO `maintenance` (`id`, `device_id`, `maintenance_type`, `description`, `scheduled_date`, `status`, `performed_by`, `completion_date`, `notes`, `created_at`, `maintenance_cost`, `parts_replaced`, `parts_details`, `issues_found`, `resolution`, `device_health_impact`, `next_recommended_date`) VALUES
(9, 249, 'Preventive', 'Routine system maintenance and optimization', '2024-06-15', 'completed', 1, '2024-06-15', 'Cleaned system files, updated drivers, and optimized startup programs. System performance improved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'High disk fragmentation', 'Defragmented hard drive and cleaned temporary files', 'significant_improvement', '2024-09-15'),
(10, 249, 'Corrective', 'Graphics card driver update and system diagnostics', '2024-06-20', 'completed', 1, '2024-06-20', 'Updated graphics drivers to resolve blue screen errors. Ran system diagnostics to ensure stability.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Outdated graphics drivers causing crashes', 'Updated to latest stable drivers', 'significant_improvement', '2024-09-20'),
(11, 249, 'Preventive', 'Network configuration and connectivity check', '2024-07-10', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 0.00, 0, NULL, NULL, NULL, 'no_change', '2024-10-10'),
(12, 250, 'Corrective', 'RAM module replacement and system testing', '2024-06-18', 'completed', 1, '2024-06-19', 'Replaced faulty RAM module with new 8GB DDR4 module. System stability restored.', '2025-07-12 20:28:12', 45.00, 1, '8GB DDR4 RAM module', 'Faulty RAM causing system crashes', 'Replaced with new RAM module', 'significant_improvement', '2024-09-18'),
(13, 250, 'Preventive', 'Development environment maintenance', '2024-06-25', 'completed', 1, '2024-06-26', 'Updated development tools and frameworks. Reinstalled Visual Studio and related components.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Corrupted development tool installations', 'Clean reinstall of development environment', 'significant_improvement', '2024-09-25'),
(14, 250, 'Corrective', 'USB controller driver update', '2024-07-05', 'in_progress', 1, NULL, 'Currently updating USB drivers and testing external device connectivity.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'USB ports not detecting devices', 'Updating USB controller drivers', 'no_change', '2024-10-05'),
(15, 251, 'Corrective', 'HR software update and cache clearing', '2024-06-22', 'completed', 1, '2024-06-23', 'Updated HR management software to latest version and cleared application cache. Performance improved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Software crashes due to corrupted cache', 'Updated software and cleared cache', 'significant_improvement', '2024-09-22'),
(16, 251, 'Preventive', 'Printer driver update and configuration', '2024-06-28', 'completed', 1, '2024-06-29', 'Updated printer drivers and reconfigured print settings for HR applications.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Outdated printer drivers', 'Updated to latest printer drivers', 'minor_improvement', '2024-09-28'),
(17, 251, 'Preventive', 'Excel optimization and performance tuning', '2024-07-08', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 0.00, 0, NULL, NULL, NULL, 'no_change', '2024-10-08'),
(18, 252, 'Corrective', 'Display cable replacement and graphics driver update', '2024-06-30', 'completed', 1, '2024-07-01', 'Replaced faulty display cable and updated graphics drivers. Display issues resolved.', '2025-07-12 20:28:12', 12.50, 1, 'HDMI cable', 'Faulty display cable causing screen issues', 'Replaced display cable and updated drivers', 'significant_improvement', '2024-09-30'),
(19, 252, 'Corrective', 'Office 365 credential reset and cache clearing', '2024-07-02', 'completed', 1, '2024-07-03', 'Reset Office 365 credentials and cleared browser cache. Login issues resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Corrupted Office credentials', 'Reset credentials and cleared cache', 'significant_improvement', '2024-10-02'),
(20, 252, 'Corrective', 'Keyboard cleaning and key replacement', '2024-07-12', 'in_progress', 1, NULL, 'Cleaning keyboard and replacing non-responsive keys.', '2025-07-12 20:28:12', 8.00, 1, 'Individual keyboard keys', 'Dirty keyboard and stuck keys', 'Cleaning and replacing keys', 'minor_improvement', '2024-10-12'),
(21, 253, 'Corrective', 'Accounting software update and database verification', '2024-06-12', 'completed', 1, '2024-06-13', 'Updated accounting software and verified database integrity. Calculation errors resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Software bugs causing calculation errors', 'Updated to latest software version', 'significant_improvement', '2024-09-12'),
(22, 253, 'Corrective', 'Network cable replacement and driver update', '2024-06-19', 'completed', 1, '2024-06-20', 'Replaced faulty network cable and updated network drivers. Connection stability improved.', '2025-07-12 20:28:12', 15.00, 1, 'Cat6 network cable', 'Faulty network cable causing disconnections', 'Replaced network cable and updated drivers', 'significant_improvement', '2024-09-19'),
(23, 253, 'Preventive', 'Backup software configuration and testing', '2024-07-01', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 0.00, 0, NULL, NULL, NULL, 'no_change', '2024-10-01'),
(24, 254, 'Corrective', 'Dual monitor configuration and graphics driver update', '2024-06-14', 'completed', 1, '2024-06-15', 'Reconfigured dual monitor setup and updated graphics drivers. Display issues resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Incorrect display configuration', 'Reconfigured monitor settings', 'significant_improvement', '2024-09-14'),
(25, 254, 'Corrective', 'Excel file recovery and data restoration', '2024-06-21', 'completed', 1, '2024-06-22', 'Recovered corrupted Excel file from backup and restored data integrity.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Corrupted Excel file', 'Recovered from backup and restored data', 'significant_improvement', '2024-09-21'),
(26, 254, 'Preventive', 'System optimization and startup program management', '2024-07-03', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 0.00, 0, NULL, NULL, NULL, 'no_change', '2024-10-03'),
(27, 255, 'Corrective', 'Adobe Creative Suite reinstallation and preferences reset', '2024-06-16', 'completed', 1, '2024-06-17', 'Reinstalled Adobe Creative Suite and reset preferences. Crashes resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Corrupted Adobe installation', 'Clean reinstall of Adobe Creative Suite', 'significant_improvement', '2024-09-16'),
(28, 255, 'Corrective', 'Graphics card cleaning and thermal paste replacement', '2024-06-24', 'completed', 1, '2024-06-25', 'Cleaned graphics card and replaced thermal paste. Overheating issues resolved.', '2025-07-12 20:28:12', 25.00, 1, 'Thermal paste', 'Dust buildup and dried thermal paste', 'Cleaned card and replaced thermal paste', 'significant_improvement', '2024-09-24'),
(29, 255, 'Preventive', 'System optimization for large file processing', '2024-07-06', 'in_progress', 1, NULL, 'Optimizing system settings and increasing virtual memory for large file processing.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Insufficient virtual memory', 'Increasing virtual memory allocation', 'minor_improvement', '2024-10-06'),
(30, 256, 'Corrective', 'Monitor color calibration and profile update', '2024-06-26', 'completed', 1, '2024-06-27', 'Recalibrated monitor colors and updated color profiles for accurate design work.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Incorrect color calibration', 'Recalibrated monitor and updated profiles', 'significant_improvement', '2024-09-26'),
(31, 256, 'Preventive', 'System optimization and memory management', '2024-07-04', 'completed', 1, '2024-07-05', 'Optimized system settings and increased virtual memory for better design software performance.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Low virtual memory affecting performance', 'Increased virtual memory allocation', 'minor_improvement', '2024-10-04'),
(32, 256, 'Corrective', 'External hard drive driver update and testing', '2024-07-09', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 0.00, 0, NULL, NULL, NULL, 'no_change', '2024-10-09'),
(33, 274, 'Corrective', 'Battery replacement and charging system check', '2024-06-17', 'completed', 1, '2024-06-18', 'Replaced faulty battery with new one and tested charging system. Charging issues resolved.', '2025-07-12 20:28:12', 85.00, 1, 'Laptop battery', 'Faulty battery not charging', 'Replaced with new battery', 'significant_improvement', '2024-09-17'),
(34, 274, 'Corrective', 'WiFi driver update and network configuration', '2024-06-23', 'completed', 1, '2024-06-24', 'Updated WiFi drivers and reset network settings. Connectivity issues resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Outdated WiFi drivers', 'Updated to latest WiFi drivers', 'significant_improvement', '2024-09-23'),
(35, 274, 'Preventive', 'Laptop cleaning and thermal management', '2024-07-07', 'in_progress', 1, NULL, 'Cleaning laptop internals and improving thermal management to resolve overheating.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Dust buildup causing overheating', 'Cleaning laptop and improving ventilation', 'minor_improvement', '2024-10-07'),
(36, 275, 'Corrective', 'Linux system recovery and package repair', '2024-06-19', 'completed', 1, '2024-06-20', 'Booted in recovery mode and fixed broken packages. System update issues resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Broken packages from failed update', 'Repaired packages in recovery mode', 'significant_improvement', '2024-09-19'),
(37, 275, 'Corrective', 'Docker reinstallation and container configuration', '2024-06-27', 'completed', 1, '2024-06-28', 'Reinstalled Docker and updated container configurations. Development environment restored.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Corrupted Docker installation', 'Clean reinstall of Docker', 'significant_improvement', '2024-09-27'),
(38, 275, 'Preventive', 'Display driver update and brightness calibration', '2024-07-11', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 0.00, 0, NULL, NULL, NULL, 'no_change', '2024-10-11'),
(39, 294, 'Corrective', 'Paper sensor cleaning and printer reset', '2024-06-13', 'completed', 1, '2024-06-14', 'Cleaned paper sensors and reset printer. False paper jam errors resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Dirty paper sensors causing false errors', 'Cleaned sensors and reset printer', 'significant_improvement', '2024-09-13'),
(40, 294, 'Corrective', 'Toner cartridge replacement and print head cleaning', '2024-06-29', 'completed', 1, '2024-06-30', 'Replaced toner cartridge and cleaned print heads. Print quality improved.', '2025-07-12 20:28:12', 45.00, 1, 'Toner cartridge', 'Low toner and dirty print heads', 'Replaced toner and cleaned heads', 'significant_improvement', '2024-09-29'),
(41, 294, 'Preventive', 'Network printing configuration and testing', '2024-07-02', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 0.00, 0, NULL, NULL, NULL, 'no_change', '2024-10-02'),
(42, 295, 'Corrective', 'Network settings reset and firmware update', '2024-06-25', 'completed', 1, '2024-06-26', 'Reset network settings and updated printer firmware. Offline status resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Network configuration issues', 'Reset network settings and updated firmware', 'significant_improvement', '2024-09-25'),
(43, 295, 'Corrective', 'Print spooler service restart and queue clearing', '2024-07-01', 'completed', 1, '2024-07-02', 'Restarted print spooler service and cleared stuck print jobs. Queue issues resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Stuck print jobs in queue', 'Restarted spooler and cleared queue', 'significant_improvement', '2024-10-01'),
(44, 295, 'Preventive', 'Toner level check and replacement planning', '2024-07-10', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 0.00, 0, NULL, NULL, NULL, 'no_change', '2024-10-10'),
(45, 330, 'Corrective', 'Process optimization and resource management', '2024-06-11', 'completed', 1, '2024-06-12', 'Identified and terminated resource-intensive processes. CPU usage normalized.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Resource-intensive processes consuming CPU', 'Terminated unnecessary processes', 'significant_improvement', '2024-09-11'),
(46, 330, 'Preventive', 'Log file cleanup and disk space management', '2024-06-28', 'completed', 1, '2024-06-29', 'Cleaned log files and archived old data. Disk space issues resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Accumulated log files consuming space', 'Cleaned logs and archived data', 'significant_improvement', '2024-09-28'),
(47, 330, 'Corrective', 'Service crash investigation and recovery', '2024-07-04', 'in_progress', 1, NULL, 'Investigating service crashes and implementing recovery procedures.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Critical services crashing frequently', 'Investigating crash causes and implementing fixes', 'no_change', '2024-10-04'),
(48, 331, 'Corrective', 'Database optimization and connection pool management', '2024-06-15', 'completed', 1, '2024-06-16', 'Optimized database queries and increased connection pool. Connection timeouts resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Database connection timeouts', 'Optimized queries and increased connection pool', 'significant_improvement', '2024-09-15'),
(49, 331, 'Corrective', 'Backup script repair and storage verification', '2024-06-30', 'completed', 1, '2024-07-01', 'Fixed backup script and verified storage space. Backup failures resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Backup script errors and insufficient space', 'Fixed script and verified storage', 'significant_improvement', '2024-09-30'),
(50, 331, 'Preventive', 'Memory leak investigation and monitoring', '2024-07-08', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 0.00, 0, NULL, NULL, NULL, 'no_change', '2024-10-08'),
(51, 340, 'Corrective', 'Touch screen calibration and driver update', '2024-06-20', 'completed', 1, '2024-06-21', 'Calibrated touch screen and updated drivers. Touch response improved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Touch screen calibration issues', 'Recalibrated screen and updated drivers', 'significant_improvement', '2024-09-20'),
(52, 340, 'Preventive', 'App updates and cache clearing', '2024-07-03', 'completed', 1, '2024-07-04', 'Updated business applications and cleared cache data. App crashes resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Outdated apps and corrupted cache', 'Updated apps and cleared cache', 'minor_improvement', '2024-10-03'),
(53, 340, 'Preventive', 'Battery optimization and performance tuning', '2024-07-13', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 0.00, 0, NULL, NULL, NULL, 'no_change', '2024-10-13'),
(54, 341, 'Corrective', 'Network settings reset and WiFi driver update', '2024-06-22', 'completed', 1, '2024-06-23', 'Reset network settings and updated WiFi drivers. Connectivity issues resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Network configuration problems', 'Reset settings and updated drivers', 'significant_improvement', '2024-09-22'),
(55, 341, 'Corrective', 'iOS update completion and system verification', '2024-07-05', 'completed', 1, '2024-07-06', 'Forced restart and completed iOS update via iTunes. Update issues resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Stuck iOS update process', 'Completed update via iTunes', 'significant_improvement', '2024-10-05'),
(56, 341, 'Preventive', 'App Store configuration and testing', '2024-07-14', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 0.00, 0, NULL, NULL, NULL, 'no_change', '2024-10-14'),
(57, 346, 'Corrective', 'Screen replacement and display testing', '2024-06-24', 'completed', 1, '2024-06-25', 'Replaced cracked screen with new one and tested display functionality.', '2025-07-12 20:28:12', 120.00, 1, 'Phone screen assembly', 'Cracked screen from accidental drop', 'Replaced with new screen', 'significant_improvement', '2024-09-24'),
(58, 346, 'Preventive', 'Camera lens cleaning and app update', '2024-07-06', 'completed', 1, '2024-07-07', 'Cleaned camera lens and updated camera app. Focus issues resolved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Dirty camera lens affecting focus', 'Cleaned lens and updated app', 'minor_improvement', '2024-10-06'),
(59, 346, 'Corrective', 'Battery replacement due to swelling', '2024-07-15', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 65.00, 1, 'Phone battery', NULL, NULL, 'no_change', '2024-10-15'),
(60, 347, 'Corrective', 'Charging port assembly replacement', '2024-06-26', 'completed', 1, '2024-06-27', 'Replaced damaged charging port assembly. Charging functionality restored.', '2025-07-12 20:28:12', 35.00, 1, 'Charging port assembly', 'Damaged charging port preventing charging', 'Replaced charging port assembly', 'significant_improvement', '2024-09-26'),
(61, 347, 'Preventive', 'Speaker cleaning and audio driver update', '2024-07-08', 'completed', 1, '2024-07-09', 'Cleaned speaker grills and updated audio drivers. Sound quality improved.', '2025-07-12 20:28:12', 0.00, 0, NULL, 'Dirty speaker grills affecting sound', 'Cleaned grills and updated drivers', 'minor_improvement', '2024-10-08'),
(62, 347, 'Preventive', 'GPS functionality check and calibration', '2024-07-16', 'scheduled', NULL, NULL, NULL, '2025-07-12 20:28:12', 0.00, 0, NULL, NULL, NULL, 'no_change', '2024-10-16');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `device_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`, `device_id`) VALUES
(1, 1, 4, 'i have got your issue', 1, '2025-06-19 07:27:50', NULL),
(2, 1, 4, 'come to my ffice', 1, '2025-06-19 07:34:48', NULL),
(3, 1, 4, 'Hey kelvin see me in the office', 1, '2025-06-19 16:56:28', NULL),
(4, 1, 4, 'Kevi kutokana na tatizo ulilo report power off, or restart Computer ita solve kama kuna tatizo lingine utanitaarifu', 1, '2025-06-20 16:25:17', NULL),
(5, 1, 4, 'Oi niaje', 1, '2025-06-20 22:03:15', NULL),
(6, 4, 1, 'New issue reported: Power supply (Priority: high)', 1, '2025-06-25 16:48:34', NULL),
(7, 1, 4, 'fanya kuchukua power suplly ip[o kabatini', 1, '2025-06-25 16:50:39', NULL),
(8, 1, 9, 'Nimepokea Ntaifanyia kazi', 1, '2025-07-09 17:00:57', NULL),
(9, 1, 4, 'hmhfcjkmghc', 1, '2025-07-12 16:38:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Device Management System', '2025-06-28 20:36:41', '2025-06-28 20:36:41'),
(2, 'company_name', '', '2025-06-28 20:36:41', '2025-07-02 12:50:13'),
(3, 'contact_email', 'admin@example.com', '2025-06-28 20:36:41', '2025-06-28 20:36:41'),
(4, 'password_expiry_days', '90', '2025-06-28 20:36:41', '2025-06-28 20:36:41'),
(5, 'max_login_attempts', '5', '2025-06-28 20:36:41', '2025-06-28 20:36:41'),
(6, 'session_timeout', '30', '2025-06-28 20:36:41', '2025-06-28 20:36:41'),
(7, 'default_maintenance_interval', '90', '2025-06-28 20:36:41', '2025-06-28 20:36:41'),
(8, 'maintenance_reminder_days', '7', '2025-06-28 20:36:41', '2025-06-28 20:36:41'),
(9, 'auto_schedule_maintenance', '1', '2025-06-28 20:36:41', '2025-06-28 20:36:41'),
(10, 'email_notifications', '1', '2025-06-28 20:36:41', '2025-06-28 20:36:41'),
(11, 'maintenance_notifications', '1', '2025-06-28 20:36:41', '2025-06-28 20:36:41'),
(12, 'issue_notifications', '1', '2025-06-28 20:36:41', '2025-06-28 20:36:41');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `timestamp`, `user_id`, `username`, `action`, `description`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, '2025-07-12 00:19:26', 1, 'admin', 'login', NULL, 'Admin user logged in', '127.0.0.1', 'Mozilla/5.0', '2025-06-26 19:36:49'),
(2, '2025-07-12 00:19:26', 1, 'admin', 'settings_update', NULL, 'Updated system settings', '127.0.0.1', 'Mozilla/5.0', '2025-06-27 19:36:49'),
(3, '2025-07-12 00:19:26', 2, 'user1', 'login', NULL, 'User logged in', '127.0.0.1', 'Mozilla/5.0', '2025-06-28 07:36:49'),
(4, '2025-07-12 00:19:26', 2, 'user1', 'maintenance_complete', NULL, 'Completed maintenance #123', '127.0.0.1', 'Mozilla/5.0', '2025-06-28 09:36:49'),
(5, '2025-07-12 00:19:26', 1, 'admin', 'user_create', NULL, 'Created new user: technician1', '127.0.0.1', 'Mozilla/5.0', '2025-06-28 14:36:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `department`, `role`, `created_at`, `deleted_at`) VALUES
(1, 'admin', '$2y$12$7JPfMFKrcBgdy99MZ9MaNOE.vIm05yd170nUqAKl/ZOBE9Wm0ushu', 'admin@emis.com', 'System Administrator', 'IT', 'admin', '2025-06-18 04:07:28', NULL),
(4, 'kevi', '$2y$12$7NCNa/02pNH1MshOpugCDel2uyP.ECc8.RuvRzK4Cglr1tlvc.yZ6', 'kevi@gmail.com', 'kevin samweli', 'IT', 'user', '2025-06-19 07:21:05', NULL),
(8, 'ema001', '$2y$12$mJ4TE/jaXbAgZyvvGf0p8O3Cfgt5QwnmT3.NHY/U9nbTTXMY0Qihi', 'emanuelchacha2468@gmail.com', 'emanuel chacha', 'IT', 'admin', '2025-06-29 13:49:37', NULL),
(9, 'nesta', '$2y$12$Z5s4Kvy1lQG.Rzz/fOqA6.i2kU.zr7evdlnsGnWi.t5eYfkSz.Rt2', 'nesta@gmail.com', 'nesta nesta', 'Finance', 'user', '2025-07-09 16:57:34', '2025-07-11 22:40:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `device_history`
--
ALTER TABLE `device_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `issues`
--
ALTER TABLE `issues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `reported_by` (`reported_by`);

--
-- Indexes for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `fk_messages_device` (`device_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=350;

--
-- AUTO_INCREMENT for table `device_history`
--
ALTER TABLE `device_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=511;

--
-- AUTO_INCREMENT for table `issues`
--
ALTER TABLE `issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `device_history`
--
ALTER TABLE `device_history`
  ADD CONSTRAINT `device_history_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `device_history_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `issues`
--
ALTER TABLE `issues`
  ADD CONSTRAINT `issues_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issues_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD CONSTRAINT `maintenance_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_device` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
