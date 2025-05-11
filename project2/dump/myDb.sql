-- ==========================================================
-- Global setup
-- ==========================================================
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
 /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
 /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 /*!40101 SET NAMES utf8mb4 */;

-- ==========================================================
-- Dumping database structure for `myDb`
-- ==========================================================

-- --------------------------------------------------------
-- Table `agency`
-- --------------------------------------------------------
CREATE TABLE `agency` (
  `ID` INT NOT NULL, -- domain: numerical ID, primary key
  `NAME` VARCHAR(100) NOT NULL, -- domain: non-empty string
  `URL` VARCHAR(255) NOT NULL, -- domain: non-empty string
  `TIME_ZONE` VARCHAR(50) NOT NULL, -- domain: non-empty string
  `TELEPHONE` VARCHAR(20) NOT NULL, -- domain: non-empty string
  `SIEGE` VARCHAR(255) NOT NULL, -- domain: non-empty string
  PRIMARY KEY (`ID`),
  UNIQUE KEY `NAME` (`NAME`) -- integrity constraint: unique name
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table `main_language`
-- --------------------------------------------------------
CREATE TABLE `main_language` (
  `AGENCY_ID` INT NOT NULL, -- domain: numerical ID, foreign key to `agency` table
  `LANGUAGE` VARCHAR(2) NOT NULL, -- domain: non-empty string (ISO 639-1 code)
  PRIMARY KEY (`AGENCY_ID`, `LANGUAGE`),
  FOREIGN KEY (`AGENCY_ID`) REFERENCES `agency`(`ID`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table `itinerary`
-- --------------------------------------------------------
CREATE TABLE `itinerary` (
  `ID` INT NOT NULL, -- domain: numerical ID, primary key
  `AGENCY_ID` INT NOT NULL, -- domain: numerical ID, foreign key to `agency` table
  `TYPE` VARCHAR(20) NOT NULL, -- domain: non-empty string
  `NAME` VARCHAR(100) NOT NULL, -- domain: non-empty string
  PRIMARY KEY (`ID`),
  FOREIGN KEY (`AGENCY_ID`) REFERENCES `agency`(`ID`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `TYPE_NAME` (`TYPE`, `NAME`) -- integrity constraint: unique combination of `TYPE` and `NAME`
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table `stop`
-- --------------------------------------------------------
CREATE TABLE `stop` (
  `ID` INT NOT NULL, -- domain: numerical ID, primary key
  `NAME` VARCHAR(100) NOT NULL, -- domain: non-empty string
  `LATITUDE` DECIMAL(10, 7) NOT NULL CHECK (`LATITUDE` BETWEEN -90 AND 90), -- domain: decimal value between -90 and 90
  `LONGITUDE` DECIMAL(10, 7) NOT NULL CHECK (`LONGITUDE` BETWEEN -180 AND 180), -- domain: decimal value between -180 and 180
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table `stop_serviced`
-- --------------------------------------------------------
CREATE TABLE `stop_serviced` (
  `ITINERAIRE_ID` INT NOT NULL, -- domain: numerical ID, foreign key to `itinerary` table
  `STOP_ID` INT NOT NULL, -- domain: numerical ID, foreign key to `stop` table
  `SEQUENCE` INT NOT NULL, -- domain: numerical ID
  PRIMARY KEY (`ITINERAIRE_ID`, `STOP_ID`), -- primary key: combination of `ITINERAIRE_ID` and `STOP_ID`
  FOREIGN KEY (`ITINERAIRE_ID`) REFERENCES `itinerary`(`ID`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`STOP_ID`) REFERENCES `stop`(`ID`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table `service`
-- --------------------------------------------------------
CREATE TABLE `service` (
  `ID` INT NOT NULL AUTO_INCREMENT, -- domain: numerical ID, primary key
  `NAME` VARCHAR(100) NOT NULL, -- domain: non-empty string
  `MONDAY` BOOLEAN NOT NULL, -- domain: boolean value
  `TUESDAY` BOOLEAN NOT NULL, -- domain: boolean value
  `WEDNESDAY` BOOLEAN NOT NULL, -- domain: boolean value
  `THURSDAY` BOOLEAN NOT NULL, -- domain: boolean value
  `FRIDAY` BOOLEAN NOT NULL, -- domain: boolean value
  `SATURDAY` BOOLEAN NOT NULL, -- domain: boolean value
  `SUNDAY` BOOLEAN NOT NULL, -- domain: boolean value
  `START_DATE` DATE NOT NULL, -- domain: date value
  `END_DATE` DATE NOT NULL, -- domain: date value
  CHECK (`START_DATE` <= `END_DATE`), -- integrity constraint: `START_DATE` <= `END_DATE`
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table `exception`
-- --------------------------------------------------------
CREATE TABLE `exception` (
  `SERVICE_ID` INT NOT NULL, -- domain: numerical ID, foreign key to `service` table
  `DATE` DATE NOT NULL, -- domain: date value
  `CODE` TINYINT(1) NOT NULL, -- domain: {1, 2}
  PRIMARY KEY (`SERVICE_ID`, `DATE`),
  FOREIGN KEY (`SERVICE_ID`) REFERENCES `service`(`ID`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table `route`
-- --------------------------------------------------------
CREATE TABLE `route` (
  `ROUTE_ID` VARCHAR(100) NOT NULL, -- domain: non-empty string
  `SERVICE_ID` INT NOT NULL, -- domain: numerical ID, foreign key to `service` table
  `ITINERAIRE_ID` INT NOT NULL, -- domain: numerical ID, foreign key to `itinerary` table
  `DIRECTION` BOOLEAN NOT NULL, -- domain: boolean value
  PRIMARY KEY (`ROUTE_ID`),
  FOREIGN KEY (`SERVICE_ID`) REFERENCES `service`(`ID`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`ITINERAIRE_ID`) REFERENCES `itinerary`(`ID`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table `schedule`
-- --------------------------------------------------------
CREATE TABLE `schedule` (
  `ROUTE_ID` VARCHAR(100) NOT NULL, -- domain: non-empty string, foreign key to `route` table
  `ITINERAIRE_ID` INT NOT NULL, -- domain: numerical ID, foreign key to `itinerary` table
  `STOP_ID` INT NOT NULL, -- domain: numerical ID, foreign key to `stop` table
  `ARRIVAL_TIME` TIME, -- domain: time value
  `DEPARTURE_TIME` TIME, -- domain: time value
  CHECK (
    (`DEPARTURE_TIME` IS NULL AND `ARRIVAL_TIME` IS NOT NULL) OR
    (`ARRIVAL_TIME` IS NULL AND `DEPARTURE_TIME` IS NOT NULL) OR
    (`DEPARTURE_TIME` >= `ARRIVAL_TIME`)
  ), -- integrity constraint: `DEPARTURE_TIME` >= `ARRIVAL_TIME` or one of them is NULL
  PRIMARY KEY (`ROUTE_ID`, `ITINERAIRE_ID`, `STOP_ID`),
  FOREIGN KEY (`ROUTE_ID`) REFERENCES `route`(`ROUTE_ID`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`ITINERAIRE_ID`) REFERENCES `itinerary`(`ID`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`STOP_ID`) REFERENCES `stop`(`ID`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;


-- ==========================================================
-- Final COMMIT
-- ==========================================================
COMMIT;


-- ==========================================================
-- Dumping database structure for `myDb`
-- ==========================================================

-- --------------------------------------------------------
-- Load `agency`
-- --------------------------------------------------------
LOAD DATA INFILE '/docker-entrypoint-initdb.d/AGENCE.CSV'
INTO TABLE `agency`
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 ROWS
(
  @ID, @NOM, @URL, @FUSEAU_HORAIRE, @TELEPHONE, @SIEGE
)
SET
  `ID` = NULLIF(@ID, ''),
  `NAME` = NULLIF(@NOM, ''),
  `URL` = NULLIF(@URL, ''),
  `TIME_ZONE` = NULLIF(@FUSEAU_HORAIRE, ''),
  `TELEPHONE` = NULLIF(@TELEPHONE, ''),
  `SIEGE` = NULLIF(@SIEGE, '');

-- INSERT INTO `agency` (`ID`, `NAME`, `URL`, `TIME_ZONE`, `TELEPHONE`, `SIEGE`) VALUES (...);


-- --------------------------------------------------------
-- Load `main_language`
-- --------------------------------------------------------
LOAD DATA INFILE '/docker-entrypoint-initdb.d/LANGUEPRINCIPALE.CSV'
INTO TABLE `main_language`
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 ROWS
(
  @AGENCE_ID, @LANGUE
)
SET
  `AGENCY_ID` = NULLIF(@AGENCE_ID, ''),
  `LANGUAGE` = NULLIF(@LANGUE, '');

-- INSERT INTO `main_language` (`AGENCY_ID`, `LANGUAGE`) VALUES (...);


-- --------------------------------------------------------
-- Load `itinerary`
-- --------------------------------------------------------
LOAD DATA INFILE '/docker-entrypoint-initdb.d/ITINERAIRE.CSV'
INTO TABLE `itinerary`
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 ROWS
(
  @ID, @AGENCE_ID, @TYPE, @NOM
)
SET
  `ID` = NULLIF(@ID, ''),
  `AGENCY_ID` = NULLIF(@AGENCE_ID, ''),
  `TYPE` = NULLIF(@TYPE, ''),
  `NAME` = NULLIF(@NOM, '');

-- INSERT INTO `itinerary` (`ID`, `AGENCY_ID`, `TYPE`, `NAME`) VALUES (...);


-- --------------------------------------------------------
-- Load `stop`
-- --------------------------------------------------------
LOAD DATA INFILE '/docker-entrypoint-initdb.d/ARRET.CSV'
INTO TABLE `stop`
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 ROWS
(
  @ID, @NOM, @LATITUDE, @LONGITUDE
)
SET
  `ID` = NULLIF(@ID, ''),
  `NAME` = NULLIF(@NOM, ''),
  `LATITUDE` = NULLIF(@LATITUDE, ''),
  `LONGITUDE` = NULLIF(@LONGITUDE, '');

-- INSERT INTO `stop` (`ID`, `NAME`, `LATITUDE`, `LONGITUDE`) VALUES (...);


-- --------------------------------------------------------
-- Load `stop_serviced`
-- --------------------------------------------------------
LOAD DATA INFILE '/docker-entrypoint-initdb.d/ARRET_DESSERVI.CSV'
INTO TABLE `stop_serviced`
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 ROWS
(
  @ITINERAIRE_ID, @ARRET_ID, @SEQUENCE
)
SET
  `ITINERAIRE_ID` = NULLIF(@ITINERAIRE_ID, ''),
  `STOP_ID` = NULLIF(@ARRET_ID, ''),
  `SEQUENCE` = NULLIF(@SEQUENCE, '');

-- INSERT INTO `stop_serviced` (`ITINERAIRE_ID`, `STOP_ID`, `SEQUENCE`) VALUES (...);


-- --------------------------------------------------------
-- Load `service`
-- --------------------------------------------------------
LOAD DATA INFILE '/docker-entrypoint-initdb.d/SERVICE.CSV'
INTO TABLE `service`
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 ROWS
(
  @ID, @NOM, @LUNDI, @MARDI, @MERCREDI, @JEUDI, @VENDREDI, @SAMEDI, @DIMANCHE, @DATE_DEBUT, @DATE_FIN
)
SET
  `ID` = NULLIF(@ID, ''),
  `NAME` = NULLIF(@NOM, ''),
  `MONDAY` = NULLIF(@LUNDI, ''),
  `TUESDAY` = NULLIF(@MARDI, ''),
  `WEDNESDAY` = NULLIF(@MERCREDI, ''),
  `THURSDAY` = NULLIF(@JEUDI, ''),
  `FRIDAY` = NULLIF(@VENDREDI, ''),
  `SATURDAY` = NULLIF(@SAMEDI, ''),
  `SUNDAY` = NULLIF(@DIMANCHE, ''),
  `START_DATE` = NULLIF(@DATE_DEBUT, ''),
  `END_DATE` = NULLIF(@DATE_FIN, '');

-- NEXT STOP VS. PREVIOUS STOP (FEEDBACK OF PROJECT 1)
-- INSERT INTO `service` (`ID`, `NAME`, `MONDAY`, `TUESDAY`, `WEDNESDAY`, `THURSDAY`, `FRIDAY`, `SATURDAY`, `SUNDAY`, `START_DATE`, `END_DATE`) VALUES (...);


-- --------------------------------------------------------
-- Load `exception`
-- --------------------------------------------------------
LOAD DATA INFILE '/docker-entrypoint-initdb.d/EXCEPTION.CSV'
INTO TABLE `exception`
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 ROWS
(
  @SERVICE_ID, @DATE, @CODE
)
SET
  `SERVICE_ID` = NULLIF(@SERVICE_ID, ''),
  `DATE` = NULLIF(@DATE, ''),
  `CODE` = NULLIF(@CODE, '');

-- INSERT INTO `exception` (...) VALUES (...);


-- --------------------------------------------------------
-- Load `route`
-- --------------------------------------------------------
LOAD DATA INFILE '/docker-entrypoint-initdb.d/TRAJET.CSV'
INTO TABLE `route`
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 ROWS
(
  @TRAJET_ID, @SERVICE_ID, @ITINERAIRE_ID, @DIRECTION
)
SET
  `ROUTE_ID` = NULLIF(@TRAJET_ID, ''),
  `SERVICE_ID` = NULLIF(@SERVICE_ID, ''),
  `ITINERAIRE_ID` = NULLIF(@ITINERAIRE_ID, ''),
  `DIRECTION` = NULLIF(@DIRECTION, '');

-- INSERT INTO `route` (`ROUTE_ID`, `SERVICE_ID`, `ITINERAIRE_ID`, `DIRECTION`) VALUES (...);


-- --------------------------------------------------------
-- Load `schedule`
-- --------------------------------------------------------
LOAD DATA INFILE '/docker-entrypoint-initdb.d/HORRAIRE.CSV'
INTO TABLE `schedule`
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
IGNORE 1 ROWS
(
  @TRAJET_ID, @ITINERAIRE_ID, @ARRET_ID, @HEURE_ARRIVEE, @HEURE_DEPART
)
SET
  `ROUTE_ID` = NULLIF(@TRAJET_ID, ''),
  `ITINERAIRE_ID` = NULLIF(@ITINERAIRE_ID, ''),
  `STOP_ID` = NULLIF(@ARRET_ID, ''),
  `ARRIVAL_TIME` = NULLIF(@HEURE_ARRIVEE, ''),
  `DEPARTURE_TIME` = NULLIF(@HEURE_DEPART, '');

-- INSERT INTO `schedule` (`ROUTE_ID`, `ITINERAIRE_ID`, `STOP_ID`, `ARRIVAL_TIME`, `DEPARTURE_TIME`) VALUES (...);



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
 /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
 /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;