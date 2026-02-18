<?php
session_start();
require_once '../../include/config.php';

$formateur_id = $_SESSION['formateur_id'] ?? 0;
if ($formateur_id == 0) {
    header('Location: ../../index.php');
    exit();
}

if (!isset($_GET['cours_id']) || !filter_var($_GET['cours_id'], FILTER_VALIDATE_INT)) {
    header('Location: formateur_page.php');
    exit();
}
$cours_id = (int) $_GET['cours_id'];

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier cours appartient au formateur
    $stmt = $conn->prepare("SELECT titre FROM cours WHERE id = :id AND id_formateur = :fid");
    $stmt->execute([':id' => $cours_id, ':fid' => $formateur_id]);
    $cours = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cours) die("Cours non trouvé ou accès refusé.");

    // Requête SQL pour récupérer chapitres & leçons
    $stmt = $conn->prepare("
        SELECT ch.id AS chapitre_id, ch.titre_chapitre, ch.ordre AS chapitre_ordre,
               l.id AS lecon_id, l.titre_lecon, l.type_contenu, l.contenu_texte, l.chemin_fichier, l.ordre AS lecon_ordre
        FROM chapitres ch
        LEFT JOIN lecons l ON ch.id = l.chapitre_id
        WHERE ch.cours_id = :cours_id
        ORDER BY ch.ordre ASC, l.ordre ASC
    ");
    $stmt->execute([':cours_id' => $cours_id]);
    $contenus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Reorganiser données/chapitre
    $chapitres_organises = [];
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
        // Ajouter leçon
        if ($item['lecon_id']) {
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


} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Contenu du cours - <?= htmlspecialchars($cours['titre']) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/index.css">
<link rel="stylesheet" href="../../assets/css/contact.css">
<script src="../../assets/js/Tailwind.js"></script>
<style>
    .hover\:bg-blue-700:hover { background-color: #1d4ed8; } 
</style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-6 border-2 bordure-conteneur-animee">
        <div class="flex items-center justify-between mb-8">
            <!-- BOUTON RETOUR -->
            <a href="formateur_page.php" class="text-white bg-primary-local px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                <i class="fas fa-arrow-left mr-2"></i> Retour
            </a>

            <!-- TITRE -->
            <h1 class="text-2xl font-bold text-primary-local text-center flex-1 ml-4 mr-16">
                CONTENU DU COURS : <?= htmlspecialchars($cours['titre']) ?>
            </h1>
        </div>


        <?php
        if (empty($chapitres_organises)) {
            echo "<p class='text-gray-600 mt-8 text-center'>Aucun contenu ajouté pour ce cours.</p>";
        } else {
            foreach ($chapitres_organises as $chapitre) {
                // Div chaque chapitre
                echo "<div class='border border-blue-200 rounded-xl p-4 mb-6 shadow-md bg-white'>";

                // Titre du chapitre et actions
                echo "<div class='flex items-center justify-between bg-blue-50 p-3 rounded-md mb-4 shadow-sm'>";
                echo "<h2 class='text-xl font-semibold text-primary-local'>
                        Chapitre {$chapitre['ordre']} : " . htmlspecialchars($chapitre['titre_chapitre']) . "
                      </h2>";
                // Modifier/supprimer chapitre 
                echo "<div class='flex items-center space-x-3'>";
                echo "<a href='edit_chapitre.php?chapitre_id=" . $chapitre['id'] . "' title='Modifier le chapitre' class='text-blue-500 hover:text-blue-700 transition'><i class='fas fa-edit'></i></a>";
                echo "<a href='../../action/formateur/delete_chapitre.php?chapitre_id=" . $chapitre['id'] . "&cours_id=" . $cours_id . "' title='Supprimer le chapitre' class='text-red-500 hover:text-red-700 transition' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer ce chapitre et toutes ses leçons ?');\"><i class='fas fa-trash-alt'></i></a>";
                echo "</div>"; // actions chapitre
                echo "</div>"; // titre chapitre

                // Div leçons
                echo "<div class='space-y-4'>";
                if (empty($chapitre['lecons'])) {
                    echo "<p class='text-gray-500 italic px-3'>Aucune leçon pour ce chapitre.</p>";
                } else {
                    foreach ($chapitre['lecons'] as $lecon) {
                        // Afficher détails leçon
                        echo "<div class='p-3 bg-gray-50 rounded-lg shadow-sm'>";
                        echo "<div class='flex items-center justify-between'>"; 
                        echo "<p class='font-semibold text-gray-800'>Leçon {$lecon['ordre']} : " . htmlspecialchars($lecon['titre_lecon']) . "</p>";
                        // Modifier/supprimer leçon
                        echo "<div class='flex items-center space-x-2'>";
                        echo "<a href='edit_lecon.php?lecon_id=" . $lecon['id'] . "' title='Modifier la leçon' class='text-green-500 hover:text-green-700 transition'><i class='fas fa-edit'></i></a>";
                        echo "<a href='../../action/formateur/delete_lecon.php?lecon_id=" . $lecon['id'] . "&cours_id=" . $cours_id . "' title='Supprimer la leçon' class='text-red-400 hover:text-red-600 transition' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer cette leçon ?');\"><i class='fas fa-times-circle'></i></a>";
                        echo "</div>";
                        echo "</div>"; 

                        // Description texte
                        if (!empty($lecon['contenu_texte'])) {
                            echo "<div class='p-2 mt-2 text-gray-700 bg-white rounded'>" . nl2br(htmlspecialchars($lecon['contenu_texte'])) . "</div>";
                        }

                        // PDF / Vidéo
                        if (($lecon['type_contenu'] === 'pdf' || $lecon['type_contenu'] === 'video') && !empty($lecon['chemin_fichier'])) {
                            $filename = basename($lecon['chemin_fichier']);
                            echo "<div class='mt-2 flex items-center gap-2'>";

                            if ($lecon['type_contenu'] === 'pdf') {
                                echo "<i class='fa-solid fa-file-pdf text-red-600 text-lg'></i>";
                            } elseif ($lecon['type_contenu'] === 'video') {
                                echo "<i class='fa-solid fa-circle-play text-green-600 text-lg'></i>";
                            }

                            echo "<a href='../../" . htmlspecialchars($lecon['chemin_fichier']) . "' target='_blank' class='text-primary-local underline hover:text-blue-600'>"
                                . htmlspecialchars($filename) .
                                "</a></div>";
                        }
                        echo "</div>"; // Fin leçon
                    }
                }
                echo "</div>"; // Fin toutes les leçons 
                echo "</div>"; // Fin  chapitre
            }
        }
        ?>

    </div>
</body>
</html>