<!DOCTYPE html>
<html>

<head>
    <title>Formulaire</title>
</head>

<body>

    <form method="post" action="exemple-form-transactions.php">
        <p>Création de départements aléatoires. Il est possible que nous créions deux départements portant le même nom, ce qui devrait être évité.</p>
        <input type="hidden" name="action" value="exemple" />
        <input type="submit" value="Soumettre" />
    </form>

    <h1>Départements avant la transaction</h1>

    <table>
        <tr>
            <th>DNO</th>
            <th>DNAME</th>
        </tr>

    <?php
        $bdd = new PDO('mysql:host=db;dbname=groupXX;charset=utf8', 'groupXX', 'secret');
    
        $req = $bdd->query('SELECT DNO, DNAME FROM department');
        while ($tuple = $req->fetch()) {
            echo "<tr><td>" . $tuple['DNO'] . "</td><td>" . $tuple['DNAME'] . "</td></tr>";
        }
    ?>

    </table>

    <?php
        if ($_POST['action'] == 'exemple') {
    ?>
            <h1>Vous avez cliqué sur "Soumettre", nous démarrons donc une transaction dans cet exemple.</h1>
    <?php
    
            try {
                $bdd->beginTransaction();
                
                $sql = 'INSERT INTO department(DNO, DNAME) VALUES (:dno, :dname)';
                
                for($i = 0; $i < 3; $i++) {
                    $randomID = rand(5, 15);
                    $randomNAME = "Departement " . $randomID;

                    echo "<p>Insérer le département $randomNAME</p>";

                    $statement = $bdd->prepare($sql);                
                    $statement->bindParam(':dno', $randomID, PDO::PARAM_INT);
                    $statement->bindParam(':dname', $randomNAME, PDO::PARAM_STR);
                    $res = $statement->execute();

                    if ($res) { 
                        echo $statement->rowCount() . " ligne(s) insérée(s) (prévu)" ;
                    } else {
                        print_r($statement->errorInfo());
                        $bdd->rollBack();
                        echo "<p>Rollback: aucune ligne insérée</p>" ;
                        return;
                    }
                }

                $c = $bdd->query('SELECT COUNT(*) AS COUNT FROM department')->fetch();

                echo '<p>Nous avons maintenant ' .  $c['COUNT'] . ' départements.</p>';

                $bdd->commit();
            } catch (\PDOException $e) {
                // rollback 
                $bdd->rollBack();    
                // Montrer l'erreur
                die($e->getMessage());
            }
            
        }
    ?>

</body>

</html>