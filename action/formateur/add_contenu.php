<?php
session_start();
require_once '../../include/config.php';

// Formateur connecté
if (!isset($_SESSION['formateur_id'])) {
    header('Location: ../../front_office/formateur_login.php');
    exit();
}

$identifiant_formateur = $_SESSION['formateur_id'];
// Id_cours existe
if (!isset($_GET['cours_id']) || !filter_var($_GET['cours_id'], FILTER_VALIDATE_INT)) {
    header("Location: formateur_page.php");
    exit();
}

$identifiant_cours = (int) $_GET['cours_id'];

// Fonction pour nettoyer le nom du fichier
function nettoyerNomFichier($nomFichier) {
    // Supprime caractères non alphanumériques (espaces, tirets, underscores, points)
    $nomFichier = preg_replace('/[^\p{L}\p{N}\s\._-]/u', '', $nomFichier);
    $nomFichier = preg_replace('/[\s_-]+/', '-', $nomFichier);
    // Supprime "-"  début & fin
    $nomFichier = trim($nomFichier, '-');
    return $nomFichier;
}

// Récupérer prochain ordre disponible pour chapitre
$next_chapter_order = 1; 
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $connexion_order = new PDO($dsn, $user, $password);
    $connexion_order->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt_max_ordre = $connexion_order->prepare("SELECT MAX(ordre) FROM chapitres WHERE cours_id = :cours_id");
    $stmt_max_ordre->execute([':cours_id' => $identifiant_cours]);
    $max_ordre = $stmt_max_ordre->fetchColumn();
    if ($max_ordre !== null) {
        $next_chapter_order = $max_ordre + 1;
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du prochain ordre de chapitre : " . $e->getMessage());
} finally {
    $connexion_order = null; 
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre_chapitre = trim($_POST['titre_chapitre'] ?? '');
    $ordre_chapitre = $next_chapter_order; // Ordre incrémenté 

    if (empty($titre_chapitre)) {
        $_SESSION['error_message'] = "Veuillez entrer un titre de chapitre.";
        header("Location: add_contenu.php?cours_id=$identifiant_cours");
        exit();
    }

    try {
        // Connexion PostgreSQL
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $connexion = new PDO($dsn, $user, $password);
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // REQUETE SQL Insert chapitre
        $requete_chapitre = $connexion->prepare("INSERT INTO chapitres (cours_id, titre_chapitre, ordre)
                                VALUES (:cours_id, :titre, :ordre) RETURNING id");
        $requete_chapitre->execute([
            ':cours_id' => $identifiant_cours,
            ':titre' => $titre_chapitre,
            ':ordre' => $ordre_chapitre
        ]);
        $identifiant_chapitre = $requete_chapitre->fetchColumn();

        // Insert leçons liées
        if (!empty($_POST['lecon_titre'])) {
            foreach ($_POST['lecon_titre'] as $index => $titre_lecon) {
                $titre_lecon = trim($titre_lecon);
                $contenu_texte_lecon = trim($_POST['lecon_contenu_texte'][$index] ?? '');

                // Leçon ni titre, ni contenu texte, ni fichier, saute
                if (empty($titre_lecon) && empty($contenu_texte_lecon) &&
                    (!isset($_FILES['lecon_fichier']['tmp_name'][$index]) || $_FILES['lecon_fichier']['error'][$index] != UPLOAD_ERR_OK)) {
                    continue;
                }

                $type_contenu = $_POST['lecon_type'][$index] ?? 'texte';
                $chemin_vers_fichier = null;
                $ordre_lecon = $index + 1;

                // TYPE PDF/Vidéo
                if ($type_contenu === 'pdf' || $type_contenu === 'video') {
                    // Fichier uploadé
                    if (isset($_FILES['lecon_fichier']['tmp_name'][$index]) && $_FILES['lecon_fichier']['error'][$index] == UPLOAD_ERR_OK) {
                        $nom_fichier_original = $_FILES['lecon_fichier']['name'][$index];
                        $extension = strtolower(pathinfo($nom_fichier_original, PATHINFO_EXTENSION));
                        $nom_base_fichier = pathinfo($nom_fichier_original, PATHINFO_FILENAME);

                        // Nettoyer nom_fichier_base
                        $nom_fichier_nettoye = nettoyerNomFichier($nom_base_fichier);

                        $dossier_telechargement = '../../uploads/contenus/';
                        if (!is_dir($dossier_telechargement)) mkdir($dossier_telechargement, 0777, true);

                        $nom_fichier_final = $nom_fichier_nettoye . '.' . $extension;
                        $destination = $dossier_telechargement . $nom_fichier_final;

                        // Collisions noms_fichiers
                        $compteur = 1;
                        while (file_exists($destination)) {
                            $nom_fichier_final = $nom_fichier_nettoye . '(' . $compteur . ').' . $extension;
                            $destination = $dossier_telechargement . $nom_fichier_final;
                            $compteur++;
                        }

                        if (move_uploaded_file($_FILES['lecon_fichier']['tmp_name'][$index], $destination)) {
                            $chemin_vers_fichier = 'uploads/contenus/' . $nom_fichier_final;
                        }
                    }
                }

                // Insert leçon
                $requete_lecon = $connexion->prepare("INSERT INTO lecons
                        (chapitre_id, titre_lecon, type_contenu, chemin_fichier, contenu_texte, ordre)
                        VALUES (:chapitre_id, :titre, :type, :fichier, :contenu, :ordre)");
                $requete_lecon->execute([
                    ':chapitre_id' => $identifiant_chapitre,
                    ':titre' => $titre_lecon,
                    ':type' => $type_contenu,
                    ':fichier' => $chemin_vers_fichier,
                    ':contenu' => $contenu_texte_lecon,
                    ':ordre' => $ordre_lecon
                ]);
            }
        }

        $_SESSION['success_message'] = "Chapitre et ses leçons ajoutés avec succès.";
        header("Location: add_contenu.php?cours_id=$identifiant_cours");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
        header("Location: add_contenu.php?cours_id=$identifiant_cours");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter Contenu du Cours</title>
<!-- <script src="https://cdn.tailwindcss.com"></script> -->
<script src="../../assets/js/Tailwind.js"></script>
<link rel="stylesheet" href="../../assets/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/index.css">
<link rel="stylesheet" href="../../assets/css/contact.css">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-6 border-2 bordure-conteneur-animee">
        <div class="flex items-center justify-between mb-8">
            <!-- Bouton retour -->
            <a href="../../front_office/formateur/formateur_page.php" class="text-white bg-primary-local px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                <i class="fas fa-arrow-left mr-2"></i> Retour
            </a>
            <h1 class="text-2xl font-bold text-center text-secondary-local mb-6">AJOUTER LES CHAPITRE ET LECONS</h1> 
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="add_contenu.php?cours_id=<?= $identifiant_cours ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- CHAPITRE -->
            <div>
                <label class="block text-primary-local font-semibold">Titre du Chapitre</label>
                <input type="text" name="titre_chapitre" required class="w-full border rounded px-3 py-2 mt-1">
            </div>
            <div>
                <label class="block text-primary-local font-semibold">Ordre</label>
                <input type="number" name="ordre_chapitre" value="<?= $next_chapter_order ?>" min="1" class="w-full border rounded px-3 py-2 mt-1 bg-gray-100 cursor-not-allowed" readonly>
            </div>

            <!-- LECONS -->
            <h2 class="text-xl font-bold text-secondary-local">LECONS</h2>
            <div id="lecons-container" class="space-y-4">
                <div class="lecon-item border p-4 rounded-lg bg-gray-50">
                    <label class="block text-primary-local">Titre de la leçon</label>
                    <input type="text" name="lecon_titre[]" class="w-full border rounded px-3 py-2 mb-2">

                    <label class="block text-primary-local">Type de contenu</label>
                    <select name="lecon_type[]" class="w-full border rounded px-3 py-2 mb-2" onchange="alternerChampsContenu(this)">
                        <option value="texte">Texte</option>
                        <option value="pdf">PDF</option>
                        <option value="video">Vidéo</option>
                    </select>

                    <div class="lecon-texte-field">
                        <label class="block text-primary-local">Description / Contenu texte</label>
                        <textarea name="lecon_contenu_texte[]" class="w-full border rounded px-3 py-2 mb-2"></textarea>
                    </div>

                    <div class="lecon-fichier-field" style="display:none;">
                        <label class="block text-primary-local">Fichier (PDF ou Vidéo)</label>
                        <input type="file" name="lecon_fichier[]" class="w-full border rounded px-3 py-2" disabled>
                    </div>
                    <button type="button" onclick="supprimerLecon(this)" class="mt-2 bg-red-700 text-white px-3 py-1 rounded hover:bg-red-600">Supprimer cette leçon</button>

                </div>
            </div>

            <button type="button" onclick="ajouterNouvelleLecon()"
                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">
                + Ajouter une autre leçon
            </button>

            <div class="pt-4">
                <button type="submit" class="bg-primary-local text-white px-6 py-2 rounded hover:bg-blue-700">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
<script src="../../assets/js/add_contenu_formateur.js"> </script>

</body>
</html>