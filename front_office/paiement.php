<?php
session_start();
require_once '../include/config.php';

// Récupérer & effacer messages d'erreur 
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);

// Vérifier connexion user
if (!isset($_SESSION['user_id'])) {
    header('Location: login_user.php');
    exit();
}

// Valider ID du cours reçu
if (!isset($_GET['course_id']) || !filter_var($_GET['course_id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "Cours non spécifié ou invalide.";
    header('Location: catalogue_cours.php');
    exit();
}
$cours_id = $_GET['course_id'];

$cours = null;
$user_email = ''; 
$user_fullname = '';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête SQL pour récupérer cours
    $stmt_cours = $conn->prepare("
        SELECT c.id, c.titre, c.prix, c.description, c.chemin_image, c.formateur_nom, c.chemin_image_formateur,niv.nom AS niveau_nom, c.duree
        FROM public.cours c
        LEFT JOIN public.niveaux niv ON c.niveau_id = niv.id
        WHERE c.id = :id
    ");
    $stmt_cours->execute([':id' => $cours_id]);
    $cours = $stmt_cours->fetch(PDO::FETCH_ASSOC);

    if (!$cours) { throw new Exception("Aucun cours trouvé avec cet ID."); }

    //  Récupérer email & fullname user connecté
    $user_id = $_SESSION['user_id'];
    $stmt_user = $conn->prepare("SELECT email, first_name, last_name FROM public.users WHERE id = :id");
    $stmt_user->execute([':id' => $user_id]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
    if ($user_data) {
        $user_email = $user_data['email'];
        $user_fullname = $user_data['first_name'] . ' ' . $user_data['last_name'];
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    header('Location: catalogue_cours.php');
    exit();
} finally {
    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Paiement - <?php echo htmlspecialchars($cours['titre']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />    
    <link rel="stylesheet" href="../assets/css/all.min.css"> 
    <link rel="stylesheet" href="../assets/css/index.css"> 
    <link rel="stylesheet" href="../assets/css/paiement.css">
    
    <script src="../assets/js/Tailwind.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .modal { display: none; }
        .modal.open { display: flex; }
        #receipt-column {
            opacity: 0;
            transform: translateX(20px);
            transition: opacity 0.5s ease-out, transform 0.5s ease-out;
        }
        #receipt-column.visible {
            opacity: 1;
            transform: translateX(0);
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-700 antialiased">

  <main class="py-16 sm:py-24">
    <div id="main-container" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <a href="catalogue_cours.php" class="text-sm font-semibold text-sky-600 hover:text-sky-800 mb-4 inline-block">
                <i class="fa-solid fa-arrow-left mr-2"></i>Retour au catalogue
            </a>
            <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight">Finaliser votre inscription</h1>
            <p class="mt-3 max-w-2xl mx-auto text-lg text-slate-500">
                Vous êtes sur le point d'accéder à la formation !
            </p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg" role="alert">
                <p class="font-bold">Erreur</p>
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php endif; ?>

        <div id="content-wrapper" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-10">
            <!-- PREMIERE COLONNE  -->
            <div id="recap-column" class="bg-white p-8 rounded-2xl shadow-xl text-center border border-slate-200 flex flex-col">
                <h2 class="text-2xl font-bold text-center text-secondary-local border-b pb-4 mb-6">Votre cours</h2>
                <div class="space-y-6 flex flex-col flex-grow">

                    <!-- Récupérer IMAGE_formation & TITRE_cours -->
                    <div>
                        <?php
                            $image_path = !empty($cours['chemin_image']) 
                                ? '../' . htmlspecialchars($cours['chemin_image']) 
                                : '../assets/img/cours.jpg';
                        ?>
                        <img src="<?php echo $image_path; ?>" alt="Image du cours" class="w-full h-40 object-contain rounded-lg mb-4">
                        <h3 class="font-bold text-2xl text-slate-800"><?php echo htmlspecialchars($cours['titre']); ?></h3>
                    </div>
                    <!-- Récupérer NIVEAU & DESCRIPTION -->
                    <div class="text-sm text-slate-600 space-y-4 flex-grow">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-graduation-cap text-center text-sky-500"></i>
                            <span>Niveau <strong class="text-slate-800"><?php echo htmlspecialchars($cours['niveau_nom'] ?? 'Non spécifié'); ?></strong></span>
                        </div>
                         <div class="flex items-center gap-3">
                            <i class="fa-regular fa-clock text-sky-500"></i>
                            <span>Durée <strong class="text-slate-800"><?php echo htmlspecialchars($cours['duree'] ?? 'Non spécifié'); ?></strong></span>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-info w-5 text-center text-sky-500 pt-1"></i>
                            <p class="line-clamp-3"><?php echo htmlspecialchars($cours['description']); ?></p>
                        </div>
                    </div>
                      
                            <div class="flex items-center gap-4">
                                <?php 
                                    $formateur_img = !empty($cours['chemin_image_formateur']) 
                                        ? '../' . htmlspecialchars($cours['chemin_image_formateur']) 
                                        : '../assets/img/incognito.png'; 
                                ?>
                                <img src="<?php echo $formateur_img; ?>" alt="Photo de <?php echo htmlspecialchars($cours['formateur_nom']); ?>" class="w-12 h-12 rounded-full object-cover">
                                <div>
                                    <p class="text-sm text-slate-500">Formateur</p>
                                    <p class="font-bold text-slate-800"><?php echo htmlspecialchars($cours['formateur_nom']); ?></p>
                                </div>
                            </div>
                
                    <!-- Récupérer PRIX_formation -->
                    <div class="pt-6 border-t border-slate-200">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-primary-local">Total à payer</span>
                            <span class="text-3xl font-bold text-slate-900"><?php echo htmlspecialchars(number_format($cours['prix'], 0, '', ' ')); ?> MGA</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DEUXIEME COLONNE -->
            <div id="payment-column" class="bg-white p-8 rounded-2xl shadow-xl border border-slate-200">
                <h2 class="text-2xl font-bold text-center text-secondary-local border-b pb-4 mb-6">Payer par Mobile Money</h2>
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center font-bold text-lg">1</div>
                        <!-- Récupérer PRIX_cours -->
                        <div>
                            <h3 class="font-bold text-slate-800">Effectuez le virement</h3>
                            <p class="text-sm text-slate-600 mt-1">Veuillez envoyer <strong class="text-slate-900"><?php echo htmlspecialchars(number_format($cours['prix'], 0, '', ' ')); ?> MGA</strong> au numéro :</p>
                            <div class="mt-2 text-center bg-slate-100 border border-slate-200 rounded-lg py-2 px-4 font-mono text-lg font-semibold text-slate-800">034 45 039 43</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center font-bold text-lg">2</div>
                        <div>
                            <h3 class="font-bold text-slate-800">Confirmez votre paiement</h3>
                            <p class="text-sm text-slate-600 mt-1">Après le virement, complétez les informations ci-dessous.</p>
                            <form id="payment-form" action="../action/processus_paiement.php" method="POST" class="mt-4 space-y-4">
                                <input type="hidden" name="course_id" value="<?php echo $cours['id']; ?>">
                                <!-- Récupérer EMAIL lors d'inscription -->
                                <div>
                                    <label for="user_email" class="block text-sm font-medium text-slate-700 mb-1">Votre email d'inscription</label>
                                    <input type="email" id="user_email" name="user_email" value="<?php echo htmlspecialchars($user_email); ?>" readonly class="w-full p-3 border border-slate-300 rounded-lg bg-slate-100 text-slate-500 cursor-not-allowed outline-none">
                                </div>
                                <div>
                                    <label for="transaction_id" class="block text-sm font-medium text-slate-700 mb-1">Référence de transaction</label>
                                    <input type="text" id="transaction_id" name="transaction_id" placeholder="" required class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-300 focus:border-sky-500 outline-none transition">
                                </div>
                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-slate-700 mb-1">Votre numéro de téléphone</label>
                                    <input type="tel" id="phone_number" name="phone_number" placeholder="03x xx xxx xx" required pattern="03[23478][0-9]{7}" maxlength="10" title="Veuillez entrer un numéro de téléphone valide (10 chiffres)." class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-300 focus:border-sky-500 outline-none transition">
                                </div>
                                <div class="pt-2">
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-primary-local text-white font-bold text-lg px-6 py-3 rounded-lg shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                        <i class="fa-solid fa-check-circle text-sm"></i>
                                        <span>Confirmer et s'inscrire</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TROISIEME COLONNE -->
            <div id="receipt-column" class="hidden bg-white p-8 rounded-2xl shadow-xl border border-slate-200 bordure-conteneur-animee">
                 <div class="flex justify-between items-start mb-6">
                    <img src="../assets/img/inf.png" alt="Logo INFOMADA" class="h-10 w-auto">
                    <div class="text-right">
                        <h2 class="text-2xl font-bold text-primary-local">Reçu de Paiement</h2>
                        <p class="text-sm text-slate-500">Date : <span id="receipt-date"></span></p>
                    </div>
                </div>
                <div class="border-t border-slate-200 pt-6 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Facturé à :</span>
                        <span id="receipt-user" class="font-semibold text-slate-800"><?php echo htmlspecialchars($user_fullname); ?></span>
                    </div>
                    <div class="flex justify-between"><span class="text-slate-500">Cours :</span>
                        <span id="receipt-course" class="font-semibold text-slate-800"><?php echo htmlspecialchars($cours['titre']); ?></span>
                    </div>
                    <div class="flex justify-between"><span class="text-slate-500">Niveau :</span>
                        <span id="receipt-level" class="font-semibold text-slate-800"><?php echo htmlspecialchars($cours['niveau_nom']); ?></span>
                    </div>
                    <div class="flex justify-between"><span class="text-slate-500">Durée :</span>
                        <span id="receipt-duration" class="font-semibold text-slate-800"><?php echo htmlspecialchars($cours['duree']); ?></span>
                    </div>
                    <div class="flex justify-between"><span class="text-slate-500">N° de téléphone :</span>
                        <span id="receipt-phone" class="font-semibold text-slate-800"></span>
                    </div>
                    <div class="flex justify-between items-center"><span class="text-slate-500">Référence :</span>
                        <span id="receipt-ref" class="font-mono text-xs font-semibold text-slate-800 break-all"></span>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-200 flex justify-between items-center">
                    <span class="text-base font-medium text-primary-local">Montant Payé</span>
                    <span id="receipt-price" class="text-2xl font-bold text-slate-900"><?php echo htmlspecialchars(number_format($cours['prix'], 0, '', ' ')); ?> MGA</span>
                </div>
                <!-- TÉLÉCHARGEMENT -->
                <div class="mt-8">
                    <a href="#" id="download-receipt-btn" class="w-full inline-flex items-center justify-center gap-2 text-white font-bold px-6 py-3 rounded-lg shadow-inner cursor-not-allowed pointer-events-none transition-all duration-300">
                        <i class="fa-solid fa-download text-sm"></i>
                        <span>Télécharger le reçu</span>
                    </a>

                    <a href="mes_cours.php" id="my-courses-btn" class="w-full mt-7 inline-flex items-center justify-center gap-2 bg-sky-600 text-white font-bold px-6 py-3 rounded-lg shadow-lg hover:bg-sky-700 hover:-translate-y-1 transition-all duration-300">
                        <i class="fa-solid fa-graduation-cap text-sm"></i>
                        <span>Accéder à mes Cours</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
  </main>

  <div id="success-modal" class="modal fixed inset-0 bg-black bg-opacity-60 z-50 items-center justify-center p-4">
      <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-sm text-center">
          <i class="fa-solid fa-check-circle fa-4x text-cyan-300 mb-4"></i>
          <h3 class="text-xl font-bold text-slate-800 mb-2">Paiement soumis avec succès !</h3>
          <p class="text-slate-600 mb-6">Votre demande est en cours de validation. Cliquez sur OK pour voir votre reçu.</p>
          <button id="ok-button" class="w-full bg-sky-300 hover:bg-sky-400 text-white font-semibold py-2 px-4 rounded-md">OK</button>
      </div>
  </div>

  <script src="../assets/js/paiement.js"></script>

</body>
</html>