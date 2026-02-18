<?php
session_start();
require_once '../include/config.php';

// User non connecté & formulaire non soumis
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../front_office/login_user.php');
    exit();
}

// Champs remplis
if (empty(trim($_POST['Sujet'])) || empty(trim($_POST['message']))) {
    header('Location: ../front_office/contact.php?status=error&message=empty');
    exit();
}

try {
    // Chaîne connex PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    // Connexion
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // REQUETE SQL Insert
    $stmt = $conn->prepare("INSERT INTO public.messages (user_id, sujet, message) VALUES (:user_id, :sujet, :message)");
    
    // Lier paramètres
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':sujet', $_POST['Sujet'], PDO::PARAM_STR);
    $stmt->bindParam(':message', $_POST['message'], PDO::PARAM_STR);
    
    // Exécuter requête
    $stmt->execute();
    
    header('Location: ../front_office/contact.php?status=success');
    exit();

} catch(PDOException $e) {
    header('Location: ../front_office/contact.php?status=error&message=db_error');
    exit();
} finally {
    $conn = null;
}
?>
