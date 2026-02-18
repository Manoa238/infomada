<?php
require_once '../../include/config.php';

//  Vérifier si formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //  Récupérer & valider nom _catégorie
    $nom_categorie = trim($_POST['nom_categorie']);

    if (!empty($nom_categorie)) {
        try {
            //  Connexion à la BD
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $conn = new PDO($dsn, $user, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // REQUETE SQL INSERT 
            $sql = "INSERT INTO public.categories (nom) VALUES (:nom)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nom', $nom_categorie);
            
            $stmt->execute();

            //  Message de succès
            header('Location: ../../back_office/admin_page_formation.php?status=success_cat');
            exit();

        } catch (PDOException $e) {
            // Catégorie existe 
            $error_code = $e->getCode();
            // Le code '23505' doublon
            if ($error_code == '23505') {
                 header('Location: ../../back_office/admin_page.php?status=error_cat_exists');
            } else {
                 header('Location: ../../back_office/admin_page.php?status=error_db');
            }
            exit();
        } finally {
            $conn = null;
        }
    } else {
        header('Location: ../../back_office/admin_page.php?status=error_cat_empty');
        exit();
    }
} else {
    header('Location: ../../back_office/admin_page.php');
    exit();
}