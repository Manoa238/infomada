<?php
session_start();
require_once '../include/config.php';

// User != connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login_user.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// ID_cours existe & validé
if (!isset($_GET['course_id']) || !filter_var($_GET['course_id'], FILTER_VALIDATE_INT)) {
    header('Location: mes_cours.php'); // Rediriger invalide
    exit();
}

$cours_id = (int) $_GET['course_id'];
$db_error = '';
$cours_titre = '';
$chapitres_organises = [];
$examen_questions = []; // Stocker questions_examen

// --- NOUVEAU CODE POUR LA GESTION DU TIMER ---
$exam_duration_seconds = 30 * 60; // Durée de l'examen en secondes (30 minutes)
$exam_start_time_session_key = 'exam_start_time_' . $user_id . '_' . $cours_id;
$exam_end_time_session_key = 'exam_end_time_' . $user_id . '_' . $cours_id;

$current_time = time(); // Temps actuel en secondes (timestamp Unix)
$time_left_seconds = $exam_duration_seconds; // Initialisation par défaut
// --- FIN NOUVEAU CODE POUR LA GESTION DU TIMER ---


try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // User accès à cours (paiement approuvé)
    $stmt_access = $conn->prepare("
        SELECT COUNT(*)
        FROM public.paiements
        WHERE id_utilisateur = :user_id
        AND id_cours = :cours_id
        AND statut = 'approuvé'
    ");
    $stmt_access->execute([':user_id' => $user_id, ':cours_id' => $cours_id]);
    if ($stmt_access->fetchColumn() == 0) {
        // User pas d'accès
        $_SESSION['error_message'] = "Vous n'avez pas accès à ce cours ou votre paiement n'est pas encore approuvé.";
        header('Location: mes_cours.php');
        exit();
    }

    // Récupérer titre_cours
    $stmt_cours = $conn->prepare("SELECT titre FROM public.cours WHERE id = :cours_id");
    $stmt_cours->execute([':cours_id' => $cours_id]);
    $cours_data = $stmt_cours->fetch(PDO::FETCH_ASSOC);
    if ($cours_data) {
        $cours_titre = $cours_data['titre'];
    } else {
        // Cours non trouvé
        $_SESSION['error_message'] = "Cours non trouvé.";
        header('Location: mes_cours.php');
        exit();
    }

    // Récupérer chapitres & leçons_cours
    $stmt_contenu = $conn->prepare("
        SELECT
            ch.id AS chapitre_id,
            ch.titre_chapitre,
            ch.ordre AS chapitre_ordre,
            l.id AS lecon_id,
            l.titre_lecon,
            l.type_contenu,
            l.contenu_texte,
            l.chemin_fichier,
            l.ordre AS lecon_ordre
        FROM chapitres ch
        LEFT JOIN lecons l ON ch.id = l.chapitre_id
        WHERE ch.cours_id = :cours_id
        ORDER BY ch.ordre ASC, l.ordre ASC
    ");
    $stmt_contenu->execute([':cours_id' => $cours_id]);
    $contenus = $stmt_contenu->fetchAll(PDO::FETCH_ASSOC);

    // Données par chapitre
    foreach ($contenus as $item) {
        $chapitre_id = $item['chapitre_id'];
        if (!isset($chapitres_organises[$chapitre_id])) {
            $chapitres_organises[$chapitre_id] = [
                'id' => $item['chapitre_id'],
                'titre_chapitre' => $item['titre_chapitre'],
                'ordre' => $item['chapitre_ordre'],
                'lecons' => []
            ];
        }
        if ($item['lecon_id']) { // Ajouter leçon si existe
            $chapitres_organises[$chapitre_id]['lecons'][] = [
                'id' => $item['lecon_id'],
                'titre_lecon' => $item['titre_lecon'],
                'type_contenu' => $item['type_contenu'],
                'contenu_texte' => $item['contenu_texte'],
                'chemin_fichier' => $item['chemin_fichier'],
                'ordre' => $item['lecon_ordre']
            ];
        }
    }

    // Lecture_examen s'il est publié par cours
    $examen_cours_json_file = '../uploads/examens_json/examen_cours_' . $cours_id . '.json';
    if (file_exists($examen_cours_json_file)) {
        $json_content = file_get_contents($examen_cours_json_file);
        $examen_questions = json_decode($json_content, true);

        if (json_last_error() === JSON_ERROR_NONE && !empty($examen_questions)) {
            // L'examen est présent, nous pouvons gérer le timer
            if (!isset($_SESSION[$exam_start_time_session_key])) {
                // Si le temps de début n'est pas en session, c'est un nouveau démarrage (ou le premier chargement)
                $_SESSION[$exam_start_time_session_key] = $current_time;
                $_SESSION[$exam_end_time_session_key] = $current_time + $exam_duration_seconds;
            }

            // Calculer le temps restant
            $time_left_seconds = $_SESSION[$exam_end_time_session_key] - $current_time;

            // Si le temps est négatif, il est écoulé, on le met à zéro
            if ($time_left_seconds < 0) {
                $time_left_seconds = 0;
            }
        } else {
            $examen_questions = []; // JSON malformé ou vide
        }
    }

} catch (PDOException $e) {
    $db_error = "Erreur de connexion à la base de données ou de récupération du contenu du cours : " . $e->getMessage();
} finally {
    $conn = null;
}
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apprendre : <?= htmlspecialchars($cours_titre) ?> - INFOMADA</title>
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

        /* Styles spécifiques à la page apprendre_cours.php */
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            max-width: 100%;
            background: #000;
            margin-top: 1rem;
            margin-bottom: 1rem;
        }
        .video-container iframe,
        .video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
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
                        $profile_pic = '../uploads/' . htmlspecialchars($_SESSION['user_id']) . '.jpg'; 
                        if (!file_exists($profile_pic)) { $profile_pic = '../assets/img/incognito.png'; } 
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
    <div class="flex items-center justify-between mb-8">
        <a href="mes_cours.php" class="text-primary-local hover:text-sky-700 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Retour à Mes Cours
        </a>
        <h2 class="text-3xl font-extrabold text-center text-slate-800 flex-grow">
            Apprendre : <?= htmlspecialchars($cours_titre) ?>
        </h2>
         <div></div>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($db_error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p><?php echo htmlspecialchars($db_error); ?></p>
        </div>
    <?php elseif (empty($chapitres_organises) && empty($examen_questions)): ?>
        <div class="bg-sky-100 border-l-4 border-sky-500 text-sky-700 p-4 text-center" role="alert">
            <p class="font-bold">Ce cours ne contient pas encore de chapitres, de leçons ou d'examen publié.</p>
            <p class="mt-2">Veuillez revenir plus tard, le formateur n'a pas encore ajouté de contenu.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-xl p-6 border border-slate-200 mx-auto max-w-3xl">
            <!-- Section pour l'examen -->
            <?php if (!empty($examen_questions)): ?>
                <div id="exam-container" class="mb-8 p-6 bg-blue-50 rounded-lg shadow-md border border-blue-200">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-blue-300">
                        <h3 class="text-2xl font-bold text-primary-local">
                            Examen du Cours
                        </h3>
                        <div class="text-lg font-bold text-slate-700">
                            Temps restant : <span id="exam-timer" class="text-2xl text-red-600 ml-2"></span>
                        </div>
                    </div>
                    <form action="soumettre_cours.php" method="POST" id="exam-form">
                        <input type="hidden" name="cours_id" value="<?= htmlspecialchars($cours_id) ?>">
                        <div class="space-y-6">
                            <?php foreach ($examen_questions as $index => $q): ?>
                                <div class="bg-slate-50 p-6 rounded-lg shadow-sm border border-gray-200">
                                    <p class="text-lg font-semibold text-gray-800 mb-2">
                                        Question <?= $index + 1 ?> (<?= htmlspecialchars($q['points'] ?? 0) ?> points)
                                        <?php if (isset($q['multiple']) && $q['multiple']): ?>
                                            <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">Choix Multiples</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-gray-700 mb-4"><?= htmlspecialchars($q['question']) ?></p>

                                    <div class="space-y-2">
                                        <?php foreach ($q['options'] as $opt_index => $option): ?>
                                            <div class="flex items-center text-gray-700">
                                                <?php if (isset($q['multiple']) && $q['multiple']): ?>
                                                    <input type="checkbox"
                                                           name="reponses[<?= $index ?>][]"
                                                           value="<?= htmlspecialchars($option) ?>"
                                                           id="q<?= $index ?>_opt<?= $opt_index ?>"
                                                           class="mr-2 h-4 w-4 text-primary-local border-gray-300 rounded focus:ring-primary-local">
                                                <?php else: ?>
                                                    <input type="radio"
                                                           name="reponses[<?= $index ?>]"
                                                           value="<?= htmlspecialchars($option) ?>"
                                                           id="q<?= $index ?>_opt<?= $opt_index ?>"
                                                           class="mr-2 h-4 w-4 text-primary-local border-gray-300 focus:ring-primary-local">
                                                <?php endif; ?>
                                                <label for="q<?= $index ?>_opt<?= $opt_index ?>" class="ml-2 text-base"><?= htmlspecialchars($option) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-8 text-center">
                            <button type="submit" id="submit-exam-btn" class="bg-primary-local hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition duration-300 ease-in-out text-xl">
                                <i class="fas fa-paper-plane mr-3"></i> Soumettre l'Examen
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Contenu existant chapitres et leçons -->
            <?php if (!empty($chapitres_organises)): ?>
                <h3 class="text-2xl font-bold text-primary-local mb-4 border-b pb-2 <?= !empty($examen_questions) ? 'mt-8' : '' ?>">
                    Contenu du Cours
                </h3>
                <div class="space-y-6">
                    <?php foreach ($chapitres_organises as $chapitre): ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-blue-200">
                            <button class="w-full text-left bg-primary-local text-white p-4 flex items-center justify-between focus:outline-none toggle-chapitre-btn" data-chapitre-id="<?= $chapitre['id'] ?>">
                                <h3 class="text-xl font-semibold">
                                    Chapitre <?= htmlspecialchars($chapitre['ordre']) ?> : <?= htmlspecialchars($chapitre['titre_chapitre']) ?>
                                </h3>
                                <i class="fas fa-chevron-down transform transition-transform duration-300"></i>
                            </button>

                            <div id="chapitre-<?= $chapitre['id'] ?>-content" class="p-6 space-y-6 hidden">
                                <?php if (empty($chapitre['lecons'])): ?>
                                    <p class="text-slate-600 italic">Aucune leçon pour ce chapitre.</p>
                                <?php else: ?>
                                    <?php foreach ($chapitre['lecons'] as $lecon): ?>
                                        <div class="bg-slate-50 rounded-md p-4 shadow-sm border border-slate-200 flex items-start space-x-4">
                                            <?php if ($lecon['type_contenu'] === 'pdf'): ?>
                                                <i class="fa-solid fa-book text-2xl text-blue-600 mt-1"></i>
                                            <?php elseif ($lecon['type_contenu'] === 'video'): ?>
                                                <i class="fa-solid fa-play-circle text-2xl text-green-600 mt-1"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-file-alt text-2xl text-gray-500 mt-1"></i>
                                            <?php endif; ?>

                                            <div class="flex-grow">
                                                <h4 class="text-lg font-bold text-slate-800 mb-1">
                                                    Leçon <?= htmlspecialchars($lecon['ordre']) ?> : <?= htmlspecialchars($lecon['titre_lecon']) ?>
                                                </h4>

                                                <?php if (!empty($lecon['contenu_texte'])): ?>
                                                    <div class="prose max-w-none text-slate-700 text-sm mb-2">
                                                        <?= nl2br(htmlspecialchars(mb_substr($lecon['contenu_texte'], 0, 100))) . (mb_strlen($lecon['contenu_texte']) > 100 ? '...' : '') ?>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (($lecon['type_contenu'] === 'pdf' || $lecon['type_contenu'] === 'video') && !empty($lecon['chemin_fichier'])): ?>
                                                    <?php $file_path = '../' . htmlspecialchars($lecon['chemin_fichier']); ?>
                                                    <?php $filename = basename($lecon['chemin_fichier']); ?>

                                                    <?php if ($lecon['type_contenu'] === 'pdf'): ?>
                                                        <a href="<?= $file_path ?>" target="_blank" class="text-primary-local hover:underline font-medium text-sm">
                                                            Télécharger le PDF : <?= htmlspecialchars($filename) ?>
                                                        </a>
                                                    <?php elseif ($lecon['type_contenu'] === 'video'): ?>
                                                        <div class="video-container">
                                                            <video controls preload="metadata">
                                                                <source src="<?= $file_path ?>" type="video/mp4">
                                                                Votre navigateur ne prend pas en charge la balise vidéo.
                                                            </video>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-chapitre-btn');

        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const chapitreId = this.dataset.chapitreId;
                const contentDiv = document.getElementById(`chapitre-${chapitreId}-content`);
                const icon = this.querySelector('i');

                contentDiv.classList.toggle('hidden'); // Masquer & afficher contenu
                icon.classList.toggle('fa-chevron-down'); // Changer icône
                icon.classList.toggle('fa-chevron-up');
                icon.classList.toggle('rotate-180'); // Rotation animation
            });
        });

        // Timer d'examen
        const examForm = document.getElementById('exam-form'); // Utilise l'ID ajouté
        const submitButton = document.getElementById('submit-exam-btn'); // Utilise l'ID ajouté
        const timerDisplay = document.getElementById('exam-timer');
        const examContainer = document.getElementById('exam-container'); // Nécessaire pour vérifier la présence du bloc d'examen

        // Récupérer le temps restant calculé par PHP
        let timeLeft = <?= $time_left_seconds ?>; // Le temps restant initial vient de PHP
        let timerInterval;

        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
        }

        // Démarrer timer seulement si le bloc d'examen et ses éléments sont présents
        if (examContainer && timerDisplay && submitButton && examForm) {
            // Si le temps est déjà écoulé à l'arrivée sur la page
            if (timeLeft <= 0) {
                timerDisplay.textContent = "00:00";
                submitButton.disabled = true;
                // Le formulaire est soumis automatiquement par PHP si le temps est dépassé,
                // mais une alerte pour l'utilisateur peut être utile.
                alert("Le temps est écoulé ! Vous ne pouvez plus soumettre l'examen.");
                // Pas besoin de soumettre le formulaire ici, PHP peut le gérer ou l'utilisateur ne peut juste plus soumettre.
                return; // Arrêter l'exécution du timer si déjà fini
            }

            timerDisplay.textContent = formatTime(timeLeft); // Afficher temps initial

            timerInterval = setInterval(() => {
                timeLeft--;
                timerDisplay.textContent = formatTime(timeLeft);

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    timerDisplay.textContent = "00:00";
                    alert("Le temps est écoulé ! Votre examen va être soumis automatiquement.");
                    if (submitButton) {
                        submitButton.disabled = true; // Empêcher clic manuel
                    }
                    if (examForm) {
                        examForm.submit(); // Soumettre formulaire
                    }
                } else if (timeLeft <= 60 && !timerDisplay.classList.contains('text-red-500')) {
                    timerDisplay.classList.add('text-red-500', 'font-bold');
                }
            }, 1000); // MàJ toutes secondes

            // Optionnel: Empêcher l'utilisateur de quitter la page pendant l'examen (peut être intrusif)
            // window.addEventListener('beforeunload', function (e) {
            //     if (timeLeft > 0 && timerInterval) {
            //         e.preventDefault();
            //         e.returnValue = '';
            //     }
            // });
        }
    });
</script>
<script src="../assets/js/script.js"></script>
</body>
</html>