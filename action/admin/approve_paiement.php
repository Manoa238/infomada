<?php
// Démarrer session 
session_start();

require_once '../../include/config.php';

// ID_paiement présent dans URL &  nombre valide
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    // ID manquant/invalide
    $_SESSION['error_message'] = "ID de paiement invalide ou manquant.";
    header('Location: ../../back_office/admin_page_paiement.php');
    exit();
}

// Récupérer ID_paiement 
$paiement_id = (int)$_GET['id'];

try {
    // Connexion à BD via PDO
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // REQUETE SQL UPDATE paiement 
    $sql = "UPDATE public.paiements SET statut = 'approuvé' WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    
    // Lier paramètre id à $paiement_id
    $stmt->bindParam(':id', $paiement_id, PDO::PARAM_INT);
    
    // Exécuter requête
    $stmt->execute();

    // MàJ a affecté une ligne
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "Le paiement a été approuvé avec succès et l'accès au cours a été débloqué.";
    } else {
        // Aucune ligne modifiée
        $_SESSION['error_message'] = "Aucun paiement trouvé avec cet ID ou le statut était déjà approuvé.";
    }

} catch (PDOException $e) {
    // Erreur BD
    $_SESSION['error_message'] = "Erreur lors de la mise à jour du paiement : " . $e->getMessage();
} finally {
    // Fermer connexion à BD
    $conn = null;
}

// Redirection admini vers admin_page_paiement
header('Location: ../../back_office/admin_page_paiement.php');
exit();
?>