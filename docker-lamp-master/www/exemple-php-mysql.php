<!DOCTYPE html>
<html>
    <!-- Connexion a la base de données -->
    <?php
    $bdd = new PDO('mysql:host=db;dbname=groupXX;charset=utf8', 'groupXX', 'secret');
    ?>
    <head>
        <title>Départements</title>
    </head>
    <body>
        <h1>Départements</h1>
        <?php
        /*$req contient les tuples de la requête*/
        $req = $bdd->query('SELECT * FROM department');
        /*On affiche tous les résultats de la requête*/
        while ($tuple = $req->fetch()) {
            echo "<p>" . $tuple['DNO'] . " " . $tuple['DNAME'] . "</p>";
        }
        ?>
    </body>
</html>