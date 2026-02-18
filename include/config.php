<?php

$host = 'localhost';
$port = '5432'; // Port PostgreSQL
$dbname = 'infomada';
$user = 'postgres'; // Nom user
$password = 'ravomanoa'; // Mot de passe utilisateur PostgreSQL

// Chaîne de connexion (DSN)
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";

try {
    // Création instance PDO
    $pdo = new PDO($dsn);

    // Mode d'erreur de PDO sur Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>