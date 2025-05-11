<?php include 'header.php'; ?>

<head>
    <title>Task 6: Station search with stop statistics</title>
</head>

<?php
// DB config
$host = 'ms8db';
$db   = 'group21';
$user = 'group21';
$pass = 'secret';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$search_string = '';
$min_count = '';
$results = [];
$search_performed = false;
$result_count = 0;
$error = '';

try {
    $bdd = new PDO($dsn, $user, $pass);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['submit_search'])) {
        $search_string = trim($_POST['search_string']);
        $min_count = trim($_POST['min_count']);
        $search_performed = true;

        if ($search_string === '') {
            $error = '<p style="color:red"><strong>Error:</strong> Please enter a station name to search.</p>';
        } else {
            $query = "
                SELECT *
                FROM vw_station_service_stats
                WHERE LOWER(station_name) LIKE LOWER(:search_string)
            ";

            if (is_numeric($min_count) && $min_count > 0) {
                $query .= " AND (total_stops >= :min OR arrival_count >= :min OR departure_count >= :min)";
            }

            $query .= " ORDER BY total_stops DESC, arrival_count DESC, departure_count DESC";

            $stmt = $bdd->prepare($query);
            $stmt->bindValue(':search_string', '%' . $search_string . '%', PDO::PARAM_STR);
            if (is_numeric($min_count) && $min_count > 0) {
                $stmt->bindValue(':min', (int)$min_count, PDO::PARAM_INT);
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result_count = count($results);
        }
    }

} catch (Exception $e) {
    $error = '<p style="color:red"><strong>Fatal Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>

<h1>Task 6: Pattern matching queries</h1>

<?= $error ?>

<section>
    <h2>Station search with stop statistics</h2>
    <p>Enter a partial station name (case-insensitive). Optionally, set a minimum count filter for stops, arrivals, or departures.</p>

    <form method="post">
        <p>
            <label for="search_string">Station name contains:</label>
            <input type="text" id="search_string" name="search_string" value="<?= htmlspecialchars($search_string) ?>" required>

            <label for="min_count" style="margin-left: 1em;">Minimum count (optional):</label>
            <input type="number" id="min_count" name="min_count" value="<?= htmlspecialchars($min_count) ?>" min="1">

            <input type="submit" name="submit_search" value="Search" style="margin-left: 1em;">
            <button type="button" onclick="window.location='t6_pattern_search_stops.php'" style="margin-left: 0.5em;">Clear</button>
        </p>
    </form>
</section>

<?php if ($search_performed): ?>
<section>
    <h2>Results</h2>

    <?php if ($result_count > 0): ?>
        <p>Found <?= $result_count ?> record(s) for stations matching "<strong><?= htmlspecialchars($search_string) ?></strong>"
            <?= !empty($min_count) ? "with at least {$min_count} stops/arrivals/departures" : "" ?>.</p>

        <table border="1" cellpadding="5" cellspacing="0" style="margin-top: 1em; width: 100%;">
            <thead>
                <tr>
                    <th>STATION NAME</th>
                    <th>SERVICE</th>
                    <th>TOTAL STOPS</th>
                    <th>ARRIVALS</th>
                    <th>DEPARTURES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['station_name']) ?></td>
                        <td><?= htmlspecialchars($row['service_name']) ?></td>
                        <td><?= htmlspecialchars($row['total_stops']) ?></td>
                        <td><?= htmlspecialchars($row['arrival_count']) ?></td>
                        <td><?= htmlspecialchars($row['departure_count']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <p><em>No results found. Try a different search term or reduce the minimum count.</em></p>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php include 'footer.php'; ?>
