<?php include 'header.php'; ?>

<head>
    <title>Task 5: Average stopping time</title>
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
$formatted_results = [];

try {
    $bdd = new PDO($dsn, $user, $pass);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch average stop times
    $stmt = $bdd->query("SELECT * FROM vw_stop_time_averages");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $avg_time_seconds = round($row['AVG_STOP_TIME']);
        $avg_time_formatted = floor($avg_time_seconds / 60) . 'm ' . ($avg_time_seconds % 60) . 's';
        
        if ($row['ITINERARY'] === null && $row['ROUTE'] === null) {
            $formatted_row = [
                'ITINERARY' => 'All Itineraries',
                'ROUTE' => 'Global Average',
                'AVG_STOP_TIME' => $avg_time_seconds,
                'AVG_STOP_TIME_FORMATTED' => $avg_time_formatted,
                'ROW_TYPE' => 'grand_total'
            ];
        } else if ($row['ROUTE'] === null) {
            $formatted_row = [
                'ITINERARY' => $row['ITINERARY'],
                'ROUTE' => 'Average',
                'AVG_STOP_TIME' => $avg_time_seconds,
                'AVG_STOP_TIME_FORMATTED' => $avg_time_formatted,
                'ROW_TYPE' => 'itinerary_total'
            ];
        } else {
            $formatted_row = [
                'ITINERARY' => $row['ITINERARY'],
                'ROUTE' => $row['ROUTE'],
                'AVG_STOP_TIME' => $avg_time_seconds,
                'AVG_STOP_TIME_FORMATTED' => $avg_time_formatted,
                'ROW_TYPE' => 'regular'
            ];
        }
        
        $formatted_results[] = $formatted_row;
    }

} catch (Exception $e) {
    $error = "<p style='color:red'><strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

?>

<h1>Task 5: Aggregation gueries</h1>


<!-- Show any validation or DB error -->
<?= $error ?>

<!-- Results Table -->
<section>
    <h2>Average stopping time per route and per itinerary</h2>
    <?php if (isset($formatted_results) && !empty($formatted_results)): ?>
        <p>This table shows the average time vehicles spend at each stop, organized by itinerary and route.</p>
        
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>ITINERARY</th>
                    <th>ROUTE</th>
                    <th>AVG_STOP_TIME</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($formatted_results as $row): ?>
                    <tr <?php if ($row['ROW_TYPE'] === 'grand_total'): ?>style="font-weight: bold; background-color: #f0f0f0;"<?php elseif ($row['ROW_TYPE'] === 'itinerary_total'): ?>style="font-weight: bold; background-color: #f8f8f8;"<?php endif; ?>>
                        <td><?= htmlspecialchars($row['ITINERARY']) ?></td>
                        <td><?= htmlspecialchars($row['ROUTE']) ?></td>
                        <td><?= htmlspecialchars($row['AVG_STOP_TIME_FORMATTED']) ?> (<?= $row['AVG_STOP_TIME'] ?> seconds)</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><em>
            <strong>Notes:</strong><br>
            - "Average" rows show the mean stopping time for all routes in that itinerary.<br>
            - "Global Average" shows the mean for all routes across all itineraries.<br>
        <em></p>
    <?php else: ?>
        <p><em>No data available. Ensure there are schedules with valid arrival and departure times.</em></p>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>