<?php include 'header.php'; ?>

<head>
    <title>Task 7: Intinerary and its routes</title>
</head>

<?php
// Function to generate a ROUTE_ID follow existing formatting
function generateRouteId($start, $end, $block, $departure_time, $date_ymd) {
    $time_clean = str_pad(str_replace(":", "", substr($departure_time, 0, 5)), 4, "0", STR_PAD_LEFT);
    return "88____:007::{$start}:{$end}:{$block}:{$time_clean}:{$date_ymd}"; // Vu cmts: I dont know "88____:007" meaning
}

// Database connection configuration
$host = 'ms8db';
$db   = 'group21';
$user = 'group21';
$pass = 'secret';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$delete_notice = '';
$route_notice = '';
$route_error = '';
$error = '';
$stops_to_edit = [];
$arrival_values = [];
$departure_values = [];

try {
    // Connect to MySQL database using PDO
    $bdd = new PDO($dsn, $user, $pass);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    // Handle itinerary deletion
    if (isset($_POST['delete_itinerary_id'])) {
        $id = (int)$_POST['delete_itinerary_id'];

        // Retrieve itinerary name before deletion
        $name_stmt = $bdd->prepare("SELECT NAME FROM itinerary WHERE ID = ?");
        $name_stmt->execute([$id]);
        $it_name = $name_stmt->fetchColumn();

        // Cascade deletion handled by ON DELETE CASCADE
        $bdd->beginTransaction();
        $bdd->prepare("DELETE FROM itinerary WHERE ID = ?")->execute([$id]);
        $bdd->commit();

        // Set deletion success message
        $delete_notice = "<p style='color:green'>Itinerary <strong>" . htmlspecialchars($it_name) . " (ID $id)</strong> and all related data have been successfully deleted.</p>";
    }

    // Load all available itineraries for selection (to initialize and refresh after deletion)
    $itineraries = $bdd->query("SELECT ID, NAME FROM itinerary ORDER BY NAME")->fetchAll(PDO::FETCH_ASSOC);

    // Handle route form submission
    if (isset($_POST['submit_route'])) {
        $itinerary_id = (int)$_POST['final_itinerary_id'];
        $direction = (int)$_POST['final_direction'];
        $stop_ids = $_POST['stop_id'];
        $arrivals = $_POST['arrival_time'];
        $departures = $_POST['departure_time'];
        $n = count($stop_ids);

        // Save user input to preserve values on error
        $arrival_values = $arrivals;
        $departure_values = $departures;
        $error_list = [];

        // Validate time fields
        for ($i = 0; $i < $n; $i++) {
            if ($i > 0 && empty($arrivals[$i])) $error_list[] = "Stop " . ($i + 1) . ": arrival time is required.";
            if ($i < $n - 1 && empty($departures[$i])) $error_list[] = "Stop " . ($i + 1) . ": departure time is required.";
        }

        // Validate arrival <= departure at intermediate stops
        for ($i = 1; $i < $n - 1; $i++) {
            if (!empty($arrivals[$i]) && !empty($departures[$i]) && $departures[$i] < $arrivals[$i]) {
                $error_list[] = "Stop " . ($i + 1) . ": departure must be ≥ arrival.";
            }
        }

        // Validate arrival at next stop > previous departure
        for ($i = 0; $i < $n - 1; $i++) {
            if (!empty($departures[$i]) && !empty($arrivals[$i + 1]) && $arrivals[$i + 1] <= $departures[$i]) {
                $error_list[] = "Between stop " . ($i + 1) . " and " . ($i + 2) . ": arrival must be > previous departure.";
            }
        }

        // If there are errors, show message and reload stops
        if (!empty($error_list)) {
            $route_error = "<div style='color:red'><strong>Validation Error:</strong><ul><li>" . implode("</li><li>", $error_list) . "</li></ul></div>";
            $stmt = $bdd->prepare("
                SELECT ss.SEQUENCE, s.ID AS STOP_ID, s.NAME
                FROM stop_serviced ss
                JOIN stop s ON ss.STOP_ID = s.ID
                WHERE ss.ITINERAIRE_ID = ?
                ORDER BY ss.SEQUENCE " . ($direction === 0 ? "ASC" : "DESC")
            );
            $stmt->execute([$itinerary_id]);
            $stops_to_edit = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $selected_itinerary = $itinerary_id;
        } else {
            // Generate a unique ROUTE_ID
            $start = $stop_ids[0];
            $end = $stop_ids[$n - 1];
            $block = "40";
            $date_ymd = date("Ymd");
            $first_departure = $departures[0];
            $route_id = generateRouteId($start, $end, $block, $first_departure, $date_ymd);

            // Insert route and schedule data
            $bdd->beginTransaction();
            $bdd->prepare("INSERT INTO route (ROUTE_ID, SERVICE_ID, ITINERAIRE_ID, DIRECTION) VALUES (?, 1, ?, ?)")
                ->execute([$route_id, $itinerary_id, $direction]);

            for ($i = 0; $i < $n; $i++) {
                $arrival = $arrivals[$i] ?: null;
                $departure = $departures[$i] ?: null;
                $bdd->prepare("INSERT INTO schedule (ROUTE_ID, ITINERAIRE_ID, STOP_ID, ARRIVAL_TIME, DEPARTURE_TIME)
                               VALUES (?, ?, ?, ?, ?)")
                    ->execute([$route_id, $itinerary_id, $stop_ids[$i], $arrival, $departure]);
            }

            $bdd->commit();

            // Prepare success notice with stop summaries
            $route_notice = "<div style='color:green'><strong>route created successfully!</strong><br>ROUTE_ID: <code>$route_id</code><br><br>";
            $route_notice .= "<strong>Inserted Stops:</strong><br>";
            for ($i = 0; $i < $n; $i++) {
                $route_notice .= "#" . ($i + 1) .
                    " - Stop ID: " . htmlspecialchars($stop_ids[$i]) .
                    " - Arrival: " . ($arrivals[$i] ?: '-') .
                    " - Departure: " . ($departures[$i] ?: '-') . "<br>";
            }
            $route_notice .= "</div>";
        }
    }

    // Handle form generation for itinerary and direction
    if (isset($_POST['generate_route_form']) && isset($_POST['add_itinerary_id'], $_POST['direction'])) {
        $selected_itinerary = (int)$_POST['add_itinerary_id'];
        $direction = (int)$_POST['direction'];

        $stmt = $bdd->prepare("
            SELECT ss.SEQUENCE, s.ID AS STOP_ID, s.NAME
            FROM stop_serviced ss
            JOIN stop s ON ss.STOP_ID = s.ID
            WHERE ss.ITINERAIRE_ID = ?
            ORDER BY ss.SEQUENCE " . ($direction === 0 ? "ASC" : "DESC")
        );
        $stmt->execute([$selected_itinerary]);
        $stops_to_edit = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    if (isset($bdd) && $bdd->inTransaction()) $bdd->rollBack();
    $error = "<p style='color:red'><strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<h1>Task 7: Deletion and insertion queries</h1>

<!-- Show any validation or DB error -->
<?= $error ?>

<!-- Itinerary deletion -->
<section>
    <h2>Itinerary deletion</h2>
    <p>Select an itinerary to delete along with all its scheduled routes and stops.</p>
    <form method="post">
        <select name="delete_itinerary_id" required>
            <option value="">-- Select --</option>
            <?php foreach ($itineraries as $it): ?>
                <option value="<?= $it['ID'] ?>"><?= htmlspecialchars($it['NAME']) ?> (ID <?= $it['ID'] ?>)</option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Delete">
    </form>
    <?= $delete_notice ?>
</section>

<!-- Route addition -->
<section>
    <h2>Route addition</h2>
    <p>Select an itinerary and direction to begin.</p>

    <?= $route_error ?>
    <?= $route_notice ?>

    <form method="post">
        <label>Itinerary:</label>
        <select name="add_itinerary_id" required>
            <option value="">-- Select --</option>
            <?php foreach ($itineraries as $it): ?>
                <option value="<?= $it['ID'] ?>" <?= (isset($selected_itinerary) && $selected_itinerary == $it['ID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($it['NAME']) ?> (ID <?= $it['ID'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label>Direction:</label>
        <select name="direction" required>
            <option value="0" <?= (isset($direction) && $direction == 0) ? 'selected' : '' ?>>0 (Forward)</option>
            <option value="1" <?= (isset($direction) && $direction == 1) ? 'selected' : '' ?>>1 (Return)</option>
        </select>

        <input type="submit" name="generate_route_form" value="Generate">
    </form>

    <?php if (!empty($stops_to_edit)): ?>
        <p>Then, fill in arrival and departure times for each stop below:</p>
        <form method="post">

            <input type="hidden" name="final_itinerary_id" value="<?= htmlspecialchars($selected_itinerary) ?>">
            <input type="hidden" name="final_direction" value="<?= htmlspecialchars($direction) ?>">
            <table>
                <tr>
                    <th>#</th>
                    <th>STOP ID</th>
                    <th>STOP NAME</th>
                    <th>ARRIVAL TIME</th>
                    <th>DEPARTURE TIME</th>
                </tr>
                <?php foreach ($stops_to_edit as $i => $stop): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <?= htmlspecialchars($stop['STOP_ID']) ?>
                            <input type="hidden" name="stop_id[]" value="<?= htmlspecialchars($stop['STOP_ID']) ?>">
                        </td>
                        <td><?= htmlspecialchars($stop['NAME']) ?></td>
                        <td>
                            <?php if ($i > 0): ?>
                                <input type="time" name="arrival_time[]" value="<?= htmlspecialchars($arrival_values[$i] ?? '') ?>" required>
                            <?php else: ?>
                                <input type="hidden" name="arrival_time[]" value="">
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($i < count($stops_to_edit) - 1): ?>
                                <input type="time" name="departure_time[]" value="<?= htmlspecialchars($departure_values[$i] ?? '') ?>" required>
                            <?php else: ?>
                                <input type="hidden" name="departure_time[]" value="">
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <p><em>
                <strong>Notes:</strong><br>
                - All required fields must be filled.<br>
                - For intermediate stops, <strong>departure time must be ≥ arrival time</strong>.<br>
                - For consecutive stops, <strong>arrival at next stop must be > previous departure</strong>.<br>
                - First stop has no arrival, last stop has no departure.
            <em></p>

            <input type="submit" name="submit_route" value="Add route">
        </form>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
