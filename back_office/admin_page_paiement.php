<?php
require_once '../include/config.php'; 

// Démarrer session 
session_start();

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

$error_from_action = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);

// Init listes & message d'erreur
$paiements_list = [];
$niveaux_list = [];
$error_message = '';

try {
    // Connexion à BD
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // REQUETE SQL pour récupérer paiements
    $sql_paiements = "
        SELECT 
            p.id, 
            u.email AS email_utilisateur, 
            c.titre AS titre_cours, 
            p.reference_transaction, 
            p.numero_telephone, 
            p.statut, 
            p.date_paiement,
            niv.code_couleur,
            c.formateur_nom,
            c.chemin_image_formateur
        FROM public.paiements p
        JOIN public.users u ON p.id_utilisateur = u.id
        JOIN public.cours c ON p.id_cours = c.id
        LEFT JOIN public.niveaux niv ON c.niveau_id = niv.id
        ORDER BY p.date_paiement DESC
    ";
    $stmt_paiements = $conn->query($sql_paiements);
    $paiements_list = $stmt_paiements->fetchAll(PDO::FETCH_ASSOC);

    // REQUETE SQL légende des niveaux
    $sql_niveaux = "SELECT nom, code_couleur FROM public.niveaux WHERE code_couleur IS NOT NULL ORDER BY id";
    $stmt_niveaux = $conn->query($sql_niveaux);
    $niveaux_list = $stmt_niveaux->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) { 
    $error_message = "Erreur de base de données : " . $e->getMessage(); 
} finally { 
    $conn = null; 
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Paiements</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/Tailwind.js"></script>
    <link rel="stylesheet" href="../assets/css/all.min.css"> 
    <link rel="stylesheet" href="../assets/css/index.css">
    <style>
        .modal { display: none; } .modal.open { display: flex; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .animate-fadeIn { animation: fadeIn 0.2s ease-out; }
        #modale-confirmation { background-color: rgba(0, 0, 0, 0.6); }
        @keyframes fadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-20px); } }
        .fade-out { animation: fadeOut 0.5s ease-out forwards; }
    </style>
</head>
<body class="font-sans bg-slate-100 text-slate-800 antialiased">

