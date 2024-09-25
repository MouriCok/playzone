CREATE TABLE `courts` (
  `court_id` VARCHAR(10) PRIMARY KEY,
  `courtType` VARCHAR(60) NOT NULL,
  FOREIGN KEY (`courtType`) REFERENCES `court_count`(`courtType`) ON DELETE CASCADE
);

-- Insert courts for each category
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
