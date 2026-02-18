<?php
session_start();
require_once '../include/config.php';

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login_user.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$certificate_data = null;
$error_message = '';

// Get certificate ID from URL
if (isset($_GET['certificate_id']) && filter_var($_GET['certificate_id'], FILTER_VALIDATE_INT)) {
    $certificate_id = (int) $_GET['certificate_id'];

    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.id,
                c.titre_cours,
                c.niveau_cours,
                c.nom_utilisateur,
                c.date_naissance_utilisateur,
                c.mention,
                c.date_delivrance,
                u.first_name,
                u.last_name
            FROM 
                certificats c
            JOIN 
                users u ON c.user_id = u.id
            WHERE 
                c.id = ? AND c.user_id = ?
        ");
        $stmt->execute([$certificate_id, $user_id]);
        $certificate_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$certificate_data) {
            $error_message = "Certificat non trouvé ou vous n'avez pas l'autorisation de le voir.";
        } else {
            // Format dates for display
            if (!empty($certificate_data['date_naissance_utilisateur'])) {
                $dob_obj = new DateTime($certificate_data['date_naissance_utilisateur']);
                $certificate_data['date_naissance_utilisateur_display'] = $dob_obj->format('d/m/Y');
            } else {
                $certificate_data['date_naissance_utilisateur_display'] = 'N/A';
            }
            $delivery_date_obj = new DateTime($certificate_data['date_delivrance']);
            $certificate_data['date_delivrance_display'] = $delivery_date_obj->format('d/m/Y');
        }

    } catch (PDOException $e) {
        error_log("Database Error (Certificate Display): " . $e->getMessage());
        $error_message = "Une erreur est survenue lors du chargement du certificat.";
    }
} else {
    $error_message = "ID de certificat invalide.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voir votre Certificat - INFOMADA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&family=Georgia&family=Times+New+Roman&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/index.css">
    
    <style>
        .text-primary-local { color: #2563eb; }
        .bg-primary-local { background-color: #2563eb; }

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
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="%23e0f2fe"><circle cx="40" cy="40" r="30" fill="none" stroke="%23bbdefb" stroke-width="1"/></svg>') repeat; 
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

<main class="container mx-auto p-8">
    <div class="flex items-center justify-between mb-8">
        <a href="mes_cours.php" class="text-primary-local hover:text-sky-700 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Retour à Mes Cours
        </a>
        <h2 class="text-3xl font-extrabold text-center text-slate-800 flex-grow">
            Votre Certificat
        </h2>
        <div></div>
    </div>

    <?php if ($error_message): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php elseif ($certificate_data): ?>
        <!-- Conteneur CERTIFICAT -->
        <div class="w-full max-w-xl mx-auto bg-white rounded-lg shadow-xl p-8 relative overflow-hidden
                    certificate-bg-gradient certificate-border-blue-dark certificate-pattern">
            <div class="relative z-10 text-center text-gray-800 font-times">
                <!-- LOGO -->
                <img src="../assets/img/inf.png" alt="Logo INFOMADA" class="absolute bottom-3 left-15 h-24 md:h-32 w-auto object-contain shadow-sm">
                <h1 class="font-georgia text-3xl md:text-3xl text-[#004085] mb-3 md:mb-4 drop-shadow">CERTIFICAT DE FORMATION</h1>
                <p class="text-base md:text-lg text-[#0056b3] mb-3 md:mb-4">Ce document certifie que</p>
                <p class="font-dancing text-3xl md:text-2xl text-[#007bff] my-3 md:my-4 border-b-2 border-gray-300 inline-block px-3 pb-1">
                    <?= htmlspecialchars($certificate_data['nom_utilisateur']) ?>
                </p>
                <?php if (!empty($certificate_data['date_naissance_utilisateur_display'])): ?>
                    <p class="text-sm md:text-base leading-relaxed mb-1">Né(e) le : <?= htmlspecialchars($certificate_data['date_naissance_utilisateur_display']) ?></p>
                <?php endif; ?>
                <p class="text-sm md:text-base leading-relaxed mb-1">a réussi avec succès l'examen final du cours</p>
                <p class="text-base md:text-lg font-semibold text-[#0056b3] mb-2">
                    <?= htmlspecialchars($certificate_data['titre_cours']) ?> (Niveau : <?= htmlspecialchars($certificate_data['niveau_cours']) ?>)
                </p>
                <p class="text-sm md:text-base leading-relaxed mb-1">avec une mention :<span class="text-lg md:text-xl font-bold text-red-600 mb-5"><?= htmlspecialchars($certificate_data['mention']) ?></span></p>
                <p class="text-xs md:text-sm mt-6 text-gray-600 italic">Délivré le : <?= htmlspecialchars($certificate_data['date_delivrance_display']) ?></p>

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
        <div class="text-center mt-8">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-300 ease-in-out text-lg">
                <i class="fas fa-print mr-2"></i> Imprimer le Certificat
            </button>
        </div>
    <?php endif; ?>
</main>

</body>
</html>