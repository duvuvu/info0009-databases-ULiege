-- --------------------------------------------------------
-- View: vw_service_dates_raw
-- --------------------------------------------------------
CREATE OR REPLACE VIEW vw_service_dates_raw AS
WITH RECURSIVE all_dates AS (
  SELECT 
    ID AS SERVICE_ID,
    NAME,
    START_DATE AS `DATE`,
    MONDAY, TUESDAY, WEDNESDAY, THURSDAY, FRIDAY, SATURDAY, SUNDAY,
    END_DATE
  FROM service
  UNION ALL
  SELECT
    SERVICE_ID,
    NAME,
    DATE_ADD(`DATE`, INTERVAL 1 DAY),
    MONDAY, TUESDAY, WEDNESDAY, THURSDAY, FRIDAY, SATURDAY, SUNDAY,
    END_DATE
  FROM all_dates
  WHERE `DATE` < END_DATE
)
SELECT SERVICE_ID, NAME, `DATE`
FROM all_dates
WHERE (
  (DAYOFWEEK(`DATE`) = 1 AND SUNDAY = 1) OR
  (DAYOFWEEK(`DATE`) = 2 AND MONDAY = 1) OR
  (DAYOFWEEK(`DATE`) = 3 AND TUESDAY = 1) OR
  (DAYOFWEEK(`DATE`) = 4 AND WEDNESDAY = 1) OR
  (DAYOFWEEK(`DATE`) = 5 AND THURSDAY = 1) OR
  (DAYOFWEEK(`DATE`) = 6 AND FRIDAY = 1) OR
  (DAYOFWEEK(`DATE`) = 7 AND SATURDAY = 1)
);

-- --------------------------------------------------------
-- View: vw_service_dates_filtered
-- --------------------------------------------------------
CREATE OR REPLACE VIEW vw_service_dates_filtered AS
SELECT DATE, GROUP_CONCAT(DISTINCT NAME ORDER BY NAME SEPARATOR ', ') AS SERVICES
FROM (
  -- Regular services from raw, not overridden by EXCLUSIONS
  SELECT r.SERVICE_ID, r.DATE, r.NAME
  FROM vw_service_dates_raw r
  LEFT JOIN exception e
    ON r.SERVICE_ID = e.SERVICE_ID AND r.DATE = e.DATE AND e.CODE = 2
  WHERE e.SERVICE_ID IS NULL

  UNION

  -- INCLUDED exceptions, even if not normally active
  SELECT s.ID AS SERVICE_ID, e.DATE, s.NAME
  FROM exception e
  JOIN service s ON s.ID = e.SERVICE_ID
  WHERE e.CODE = 1
) all_valid
GROUP BY DATE
ORDER BY DATE;

-- --------------------------------------------------------
-- View: vw_service_dates
-- --------------------------------------------------------
CREATE OR REPLACE VIEW vw_stop_times AS
SELECT 
    i.ID AS itinerary_id,
    i.NAME AS itinerary_name,
    t.ROUTE_ID,
    TIME_TO_SEC(TIMEDIFF(s.DEPARTURE_TIME, s.ARRIVAL_TIME)) AS stop_time
FROM 
    schedule s
JOIN 
    itinerary i ON s.ITINERAIRE_ID = i.ID
JOIN 
    route t ON s.ROUTE_ID = t.ROUTE_ID AND s.ITINERAIRE_ID = t.ITINERAIRE_ID
WHERE 
    s.DEPARTURE_TIME IS NOT NULL 
    AND s.ARRIVAL_TIME IS NOT NULL;

-- --------------------------------------------------------
-- View: vw_stop_time_averages
-- --------------------------------------------------------
CREATE OR REPLACE VIEW vw_stop_time_averages AS
SELECT 
    itinerary_name AS ITINERARY,
    ROUTE_ID AS ROUTE,
    AVG(stop_time) AS AVG_STOP_TIME
FROM 
    vw_stop_times
GROUP BY 
    itinerary_name, ROUTE_ID WITH ROLLUP;

-- --------------------------------------------------------
-- View: vw_station_service_stats
-- --------------------------------------------------------
CREATE OR REPLACE VIEW vw_station_service_stats AS
SELECT 
    st.ID AS station_id,
    st.NAME AS station_name,
    sv.NAME AS service_name,
    COUNT(s.STOP_ID) AS total_stops,
    SUM(s.ARRIVAL_TIME IS NOT NULL) AS arrival_count,
    SUM(s.DEPARTURE_TIME IS NOT NULL) AS departure_count
FROM schedule s
JOIN stop st ON st.ID = s.STOP_ID
JOIN route r ON r.ROUTE_ID = s.ROUTE_ID
JOIN service sv ON sv.ID = r.SERVICE_ID
GROUP BY st.ID, sv.ID;
