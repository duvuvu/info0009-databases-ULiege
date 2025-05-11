<?php
session_start();
include 'header.php';
?>

<head>
    <title>Task 8: Stop in Belgium</title>
</head>

<?php
// Database connection
$host = 'ms8db';
$db   = 'group21';
$user = 'group21';
$pass = 'secret';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$error = '';
$edit_error = '';
$edit_success = '';

// Bounds for coordinates (used in update validation)
define('MIN_LAT', 49.5294835476);
define('MAX_LAT', 51.4750237087);
define('MIN_LON', 2.51357303225);
define('MAX_LON', 6.15665815596);

try {
    $bdd = new PDO($dsn, $user, $pass);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $edit_row_id = $_POST['edit_row_id'] ?? null;

    // Update handler
    if (isset($_POST['update_stop'])) {
        $original_id = $_POST['original_id'];
        $new_id = $_POST['new_id'];
        $name = $_POST['name'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            $edit_error = "<p style='color:red'>Latitude and longitude must be numeric.</p>";
            $edit_row_id = $original_id;
        } elseif (
            $longitude < MIN_LON || $longitude > MAX_LON ||
            $latitude < MIN_LAT || $latitude > MAX_LAT
        ) {
            $edit_error = "<p style='color:red'>Error: Coordinates must be inside Belgium (" . MIN_LAT . " ≤ lat ≤ " . MAX_LAT . ", " . MIN_LON . " ≤ lon ≤ " . MAX_LON . ").</p>";
            $edit_row_id = $original_id;
        } else {
            try {
                $bdd->beginTransaction();

                if ($new_id != $original_id) {
                    $check = $bdd->prepare("SELECT COUNT(*) FROM stop WHERE ID = ?");
                    $check->execute([$new_id]);
                    if ($check->fetchColumn() > 0) {
                        throw new Exception("The ID $new_id already exists.");
                    }
                }

                // Let ON UPDATE CASCADE handle foreign keys
                $bdd->prepare("UPDATE stop SET ID = ?, NAME = ?, LATITUDE = ?, LONGITUDE = ? WHERE ID = ?")
                    ->execute([$new_id, $name, $latitude, $longitude, $original_id]);

                $bdd->commit();
                $edit_success = "<p style='color:green'>Stop \"" . htmlspecialchars($name) . "\" updated successfully (ID $original_id → $new_id).</p>";
                $edit_row_id = null;

            } catch (Exception $e) {
                $bdd->rollBack();
                $edit_error = "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                $edit_row_id = $original_id;
            }
        }
    }

    // Filter handling
    if (isset($_POST['apply_filter'])) {
        $_SESSION['filter_id'] = $_POST['id'] ?? '';
        $_SESSION['filter_name'] = $_POST['name'] ?? '';
    }

    if (isset($_POST['clear_filter'])) {
        unset($_SESSION['filter_id'], $_SESSION['filter_name']);
    }

    $id_filter = $_SESSION['filter_id'] ?? '';
    $name_filter = $_SESSION['filter_name'] ?? '';

    // Query build
    $query = "SELECT * FROM stop WHERE NAME NOT LIKE '%(%)%'";
    $params = [];

    if ($id_filter !== '' && ctype_digit($id_filter)) {
        $query .= " AND ID = ?";
        $params[] = (int)$id_filter;
    }
    if ($name_filter !== '') {
        $query .= " AND NAME LIKE BINARY ?";
        $params[] = '%' . $name_filter . '%';
    }

    $stmt = $bdd->prepare($query);
    $stmt->execute($params);
    $stops = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = "<p style='color:red'><strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<h1>Task 8: Update queries</h1>

<?= $error ?>

<h2>Stop in Belgium</h2>
<p>Use the form below to filter stops by ID or name. Only stops without parentheses in their name are shown.</p>

<!-- Filter Form -->
<form method="post">
    <input type="number" name="id" placeholder="ID..." value="<?= htmlspecialchars($id_filter) ?>" min="0">
    <input type="text" name="name" placeholder="Name..." value="<?= htmlspecialchars($name_filter) ?>">
    <button type="submit" name="apply_filter">Filter</button>
    <button type="submit" name="clear_filter">Clear</button>
</form>

<?= $edit_error ?>
<?= $edit_success ?>

<!-- Editable Stop Table -->
<form method="post">
<table border="1" cellpadding="5" cellspacing="0" style="margin-top: 1em; width: 100%;">
    <tr>
        <th>ID</th>
        <th>NAME</th>
        <th>LATITUDE</th>
        <th>LONGITUDE</th>
        <th>ACTION</th>
    </tr>

    <?php foreach ($stops as $stop): ?>
        <?php if ($edit_row_id == $stop['ID']): ?>
            <tr>
                <td>
                    <input type="hidden" name="original_id" value="<?= $stop['ID'] ?>">
                    <input type="number" name="new_id" value="<?= htmlspecialchars($stop['ID']) ?>" required min="0">
                </td>
                <td><input type="text" name="name" value="<?= htmlspecialchars($stop['NAME']) ?>" required></td>
                <td><input type="number" step="any" name="latitude" value="<?= htmlspecialchars($stop['LATITUDE']) ?>" required></td>
                <td><input type="number" step="any" name="longitude" value="<?= htmlspecialchars($stop['LONGITUDE']) ?>" required></td>
                <td>
                    <input type="submit" name="update_stop" value="Update">
                    <button type="button" onclick="window.location.href='t8_update_stop_table.php'">Cancel</button>
                </td>
            </tr>
        <?php else: ?>
            <tr>
                <td><?= htmlspecialchars($stop['ID']) ?></td>
                <td><?= htmlspecialchars($stop['NAME']) ?></td>
                <td><?= htmlspecialchars($stop['LATITUDE']) ?></td>
                <td><?= htmlspecialchars($stop['LONGITUDE']) ?></td>
                <td>
                    <button type="submit" name="edit_row_id" value="<?= $stop['ID'] ?>">Edit</button>
                </td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</table>
</form>

<?php include 'footer.php'; ?>
