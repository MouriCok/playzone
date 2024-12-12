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



CREATE TABLE `courts` (
  `court_id` VARCHAR(10) PRIMARY KEY,
  `courtType` VARCHAR(60) NOT NULL,
  FOREIGN KEY (`courtType`) REFERENCES `court_count`(`courtType`) ON DELETE CASCADE
);

INSERT INTO `courts` (`court_id`, `courtType`) VALUES
('Bb1', 'Basketball'),
('Bb2', 'Basketball'),
('Bm1', 'Badminton'),
('Bm2', 'Badminton'),
('Bm3', 'Badminton'),
('Bm4', 'Badminton'),
('V1', 'Volleyball'),
('V2', 'Volleyball'),
('V3', 'Volleyball'),
('T1', 'Tennis'),
('T2', 'Tennis'),
('T3', 'Tennis'),
('F1', 'Futsal'),
('F2', 'Futsal'),
('Bw1', 'Bowling'),
('Bw2', 'Bowling'),
('Bw3', 'Bowling'),
('Bw4', 'Bowling'),
('PX1', 'PSXbox'),
('PX2', 'PSXbox'),
('PX3', 'PSXbox'),
('PX4', 'PSXbox'),
('PX5', 'PSXbox');



CREATE TABLE `premises_hours` (
  `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
  `open_time` TIME NOT NULL,
  `close_time` TIME NOT NULL,
  PRIMARY KEY (`day_of_week`)
);

INSERT INTO `premises_hours` (`day_of_week`, `open_time`, `close_time`) VALUES
('Monday', '10:00:00', '22:00:00'),
('Tuesday', '10:00:00', '22:00:00'),
('Wednesday', '10:00:00', '22:00:00'),
('Thursday', '10:00:00', '22:00:00'),
('Friday', '14:00:00', '23:59:59'),
('Saturday', '10:00:00', '23:59:59');



CREATE TABLE `bookings` (
  `bID` int(11) NOT NULL AUTO_INCREMENT,
  `cName` varchar(60) NOT NULL,
  `cEmail` varchar(60) NOT NULL,
  `cPhone` varchar(30) NOT NULL,
  `datestart` datetime NOT NULL,
  `dateend` datetime NOT NULL,
  `courtType` varchar(60) NOT NULL,
  `people` int(5) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `preferredCourt` VARCHAR(10),
  `court_id` VARCHAR(10),
  `payment_status` VARCHAR(20) NOT NULL,
  `transaction_id` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`bID`)
);



CREATE TABLE booking_history (
  `historyID` INT AUTO_INCREMENT PRIMARY KEY,
  `bID` INT NOT NULL,
  `cEmail` VARCHAR(255) NOT NULL,
  `courtType` VARCHAR(100),
  `preferredCourt` VARCHAR(100),
  `datestart` DATETIME,
  `dateend` DATETIME,
  `people` INT,
  `payment_status` VARCHAR(50),
  `moved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);