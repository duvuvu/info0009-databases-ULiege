<?php include 'header.php'; ?>

<head>
    <title>Task 4: Services available by date</title>
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
$services = [];

try {
    $bdd = new PDO($dsn, $user, $pass);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch all services available by date
    $stmt = $bdd->query("SELECT * FROM vw_service_dates_filtered");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "<p style='color:red'><strong>Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<h1>Task 4: Recursive view queries</h1>

<!-- Show any validation or DB error -->
<?= $error ?>

<!-- Results Table -->
<section>
    <h2>Services available by date</h2>
    <p>Below is a list of all available services grouped by date.</p>
    <table border="1" cellpadding="5" cellspacing="0" style="margin-top: 1em; width: 100%;">
        <tr>
            <th>DATE</th>
            <th>AVAILABLE SERVICES</th>
        </tr>
        <?php foreach ($services as $service): ?>
            <tr>
                <td><?= htmlspecialchars($service['DATE']) ?></td>
                <td><?= htmlspecialchars($service['SERVICES']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php include 'footer.php'; ?>
