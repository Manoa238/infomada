<?php
session_start();

// Configuration BD
require '../include/config.php';

// User connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../front_office/login_user.php');
    exit();
}

// Fichier envoyé & pas d'erreur
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    
    $allowed_types = ['image/jpeg', 'image/png'];
    $file_type = mime_content_type($_FILES['profile_image']['tmp_name']);

    if (in_array($file_type, $allowed_types)) {
        
        $upload_dir = '../uploads/';

        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
        $upload_file = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_file)) {
            try {
                $sql = "UPDATE users SET profile_image = :profile_image WHERE id = :user_id";
                $stmt = $pdo->prepare($sql);
                
                $stmt->bindParam(':profile_image', $new_filename, PDO::PARAM_STR);
                $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                
                $stmt->execute();
                header('Location: ../front_office/profil.php?success=1');
                exit();

            } catch (PDOException $e) {
                unlink($upload_file);
                die("Erreur lors de la mise à jour de la base de données : " . $e->getMessage());
            }

        } else {
            // Échec déplacement
            header('Location: ../front_office/profil.php?error=upload');
            exit();
        }
    } else {
        // Mauvais type de fichier
        header('Location: ../front_office/profil.php?error=filetype');
        exit();
    }
} else {
    // Pas de fichier ou erreur
    header('Location: ../front_office/profil.php?error=nofile');
    exit();
}
?>