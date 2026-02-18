<?php
session_start();

require_once '../../include/config.php';

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "Action non autorisée : ID du cours invalide.";
    header('Location: ../../back_office/admin_page_formation.php');
    exit();
}
$cours_id = $_GET['id'];

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->beginTransaction();

    // Supprimer paiements associés à cours
    $stmt_delete_paiements = $conn->prepare("DELETE FROM public.paiements WHERE id_cours = :id_cours");
    $stmt_delete_paiements->execute([':id_cours' => $cours_id]);

    // Supprimer cours 
    $stmt_delete_cours = $conn->prepare("DELETE FROM public.cours WHERE id = :id");
    $stmt_delete_cours->execute([':id' => $cours_id]);

    $conn->commit();

    $_SESSION['success_message'] = "Le cours et tous les paiements associés ont été supprimés avec succès.";
    header('Location: ../../back_office/admin_page_formation.php');
    exit();

} catch (PDOException $e) {
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    $_SESSION['error_message'] = "Erreur lors de la suppression du cours et de ses dépendances : " . $e->getMessage();
    header('Location: ../../back_office/admin_page_formation.php');
    exit();

} finally {
    $conn = null;
}
?>