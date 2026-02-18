<?php
session_start();
// Configuration BD
require '../include/config.php';

// User connecté
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../front_office/login_user.php');
    exit();
}

// Récupérer données envoyées via formulaire
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$email = trim($_POST['email']);
$datenais = trim($_POST['datenais']);
$user_id = $_SESSION['user_id'];

// Validation données
if (empty($first_name) || empty($last_name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($datenais)) {
    // Données invalide
    header('Location: ../front_office/profil.php?error=invalid_data');
    exit();
}

// MàJ BD
try {
    // REQUETE SQL Update user
    $sql = "UPDATE users 
            SET first_name = :first_name, 
                last_name = :last_name, 
                email = :email, 
                datenais = :datenais 
            WHERE id = :user_id";
            
    $stmt = $pdo->prepare($sql);
    
    // Lier variables aux placeholders
    $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
    $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':datenais', $datenais, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    // Exécution requête
    $stmt->execute();
    
    header('Location: ../front_office/profil.php?success=profile_updated');
    exit();

} catch (PDOException $e) {
    header('Location: ../front_office/profil.php?error=db_error');
    exit();
}
?>