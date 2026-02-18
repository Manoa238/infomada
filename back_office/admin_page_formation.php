<?php
require_once '../include/config.php';

session_start();

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

$error_from_action = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);

$filtre_categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$filtre_niveau_id = isset($_GET['niveau']) ? (int)$_GET['niveau'] : 0;

$niveaux_list = [];
$categories_list = [];
$cours_list = [];
$error_message = '';
$formateurs_list = []; // liste menu déroulant

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $niveaux_list = $conn->query("SELECT id, nom FROM public.niveaux ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
    $categories_list = $conn->query("SELECT id, nom FROM public.categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
    $formateurs_list = $conn->query("SELECT id_formateur, nom_formateur FROM public.formateurs ORDER BY nom_formateur")->fetchAll(PDO::FETCH_ASSOC);

    // Requete SQL
    $sql_base = "SELECT 
                    c.id, c.titre, c.description, c.chemin_image, c.nom_original_image,
                    c.duree, c.prix, 
                    cat.nom AS categorie_nom, 
                    niv.nom AS niveau_nom,
                    f.nom_formateur, f.email_formateur, f.image_formateur  
                 FROM public.cours c 
                 LEFT JOIN public.categories cat ON c.categorie_id = cat.id 
                 LEFT JOIN public.niveaux niv ON c.niveau_id = niv.id
                 LEFT JOIN public.formateurs f ON c.id_formateur = f.id_formateur";
    
    $conditions = [];
    $parametres = [];

    if ($filtre_categorie_id > 0) {
        $conditions[] = "c.categorie_id = :categorie_id";
        $parametres[':categorie_id'] = $filtre_categorie_id;
    }

    if ($filtre_niveau_id > 0) {
        $conditions[] = "c.niveau_id = :niveau_id";
        $parametres[':niveau_id'] = $filtre_niveau_id;
    }

    if (!empty($conditions)) {
        $sql_base .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql_base .= " ORDER BY c.date_creation DESC";

    $stmt_cours = $conn->prepare($sql_base);
    $stmt_cours->execute($parametres);
    $cours_list = $stmt_cours->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Admin - Gestion Formation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    
    <script src="../assets/js/Tailwind.js"></script>

    <style>
        .form-container { 
            transition: all 0.5s ease-in-out; 
            max-height: 0; 
            overflow: hidden; 
            opacity: 0; 
            transform: translateY(-10px); 
        }
        .form-container.open { max-height: 2000px; opacity: 1; transform: translateY(0); margin-bottom: 2rem; }
        .modal { display: none; }
        .modal.open { display: flex; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-fadeIn {
             animation: fadeIn 0.2s ease-out;
        }

        #modale-parametres {
            background-color: rgba(0, 0, 0, 0.5);
        }
        #modale-confirmation {
            background-color: rgba(0, 0, 0, 0.6); 
        }
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
                        <a href="admin_page_dashboard.php" class="nav-link flex items-center py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors" data-target="dashboard">
                        <i class="fa-solid fa-table-columns w-5 text-center mr-4"></i> Dashboard</a></li>
                    <li>
                        <a href="#" class="nav-link flex items-center font-bold py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors" data-target="formation">
                        <i class="fa-solid fa-layer-group w-5 text-center mr-4"></i> Formation</a></li>
                    <li>
                        <a href="admin_page_paiement.php" class="nav-link flex items-center py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors" data-target="users">
                        <i class="fa-solid fa-credit-card w-5 text-center mr-4"></i> Paiement</a>
                    </li>
                    <li>
                        <a href="admin_page.php" class="nav-link flex items-center py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors" data-target="messages">
                            <i class="fa-solid fa-envelope w-5 text-center mr-4"></i> Messages</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="flex-grow p-6 sm:p-10">
            <div id="formation" class="page">
                
                <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
                    <div>
                        <h1 class="text-4xl font-bold text-primary-local">Information du cours</h1>
                        <p class="mt-1 text-secondary">Gérez, modifiez et publiez les formations.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <form id="formulaire-filtre" action="admin_page_formation.php" method="GET" class="flex items-center gap-3">
                            <select name="categorie" id="filtre-categorie" class="bg-white border border-slate-200 rounded-lg py-2 px-4 text-sm text-slate-700 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none appearance-none">
                                <option value="0">Toutes les catégories</option>
                                <?php foreach($categories_list as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php if($filtre_categorie_id == $cat['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($cat['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <select name="niveau" id="filtre-niveau" class="bg-white border border-slate-200 rounded-lg py-2 px-4 text-sm text-slate-700 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none appearance-none">
                                <option value="0">Tous les niveaux</option>
                                <?php foreach($niveaux_list as $niv): ?>
                                    <option value="<?php echo $niv['id']; ?>" <?php if($filtre_niveau_id == $niv['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($niv['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <div class="border-l border-slate-300 h-6"></div>
                        <div class="flex items-center gap-2">
                            <button id="btn-parametres" title="Gérer les catégories et niveaux" class="text-slate-600 p-3 rounded-lg hover:bg-slate-200 transition-colors">
                                <i class="fa-solid fa-gear text-xl"></i>
                            </button>
                            <button id="btn-ajouter-cours" class="bg-primary-local text-white font-semibold py-3 px-5 rounded-lg flex items-center gap-2 hover:bg-primary-hover transition-all hover:scale-105 shadow-sm hover:shadow-lg">
                                <i class="fa-solid fa-plus"></i>
                                <span>Nouveau Cours</span>
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (!empty($success_message)): ?>
                    <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg" role="alert">
                        <p class="font-bold">Succès</p>
                        <p><?php echo htmlspecialchars($success_message); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error_from_action)): ?>
                    <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg" role="alert">
                        <p class="font-bold">Erreur</p>
                        <p><?php echo htmlspecialchars($error_from_action); ?></p>
                    </div>
                <?php endif; ?>

                <div id="formulaire-ajout-cours" class="form-container">
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-slate-200">
                           <h2 class="text-xl font-bold mb-4 text-primary-local border-b pb-3">Ajouter un nouveau cours</h2>
                           <form action="../action/admin/add_cours.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="titre" class="block text-sm font-semibold text-slate-600 mb-1.5">Titre</label>
                                        <input id="titre" type="text" name="titre" placeholder="Introduction à PHP" required class="w-full border-2 border-slate-200 rounded-lg py-2 px-3 text-sm">
                                    </div>
                                    <div>
                                        <label for="fichier_image" class="block text-sm font-semibold text-slate-600 mb-1.5">Image de couverture</label>
                                        <input id="fichier_image" type="file" name="fichier_image" accept="image/png, image/jpeg, image/gif" class="w-full text-sm text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold">
                                    </div>
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-semibold text-slate-600 mb-1.5">Description</label>
                                    <textarea id="description" name="description" placeholder="Objectifs du cours..." rows="3" class="w-full border-2 border-slate-200 rounded-lg py-2 px-3 text-sm"></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="categorie_id" class="block text-sm font-semibold text-slate-600 mb-1.5">Catégorie</label>
                                        <select id="categorie_id" name="categorie_id" required class="w-full border-2 border-slate-200 rounded-lg py-2 px-3 text-sm bg-white appearance-none">
                                            <option value="" disabled selected>Sélectionner...</option>
                                            <?php foreach($categories_list as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>">
                                                    <?php echo htmlspecialchars($cat['nom']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="niveau_id" class="block text-sm font-semibold text-slate-600 mb-1.5">Niveau</label>
                                        <select id="niveau_id" name="niveau_id" required class="w-full border-2 border-slate-200 rounded-lg py-2 px-3 text-sm bg-white appearance-none">
                                            <option value="" disabled selected>Sélectionner...</option>
                                            <?php foreach($niveaux_list as $niv): ?>
                                                <option value="<?php echo $niv['id']; ?>">
                                                    <?php echo htmlspecialchars($niv['nom']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="border-t border-slate-200 pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="duree" class="block text-sm font-semibold text-slate-600 mb-1.5">Durée</label>
                                        <input id="duree" type="text" name="duree" placeholder="7 semaines " class="w-full border-2 border-slate-200 rounded-lg py-2 px-3 text-sm">
                                    </div>
                                    <div>
                                        <label for="prix" class="block text-sm font-semibold text-slate-600 mb-1.5">Prix (MGA)</label>
                                        <input id="prix" type="number" name="prix" step="1" min="0" value="0" placeholder="50000" class="w-full border-2 border-slate-200 rounded-lg py-2 px-3 text-sm">
                                    </div>
                                </div>
                                <div class="pt-4 border-t border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                                     <div>
                                         <label for="formateur_nom" class="block text-sm font-semibold text-slate-600 mb-1.5">Nom du formateur</label>
                                         <input id="formateur_nom" type="text" name="formateur_nom" placeholder="Herimanoa" required class="w-full border-2 border-slate-200 rounded-lg py-2 px-3 text-sm">
                                     </div>
                                     <div>
                                         <label for="formateur_email" class="block text-sm font-semibold text-slate-600 mb-1.5">Email du formateur</label>
                                         <input id="formateur_email" type="email" name="formateur_email" placeholder="formateur@gmail.com" required class="w-full border-2 border-slate-200 rounded-lg py-2 px-3 text-sm">
                                     </div>
                                </div>
                                <div class="pt-4 border-t border-slate-200">
                                     <label for="formateur_image" class="block text-sm font-semibold text-slate-600 mb-1.5">Image du formateur (Optionnel)</label>
                                     <input id="formateur_image" type="file" name="formateur_image" accept="image/png, image/jpeg, image/gif" class="w-full text-sm text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold">
                                </div>
                                <div class="pt-4 border-t border-slate-200">
                                    <button type="submit" class="w-full bg-primary-local text-white font-bold py-2.5 px-4 rounded-lg hover:bg-primary-hover">
                                        <i class="fa-solid fa-rocket mr-2"></i> Publier le Cours
                                    </button>
                                </div>
                           </form>
                    </div>
                </div>

                <div class="max-w-7xl mx-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php if (empty($cours_list)): ?>
                            <div class="col-span-full text-center py-10 bg-white rounded-xl shadow-md">
                                <p class="text-slate-500">Aucun cours ne correspond aux filtres sélectionnés.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($cours_list as $cours): ?>
                                <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 overflow-hidden group flex flex-col">
                                    <div class="h-40 bg-white flex items-center justify-center overflow-hidden border-b border-slate-200">
                                        <?php $image_path = !empty($cours['chemin_image']) ? '../' . htmlspecialchars($cours['chemin_image']) : '../assets/img/cours.jpg'; ?>
                                        <img src="<?php echo $image_path; ?>" alt="Image du cours <?php echo htmlspecialchars($cours['titre']); ?>" class="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105">
                                    </div>
                                    <div class="p-6 flex flex-col flex-grow">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="font-bold text-base text-primary-local group-hover:text-sky-600 transition-colors pr-2">
                                                <?php echo htmlspecialchars($cours['titre']); ?>
                                            </h3>
                                            <div class="flex-shrink-0 space-x-2">
                                                <a href="edit_cours.php?id=<?php echo $cours['id']; ?>" class="text-indigo-400 hover:text-primary" title="Modifier le cours">
                                                    <i class="fa-solid fa-pencil"></i>
                                                </a>
                                                <a href="../action/admin/delete_cours.php?id=<?php echo $cours['id']; ?>" class="text-red-400 hover:text-danger lien-supprimer-cours" title="Supprimer le cours">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 mb-3">
                                            <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                <?php echo htmlspecialchars($cours['categorie_nom']); ?>
                                            </span>
                                            <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                <?php echo htmlspecialchars($cours['niveau_nom']); ?>
                                            </span>
                                        </div>
                                        <p class="text-sm text-slate-600 line-clamp-2 flex-grow">
                                            <?php echo htmlspecialchars($cours['description'] ?: 'Aucune description disponible.'); ?>
                                        </p>
                                        <div class="mt-auto pt-4 border-t border-slate-100">
                                            <?php if (!empty($cours['nom_formateur'])): ?>
                                            <div class="flex items-center gap-3 mb-4">
                                                <?php 
                                                $formateur_image_path = !empty($cours['image_formateur']) ? '../' . htmlspecialchars($cours['image_formateur']) : '../assets/img/incognito.png'; 
                                                ?>
                                                <img src="<?php echo $formateur_image_path; ?>" alt="Photo de <?php echo htmlspecialchars($cours['nom_formateur']); ?>" class="w-10 h-10 rounded-full object-cover">
                                                <div>
                                                    <p class="text-xs text-slate-400">Formateur</p>
                                                    <p class="font-semibold text-sm text-slate-700">
                                                        <?php echo htmlspecialchars($cours['nom_formateur']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <div class="flex justify-between items-center text-sm text-slate-600">
                                                <div class="flex items-center gap-2">
                                                    <i class="fa-regular fa-clock text-sky-500"></i>
                                                    <span><?php echo htmlspecialchars($cours['duree'] ?: '---'); ?></span>
                                                </div>
                                                <div class="font-bold text-lg text-primary-local">
                                                    <?php if (($cours['prix'] ?? 0) > 0): ?>
                                                        <?php echo htmlspecialchars(number_format($cours['prix'], 0, '', ' ')); ?> MGA
                                                    <?php else: ?>
                                                        Gratuit
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- CONFIGURATION -->
    <div id="modale-parametres" class="modal fixed inset-0 z-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-4xl">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-secondary-local text-center flex-1">Configuration</h2>
                <button id="btn-fermer-modale" class="text-slate-400 hover:text-danger">
                    <i class="fa-solid fa-times fa-2x cursor-pointer"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4 text-center">Catégories</h3>
                    <form action="../action/admin/add_categorie.php" method="POST" class="mb-4 flex gap-2">
                        <input type="text" name="nom_categorie" required placeholder="Nouvelle catégorie..." class="flex-grow border-2 border-slate-200 rounded-lg p-2">
                        <button type="submit" class="bg-primary-local text-white font-semibold px-4 rounded-lg hover:bg-primary-hover">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </form>
                    <div id="liste-categories-container" class="space-y-2 max-h-64 overflow-y-auto pr-2">
                        <?php foreach($categories_list as $cat): ?>
                        <div class="deletable-item flex justify-between items-center bg-slate-100 p-2 rounded-lg">
                            <span><?php echo htmlspecialchars($cat['nom']); ?></span>
                            <a href="../action/admin/delete_categorie.php?id=<?php echo $cat['id']; ?>" class="confirm-delete-link text-slate-400 hover:text-danger" data-type="categorie" title="Supprimer la catégorie">
                                <i class="fa-solid fa-times"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4 text-center">Niveaux</h3>
                    <form action="../action/admin/add_niveau.php" method="POST" class="mb-4 flex gap-2">
                        <input type="text" name="nom_niveau" required placeholder="Nouveau niveau..." class="flex-grow border-2 border-slate-200 rounded-lg p-2">
                        <button type="submit" class="bg-primary-local text-white font-semibold px-4 rounded-lg hover:bg-primary-hover">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </form>
                    <div id="liste-niveaux-container" class="space-y-2 max-h-64 overflow-y-auto pr-2">
                        <?php foreach($niveaux_list as $niv): ?>
                            <div class="deletable-item flex justify-between items-center bg-slate-100 p-2 rounded-lg">
                                <span><?php echo htmlspecialchars($niv['nom']); ?></span>
                                <a href="../action/admin/delete_niveau.php?id=<?php echo $niv['id']; ?>" class="confirm-delete-link text-slate-400 hover:text-danger" data-type="niveau" title="Supprimer le niveau">
                                    <i class="fa-solid fa-times"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formulaireFiltre = document.getElementById('formulaire-filtre');
            const selectCategorie = document.getElementById('filtre-categorie');
            const selectNiveau = document.getElementById('filtre-niveau');

            function submitFilterForm() {
                formulaireFiltre.submit();
            }

            selectCategorie.addEventListener('change', submitFilterForm);
            selectNiveau.addEventListener('change', submitFilterForm);
        });
    </script>
    <script src="../assets/js/admin_scripts.js"></script>
</body>
</html>