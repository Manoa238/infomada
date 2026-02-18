<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
</head>
<body>
    <h1>Bienvenue sur votre tableau de bord !</h1>
    <p>Vous êtes connecté en tant que : <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
    <a href="logout.php">Se déconnecter</a>
</body>
</html>