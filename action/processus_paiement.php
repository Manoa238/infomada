<?php
session_start();
require_once '../include/config.php';

// JSON pour réponse
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Accès non autorisé.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {

    $id_cours = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    $id_utilisateur = $_SESSION['user_id'];
    $reference_transaction = trim($_POST['transaction_id']);
    $numero_telephone = trim($_POST['phone_number']);

    // Validation N° de téléphone 
    if (!preg_match('/^03[23478][0-9]{7}$/', $numero_telephone)) {
        $response['message'] = "Le format du numéro de téléphone est invalide. Il doit comporter 10 chiffres (ex: 034 XX XXX XX).";
        echo json_encode($response);
        exit();
    }

    if (!$id_cours || empty($reference_transaction)) {
        $response['message'] = "Veuillez remplir tous les champs de confirmation.";
        echo json_encode($response);
        exit();
    }

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $conn = new PDO($dsn, $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // REQUETE SQL Insert paiement & récupérer le nouvel ID
        $sql = "INSERT INTO public.paiements (id_utilisateur, id_cours, reference_transaction, numero_telephone, statut) 
                VALUES (:id_utilisateur, :id_cours, :reference_transaction, :numero_telephone, 'en attente')
                RETURNING id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id_utilisateur' => $id_utilisateur,
            ':id_cours' => $id_cours,
            ':reference_transaction' => $reference_transaction,
            ':numero_telephone' => $numero_telephone
        ]);

        $new_paiement_id = $stmt->fetchColumn();

        if ($new_paiement_id) {
            $response['success'] = true;
            $response['message'] = "Paiement soumis avec succès !";
        } else {
            $response['message'] = "Impossible d'enregistrer le paiement.";
        }

    } catch (PDOException $e) {
        $response['message'] = "Erreur de base de données. Veuillez réessayer.";
    }
}

echo json_encode($response);
exit();
?>