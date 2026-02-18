<?php
// 1. Récupération des informations de connexion depuis les variables d'environnement de Render

// Nom de la variable que vous avez définie dans Render pour l'hôte (Host)
$host = getenv('DB_HOST'); 
$port = getenv('DB_PORT') ?: '5432'; // Utilisez le port de Render, sinon 5432
$dbname = getenv('DB_NAME'); // Nom de la DB Render
$user = getenv('DB_USER'); // Utilisateur de la DB Render
$password = getenv('DB_PASS'); // Mot de passe de la DB Render

// 2. Chaîne de connexion (DSN)
// Utilisation des variables récupérées
$dsn = "pgsql:host={$host};port={$port};dbname={$dbname};user={$user};password={$password}";

try {
    // 3. Création instance PDO
    $pdo = new PDO($dsn);

    // 4. Mode d'erreur de PDO sur Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // OPTIONNEL : Vous pouvez afficher un message de succès ici pour tester
    // die("Connexion à la base de données Render réussie !");

} catch (PDOException $e) {
    // L'erreur de connexion sera affichée si la DB n'est pas accessible
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>