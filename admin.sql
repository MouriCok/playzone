-- Table structure for table `admin`

-- Step 1: Create the admin table with empId set to allow NULL (trigger will handle its value)
CREATE TABLE `admin` (
  `aId` int(11) NOT NULL AUTO_INCREMENT,
  `aPosition` varchar(60) NOT NULL,
  `empId` varchar(60) DEFAULT NULL,  -- empId will be auto-generated
  `aAvatar` blob,
  `aName` varchar(60) NOT NULL,
  `aUser` varchar(30) NOT NULL,
  `aEmail` varchar(60) NOT NULL,
  `aPhone` varchar(30) NOT NULL,
  `aPass` varchar(255) NOT NULL,
  PRIMARY KEY (`aId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 2: Create a trigger to auto-generate empId before insert
DELIMITER //

CREATE TRIGGER `before_admin_insert`
BEFORE INSERT ON `admin`
FOR EACH ROW
BEGIN
  DECLARE maxEmpId INT DEFAULT 0;
  DECLARE newEmpId VARCHAR(60);

  -- Fetch the last numerical part of the highest empId and increment it
  SELECT COALESCE(MAX(CAST(SUBSTRING(empId, 4) AS UNSIGNED)), 0) INTO maxEmpId
  FROM `admin`
  WHERE empId REGEXP '^EMP[0-9]+$';
  
  -- Generate the new empId with a prefix 'EMP' followed by the incremented number
  SET newEmpId = CONCAT('EMP', LPAD(maxEmpId + 1, 5, '0'));
  
  -- Set the new empId
  SET NEW.empId = newEmpId;
END //

DELIMITER ;

-- Step 3: Insert example data (without specifying empId)
INSERT INTO `admin` (`aPosition`, `aAvatar`, `aName`, `aUser`, `aEmail`, `aPhone`, `aPass`) VALUES
('Assistant Manager', 'bukh.png', 'Muhammad Bukhoury Bin Muslim', 'bukh05', 'mbukhoury.mb@gmail.com', '1114956232', '#Bukh1205');
