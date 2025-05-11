<?php include 'header.php'; ?>

<head>
    <title>Task 2: Service exception</title>
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
    
    // Read filter values from POST or default to empty
    $service_id = $_POST['service_id'] ?? '';
    $date = $_POST['date'] ?? '';
    $code = $_POST['code'] ?? '';
    
    // Build SQL query with optional filters
    $query = "SELECT * FROM exception WHERE 1=1";
    $params = [];
    
    if ($service_id !== '') {
        $query .= " AND SERVICE_ID = :service_id";
        $params[':service_id'] = $service_id;
    }

    if ($date !== '') {
        $query .= " AND DATE = :date";
        $params[':date'] = $date;
    }

    if ($code !== '') {
        $query .= " AND CODE = :code";
        $params[':code'] = $code;
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
    <h2>Service exception</h2>
    <p>Filter service exceptions by ID, date, or code. All rows are shown by default.</p>
    
    <!-- Filter Form -->
    <form method="post">
        <input type="number" name="service_id" placeholder="Service ID..." value="<?= htmlspecialchars($service_id) ?>" min="0">
        <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
        <input type="number" name="code" placeholder="Code..." value="<?= htmlspecialchars($code) ?>" min="0">
        <button type="submit" name="apply_filter">Filter</button>
        <button type="button" onclick="window.location.href='t2_selection_exception.php'">Clear</button>
    </form>

    <!-- Results Table -->
    <table border="1" cellpadding="5" cellspacing="0" style="margin-top: 1em; width: 100%;">
        <tr>
            <th>SERVICE ID</th>
            <th>DATE</th>
            <th>CODE</th>
        </tr>
        <?php foreach ($results as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['SERVICE_ID']) ?></td>
            <td><?= htmlspecialchars($row['DATE']) ?></td>
            <td><?= htmlspecialchars($row['CODE']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php include 'footer.php'; ?>