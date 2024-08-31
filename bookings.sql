SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `bookings`, `court_availability`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `bID` int(11) NOT NULL AUTO_INCREMENT,
  `cName` varchar(60) NOT NULL,
  `cEmail` varchar(60) NOT NULL,
  `cPhone` varchar(30) NOT NULL,
  `datestart` datetime NOT NULL,
  `dateend` datetime NOT NULL,
  `courtType` varchar(60) NOT NULL,
  `people` int(5) NOT NULL CHECK (`people` > 0),
  `price` DECIMAL(10, 2) NOT NULL,
  `payment_status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
  `transaction_id` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`bID`),
  CONSTRAINT chk_dates CHECK (`dateend` > `datestart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `court_availability`
--

CREATE TABLE `court_availability` (
  `courtID` int(11) NOT NULL AUTO_INCREMENT,
  `courtType` varchar(60) NOT NULL,
  `availableFrom` datetime NOT NULL,
  `availableTo` datetime NOT NULL,
  PRIMARY KEY (`courtID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `court_count`
--

CREATE TABLE `court_count` (
  `courtType` varchar(60) NOT NULL PRIMARY KEY,
  `total_courts` int(11) NOT NULL
);

INSERT INTO `court_count` (`courtType`, `total_courts`) VALUES
('Basketball', 2),
('Badminton', 4),
('Volleyball', 3),
('Tennis', 3),
('Futsal', 2),
('Bowling', 4),
('PSXbox', 5);
