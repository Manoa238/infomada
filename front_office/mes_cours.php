<?php
session_start();
require_once '../include/config.php';

// User != connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login_user.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$mes_cours = [];
$db_error = '';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("
        SELECT DISTINCT
            c.id, c.titre, c.description, c.chemin_image,
            c.duree, c.formateur_nom, c.chemin_image_formateur,
            niv.nom AS niveau_nom,          
            niv.code_couleur AS niveau_couleur 
        FROM public.cours c
        JOIN public.paiements p ON c.id = p.id_cours
        LEFT JOIN public.niveaux niv ON c.niveau_id = niv.id 
        WHERE p.id_utilisateur = :user_id AND p.statut = 'approuvé'
        ORDER BY c.titre ASC
    ");

    $stmt->execute([':user_id' => $user_id]);
    $mes_cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $db_error = "Erreur de connexion à la base de données. Impossible d'afficher vos cours.";
} finally {
    $conn = null;
}
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Cours - INFOMADA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <script src="../assets/js/Tailwind.js"></script>
    <style>
        header.scrolled { background-color: rgba(0, 64, 128, 0.85); backdrop-filter: blur(10px); padding-top: 1rem; padding-bottom: 1rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); }
        .profil-menu { position: relative; display: inline-block; }
        .profil-menu .username { cursor: pointer; background-color: rgba(255, 255, 255, 0.1); padding: 0.5rem 1rem; border-radius: 9999px; font-weight: 600; transition: background-color 0.2s; }
        .profil-menu:hover .username { background-color: rgba(255, 255, 255, 0.2); }
        .profil-menu .dropdown-content { display: none; position: absolute; right: 0; background-color: white; min-width: 220px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 1; border-radius: 0.5rem; padding: 1rem; margin-top: 0.5rem; color: #333; text-align: center; }
        .profil-menu:hover .dropdown-content { display: block; }
        .dropdown-content img { border-radius: 50%; margin: 0 auto 1rem auto; border: 3px solid #004080; }
        .dropdown-content #qrcode { margin: 1rem auto; }
        .dropdown-content a { color: #004080; padding: 10px 12px; text-decoration: none; display: block; font-weight: 500; border-radius: 0.25rem; transition: background-color 0.2s, color 0.2s; }
        .dropdown-content a:hover { background-color: #f1f5f9; color: #0c4a6e; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-700 antialiased">

    <!-- HEADER -->
    <header id="site-header" class="bg-primary-local sticky top-0 z-50 text-white px-4 sm:px-6 lg:px-8 py-10 transition-all duration-300">
        <div class="container mx-auto flex justify-between items-center">
            <h2 class="font-bold text-2xl tracking-tight">INFOMADA</h2>
            <nav class="hidden md:flex items-center gap-6">
                <a href="../index.php"><span class="text-white font-semibold border-b-2 border-sky-400 pb-1">Accueil</span></a>
                <a href="../index.php#cours" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200">Cours</a>
                <a href="contact.php" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200">Contact</a>
                <a href="about.php" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200">A propos</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="mes_cours.php" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200 font-semibold">Mes Cours</a>
                <?php endif; ?>

                <div class="relative">
                    <!-- BARRE DE RECHERCHE -->
                    <input type="search" placeholder="Rechercher..." class="bg-primary-darker-local rounded-full pl-10 pr-4 py-2 text-sm text-white placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-400 transition-all duration-300 w-48 focus:w-56" />
                    <svg class="w-5 h-5 text-slate-300 absolute -mt-4 left-3 -translate-y-1/2" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27a6.471 6.471 0 001.48-5.34C15.17 6.01 12.16 3 8.58 3S2 6.01 2 9.58 5.01 16.17 8.58 16.17c1.61 0 3.09-.59 4.22-1.57l.27.28v.79l4.25 4.25c.39.39 1.02.39 1.41 0s.39-1.02 0-1.41L15.5 14zM8.58 14c-2.44 0-4.42-1.99-4.42-4.42S6.14 5.17 8.58 5.17 13 7.16 13 9.58 11.02 14 8.58 14z"></path></svg>
                </div>

                <!-- USER SESSION -->
                <?php if (isset($_SESSION['user_id'])): ?>
                  <div class="profil-menu">
                    <span class="username"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <div class="dropdown-content">
                      <?php
                        $profile_pic = '../uploads/' . htmlspecialchars($_SESSION['user_id']) . '.jpg'; // Chemin ajusté
                        if (!file_exists($profile_pic)) { $profile_pic = '../assets/img/incognito.png'; } // Chemin ajusté
                      ?>
                      <img src="<?php echo $profile_pic; ?>?v=<?php echo time(); ?>" alt="Photo de profil" width="80" height="80" style="object-fit: cover;">
                      <a href="profil.php">Modifier le profil</a>
                      <!-- <a href="voir_certificat.php">Voir les certificats</a> -->
                      <a href="../action/logout.php">Déconnexion</a>
                    </div>
                  </div>
                <?php else: ?>
                  <a href="login_user.php" class="bg-white text-primary-local font-semibold px-5 py-2 rounded-md hover:bg-slate-200 hover:-translate-y-0.5 transition-all duration-200">Connexion</a>
                <?php endif; ?>

            </nav>
            <button class="md:hidden text-white"><i class="fas fa-bars text-2xl"></i></button>
        </div>
    </header>

    <main class="container mx-auto p-8">
        <h2 class="text-3xl font-extrabold text-center text-slate-800 mb-8">Mes Cours</h2>

        <?php if ($db_error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                <p><?php echo htmlspecialchars($db_error); ?></p>
            </div>
        <?php elseif (empty($mes_cours)): ?>
            <div class="bg-sky-100 border-l-4 border-sky-500 text-sky-700 p-4 text-center" role="alert">
                <p class="font-bold">Vous n'avez accès à aucun cours pour le moment.</p>
                <p class="mt-2">Vos cours apparaîtront ici une fois votre paiement validé par un administrateur.</p>
                <a href="../index.php#cours" class="mt-4 inline-block bg-sky-600 text-white font-semibold px-6 py-2 rounded-md hover:bg-sky-700">Explorer le catalogue</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($mes_cours as $cours): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col transform hover:-translate-y-2 transition-transform duration-300">

                        <!-- Bloc Image + Étiquette  -->
                        <div class="relative">
                            <img src="../<?php echo htmlspecialchars($cours['chemin_image'] ?? 'assets/img/cours.jpg'); ?>" alt="Image du cours" class="w-full h-40 object-cover">

                            <?php if (!empty($cours['niveau_nom'])): ?>
                                <!-- L'étiquette du niveau -->
                                <span class="absolute top-1 -left-1 text-xs font-semibold text-white px-2 py-1 rounded-md"
                                      style="background-color: <?php echo htmlspecialchars($cours['niveau_couleur'] ?? '#334155'); ?>; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    <?php echo htmlspecialchars($cours['niveau_nom']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="p-4 flex flex-col flex-grow">
                            <!-- TITRE & DESCRIPTION -->
                            <h3 class="text-lg font-bold text-slate-800 mb-2"><?php echo htmlspecialchars($cours['titre']); ?></h3>
                            <p class="text-slate-600 text-sm line-clamp-3 mb-3 flex-grow">
                                <?php echo htmlspecialchars($cours['description']); ?>
                            </p>

                            <!-- FORMATEUR & DUREE -->
                            <div class="pt-3 border-t border-slate-100 mb-4">
                                <div class="flex justify-between items-center">
                                    <?php if (!empty($cours['formateur_nom'])): ?>
                                    <div class="flex items-center gap-2">
                                        <?php $formateur_img = !empty($cours['chemin_image_formateur']) ? '../' . htmlspecialchars($cours['chemin_image_formateur']) : '../assets/img/incognito.png'; ?>
                                        <img src="<?php echo $formateur_img; ?>" alt="Photo de <?php echo htmlspecialchars($cours['formateur_nom']); ?>" class="w-8 h-8 rounded-full object-cover">
                                        <div>
                                            <p class="text-xs text-slate-400">Formateur</p>
                                            <p class="font-semibold text-sm text-slate-700"><?php echo htmlspecialchars($cours['formateur_nom']); ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($cours['duree'])): ?>
                                        <div class="flex items-center gap-2 text-xs text-slate-500">
                                            <i class="fa-regular fa-clock text-sky-500"></i>
                                            <span><?php echo htmlspecialchars($cours['duree']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- BOUTTON "Commencer" -->
                            <a href="apprendre_cours.php?course_id=<?php echo $cours['id']; ?>" class="w-full mt-auto text-center inline-block bg-primary-local text-white font-semibold px-4 py-2 rounded-md hover:bg-sky-700 transition-colors text-sm">
                                Commencer à apprendre
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <script src="../assets/js/script.js"></script>
</body>
</html>