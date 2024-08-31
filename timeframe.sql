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
('Friday', '14:00:00', '24:00:00'),
('Saturday', '10:00:00', '24:00:00');