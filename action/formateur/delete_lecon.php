<?php
session_start();
require_once '../../include/config.php';

$formateur_id = $_SESSION['formateur_id'] ?? 0;
if ($formateur_id == 0) {
    header('Location: ../../index.php');
    exit();
}

if (!isset($_GET['lecon_id']) || !filter_var($_GET['lecon_id'], FILTER_VALIDATE_INT) ||
    !isset($_GET['cours_id']) || !filter_var($_GET['cours_id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "Requête de suppression de leçon invalide.";
    header('Location: ../../front_office/formateur/formateur_page.php');
    exit();
}

$lecon_id = (int) $_GET['lecon_id'];
$cours_id = (int) $_GET['cours_id'];

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si leçon appartient au formateur via chapitre et cours
    $stmt_check = $conn->prepare("
        SELECT l.id, l.chemin_fichier, l.ordre, ch.id AS chapitre_id
        FROM lecons l
        JOIN chapitres ch ON l.chapitre_id = ch.id
        JOIN cours c ON ch.cours_id = c.id
        WHERE l.id = :lecon_id AND c.id_formateur = :formateur_id AND ch.cours_id = :cours_id
    ");
    $stmt_check->execute([
        ':lecon_id' => $lecon_id,
        ':formateur_id' => $formateur_id,
        ':cours_id' => $cours_id
    ]);
    $lecon_info = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$lecon_info) {
        $_SESSION['error_message'] = "Leçon non trouvée ou vous n'avez pas la permission de la supprimer.";
        header("Location: ../../front_office/formateur/voir_contenu.php?cours_id=$cours_id");
        exit();
    }

    $chapitre_id = $lecon_info['chapitre_id'];
    $chemin_fichier_a_supprimer = $lecon_info['chemin_fichier'];
    // Commencer transaction
    $conn->beginTransaction();

    // Supprimer leçon de la BD
    $stmt_delete_lecon = $conn->prepare("DELETE FROM lecons WHERE id = :lecon_id");
    $stmt_delete_lecon->execute([':lecon_id' => $lecon_id]);

    $conn->commit(); // Valider transaction

    // Supprimer fichier physique si existe
    if ($chemin_fichier_a_supprimer && file_exists('../../' . $chemin_fichier_a_supprimer)) {
        unlink('../../' . $chemin_fichier_a_supprimer);
    }

    $_SESSION['success_message'] = "Leçon supprimée avec succès.";
    header("Location: ../../front_office/formateur/voir_contenu.php?cours_id=$cours_id");
    exit();

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack(); // Annuler transaction en cas d'erreur
    }
    $_SESSION['error_message'] = "Erreur lors de la suppression de la leçon : " . $e->getMessage();
    header("Location: ../../front_office/formateur/voir_contenu.php?cours_id=$cours_id");
    exit();
} catch (Exception $e) {
    $_SESSION['error_message'] = "Une erreur inattendue est survenue : " . $e->getMessage();
    header("Location: ../../front_office/formateur/voir_contenu.php?cours_id=$cours_id");
    exit();
}