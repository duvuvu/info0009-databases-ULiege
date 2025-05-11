<?php include 'header.php'; ?>

<head>
    <title>Task 2: Schedules</title>
</head>

<?php
// Database connection configuration
$host = 'ms8db';
$db   = 'group21';
$user = 'group21';
$pass = 'secret';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$error = '';
$results = [];

try {
    $bdd = new PDO($dsn, $user, $pass);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Capture inputs
    $route_id = $_POST['route_id'] ?? '';
    $itineraire_id = $_POST['itineraire_id'] ?? '';
    $arret_id = $_POST['arret_id'] ?? '';

    // Build query using only schedule table
    $query = "SELECT * FROM schedule WHERE 1=1";
    $params = [];

    if ($route_id !== '') {
        $query .= " AND ROUTE_ID LIKE :route_id";
        $params[':route_id'] = '%' . $route_id . '%';
    }
    if ($itineraire_id !== '') {
        $query .= " AND ITINERAIRE_ID = :itineraire_id";
        $params[':itineraire_id'] = $itineraire_id;
    }
    if ($arret_id !== '') {
        $query .= " AND STOP_ID = :arret_id";
        $params[':arret_id'] = $arret_id;
    }

    $stmt = $bdd->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "<p style='color:red'><strong>Database error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<h1>Task 2: Selection queries</h1>

<!-- Show any validation or DB error -->
<?= $error ?>


<section>
    <h2>Schedule</h2>
    <p>Filter schedule entries by route ID, itinerary ID, or stop ID. All rows are shown by default.</p>
    
    <!-- Filter Form -->
    <form method="post">
        <input type="text" name="route_id" placeholder="Route ID..." value="<?= htmlspecialchars($route_id) ?>">
        <input type="number" name="itineraire_id" placeholder="Itinerary ID..." value="<?= htmlspecialchars($itineraire_id) ?>" min="0">
        <input type="number" name="arret_id" placeholder="Stop ID..." value="<?= htmlspecialchars($arret_id) ?>" min="0">

        <button type="submit" name="apply_filter">Filter</button>
        <button type="button" onclick="window.location.href='t2_selection_schedule.php'">Clear</button>

    </form>

    <!-- Results Table -->
    <table border="1" cellpadding="5" cellspacing="0" style="margin-top: 1em; width: 100%;">
        <tr>
            <th>ROUTE ID</th>
            <th>ITINERARY ID</th>
            <th>STOP ID</th>
            <th>ARRIVAL TIME</th>
            <th>DEPARTURE TIME</th>
        </tr>
        <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['ROUTE_ID']) ?></td>
                <td><?= htmlspecialchars($row['ITINERAIRE_ID']) ?></td>
                <td><?= htmlspecialchars($row['STOP_ID']) ?></td>
                <td><?= htmlspecialchars($row['ARRIVAL_TIME']) ?></td>
                <td><?= htmlspecialchars($row['DEPARTURE_TIME']) ?></td>
            </tr>
        <?php endforeach; ?>
</table>
</section>

<?php include 'footer.php'; ?>
