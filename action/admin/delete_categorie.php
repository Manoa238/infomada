<?php

session_start();
require_once '../../include/config.php';

// ID est passé dans l'URL & entier valide
if (isset($_GET['id']) && filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) {
    $categorie_id = $_GET['id'];

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $conn = new PDO($dsn, $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "DELETE FROM public.categories WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $categorie_id, PDO::PARAM_INT);
        $stmt->execute();

        // Suppression suucès
        $_SESSION['success_message'] = 'La catégorie a été supprimée avec succès.';

    } catch (PDOException $e) {
        // Erreur
        // Code '23503' violations clé étrangère en SQL
        if ($e->getCode() == '23503') {
            $_SESSION['error_message'] = 'Impossible de supprimer : cette catégorie est utilisée par un ou plusieurs cours.';
        } else {
            $_SESSION['error_message'] = 'Erreur lors de la suppression : ' . $e->getMessage();
        }
    } finally {
        $conn = null;
    }
} else {
    // ID invalide ou manquant
    $_SESSION['error_message'] = 'ID de catégorie invalide.';
}

// Rediriger vers la page d'administration
header('Location: ../../back_office/admin_page_formation.php');
exit();