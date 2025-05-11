<?php include 'header.php'; ?>

<head>
    <title>Task 3: Service and its exceptions</title>
</head>

<?php
// Database connection configuration
$host = 'ms8db';
$db   = 'group21';
$user = 'group21';
$pass = 'secret';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Weekday keys
$days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

// Capture inputs
$name = $_POST['name'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$exceptions = $_POST['exceptions'] ?? '';
$checked = array_fill_keys($days, false);
foreach ($days as $day) {
    $checked[$day] = isset($_POST[$day]);
}

$error = '';
$success = '';
$all_services = [];
$all_exceptions = [];

try {
    // Database connection
    $bdd = new PDO($dsn, $user, $pass);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // On form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate exceptions before transaction
        $lines = explode("\n", trim($exceptions));
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $parts = explode(" ", trim($line));
            if (count($parts) !== 2) {
                throw new Exception("Invalid exception format: \"$line\"");
            }
            $date = $parts[0];
            $code_str = strtoupper($parts[1]);
            if (!in_array($code_str, ['INCLUDED', 'EXCLUDED'])) {
                throw new Exception("Invalid code in exception: \"$line\"");
            }
        }

        $bdd->beginTransaction();

        // Insert service
        $stmt = $bdd->prepare("
            INSERT INTO service (NAME, MONDAY, TUESDAY, WEDNESDAY, THURSDAY, FRIDAY, SATURDAY, SUNDAY, START_DATE, END_DATE)
            VALUES (:name, :monday, :tuesday, :wednesday, :thursday, :friday, :saturday, :sunday, :start_date, :end_date)
        ");
        $stmt->execute([
            ':name' => $name,
            ':monday' => $checked['monday'] ? 1 : 0,
            ':tuesday' => $checked['tuesday'] ? 1 : 0,
            ':wednesday' => $checked['wednesday'] ? 1 : 0,
            ':thursday' => $checked['thursday'] ? 1 : 0,
            ':friday' => $checked['friday'] ? 1 : 0,
            ':saturday' => $checked['saturday'] ? 1 : 0,
            ':sunday' => $checked['sunday'] ? 1 : 0,
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);

        $service_id = $bdd->lastInsertId();

        // Insert exceptions
        $exception_stmt = $bdd->prepare("INSERT INTO exception (SERVICE_ID, DATE, CODE) VALUES (:service_id, :date, :code)");

        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $parts = explode(" ", trim($line));
            $date = $parts[0];
            $code_str = strtoupper($parts[1]);
            $code = ($code_str === 'INCLUDED') ? 1 : 2;
            $exception_stmt->execute([
                ':service_id' => $service_id,
                ':date' => $date,
                ':code' => $code
            ]);
        }

        $bdd->commit();
        $success = "<p style='color:green'><strong>Service successfully added with ID $service_id.</strong></p>";
    }

    // Query all services
    $all_services = $bdd->query("SELECT * FROM service ORDER BY ID ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Query all exceptions
    $all_exceptions = $bdd->query("SELECT * FROM exception ORDER BY SERVICE_ID, DATE")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Rollback transaction if an error occurs
    if (isset($bdd) && $bdd->inTransaction()) $bdd->rollBack();

    // Display error message
    $error = "<p style='color:red'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<h1>Task 3: Insertion queries</h1>

<?= $error ?>

<!-- Show success message -->
<?= $success ?>

<!-- Insert Service and Exception form -->
<section>
    <h2>Service and its exceptions</h2>
    <p>Add a new service by filling in the name, start/end dates, active weekdays, and optional exceptions. Each exception should follow the format: <code>YYYY-MM-DD INCLUDED</code> or <code>YYYY-MM-DD EXCLUDED</code>.</p>
    <form method="post" action="t3_insert_service.php">
        <p>
            <label for="name">Service name:</label>
            <input type="text" id="name" name="name" placeholder="Service name..." value="<?= htmlspecialchars($name) ?>" required>
            
            <label for="start_date" style="margin-left: 1em;">Start date:</label>
            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required>

            <label for="end_date" style="margin-left: 1em;">End date:</label>
            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required>
        </p>

        <fieldset>
            <legend>Days of operation</legend>
            <?php foreach ($days as $day): ?>
                <label>
                    <input type="checkbox" name="<?= $day ?>" <?= $checked[$day] ? 'checked' : '' ?>> <?= ucfirst($day) ?>
                </label>
            <?php endforeach; ?>
        </fieldset>

        <p>
            <label for="exceptions">Exceptions (one per line):</label><br>
            <textarea name="exceptions" rows="5" cols="50" placeholder="YYYY-MM-DD INCLUDED or EXCLUDED"><?= htmlspecialchars($exceptions) ?></textarea>
        </p>

        <input type="submit" value="Add Service">
        <button type="button" onclick="window.location.href='t3_insert_service.php'">Clear</button>
    </form>
</section>

<!-- Service Table -->
<section>
    <h2>All services</h2>
    <table border="1" cellpadding="5" cellspacing="0" style="width:100%; margin-top: 1em;">
        <tr>
            <th>ID</th>
            <th>NAME</th>
            <th>START DATE</th>
            <th>END DATE</th>
            <th>MON</th>
            <th>TUE</th>
            <th>WED</th>
            <th>THU</th>
            <th>FRI</th>
            <th>SAT</th>
            <th>SUN</th>
        </tr>
        <?php foreach ($all_services as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['ID']) ?></td>
                <td><?= htmlspecialchars($s['NAME']) ?></td>
                <td><?= htmlspecialchars($s['START_DATE']) ?></td>
                <td><?= htmlspecialchars($s['END_DATE']) ?></td>
                <td><?= $s['MONDAY'] ? 'x' : '' ?></td>
                <td><?= $s['TUESDAY'] ? 'x' : '' ?></td>
                <td><?= $s['WEDNESDAY'] ? 'x' : '' ?></td>
                <td><?= $s['THURSDAY'] ? 'x' : '' ?></td>
                <td><?= $s['FRIDAY'] ? 'x' : '' ?></td>
                <td><?= $s['SATURDAY'] ? 'x' : '' ?></td>
                <td><?= $s['SUNDAY'] ? 'x' : '' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<!-- Exception Table -->
<section>
    <h2>All exceptions</h2>
    <table border="1" cellpadding="5" cellspacing="0" style="width:100%; margin-top: 1em;">
        <tr>
            <th>SERVICE ID</th>
            <th>DATE</th>
            <th>CODE</th>
        </tr>
        <?php foreach ($all_exceptions as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['SERVICE_ID']) ?></td>
                <td><?= htmlspecialchars($e['DATE']) ?></td>
                <td><?= htmlspecialchars($e['CODE']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php include 'footer.php'; ?>