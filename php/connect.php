<?php
//on a besoin de se connecter donc on stock le code de connect.php dans un
// fichier à part + on utilise required_once sur chaque page qui nécessite
// une connexion

const DBHOST = "db";
const DBNAME = "quiz_php";
const DBUSER = "test";
const DBPASS = "test";

$dsn = "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8";

try { //ici on essaie de se connecter
    $db = new PDO($dsn, DBUSER, DBPASS);
    // echo "connexion: success" . "<br>";
} catch(PDOException $error) {
    echo "connexion failed: " . $error->getMessage() . "<br>";
}

