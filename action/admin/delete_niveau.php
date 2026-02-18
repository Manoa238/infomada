<?php

session_start();
require_once '../../include/config.php';

// ID est passé dans l'URL & entier valide
if (isset($_GET['id']) && filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) {
    $niveau_id = $_GET['id'];

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $conn = new PDO($dsn, $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "DELETE FROM public.niveaux WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $niveau_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Suppression réussi
        $_SESSION['success_message'] = 'Le niveau a été supprimé avec succès.';

    } catch (PDOException $e) {
        // Erreur 
        if ($e->getCode() == '23503') {
            $_SESSION['error_message'] = 'Impossible de supprimer : ce niveau est utilisé par un ou plusieurs cours.';
        } else {
            $_SESSION['error_message'] = 'Erreur lors de la suppression : ' . $e->getMessage();
        }
    } finally {
        $conn = null;
    }
} else {
    // ID invalide ou manquant
    $_SESSION['error_message'] = 'ID de niveau invalide.';
}

// Redirection vers page_admin
header('Location: ../../back_office/admin_page_formation.php');
exit();