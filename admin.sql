CREATE TABLE `admin` (
  `aId` int(11) NOT NULL AUTO_INCREMENT,
  `aPosition` varchar(60) NOT NULL,
  `empId` varchar(60) DEFAULT NULL,
  `aAvatar` blob,
  `aName` varchar(60) NOT NULL,
  `aUser` varchar(30) NOT NULL,
  `aEmail` varchar(60) NOT NULL,
  `aPhone` varchar(30) NOT NULL,
  `aPass` varchar(255) NOT NULL,
  PRIMARY KEY (`aId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER //

CREATE TRIGGER `before_admin_insert`
BEFORE INSERT ON `admin`
FOR EACH ROW
BEGIN
  DECLARE maxEmpId INT DEFAULT 0;
  DECLARE newEmpId VARCHAR(60);

  SELECT COALESCE(MAX(CAST(SUBSTRING(empId, 4) AS UNSIGNED)), 0) INTO maxEmpId
  FROM `admin`
  WHERE empId REGEXP '^EMP[0-9]+$';
  
  SET newEmpId = CONCAT('EMP', LPAD(maxEmpId + 1, 5, '0'));
  
  SET NEW.empId = newEmpId;
END //

DELIMITER ;

INSERT INTO `admin` (`aPosition`, `aName`, `aUser`, `aEmail`, `aPhone`, `aPass`) VALUES
('Assistant Manager', 'Muhammad Bukhoury Bin Muslim', 'bukh05', 'mbukhoury.mb@gmail.com', '1114956232', '#Bukh1205');
