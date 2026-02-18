<?php
session_start();
require_once '../../include/config.php';

// user connecté
if (!isset($_SESSION['formateur_id'])) {
    header('Location: ../../index.php');
    exit();
}

if (!isset($_GET['cours_id']) || !filter_var($_GET['cours_id'], FILTER_VALIDATE_INT)) {
    $_SESSION['message_error'] = "ID de cours manquant ou invalide.";
    header('Location: formateur_page.php'); // Rediriger si cours_id = 0
    exit();
}
$cours_id = (int)$_GET['cours_id'];

// Requête est POST pour éviter l'accès direct
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $source_examen_json = '../../uploads/examens_json/examen.json';
    $destination_examen_json = '../../uploads/examens_json/examen_cours_' . $cours_id . '.json';

    if (file_exists($source_examen_json)) {
        $json_content = file_get_contents($source_examen_json);
        $questions = json_decode($json_content, true);

        if (json_last_error() === JSON_ERROR_NONE && !empty($questions)) {
            // Copier le fichier JSON 
            if (copy($source_examen_json, $destination_examen_json)) {
                $_SESSION['message_success'] = "L'examen a été publié avec succès pour le cours " . $cours_id . " !";
            } else {
                $_SESSION['message_error'] = "Erreur lors de la copie du fichier d'examen.";
            }
        } else {
            $_SESSION['message_error'] = "Impossible de publier l'examen : le fichier JSON est vide ou invalide.";
        }
    } else {
        $_SESSION['message_error'] = "Impossible de publier l'examen : le fichier examen.json est introuvable.";
    }

} else {
    $_SESSION['message_error'] = "Accès non autorisé à la page de publication.";
}

header('Location: formateur_page.php');
exit();
?>