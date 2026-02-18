<?php

include '../include/config.php';

$nombre_utilisateurs = 0;
$nombre_total_certificats_emis = 0;
$nombre_total_formateur = 0;
$nombre_cours = 0;
$nombre_paiement = 0;

// User
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_users FROM users");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombre_utilisateurs = $row['total_users'];
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du nombre total d'utilisateurs : " . $e->getMessage());
    $nombre_utilisateurs = "Erreur";
}

// Certificats
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_certificats FROM certificats");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombre_total_certificats_emis = $row['total_certificats'];
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du nombre total de certificats émis : " . $e->getMessage());
    $nombre_total_certificats_emis = "Erreur";
}

// Formateur
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_formateur FROM formateurs");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombre_total_formateur = $row['total_formateur'];
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du nombre total de certificats émis : " . $e->getMessage());
    $nombre_total_formateur = "Erreur";
}

// Formation
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_cours FROM cours");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombre_cours = $row['total_cours'];
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du nombre total de certificats émis : " . $e->getMessage());
    $nombre_cours = "Erreur";
}

// Paiement
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_paiement FROM paiements");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombre_paiement = $row['total_paiement'];
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du nombre total de certificats émis : " . $e->getMessage());
    $nombre_paiement = "Erreur";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Administration</title>
    <!-- Icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="./assets/js/Tailwind.js"></script>
    <link rel="stylesheet" href="./assets/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/index.css">
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
                        <a href="admin_page_dashboard.php" class="nav-link flex items-center font-bold py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors">
                            <i class="fa-solid fa-table-columns w-5 text-center mr-4"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="admin_page_formation.php" class="nav-link flex items-center py-3 px-4 rounded-lg text-secondary hover:bg-accent-light hover:text-primary transition-colors">
                            <i class="fa-solid fa-layer-group w-5 text-center mr-4"></i> Formation
                        </a>
                    </li>
                    <li>
                        <a href="admin_page_paiement.php" class="nav-link flex items-center py-3 px-4 rounded-lg bg-accent-light text-primary transition-colors">
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
            <h1 class="text-4xl font-bold text-primary-local mb-9">Tableau de Bord </h1>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5 max-w-full mx-auto">
                    <!-- UTILISATEURS -->
                    <div class=" card bg-gradient-to-r from-teal-200 to-sky-400 rounded-xl shadow-lg p-4 flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105 duration-300">
                        <div class="card-icon text-4xl text-sky-600 mb-2">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="number text-5xl font-bold text-gray-900 mb-1">
                            <?php echo $nombre_utilisateurs; ?>
                        </div>
                        <div class="title text-sm text-gray-600 uppercase tracking-wider">
                            Utilisateurs Inscrits
                        </div>
                    </div>

                     <!-- COURS -->
                     <div class="card bg-gradient-to-r from-pink-200 to-pink-400 rounded-xl shadow-lg p-4 flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105 duration-300">
                        <div class="card-icon text-4xl  text-orange-600 mb-2">
                            <i class="fa-solid fa-book-open"></i>
                        </div>
                        <div class="number text-5xl font-bold text-gray-900 mb-1">
                            <?php echo $nombre_cours; ?>
                        </div>
                        <div class="title text-sm text-gray-600 uppercase tracking-wider">
                            Cours
                        </div>
                    </div>

                     <!-- PAIEMENT -->
                    <div class="card bg-gradient-to-r from-green-200 to-green-400 rounded-xl shadow-lg p-4 flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105 duration-300">
                        <div class="card-icon text-4xl text-green-700 mb-2">
                            <i class="fa-solid fa-credit-card"></i>
                        </div>
                        <div class="number text-5xl font-bold text-gray-900 mb-1">
                            <?php echo $nombre_paiement; ?>
                        </div>
                        <div class="title text-sm text-gray-600 uppercase tracking-wider">
                            Paiement
                        </div>
                    </div>

                    <!-- FORMATEUR -->
                    <div class="card  bg-gradient-to-r from-sky-400 to-indigo-500 rounded-xl shadow-lg p-4 flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105 duration-300">
                        <div class="card-icon text-4xl text-indigo-800 mb-2">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div class="number text-5xl font-bold text-gray-900 mb-1">
                            <?php echo $nombre_total_formateur; ?>
                        </div>
                        <div class="title text-sm text-gray-600 uppercase tracking-wider">
                            Formateur
                        </div>
                    </div>

                     <!-- CERTIFICATS -->
                     <div class="card bg-gradient-to-r from-amber-200 to-yellow-300 rounded-xl shadow-lg p-4 flex flex-col items-center justify-center text-center transition-transform transform hover:scale-105 duration-300">
                        <div class="card-icon text-4xl text-yellow-600 mb-2">
                            <i class="fa-solid fa-certificate"></i>
                        </div>
                        <div class="number text-5xl font-bold text-gray-900 mb-1">
                            <?php echo $nombre_total_certificats_emis; ?>
                        </div>
                        <div class="title text-sm text-gray-600 uppercase tracking-wider">
                            Certificats Émis
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>
</body>
</html>