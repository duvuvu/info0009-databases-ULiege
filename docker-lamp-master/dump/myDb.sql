SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;
--
-- Tabelstructuur voor tabel `department`
--
CREATE TABLE `department` (
  `DNO` int NOT NULL,
  `DNAME` varchar(15) NOT NULL,
  `MGR_ID` char(11) DEFAULT NULL,
  `MGR_START` date DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
--
-- Gegevens worden geëxporteerd voor tabel `department`
--
INSERT INTO `department` (`DNO`, `DNAME`, `MGR_ID`, `MGR_START`)
VALUES (1, 'Montefiore', NULL, NULL),
  (2, 'The Cat Cave', NULL, NULL);
-- --------------------------------------------------------
--
-- Tabelstructuur voor tabel `employee`
--
CREATE TABLE `employee` (
  `EMP_ID` char(11) NOT NULL,
  `FNAME` varchar(15) NOT NULL,
  `LNAME` varchar(15) NOT NULL,
  `BDATE` date DEFAULT NULL,
  `ADDRESS` varchar(30) DEFAULT NULL,
  `SALARY` decimal(10, 2) DEFAULT NULL,
  `DEPT_NO` int NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
--
-- Gegevens worden geëxporteerd voor tabel `employee`
--
INSERT INTO `employee` (
    `EMP_ID`,
    `FNAME`,
    `LNAME`,
    `BDATE`,
    `ADDRESS`,
    `SALARY`,
    `DEPT_NO`
  )
VALUES (
    '1',
    'Christophe',
    'Debruyne',
    NULL,
    NULL,
    NULL,
    1
  ),
  (
    '2',
    'Victor',
    'Debruyne-Sieuw',
    NULL,
    NULL,
    NULL,
    2
  ),
  (
    '3',
    'Gaston',
    'Sieuw-Debruyne',
    NULL,
    NULL,
    NULL,
    2
  ),
  (
    '4',
    'Bettina',
    'Sieuw-Debruyne',
    NULL,
    NULL,
    NULL,
    2
  );
-- --------------------------------------------------------
--
-- Tabelstructuur voor tabel `users`
--
CREATE TABLE `users` (
  `Login` varchar(20) NOT NULL,
  `Pass` varchar(20) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = latin1;
--
-- Gegevens worden geëxporteerd voor tabel `users`
--
INSERT INTO `users` (`Login`, `Pass`)
VALUES ('Pierre', 'incorrect'),
  ('Sam', 'motdepasse');
--
-- Indexen voor geëxporteerde tabellen
--
--
-- Indexen voor tabel `department`
--
ALTER TABLE `department`
ADD PRIMARY KEY (`DNO`),
  ADD UNIQUE KEY `DNAME` (`DNAME`);
--
-- Indexen voor tabel `employee`
--
ALTER TABLE `employee`
ADD PRIMARY KEY (`EMP_ID`),
  ADD KEY `DEPT_NO` (`DEPT_NO`);
--
-- Beperkingen voor geëxporteerde tabellen
--
--
-- Beperkingen voor tabel `employee`
--
ALTER TABLE `employee`
ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`DEPT_NO`) REFERENCES `department` (`DNO`);

ALTER TABLE `department`
ADD CONSTRAINT `department_ibfk_1` FOREIGN KEY (`MGR_ID`) REFERENCES `employee` (`EMP_ID`);

COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;

LOAD DATA INFILE '/docker-entrypoint-initdb.d/users.csv' INTO TABLE `users` FIELDS TERMINATED BY ',' ENCLOSED BY '"' IGNORE 1 ROWS;
