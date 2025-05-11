<!DOCTYPE html>
<html>

<head>
    <title>Formulaire</title>
</head>

<body>
    <form method="post" action="exemple-form.php">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <input type="submit" value="Soumettre">
    </form>
    <p>
        <?php
        if (isset($_POST['nom'])) {
            echo "Vous avez écrit " . $_POST['nom'] . " " . $_POST['prenom'];
        }
        ?>
    </p>

    <form method="post" action="exemple-form.php">
        <select name="liste">
            <option value="element1">nom_element1</option>
            <option value="element2">nom_element2</option>
            <option value="element3">nom_element3</option>
        </select>
        <input type="submit" value="Soumettre">
    </form>
    <?php
    if (isset($_POST['liste'])) {
        echo "<p>Vous avez écrit nom_" . $_POST['liste'] . "</p>";
    }
    ?>

</body>

</html>