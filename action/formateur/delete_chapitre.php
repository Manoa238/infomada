<?php
session_start();
require_once '../../include/config.php';

$formateur_id = $_SESSION['formateur_id'] ?? 0;
if ($formateur_id == 0) {
    header('Location: ../../index.php');
    exit();
}

if (!isset($_GET['chapitre_id']) || !filter_var($_GET['chapitre_id'], FILTER_VALIDATE_INT) ||
    !isset($_GET['cours_id']) || !filter_var($_GET['cours_id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "Requête de suppression de chapitre invalide.";
    header('Location: ../../front_office/formateur/formateur_page.php');
    exit();
}

$chapitre_id = (int) $_GET['chapitre_id'];
$cours_id = (int) $_GET['cours_id'];

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si chapitre appartient au formateur
    $stmt_check = $conn->prepare("
        SELECT ch.id, ch.ordre
        FROM chapitres ch
        JOIN cours c ON ch.cours_id = c.id
        WHERE ch.id = :chapitre_id AND ch.cours_id = :cours_id AND c.id_formateur = :formateur_id
    ");
    $stmt_check->execute([
        ':chapitre_id' => $chapitre_id,
        ':cours_id' => $cours_id,
        ':formateur_id' => $formateur_id
    ]);
    $chapitre_info = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$chapitre_info) {
        $_SESSION['error_message'] = "Chapitre non trouvé ou vous n'avez pas la permission de le supprimer.";
        header("Location: ../../front_office/formateur/voir_contenu.php?cours_id=$cours_id");
        exit();
    }

    // Récupérer chemins_fichiers des leçons 
    $stmt_files = $conn->prepare("SELECT chemin_fichier FROM lecons WHERE chapitre_id = :chapitre_id AND chemin_fichier IS NOT NULL");
    $stmt_files->execute([':chapitre_id' => $chapitre_id]);
    $files_to_delete = $stmt_files->fetchAll(PDO::FETCH_COLUMN);

    // Commencer une transaction
    $conn->beginTransaction();

    // Supprimer les leçons associées au chapitre
    $stmt_delete_lecons = $conn->prepare("DELETE FROM lecons WHERE chapitre_id = :chapitre_id");
    $stmt_delete_lecons->execute([':chapitre_id' => $chapitre_id]);

    // Supprimer chapitre
    $stmt_delete_chapitre = $conn->prepare("DELETE FROM chapitres WHERE id = :chapitre_id");
    $stmt_delete_chapitre->execute([':chapitre_id' => $chapitre_id]);

    $conn->commit(); // Valider transaction

    // Supprimer fichiers physiques après suppression du DB
    foreach ($files_to_delete as $file_path) {
        $full_path = '../../' . $file_path;
        if (file_exists($full_path)) {
            unlink($full_path);
        }
    }

    $_SESSION['success_message'] = "Chapitre et toutes ses leçons supprimés avec succès.";
    header("Location: ../../front_office/formateur/voir_contenu.php?cours_id=$cours_id");
    exit();

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack(); // Annuler transaction en cas d'erreur
    }
    $_SESSION['error_message'] = "Erreur lors de la suppression du chapitre : " . $e->getMessage();
    header("Location: ../../front_office/formateur/voir_contenu.php?cours_id=$cours_id");
    exit();
} catch (Exception $e) {
    $_SESSION['error_message'] = "Une erreur inattendue est survenue : " . $e->getMessage();
    header("Location: ../../front_office/formateur/voir_contenu.php?cours_id=$cours_id");
    exit();
}