<div class="flex w-full min-h-screen">
    <aside class="w-[260px] bg-white p-6 border-r text-primary-local flex-col shrink-0 hidden sm:flex">
        <div class="text-2xl font-bold text-primary-local text-center mb-12 flex items-center justify-center gap-2">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Admin</span>
        </div>
        <nav>
            <ul class="space-y-2">
                <li>
                    <a href="admin_page_dashboard.php" class="nav-link flex items-center py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors">
                        <i class="fa-solid fa-table-columns w-5 text-center mr-4"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="admin_page_formation.php" class="nav-link flex items-center py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors">
                        <i class="fa-solid fa-layer-group w-5 text-center mr-4"></i> Formation
                    </a>
                </li>
                <li>
                    <a href="admin_page_paiement.php" class="nav-link flex items-center font-bold py-3 px-4 rounded-lg bg-accent-light text-primary transition-colors">
                        <i class="fa-solid fa-credit-card w-5 text-center mr-4"></i> Paiements
                    </a>
                </li>
                <li>
                    <a href="admin_page.php#messages" class="nav-link flex items-center py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors">
                        <i class="fa-solid fa-envelope w-5 text-center mr-4"></i> Messages
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="flex-grow p-6 sm:p-10">
        <div>
            <h1 class="text-4xl font-bold text-primary-local">Paiements </h1>
            <p class="mt-2 text-secondary">Vérifiez les paiements et débloquez l'accès aux cours pour les apprenants.</p>
            
            <?php if (!empty($niveaux_list)): ?>
            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2">
                <span class="text-sm font-semibold text-slate-600">Légende des niveaux :</span>
                <?php foreach ($niveaux_list as $niveau): ?>
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full" style="background-color: <?php echo htmlspecialchars($niveau['code_couleur']); ?>; border: 1px solid rgba(0,0,0,0.1);"></span>
                        <span class="text-sm text-slate-500"><?php echo htmlspecialchars($niveau['nom']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
            <div id="success-alert" class="mt-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                <p class="font-bold">Succès</p><p><?php echo htmlspecialchars($success_message); ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($error_from_action)): ?>
            <div id="error-alert" class="mt-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                <p class="font-bold">Erreur</p>
                <p><?php echo htmlspecialchars($error_from_action); ?></p>
            </div>
            <?php endif; ?>

            <div class="mt-8 bg-white rounded-xl shadow-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-100 text-slate-600 uppercase">
                        <tr>
                            <th class="p-4">Apprenant</th>
                            <th class="p-4">Formation</th>
                            <th class="p-4">Référence Trans.</th>
                            <th class="p-4">N° Tel</th>
                            <th class="p-4">Statut</th>
                            <th class="p-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paiements_list)): ?>
                            <tr><td colspan="7" class="text-center p-8 text-slate-500">Aucun paiement enregistré.</td></tr>
                        <?php else: ?>
                            <?php foreach ($paiements_list as $paiement): ?>
                                <tr class="border-t" style="background-color: <?php echo htmlspecialchars($paiement['code_couleur'] ?? '#FFFFFF'); ?>;">
                                    <td class="p-4 font-medium align-top">
                                        <?php echo htmlspecialchars($paiement['email_utilisateur']); ?>
                                        <span class="block text-xs text-slate-500 font-normal mt-1"><?php echo date('d/m/Y H:i', strtotime($paiement['date_paiement'])); ?></span>
                                    </td>
                                    <!--  FORMATION & FORMATEUR  -->
                                    <td class="p-4 align-top">
                                        <div class="font-semibold"><?php echo htmlspecialchars($paiement['titre_cours']); ?></div>
                                        
                                        <?php if (!empty($paiement['formateur_nom'])): ?>
                                            <div class="flex items-center gap-2 mt-2">
                                                <?php 
                                                    $formateur_img = !empty($paiement['chemin_image_formateur']) 
                                                        ? '../' . htmlspecialchars($paiement['chemin_image_formateur']) 
                                                        : '../assets/img/incognito.png'; 
                                                ?>
                                                <img src="<?php echo $formateur_img; ?>" alt="Photo de <?php echo htmlspecialchars($paiement['formateur_nom']); ?>" class="w-6 h-6 rounded-full object-cover">
                                                <span class="text-xs text-slate-500"><?php echo htmlspecialchars($paiement['formateur_nom']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="p-4 font-mono align-top"><?php echo htmlspecialchars($paiement['reference_transaction']); ?></td>
                                    <td class="p-4 align-top"><?php echo htmlspecialchars($paiement['numero_telephone']); ?></td>
                                    <td class="p-4 align-top">
                                        <?php if ($paiement['statut'] == 'approuvé'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Débloqué</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-semibold text-orange-800 bg-orange-100 rounded-full">En attente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 flex items-center space-x-4 align-top">
                                        <?php if ($paiement['statut'] == 'en attente'): ?>
                                            <a href="../action/admin/approve_paiement.php?id=<?php echo $paiement['id']; ?>" class="font-bold text-green-600 hover:underline" title="Approuver">Débloquer</a>
                                        <?php endif; ?>
                                        <a href="../action/admin/delete_paiement.php?id=<?php echo $paiement['id']; ?>" 
                                           class="text-red-500 hover:text-red-700 lien-supprimer-paiement" 
                                           data-email="<?php echo htmlspecialchars($paiement['email_utilisateur']); ?>"
                                           title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div id="modale-confirmation" class="modal fixed inset-0 z-[100] items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-sm text-center transform transition-all animate-fadeIn">
        <h3 id="titre-modale-confirmation" class="text-xl font-medium text-slate-800 mb-4"></h3>
        <div class="flex justify-center gap-4">
            <button id="btn-confirmer-suppression" class="w-28 bg-[#76A0D6] hover:bg-[#3261A6] text-white font-semibold py-2 px-4 rounded-md">Oui</button>
            <button id="btn-annuler-suppression" class="w-28 bg-[#9176D6] hover:bg-[#5E32A6] text-white font-semibold py-2 px-4 rounded-md">Non</button>
        </div>
    </div>
</div>

<script src="../assets/js/admin_paiement_scripts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const successAlert = document.getElementById('success-alert');
    const errorAlert = document.getElementById('error-alert');
    if (successAlert) { setTimeout(() => { successAlert.classList.add('fade-out'); }, 400); }
    if (errorAlert) { setTimeout(() => { errorAlert.classList.add('fade-out'); }, 400); }
});
</script>

</body>
</html>