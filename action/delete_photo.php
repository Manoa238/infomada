<?php
session_start();
require '../include/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../front_office/login_user.php');
    exit();
}

try {
    // Récupérer le nom du fichier image pour pouvoir le supprimer du disque
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && !empty($user['profile_image'])) {
        $filename_to_delete = $user['profile_image'];
        $filepath = '../uploads/' . $filename_to_delete;

        // Mettre la colonne 'profile_image' à NULL dans la base de données
        $update_stmt = $pdo->prepare("UPDATE users SET profile_image = NULL WHERE id = :user_id");
        $update_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $update_stmt->execute();

        // Supprimer physiquement le fichier du serveur
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    // Rediriger avec le bon message de succès
    header('Location: ../front_office/profil.php?success=photo_deleted');
    exit();

} catch (PDOException $e) {
    header('Location: ../front_office/profil.php?error=db_error');
    exit();
}
?>