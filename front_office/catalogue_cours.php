<?php
session_start();
require_once '../include/config.php';

// RÉCUPÉRATION DONNÉES
$tous_les_cours = [];
$categories = [];
$niveaux = [];
$db_error = ''; //message d'erreur

try {
    // Connexion à BD
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête SQL
    $stmt_cours = $conn->query("
        SELECT c.id, c.titre, c.prix, c.description, c.chemin_image, cat.nom AS categorie_nom, niv.nom AS niveau_nom 
        FROM public.cours c
        LEFT JOIN public.categories cat ON c.categorie_id = cat.id
        LEFT JOIN public.niveaux niv ON c.niveau_id = niv.id
        ORDER BY c.date_creation DESC
    ");
    $tous_les_cours = $stmt_cours->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer catégories
    $stmt_cat = $conn->query("SELECT nom FROM public.categories ORDER BY nom");
    $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer niveaux 
    $stmt_niv = $conn->query("SELECT nom FROM public.niveaux ORDER BY id");
    $niveaux = $stmt_niv->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $db_error = "Erreur de connexion à la base de données : Impossible d'afficher les cours.";  
} finally {
    // fermer connexion
    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>INFOMADA</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="../assets/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/index.css">

  <script src="../assets/js/Tailwind.js"></script> 

  <style>
    /* Sélecteur de filtre personnalisé */
    .selection-perso {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2338bdf8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
      background-position: right 0.5rem center;
      background-repeat: no-repeat;
      background-size: 1.5em 1.5em;
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
      padding-right: 2.5rem;
    }

    /* RUBAN DE PRIX */
    .conteneur-ruban {
        width: 120px;
        height: 120px;
        position: absolute;
        top: -3px;
        right: -3px;
        overflow: hidden;
        z-index: 10;
    }
    .ruban {
        font-size: 13px;
        font-weight: bold;
        color: #FFF;
        text-align: center;
        transform: rotate(45deg);
        -webkit-transform: rotate(45deg);
        position: relative;
        padding: 7px 0;
        left: 20px;
        top: 25px;
        width: 140px;
        background:rgb(14, 164, 233); 
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
    .ruban.gratuit {
        background:rgba(226, 105, 19, 0.61); 
    }
  </style>

</head>
<body class="bg-slate-100 font-sans text-slate-700 antialiased">

  <header class="fixed top-0 left-0 w-full bg-white/95 backdrop-blur-lg border-b border-slate-200 z-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center gap-x-6 gap-y-4 py-4">

        <div class="hidden lg:flex">
              <a href="../index.php" class="flex items-center">
                <h2 class="font-bold text-2xl tracking-tight text-primary-local italic">INFOMADA</h2>
              </a>
            </div>

            <div class="flex items-center gap-3">
                <label for="search-input" class="font-medium text-slate-700 whitespace-nowrap">Rechercher</label>
                <div class="relative w-full">
                  <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fa-solid fa-search text-slate-400"></i>
                  </div>
                  <input type="search" id="search-input" class="w-full bg-white border border-sky-300 rounded-lg py-2 pl-10 pr-3 focus:border-sky-500 focus:ring-2 focus:ring-sky-200 transition-all outline-none">
                </div>
            </div>

            <!-- DROPDOWN CATEGORIE -->
            <div class="flex items-center gap-3">
                <label for="filter-category" class="font-medium text-slate-700">Catégorie</label>
                <select id="filter-category" class="selection-perso w-full bg-white border border-sky-300 rounded-lg py-2 px-3 focus:border-sky-500 focus:ring-2 focus:ring-sky-200 transition-all outline-none">
                    <option value="all">Toutes</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['nom']); ?>">
                          <?php echo htmlspecialchars($cat['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- DROPDOWN NIVEAU -->
            <div class="flex items-center gap-3">
                <label for="filter-level" class="font-medium text-slate-700">Niveau</label>
                <select id="filter-level" class="selection-perso w-full bg-white border border-sky-300 rounded-lg py-2 px-3 focus:border-sky-500 focus:ring-2 focus:ring-sky-200 transition-all outline-none">
                    <option value="all">Tous</option>
                    <?php foreach ($niveaux as $niv): ?>
                        <option value="<?php echo htmlspecialchars($niv['nom']); ?>">
                          <?php echo htmlspecialchars($niv['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
      </div>
    </div>
  </header>

  <main class="pt-48 md:pt-32">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight">Catalogue de Cours</h1>
        </div>

        <div id="course-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php if ($db_error): ?>
            <p class="col-span-full text-center text-red-500">
              <?php echo htmlspecialchars($db_error); ?>
            </p>
        <?php elseif (empty($tous_les_cours)): ?>
            <div class="col-span-full text-center py-16">
                <div class="max-w-md mx-auto bg-white p-12 rounded-lg border-2 border-dashed border-slate-300">
                    <i class="fa-solid fa-book-open-reader text-5xl text-slate-400"></i>
                    <h3 class="mt-6 text-xl font-semibold text-slate-700">Aucune formation disponible</h3>
                    <p class="mt-2 text-slate-500">Nous ajouterons bientôt de nouvelles formations. Revenez nous voir !</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($tous_les_cours as $cours): ?>
                <div class="course-card bg-white rounded-xl shadow-lg hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 overflow-hidden group flex flex-col relative"
                    data-title="<?php echo htmlspecialchars(strtolower($cours['titre'])); ?>"
                    data-category="<?php echo htmlspecialchars($cours['categorie_nom']); ?>"
                    data-level="<?php echo htmlspecialchars($cours['niveau_nom']); ?>">

                    <!-- PRIX -->
                    <div class="conteneur-ruban">
                        <div class="ruban <?php if (($cours['prix'] ?? 0) == 0) echo 'gratuit'; ?>">
                            <?php if (($cours['prix'] ?? 0) > 0): ?>
                                <?php echo htmlspecialchars(number_format($cours['prix'], 0, '', ' ')); ?> MGA
                            <?php else: ?>
                                Gratuit
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- AFFICHAGE D'IMAGE -->
                    <a href="details_cours.php?id=<?php echo $cours['id']; ?>">
                        <div class="h-40 bg-white flex items-center justify-center overflow-hidden border-b border-slate-200">
                            <?php
                                $image_path = !empty($cours['chemin_image']) 
                                    ? '../' . htmlspecialchars($cours['chemin_image']) 
                                    : '../assets/img/cours.jpg';
                            ?>
                            <img src="<?php echo $image_path; ?>" 
                                 alt="Image du cours <?php echo htmlspecialchars($cours['titre']); ?>" 
                                 class="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105">
                        </div>
                    </a>

                    <!-- CONTENU DE LA CARTE -->
                    <div class="p-5 flex flex-col flex-grow">
                        <h3 class="font-bold text-lg text-primary-local group-hover:text-sky-600 transition-colors">
                            <a href="details_cours.php?id=<?php echo $cours['id']; ?>">
                                <?php echo htmlspecialchars($cours['titre']); ?>
                            </a>
                        </h3>
                        <div class="mt-2 space-x-2">
                            <span class="inline-block bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                              <?php echo htmlspecialchars($cours['categorie_nom']); ?>
                            </span>
                            <span class="inline-block bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                              <?php echo htmlspecialchars($cours['niveau_nom']); ?>
                            </span>
                        </div>
                        <p class="mt-3 text-slate-600 text-sm line-clamp-3 flex-grow">
                            <?php echo htmlspecialchars($cours['description']); ?>
                        </p>
                        
                        <!-- SECTION "Détails" & "Valider" -->
                        <div class="mt-auto pt-4 flex justify-end items-center">
                            <a href="paiement.php?course_id=<?php echo $cours['id']; ?>" 
                               class="inline-flex items-center gap-2 bg-gradient-to-r from-sky-500 to-indigo-600 
                               text-white font-semibold text-sm px-4 py-2 rounded-lg shadow-lg hover:shadow-xl 
                               hover:-translate-y-0.5 transition-all duration-300">
                                <i class="fa-solid fa-cart-shopping text-xs"></i>
                                <span>Valider</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div id="no-results-message" class="hidden col-span-full text-center py-16">
            <div class="max-w-md mx-auto bg-white p-12 rounded-lg border-2 border-dashed border-slate-300">
                <i class="fa-solid fa-magnifying-glass-minus text-5xl text-slate-400"></i>
                <h3 class="mt-6 text-xl font-semibold text-slate-700">Aucun résultat trouvé</h3>
                <p class="mt-2 text-slate-500">Essayez d'ajuster vos filtres ou votre recherche.</p>
            </div>
        </div>
    </div>
</div>
  </main>
  <script src="../assets/js/filtre_catalogue_cours.js"></script>

</body>
</html>