<?php include 'header.php'; ?>

<head>
    <title>Task 2: Agencies</title>
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
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $siege = $_POST['siege'] ?? '';

    // Build SQL query with optional filters
    $query = "SELECT * FROM agency WHERE 1=1";
    $params = [];

    if ($id !== '') {
        $query .= " AND ID = :id";
        $params[':id'] = $id;
    }
    if ($name !== '') {
        $query .= " AND NAME LIKE BINARY :name";
        $params[':name'] = '%' . $name . '%';
    }
    if ($telephone !== '') {
        $query .= " AND TELEPHONE LIKE BINARY :telephone";
        $params[':telephone'] = '%' . $telephone . '%';
    }
    if ($siege !== '') {
        $query .= " AND SIEGE LIKE BINARY :siege";
        $params[':siege'] = '%' . $siege . '%';
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
    <h2>Agency</h2>
    <p>Filter agencies by ID, name, telephone, or siege. All rows are shown by default.</p>

    <!-- Filter Form -->
    <form method="post">
        <input type="number" name="id" placeholder="ID..." value="<?= htmlspecialchars($id) ?>" min="0">
        <input type="text" name="name" placeholder="Name..." value="<?= htmlspecialchars($name) ?>">
        <input type="text" name="telephone" placeholder="Telephone..." value="<?= htmlspecialchars($telephone) ?>">
        <input type="text" name="siege" placeholder="Siege..." value="<?= htmlspecialchars($siege) ?>">
        <button type="submit" name="apply_filter">Filter</button>
        <button type="button" onclick="window.location.href='t2_selection_agency.php'">Clear</button>
    </form>

    <!-- Results Table -->
    <table border="1" cellpadding="5" cellspacing="0" style="margin-top: 1em; width: 100%;">
        <tr>
            <th>ID</th>
            <th>NAME</th>
            <th>URL</th>
            <th>TIME ZONE</th>
            <th>TELEPHONE</th>
            <th>SIEGE</th>
        </tr>
        <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['ID']) ?></td>
                <td><?= htmlspecialchars($row['NAME']) ?></td>
                <td><?= htmlspecialchars($row['URL']) ?></td>
                <td><?= htmlspecialchars($row['TIME_ZONE']) ?></td>
                <td><?= htmlspecialchars($row['TELEPHONE']) ?></td>
                <td><?= htmlspecialchars($row['SIEGE']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php include 'footer.php'; ?>
