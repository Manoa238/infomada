<?php
session_start();
require_once '../../include/config.php';

header('Content-Type: application/json');

if(!isset($_POST['email'])){
    echo json_encode(['exists' => false, 'message' => 'Email manquant']);
    exit;
}

$email = trim($_POST['email']);

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("
        SELECT f.id_formateur, f.nom_formateur, f.email_formateur, f.image_formateur,
               c.titre, n.nom AS niveau_nom
        FROM formateurs f
        LEFT JOIN cours c ON c.id_formateur = f.id_formateur
        LEFT JOIN niveaux n ON n.id = c.niveau_id
        WHERE f.email_formateur = :email
    ");
    $stmt->execute([':email' => $email]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        // Stocker infos formateur dans session
        $_SESSION['formateur_id'] = $rows[0]['id_formateur'];
        $_SESSION['formateur_nom'] = $rows[0]['nom_formateur'];
        $_SESSION['formateur_email'] = $rows[0]['email_formateur'];

        // Liste des cours
        $cours = [];
        foreach ($rows as $r) {
            if (!empty($r['titre'])) {
                $cours[] = [
                    'titre' => $r['titre'],
                    'niveau' => $r['niveau_nom']
                ];
            }
        }

        echo json_encode([
            'exists' => true,
            'nom' => $rows[0]['nom_formateur'],
            'email' => $rows[0]['email_formateur'],
            'photo' => $rows[0]['image_formateur'] ?? null,
            'cours' => $cours
        ]);
    } else {
        echo json_encode(['exists' => false, 'message' => 'Aucun formateur trouvé']);
    }

} catch (PDOException $e) {
    echo json_encode(['exists' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
