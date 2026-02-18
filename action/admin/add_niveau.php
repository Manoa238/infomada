<?php
require_once '../../include/config.php';

// Formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer & valider nom_niveau
    $nom_niveau = trim($_POST['nom_niveau']);

    if (!empty($nom_niveau)) {
        try {
            // Connexion BD
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $conn = new PDO($dsn, $user, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // REQUETE SQL INSERT
            $sql = "INSERT INTO public.niveaux (nom) VALUES (:nom)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nom', $nom_niveau);
            
            $stmt->execute();

            // Redirection vers page_admin 
            header('Location: ../../back_office/admin_page_formation.php?status=success_niv');
            exit();

        } catch (PDOException $e) {
            // Erreur
            if ($e->getCode() == '23505') { // Violation contrainte unique
                 header('Location: ../../back_office/admin_page.php?status=error_niv_exists');
            } else {
                 header('Location: ../../back_office/admin_page.php?status=error_db');
            }
            exit();
        } finally {
            $conn = null;
        }
    } else {
        // Le champ était vide
        header('Location: ../../back_office/admin_page.php?status=error_niv_empty');
        exit();
    }
} else {
    header('Location: ../../back_office/admin_page_formation.php');
    exit();
}