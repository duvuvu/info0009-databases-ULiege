# INFO0009 – Project 2: Databases with Docker

This project is a complete implementation of the database assignment using a Dockerized LAMP stack (Linux, Apache, MySQL 8.0, PHP). It includes PhpMyAdmin for easy database access and a full suite of PHP forms to meet all project requirements.

---

## Docker Setup

To launch the application using Docker Compose, open a terminal in the root project directory and run:

```bash
docker-compose up -d
````

To shut down the services:

```bash
docker-compose down
```

---

## Access the Application

* **PhpMyAdmin**: [http://localhost:8080](http://localhost:8080)
* **Web application (PHP interface)**: [http://localhost](http://localhost)

---

## Credentials

By default, these are configured in `docker-compose.yml`:

* **MySQL Hostname**: `ms8db`
* **Database Name**: `group21`
* **Username**: `group21`
* **Password**: `secret`

---

## Project Structure

```
.
├── docker-compose.yml          # Docker service definitions
├── Dockerfile                  # PHP + Apache setup
├── conf/                       # Custom MySQL configs (if needed)
├── dump/                       # All CSV and SQL scripts
│   ├── *.CSV                   # Input data files
│   ├── myDb.sql                # CREATE TABLES + LOAD DATA
│   ├── views.sql               # All SQL VIEWS for tasks 4–6
├── www/                        # PHP files and main interface
│   ├── index.html              # Homepage (optional)
│   ├── header.php / footer.php # Layout includes
│   ├── t2_selection_agency.php           # Task 2 – Selection Queries - agencies
│   ├── t2_selection_exception.php        # Task 2 – Selection Queries - exceptions
│   ├── t2_selection_schedule.php         # Task 2 – Selection Queries - schedules
│   ├── t3_insert_service.php             # Task 3 – Insertion Queries - new service
│   ├── t4_recursive_services_by_date.php        # Task 4 – Recursive View Queries - services by date
│   ├── q5_average_stop_time.php          # Task 5 – Aggregation Queries - average stop time
│   ├── t6_pattern_search_stops.php       # Task 6 – Pattern Matching Queries - station search
│   ├── t7_delete_insert_itinerary.php    # Task 7 – Deletion and Insertion - route and schedule
│   ├── t8_update_stop_table.php          # Task 8 – Update Queries - serviced stop
```

---

## 🧪 How to Run

1. Install [Docker Desktop](https://www.docker.com/products/docker-desktop).
2. Clone this repository or download it as a ZIP.
3. Open a terminal in the project folder.
4. Run:

   ```bash
   docker-compose up -d
   ```
5. Open a browser and navigate to [http://localhost](http://localhost).

---

## Project Highlights

* **myDb.sql**: Creates all required tables with constraints and loads CSV data.
* **views.sql**: Implements all views used in Tasks 4, 5, and 6.
* **All tasks (2–8)** implemented in separate, clearly structured PHP files.
* **Transactions** and **constraint validation** are handled carefully:

  * Time logic in trips
  * Bounding box enforcement in stop edits
  * Referential integrity on itinerary deletions
* **Route IDs** are dynamically generated following the required pattern.

