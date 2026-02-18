<?php
session_start();
require_once '../include/config.php';
require_once '../lib/tcpdf/tcpdf.php'; 

// Vérification session & paramètres
if (!isset($_SESSION['user_id'])) { die("Accès non autorisé."); }
if (!isset($_GET['ref']) || empty($_GET['ref'])) { die("Référence de transaction non spécifiée ou vide."); }
$reference_transaction = $_GET['ref'];
$user_id = $_SESSION['user_id'];

try {
    // Connexion & récupération données de la base 
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("
        SELECT p.reference_transaction, p.numero_telephone, p.date_paiement, c.titre AS cours_titre, c.prix AS cours_prix, c.duree AS cours_duree, niv.nom AS niveau_nom, u.first_name, u.last_name
        FROM public.paiements p
        JOIN public.cours c ON p.id_cours = c.id
        JOIN public.users u ON p.id_utilisateur = u.id
        LEFT JOIN public.niveaux niv ON c.niveau_id = niv.id
        WHERE p.reference_transaction = :ref AND p.id_utilisateur = :user_id
        ORDER BY p.date_paiement DESC LIMIT 1
    ");
    $stmt->execute([':ref' => $reference_transaction, ':user_id' => $user_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) { die("Aucun paiement trouvé pour cette référence. Vérifiez que la référence est correcte."); }

    // Reçu
    $user_fullname = htmlspecialchars($data['first_name'] . ' ' . $data['last_name']);
    $date_paiement = (new DateTime($data['date_paiement']))->format('d/m/Y H:i');
    $montant_paye = number_format($data['cours_prix'], 0, '', ' ') . ' MGA';
    $cours_titre = htmlspecialchars($data['cours_titre']);
    $niveau_nom = htmlspecialchars($data['niveau_nom']);
    $cours_duree = htmlspecialchars($data['cours_duree']);
    $numero_telephone = htmlspecialchars($data['numero_telephone']);
    $ref_transaction_pdf = htmlspecialchars($data['reference_transaction']);

    // Capturer ce qui est dans mémoire tampon
    ob_start();
    ?>
    
    <style>
        body { font-family: helvetica; color: #333; }
        .container { padding: 20px; }
        .header { text-align: right; }
        .header h1 { color: #0ea5e9; font-size: 24px; margin: 0; }
        .header p { font-size: 12px; color: #666; margin: 0; }
        .details { margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 8px 0; border-bottom: 1px solid #f4f4f5; }
        .details .label { color: #555; }
        .details .value { font-weight: bold; text-align: right; }
        .total { margin-top: 20px; padding-top: 15px; border-top: 2px solid #333; }
        .total table { width: 100%; }
        .total .label { font-size: 14px; }
        .total .value { font-size: 22px; font-weight: bold; text-align: right; }
    </style>
    <body>
        <div class="container">
            <div class="header">
                <h1>Reçu de Paiement</h1>
                <p>Date : <?php echo $date_paiement; ?></p>
            </div>
            <div class="details">
                <table>
                    <tr><td class="label">Facturé à :</td><td class="value"><?php echo $user_fullname; ?></td></tr>
                    <tr><td class="label">Cours :</td><td class="value"><?php echo $cours_titre; ?></td></tr>
                    <tr><td class="label">Niveau :</td><td class="value"><?php echo $niveau_nom; ?></td></tr>
                    <tr><td class="label">Durée :</td><td class="value"><?php echo $cours_duree; ?></td></tr>
                    <tr><td class="label">N° de téléphone :</td><td class="value"><?php echo $numero_telephone; ?></td></tr>
                    <tr><td class="label">Référence :</td><td class="value" style="font-family: monospace;"><?php echo $ref_transaction_pdf; ?></td></tr>
                </table>
            </div>
            <div class="total">
                <table>
                    <tr><td class="label">Montant Payé</td><td class="value"><?php echo $montant_paye; ?></td></tr>
                </table>
            </div>
        </div>
    </body>

    <?php

    // Récupèrer contenu HTML/CSS qui a mis en mémoire tampon 
    $html = ob_get_clean();

    // Créer objet de TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(' Plateforme');
    $pdf->SetAuthor(' Plateforme');
    $pdf->SetTitle('Reçu de paiement - ' . $data['cours_titre']);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    // $pdf->AddPage('P',"A4"); P (portrait: default), L(paysage)
    
    $logoPath = '../assets/img/inf.png';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 15, 15, 40, '', 'PNG');
    }

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('recu_paiement_' . $ref_transaction_pdf . '.pdf', 'D');
    exit();

} catch (Exception $e) {
    die("Erreur lors de la génération du PDF : " . $e->getMessage());
} finally {
    $conn = null;
}
?>