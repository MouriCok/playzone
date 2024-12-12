CREATE TABLE `customer` (
  `cId` int(11) NOT NULL AUTO_INCREMENT,
  `google_uid` VARCHAR(255) DEFAULT NULL,
  `cAvatar` blob,
  `cName` varchar(60) NOT NULL,
  `cUser` varchar(30) NOT NULL,
  `cEmail` varchar(60) NOT NULL,
  `cPhone` varchar(30) NOT NULL,
  `cPass` varchar(255) NOT NULL,
  `reset_token` varchar(255),
  `reset_expiry` DATETIME,
  PRIMARY KEY (`cId`),
  UNIQUE KEY `unique_email` (`cEmail`),
  UNIQUE KEY `unique_google_uid` (`google_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `customer` (`cName`, `cUser`, `cEmail`, `cPhone`, `cPass`, `reset_expiry`) VALUES
('Nor Ajwad', 'ajwadgangster', 'ajwad@gmail.com', '1114956232', 'Ajwad18300', NOW() + INTERVAL 1 HOUR),
('Muhammad Zakaria Bin Sulfi', 'ria31', 'zak.akar@gmail.com', '1114956232', 'Zakaria3101', NOW() + INTERVAL 1 HOUR),
('Muhammad Bukhoury Bin Muslim', 'bukh12', '2022977505@student.uitm.edu.my', '1114956232', 'Bukh1205', NOW() + INTERVAL 1 HOUR),
('Shaikh Danial Bin Shaikh Mahmud', 'dani33', 'sahih09@gmail.com', '1114956232', 'Danial0909', NOW() + INTERVAL 1 HOUR),
('Tengku Shaiful Bin Tengku Hafiz', 'teng00', 'teng.ful@gmail.com', '1114956232', 'Tengku567', NOW() + INTERVAL 1 HOUR),
('Laila Finnaha Binti Nor Baahar', 'lailaila56', 'lailailai@gmail.com', '1114956232', 'Laila3045', NOW() + INTERVAL 1 HOUR),
('Muhammad Nur Khalid Bin Jonari', 'khalid3', 'khalid.hyper@gmail.com', '1114956232', 'Khalid8712', NOW() + INTERVAL 1 HOUR),
('Muhammad Jasin Bin Muhammad Lekir', 'jasin01', 'hang.jasin@gmail.com', '1114956232', 'Jasin172637', NOW() + INTERVAL 1 HOUR),
('Siti Senah Binti Muhammad Andul', 'senah72', 'senah_gais@gmail.com', '1114956232', 'Senah736317', NOW() + INTERVAL 1 HOUR),
('Baharudin Bin Zamri', 'din25', 'din_ganu@gmail.com', '1114956232', 'Bahar362367', NOW() + INTERVAL 1 HOUR),
('Ijo Mushiro Bin Biro', 'ijohijau', 'ijoijohijo@gmail.com', '1114956232', 'Ijom276386', NOW() + INTERVAL 1 HOUR),
('Muhammad Nur Zulfaqar', 'zul10', 'zulfa10@gmail.com', '1114956232', 'Zul12345', NOW() + INTERVAL 1 HOUR);
