<?php
session_start();

if (!isset($_SESSION['formateur_id'])) {
    header('Location: ../../formateur_login.php');
    exit();
}

if (!isset($_GET['cours_id']) || !filter_var($_GET['cours_id'], FILTER_VALIDATE_INT)) {
    header('Location: formateur_page.php'); 
    exit();
}
$cours_id = (int)$_GET['cours_id'];

$examen_json_file = '../../uploads/examens_json/examen.json'; 

$questions = [];
if (file_exists($examen_json_file)) {
    $json_content = file_get_contents($examen_json_file);
    $questions = json_decode($json_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $questions = []; // Erreur de décodage JSON
        $error_message = "Erreur de lecture du fichier examen.json : " . json_last_error_msg();
    }
} else {
    $error_message = "Le fichier examen.json est introuvable.";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voir Examen - Formateur</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../../assets/js/Tailwind.js"></script>
    <style>
        .text-primary-local { color: #2563eb; }
        .bg-primary-local { background-color: #2563eb; }
    </style>
</head>
<body class="font-sans bg-slate-100 text-slate-800 antialiased">

<div class="container mx-auto p-6 sm:p-10">
    <div class="flex items-center justify-between mb-8">
        <a href="formateur_page.php" class="text-primary-local hover:underline mr-4">
            <i class="fas fa-arrow-left mr-2"></i> Retour aux cours
        </a>
        <h1 class="text-4xl font-bold text-primary-local flex-grow text-center">Aperçu de l'Examen</h1>
        <?php if (!empty($questions) && !isset($error_message)): ?>
            <form action="publier_examen.php?cours_id=<?= htmlspecialchars($cours_id) ?>" method="POST" class="ml-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out">
                    <i class="fas fa-upload mr-2"></i> Publier l'Examen
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Erreur :</strong>
            <span class="block sm:inline"><?= htmlspecialchars($error_message) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($questions)): ?>
        <div class="space-y-6">
            <?php foreach ($questions as $index => $q): ?>
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <p class="text-lg font-semibold text-gray-800 mb-2">
                        Question <?= $index + 1 ?> (<?= htmlspecialchars($q['points'] ?? 0) ?> points)
                        <?php if (isset($q['multiple']) && $q['multiple']): ?>
                            <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">Choix Multiples</span>
                        <?php endif; ?>
                    </p>
                    <p class="text-gray-700 mb-4"><?= htmlspecialchars($q['question']) ?></p>
                    
                    <div class="space-y-2 mb-4">
                        <?php foreach ($q['options'] as $option): ?>
                            <div class="flex items-center text-gray-600">
                                <input type="<?= (isset($q['multiple']) && $q['multiple']) ? 'checkbox' : 'radio' ?>" 
                                       disabled class="mr-2">
                                <span><?= htmlspecialchars($option) ?></span>
                                <?php 
                                // Afficher bonne réponse pour formateur
                                if ((isset($q['multiple']) && is_array($q['answer']) && in_array($option, $q['answer'])) || 
                                    (!isset($q['multiple']) && $option === $q['answer'])) {
                                    echo '<span class="ml-3 text-green-600 font-medium"><i class="fas fa-check-circle mr-1"></i> (Bonne réponse)</span>';
                                }
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (!isset($error_message)): ?>
        <p class="text-gray-600 text-center py-8 bg-white rounded-xl shadow-md">Aucune question trouvée dans fichier examen.json.</p>
    <?php endif; ?>
</div>

</body>
</html>