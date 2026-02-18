<?php
session_start();
require_once '../include/config.php'; 

// Vérifie résultats_examen 
if (!isset($_SESSION['examen_resultat'])) {
    header('Location: mes_cours.php'); 
    exit();
}

$resultat = $_SESSION['examen_resultat'];
$score = $resultat['score'];
$total_points_examen = $resultat['total_points_examen'];
$note_sur_20 = $resultat['note_sur_20'];
$grade = $resultat['grade'];
$message = $resultat['message'];
$cours_id = $resultat['cours_id'];
$user_id = $_SESSION['user_id']; 

$user_full_name = '';
$user_dob_display = ''; // Affichage (d/m/Y)
$user_dob_db_format = null; // BD (Y-m-d)
$course_title = '';
$course_level = '';

// Récupérer informations user&cours
try {
    // Récupérer informat° user (last_name, first_name, datenaiss)
    $stmt_user = $pdo->prepare("SELECT first_name, last_name, datenais FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user_info) {
        $user_full_name = htmlspecialchars($user_info['first_name']) . ' ' . htmlspecialchars($user_info['last_name']);
        if (!empty($user_info['datenais'])) {
            $dob_obj = new DateTime($user_info['datenais']);
            $user_dob_display = htmlspecialchars($dob_obj->format('d/m/Y')); // Pour affichage
            $user_dob_db_format = $dob_obj->format('Y-m-d'); // Pour DB
        }
    }

    // Récupérer informat° cours&nom_niveau
    $stmt_course = $pdo->prepare("
        SELECT 
            c.titre, 
            n.nom AS nom_niveau_cours
        FROM 
            cours c
        JOIN 
            niveaux n ON c.niveau_id = n.id
        WHERE 
            c.id = ?
    ");
    $stmt_course->execute([$cours_id]);
    $course_info = $stmt_course->fetch(PDO::FETCH_ASSOC);

    if ($course_info) {
        $course_title = htmlspecialchars($course_info['titre']);
        $course_level = htmlspecialchars($course_info['nom_niveau_cours']);
    }

} catch (PDOException $e) {
    error_log("Database Error (User/Course Info): " . $e->getMessage());
    // Gérer erreur_user
}

$current_date_display = (new DateTime())->format('d/m/Y'); // Date d'affichage
$current_date_db_format = (new DateTime())->format('Y-m-d'); // Date pour BD

// SAVE CERTIFICAT DANS BD 
if ($note_sur_20 >= 10) {
    try {
        // Certificat existé pour user & cours
        $stmt_check_cert = $pdo->prepare("SELECT id FROM certificats WHERE user_id = ? AND cours_id = ?");
        $stmt_check_cert->execute([$user_id, $cours_id]);
        $existing_certificate = $stmt_check_cert->fetch(PDO::FETCH_ASSOC);

        if (!$existing_certificate) { //  certificat != existe, insérer
            $stmt_insert_cert = $pdo->prepare("
                INSERT INTO certificats (user_id, cours_id, titre_cours, niveau_cours, nom_utilisateur, date_naissance_utilisateur, mention, date_delivrance)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt_insert_cert->execute([
                $user_id,
                $cours_id,
                $course_title,
                $course_level,
                $user_full_name,
                $user_dob_db_format, // Format Y-m-d
                $grade,
                $current_date_db_format // Format Y-m-d
            ]);
;

        } else {
            // Certificat existe
            // error_log("Certificat déjà existant pour l'utilisateur ID: $user_id, Cours ID: $cours_id");
        }

    } catch (PDOException $e) {
        error_log("Database Error (Certificat Insertion): " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat de l'Examen - INFOMADA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&family=Georgia&family=Times+New+Roman&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../assets/js/Tailwind.js"></script>
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/contact.css">
    
    <style>
        .certificate-bg-gradient {
            background: linear-gradient(to bottom, #f0f9ff, #c0e8ff); 
        }
        .certificate-border-blue-dark {
            border: 8px solid #36B3CF; 
        }
        .certificate-pattern::before {
            content: '';
            position: absolute;
            top: -40px; left: -40px; right: -40px; bottom: -40px; 
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="%23e0f2fe"><circle cx="40" cy="40" r="30" fill="none" stroke="%23bbdefb" stroke-width="1"/></svg>') repeat; /* Motif SVG légèrement plus petit */
            opacity: 0.3;
            z-index: 0;
            transform: rotate(15deg); 
        }
        .font-georgia { font-family: 'Georgia', serif; }
        .font-dancing { font-family: 'Dancing Script', cursive; }
        .font-times { font-family: 'Times New Roman', serif; }
        .signature-image {
    height: 70px;
    width: auto;
    display: block; 
    margin: 0 auto 5px auto; 
    position: relative; 
    z-index: 20; 
}
    </style>
</head>
<body class="bg-slate-100 font-sans antialiased">

<header class="bg-primary-local text-white shadow-md p-4">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-xl font-bold">
            <a href="../index.php">INFOMADA</a>
        </h1>
        <nav>
            <a href="../index.php" class="hover:text-sky-300">Accueil</a>
            <a href="mes_cours.php" class="ml-4 hover:text-sky-300">Mes Cours</a>
            <a href="../action/logout.php" class="ml-4 bg-white text-primary-local font-semibold px-4 py-2 rounded-md hover:bg-slate-200">Déconnexion</a>
        </nav>
    </div>
</header>

<main class="container mx-auto p-8 flex flex-col lg:flex-row lg:justify-center lg:items-start lg:space-x-8">
    
    <!-- RESULTATS EXAMEN -->
    <div class="w-full lg:w-1/2 max-w-xl h-130 bg-white rounded-lg shadow-xl p-8 border-2 bordure-conteneur-animee <?= ($note_sur_20 >= 10) ? 'border-blue-800' : 'border-red-500' ?> mb-8 lg:mb-0">
        <h2 class="text-3xl font-extrabold text-center text-slate-800 mb-6">
            Résultat de votre Examen
        </h2>

        <div class="text-center mb-8">
            <?php if ($note_sur_20 >= 10): ?>
                <i class="fas fa-check-circle text-red-600 text-6xl mb-4"></i>
            <?php else: ?>
                <i class="fas fa-times-circle text-red-500 text-6xl mb-4"></i>
            <?php endif; ?>
            <p class="text-lg text-slate-700 font-semibold mb-2">Votre score : <span class="text-primary-local text-xl"><?= htmlspecialchars($score) ?></span> / <span class="text-slate-600 text-xl"><?= htmlspecialchars($total_points_examen) ?></span> points</p>
            <p class="text-xl font-bold text-slate-800 mb-4">Mention : <span class="<?= ($note_sur_20 >= 10) ? 'text-red-600' : 'text-red-600' ?>"><?= htmlspecialchars($grade) ?></span></p>
        </div>

        <div class="bg-slate-50 p-6 rounded-md border border-slate-200 mb-8">
            <p class="text-slate-700 text-lg leading-relaxed"><?= htmlspecialchars($message) ?></p>
        </div>

        <div class="text-center">
            <a href="apprendre_cours.php?cours_id=<?= htmlspecialchars($cours_id) ?>" class="bg-primary-darker-local items-center hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-300 ease-in-out text-lg">
                <i class="fas fa-book-open mr-2 mt-5"></i> Retour au Cours
            </a>
        </div>
    </div>

    <?php if ($note_sur_20 >= 10): // Afficher certificat (note>10)?>
        <!-- Conteneur CERTIFICAT -->
        <div class="w-full lg:w-1/2 max-w-xl h-130 bg-white rounded-lg shadow-xl p-8 relative overflow-hidden
                    certificate-bg-gradient certificate-border-blue-dark certificate-pattern">
            <div class="relative z-10 text-center text-gray-800 font-times">
                <!-- LOGO -->
                <img src="../assets/img/inf.png" alt="Logo INFOMADA" class="absolute bottom-3 left-15 h-24 md:h-32 w-auto object-contain shadow-sm">
                <h1 class="font-georgia text-3xl md:text-3xl text-[#004085] mb-3 md:mb-4 drop-shadow">CERTIFICAT DE FORMATION</h1>
                <p class="text-base md:text-lg text-[#0056b3] mb-3 md:mb-4">Ce document certifie que</p>
                <p class="font-dancing text-3xl md:text-2xl text-[#007bff] my-3 md:my-4 border-b-2 border-gray-300 inline-block px-3 pb-1">
                    <?= $user_full_name ?>
                </p>
                <?php if (!empty($user_dob_display)): // Affichage ?>
                    <p class="text-sm md:text-base leading-relaxed mb-1">Né(e) le : <?= $user_dob_display ?></p>
                <?php endif; ?>
                <p class="text-sm md:text-base leading-relaxed mb-1">a réussi avec succès l'examen final du cours</p>
                <p class="text-base md:text-lg font-semibold text-[#0056b3] mb-2">
                    <?= $course_title ?> (Niveau : <?= $course_level ?>)
                </p>
                <p class="text-sm md:text-base leading-relaxed mb-1">avec une mention :<span class="text-lg md:text-xl font-bold text-red-600 mb-5"><?= $grade ?></span></p>
                <p class="text-xs md:text-sm mt-6 text-gray-600 italic">Délivré le : <?= $current_date_display ?></p>

                <div class="mt-8 flex flex-col md:flex-row justify-around items-end text-xs md:text-sm text-gray-700">
                    <div class="flex-1 md:flex-none ml-55 w-full md:w-auto">
                    <div class="-pt-4 mx-auto max-w-[180px]">
                            <p>Le Directeur de l'INFOMADA</p>
                        </div>
                        <img src="../assets/img/signature_Manoa.png" alt="Signature du Directeur" class="signature-image">
                        
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

</body>
</html>