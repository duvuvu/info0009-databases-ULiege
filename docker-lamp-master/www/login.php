<?php
session_start(); // Starts a PHP session — needed to store user info like login status between page reloads. Session data is stored in $_SESSION
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Test de PHP : Login</title>
    </head>
    <body>
        <?php
        // Remove session data if logged out
        if (isset($_POST['disconnect'])) {
            session_unset();
        }

        // Creates a PDO connection to MySQL inside Docker
        // Host: ms8db (as defined in docker-compose.yml)
        // DB/User: groupXX
        // Password: secret
        $bdd = new PDO('mysql:host=ms8db;dbname=groupXX', 'groupXX', 'secret');

        // Check if the connection was successful
        if ($bdd == NULL)
            echo "Problème de connection";

        // If a login is submitted, it checks the users table. If a matching user is found, login is successful, and the username is stored in $_SESSION['login'].
        if (isset($_POST["login"])) {
            $req = $bdd->query("SELECT * FROM users WHERE Login = '" . $_POST["login"] . "' AND Pass = '" . $_POST["pass"] . "' ");
            $tuple = $req->fetch();
            if ($tuple) {
                $_SESSION['login'] = $tuple["Login"];
            } else
                echo "Votre login/mot de passe est incorrect<br><br>";
        }


        if (isset($_SESSION['login'])) {
            echo "<h1>Bienvenue " . $_SESSION['login'] . "</h1><br>";
            if (isset($_POST['texte']))
                echo "Vous avez écrit : " . $_POST['texte'] . "<br>";
        ?>
            <!-- Formulaire pour se déconnecter -->
            <form method="post" action="login.php">
                <p>
                    <input type="hidden" name="disconnect" value="yes">
                    <input type="submit" value="Deconnection">
                </p>
            </form>
            <h2>Entrez un petit texte</h2>
            <form method="post" action="login.php">
                <p>
                    <input type="text" name="texte">
                    <input type="submit" value="Envoyer">
                </p>
            </form>
        <?php
        } else {
        ?>
            <h1>Veuillez entrer vos identifiants</h1>
            <form method="post" action="login.php">
                <p>
                    <input type="text" name="login" required>
                    <input type="password" name="pass" required>
                    <input type="submit" value="Envoyer">
                </p>
            </form>
        <?php
        }
        ?>
    </body>
</html>