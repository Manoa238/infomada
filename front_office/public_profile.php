<?php
require '../include/config.php';

//  ID est passé dans l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Profil introuvable ou lien incorrect.");
}

$user_id = intval($_GET['id']);
$user = null;
$profile_image_path = '../assets/img/incognito.png'; // PDP par défaut

try {
    // Sélection informations publiques
    $stmt = $pdo->prepare("SELECT first_name, last_name, datenais, profile_image FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Chemin de l'image
    if ($user && !empty($user['profile_image'])) {
        $profile_image_path = '../uploads/' . htmlspecialchars($user['profile_image']);
    }

} catch (PDOException $e) {
    die("Erreur de base de données.");
}

// user n'existe pas 
if (!$user) {
    die("Ce profil n'existe pas.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?php echo htmlspecialchars($user['first_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-200 flex items-center justify-center h-screen p-4">
    <!-- Carte Identité publique -->
    <div class="w-full max-w-lg bg-white shadow-xl rounded-lg p-8">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-700">Profil Utilisateur</h1>
        <div class="flex flex-col md:flex-row items-center gap-8">
            <!-- Photo -->
            <div class="flex-shrink-0">
                <img src="<?php echo $profile_image_path; ?>" alt="Photo de profil" class="w-32 h-32 md:w-40 md:h-40 rounded-full object-cover border-4 border-slate-200">
            </div>
            <!-- Informations Textuelles -->
            <div class="text-left w-full">
                <p class="text-lg mb-2"><strong class="font-semibold text-gray-600 w-32 inline-block">Nom :</strong>
                 <?php echo htmlspecialchars($user['last_name']); ?></p>
                <p class="text-lg mb-2"><strong class="font-semibold text-gray-600 w-32 inline-block">Prénom :</strong> 
                <?php echo htmlspecialchars($user['first_name']); ?></p>
                <p class="text-lg mb-4"><strong class="font-semibold text-gray-600 w-32 inline-block">Né(e) le :</strong>
                 <?php echo htmlspecialchars($user['datenais']); ?></p>
            </div>
        </div>
    </div>
</body>
</html>