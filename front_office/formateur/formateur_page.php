<?php
session_start();
require_once '../../include/config.php';

// ID formateur_connecté
$formateur_id = $_SESSION['formateur_id'] ?? 0;

$formateur_info = null; // Stocker informations_formateur
$cours_list = []; // Stocker liste_cours

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer informations_formateur
    $stmt_formateur = $conn->prepare("
        SELECT id_formateur, nom_formateur, email_formateur, image_formateur
        FROM formateurs
        WHERE id_formateur = :id
    ");
    $stmt_formateur->execute([':id' => $formateur_id]);
    $formateur_info = $stmt_formateur->fetch(PDO::FETCH_ASSOC);

    if (!$formateur_info) {
        header('Location: ../../index.php');
        exit();
    }

    // Récupérer cours_formateur
    $stmt_cours = $conn->prepare("
        SELECT 
            c.*, 
            cat.nom AS categorie_nom, 
            niv.nom AS niveau_nom
        FROM cours c
        LEFT JOIN categories cat ON c.categorie_id = cat.id
        LEFT JOIN niveaux niv ON c.niveau_id = niv.id
        WHERE c.id_formateur = :id
        ORDER BY c.id DESC
    ");
    $stmt_cours->execute([':id' => $formateur_id]);
    $cours_list = $stmt_cours->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
} finally {
    $conn = null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formateur - Mes Cours</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../../assets/js/Tailwind.js"></script>
    <style>
        .text-primary-local { color: #2563eb; }
        .bg-primary-local { background-color: #2563eb; }
        .hover\:bg-primary-hover:hover { background-color: #1d4ed8; }
        .text-secondary { color: #4b5563; }
        .bg-accent-light { background-color: #e0f2fe; }
        .hover\:text-primary:hover { color: #2563eb; }
        .text-danger { color: #ef4444; }
    </style>
</head>
<body class="font-sans bg-slate-100 text-slate-800 antialiased flex">

<aside class="w-[260px] bg-white p-6 border-r text-primary-local flex-col shrink-0 hidden sm:flex h-screen sticky top-0">
    <div class="text-2xl font-bold text-primary-local text-center -mb-5  flex items-center justify-center gap-2"> 
        <img src="../../assets/img/inf.png" alt="Logo INFOMADA" class="h-25 -mb-4 -mt-12 w-auto"> 
    </div>
    <?php 
        $formateur_image_path = !empty($formateur_info['image_formateur']) 
                                ? "../../" . htmlspecialchars($formateur_info['image_formateur']) 
                                : "../../assets/img/incognito.png"; 
    ?>
    <div class="mb-4 text-center">
        <img src="<?= $formateur_image_path ?>" 
            alt="Photo de <?= htmlspecialchars($formateur_info['nom_formateur']) ?>" 
            class="w-24 h-24 object-cover rounded-full border-4 border-blue-900 shadow-md mx-auto mb-3">
        <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($formateur_info['nom_formateur']) ?></h3>
        <p class="text-gray-600 text-sm"><?= htmlspecialchars($formateur_info['email_formateur']) ?></p>
    </div>

    <nav>
        <ul class="space-y-2">
            <li>
                <a href="#" class="nav-link flex items-center py-3 px-4 rounded-lg bg-accent-light text-primary-local hover:bg-accent-light hover:text-primary transition-colors">
                    <i class="fa-solid fa-layer-group w-5 text-center mr-4"></i> Mes Cours
                </a>
            </li>
            <li>
                <a href="../../front_office/formateur/formateur_login.php" class="nav-link flex items-center py-3 px-4 rounded-lg text-secondary hover:bg-red-100 hover:text-red-600 transition-colors">
                    <i class="fa-solid fa-right-from-bracket w-5 text-center mr-4"></i> Déconnexion
                </a>
            </li>
        </ul>
    </nav>
</aside>

<main class="flex-grow p-6 sm:p-10">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-4xl font-bold text-primary-local">Gérer Mes Cours</h1>
            <p class="mt-1 text-secondary">Ajoutez et modifiez le contenu de vos formations.</p>
        </div>
    </div>

    <?php if (!empty($cours_list)): ?>
        <div class="overflow-x-auto bg-white shadow-lg rounded-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-sm">
                        <th class="p-4">Couverture</th>
                        <th class="p-4">Titre</th>
                        <th class="p-4">Catégorie</th>
                        <th class="p-4">Niveau</th>
                        <th class="p-4">Prix (Ar)</th>
                        <th class="p-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cours_list as $c): ?>
                        <?php 
                          $image_cours = !empty($c['chemin_image']) 
                                    ? "../../" . htmlspecialchars($c['chemin_image']) 
                                    : "../../assets/img/cours.jpg";
                        ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-4">
                                <img src="<?= $image_cours ?>" 
                                     alt="Image du cours: <?= htmlspecialchars($c['titre']) ?>" 
                                     class="w-32 h-20 object-cover rounded border border-gray-200 shadow-sm">
                            </td>
                            <td class="p-4 font-medium text-gray-900"><?= htmlspecialchars($c['titre']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($c['categorie_nom'] ?? 'Non définie') ?></td>
                            <td class="p-4"><?= htmlspecialchars($c['niveau_nom'] ?? 'Non défini') ?></td>
                            <td class="p-4 text-blue-600 font-semibold"><?php echo htmlspecialchars(number_format($c['prix'] ?? 0, 0, '', ' ')); ?></td>
                            
                            
                            <td class="p-4 text-center">
                                <div class="flex flex-wrap justify-center gap-2">
                                    <a href="../../action/formateur/add_contenu.php?cours_id=<?= $c['id'] ?>" 
                                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">
                                        <i class="fas fa-plus-circle mr-1"></i> Ajouter contenu
                                    </a>
                                    <a href="voir_contenu.php?cours_id=<?= $c['id'] ?>" 
                                    class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir contenu
                                    </a>
                                    <a href="voir_examen.php?cours_id=<?= $c['id'] ?>" class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition">
                                        <i class="fas fa-file-alt mr-1"></i> Voir Examen
                                    </a>
                                </div>
                            </td>


                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-600 text-center py-8 bg-white rounded-xl shadow-md">Vous n'avez pas encore publié de cours.</p>
    <?php endif; ?>
</main>

</body>
</html>