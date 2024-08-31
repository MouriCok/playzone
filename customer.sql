SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: `sport_booking`

-- --------------------------------------------------------

-- Table structure for table `customer`

CREATE TABLE `customer` (
  `cId` int(7) UNSIGNED NOT NULL,
  `cAvatar` blob,
  `cName` varchar(60) NOT NULL,
  `cUser` varchar(30) NOT NULL,
  `cEmail` varchar(60) NOT NULL,
  `cPhone` varchar(30) NOT NULL,
  `cPass` varchar(255) NOT NULL,
  `reset_token` varchar(255),
  `reset_expiry` DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`cId`, `cAvatar`, `cName`, `cUser`, `cEmail`, `cPhone`, `cPass`, `reset_expiry`) VALUES
(1, NULL, 'Nor Ajwad', 'ajwadgangster', 'ajwad@gmail.com', '1114956232', 'Ajwad18300', NOW() + INTERVAL 1 HOUR),
(2, NULL, 'Muhammad Zakaria Bin Sulfi', 'ria31', 'zak.akar@gmail.com', '1114956232', 'Zakaria3101', NOW() + INTERVAL 1 HOUR),
(3, 'bukh.png', 'Muhammad Bukhoury Bin Muslim', 'bukh12', '2022977505@student.uitm.edu.my', '1114956232', 'Bukh1205', NOW() + INTERVAL 1 HOUR),
(4, NULL, 'Shaikh Danial Bin Shaikh Mahmud', 'dani33', 'sahih09@gmail.com', '1114956232', 'Danial0909', NOW() + INTERVAL 1 HOUR),
(5, NULL, 'Tengku Shaiful Bin Tengku Hafiz', 'teng00', 'teng.ful@gmail.com', '1114956232', 'Tengku567', NOW() + INTERVAL 1 HOUR),
(6, NULL, 'Laila Finnaha Binti Nor Baahar', 'lailaila56', 'lailailai@gmail.com', '1114956232', 'Laila3045', NOW() + INTERVAL 1 HOUR),
(7, NULL, 'Muhammad Nur Khalid Bin Jonari', 'khalid3', 'khalid.hyper@gmail.com', '1114956232', 'Khalid8712', NOW() + INTERVAL 1 HOUR),
(8, NULL, 'Muhammad Jasin Bin Muhammad Lekir', 'jasin01', 'hang.jasin@gmail.com', '1114956232', 'Jasin172637', NOW() + INTERVAL 1 HOUR),
(9, NULL, 'Siti Senah Binti Muhammad Andul', 'senah72', 'senah_gais@gmail.com', '1114956232', 'Senah736317', NOW() + INTERVAL 1 HOUR),
(10, NULL, 'Baharudin Bin Zamri', 'din25', 'din_ganu@gmail.com', '1114956232', 'Bahar362367', NOW() + INTERVAL 1 HOUR),
(11, NULL, 'Ijo Mushiro Bin Biro', 'ijohijau', 'ijoijohijo@gmail.com', '1114956232', 'Ijom276386', NOW() + INTERVAL 1 HOUR),
(12, NULL, 'Muhammad Nur Zulfaqar', 'zul10', 'zulfa10@gmail.com', '1114956232', 'Zul12345', NOW() + INTERVAL 1 HOUR);

-- Indexes for dumped tables

-- Indexes for table `customer`

ALTER TABLE `customer`
  ADD PRIMARY KEY (`cId`);

-- AUTO_INCREMENT for dumped tables

-- AUTO_INCREMENT for table `menu`
ALTER TABLE `customer`
  MODIFY `cId` int(7) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;
