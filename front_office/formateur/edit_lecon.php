<?php
session_start();
require_once '../../include/config.php';

$formateur_id = $_SESSION['formateur_id'] ?? 0;
if ($formateur_id == 0) {
    header('Location: ../../index.php');
    exit();
}

if (!isset($_GET['lecon_id']) || !filter_var($_GET['lecon_id'], FILTER_VALIDATE_INT)) {
    header('Location: formateur_page.php'); 
    exit();
}
$lecon_id = (int) $_GET['lecon_id'];

//  nettoyer le nom_fichier
function nettoyerNomFichier($nomFichier) {
    $nomFichier = preg_replace('/[^\p{L}\p{N}\s\._-]/u', '', $nomFichier);
    $nomFichier = preg_replace('/[\s_-]+/', '-', $nomFichier);
    $nomFichier = trim($nomFichier, '-');
    return $nomFichier;
}

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer informations leçon et vérifier chapitre & cours formateur
    $stmt = $conn->prepare("
        SELECT l.id, l.titre_lecon, l.type_contenu, l.contenu_texte, l.chemin_fichier, l.ordre,
               ch.id AS chapitre_id, ch.cours_id, c.titre AS titre_cours
        FROM lecons l
        JOIN chapitres ch ON l.chapitre_id = ch.id
        JOIN cours c ON ch.cours_id = c.id
        WHERE l.id = :lecon_id AND c.id_formateur = :formateur_id
    ");
    $stmt->execute([':lecon_id' => $lecon_id, ':formateur_id' => $formateur_id]);
    $lecon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lecon) {
        die("Leçon non trouvée ou accès refusé.");
    }

    $cours_id = $lecon['cours_id'];
    $chapitre_id = $lecon['chapitre_id'];

    // Récupérer nombre leçons pour le menu d'ordre
    $stmt_max_ordre = $conn->prepare("SELECT COUNT(*) FROM lecons WHERE chapitre_id = :chapitre_id");
    $stmt_max_ordre->execute([':chapitre_id' => $chapitre_id]);
    $total_lecons = $stmt_max_ordre->fetchColumn();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nouveau_titre = trim($_POST['titre_lecon'] ?? '');
        $nouveau_type = $_POST['type_contenu'] ?? 'texte';
        $nouveau_contenu_texte = trim($_POST['contenu_texte'] ?? '');
        $nouvel_ordre = (int) ($_POST['ordre_lecon'] ?? $lecon['ordre']);
        $chemin_vers_fichier = $lecon['chemin_fichier'];

        if (empty($nouveau_titre) && empty($nouveau_contenu_texte) && ($nouveau_type === 'texte' || (!isset($_FILES['fichier']['tmp_name']) || $_FILES['fichier']['error'] != UPLOAD_ERR_OK))) {
            $_SESSION['error_message'] = "Veuillez fournir au moins un titre, un contenu texte ou un fichier pour la leçon.";
            header("Location: edit_lecon.php?lecon_id=$lecon_id");
            exit();
        }

        // GESTION FICHIER
        if ($nouveau_type === 'pdf' || $nouveau_type === 'video') {
            // Nouveau fichier uploadé
            if (isset($_FILES['fichier']['tmp_name']) && $_FILES['fichier']['error'] == UPLOAD_ERR_OK) {
                // Supprimer ancien fichier si nouveau uploadé et existe
                if ($lecon['chemin_fichier'] && file_exists('../../' . $lecon['chemin_fichier'])) {
                    unlink('../../' . $lecon['chemin_fichier']);
                }

                $nom_fichier_original = $_FILES['fichier']['name'];
                $extension = strtolower(pathinfo($nom_fichier_original, PATHINFO_EXTENSION));
                $nom_base_fichier = pathinfo($nom_fichier_original, PATHINFO_FILENAME);

                $nom_fichier_nettoye = nettoyerNomFichier($nom_base_fichier);

                $dossier_telechargement = '../../uploads/contenus/';
                if (!is_dir($dossier_telechargement)) mkdir($dossier_telechargement, 0777, true);

                $nom_fichier_final = $nom_fichier_nettoye . '.' . $extension;
                $destination = $dossier_telechargement . $nom_fichier_final;

                $compteur = 1;
                while (file_exists($destination)) {
                    $nom_fichier_final = $nom_fichier_nettoye . '(' . $compteur . ').' . $extension;
                    $destination = $dossier_telechargement . $nom_fichier_final;
                    $compteur++;
                }

                if (move_uploaded_file($_FILES['fichier']['tmp_name'], $destination)) {
                    $chemin_vers_fichier = 'uploads/contenus/' . $nom_fichier_final;
                } else {
                    $_SESSION['error_message'] = "Erreur lors de l'upload du fichier.";
                    header("Location: edit_lecon.php?lecon_id=$lecon_id");
                    exit();
                }
            } elseif ($lecon['type_contenu'] !== $nouveau_type && ($lecon['type_contenu'] === 'pdf' || $lecon['type_contenu'] === 'video')) {
                 // Type_fichier = texte, delele ancien fichier
                 if ($lecon['chemin_fichier'] && file_exists('../../' . $lecon['chemin_fichier'])) {
                    unlink('../../' . $lecon['chemin_fichier']);
                 }
                 $chemin_vers_fichier = null;
            }
        } elseif ($lecon['type_contenu'] !== 'texte' && $nouveau_type === 'texte') {
            // Type_fichier à texte, delete ancien fichier
            if ($lecon['chemin_fichier'] && file_exists('../../' . $lecon['chemin_fichier'])) {
                unlink('../../' . $lecon['chemin_fichier']);
            }
            $chemin_vers_fichier = null;
        } else {
            // type != fichier
            $chemin_vers_fichier = null;
        }

        // Vérifier si l'ordre a changé
        if ($nouvel_ordre != $lecon['ordre']) {
            // MàJ ordre leçons
            if ($nouvel_ordre > $lecon['ordre']) {
                $stmt_update_order = $conn->prepare("
                    UPDATE lecons
                    SET ordre = ordre - 1
                    WHERE chapitre_id = :chapitre_id AND ordre > :ancien_ordre AND ordre <= :nouvel_ordre
                ");
                $stmt_update_order->execute([
                    ':chapitre_id' => $chapitre_id,
                    ':ancien_ordre' => $lecon['ordre'],
                    ':nouvel_ordre' => $nouvel_ordre
                ]);
            } elseif ($nouvel_ordre < $lecon['ordre']) {
                $stmt_update_order = $conn->prepare("
                    UPDATE lecons
                    SET ordre = ordre + 1
                    WHERE chapitre_id = :chapitre_id AND ordre < :ancien_ordre AND ordre >= :nouvel_ordre
                ");
                $stmt_update_order->execute([
                    ':chapitre_id' => $chapitre_id,
                    ':ancien_ordre' => $lecon['ordre'],
                    ':nouvel_ordre' => $nouvel_ordre
                ]);
            }
        }

        // Mis à jour la leçon
        $stmt_update = $conn->prepare("
            UPDATE lecons
            SET titre_lecon = :titre, type_contenu = :type, contenu_texte = :contenu_texte, chemin_fichier = :chemin_fichier, ordre = :ordre
            WHERE id = :id
        ");
        $stmt_update->execute([
            ':titre' => $nouveau_titre,
            ':type' => $nouveau_type,
            ':contenu_texte' => ($nouveau_type === 'texte' ? $nouveau_contenu_texte : null), 
            ':chemin_fichier' => $chemin_vers_fichier,
            ':ordre' => $nouvel_ordre,
            ':id' => $lecon_id
        ]);

        $_SESSION['success_message'] = "Leçon mise à jour avec succès.";
        header("Location: voir_contenu.php?cours_id=" . $lecon['cours_id']);
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    header("Location: voir_contenu.php?cours_id=" . $lecon['cours_id']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Leçon - <?= htmlspecialchars($lecon['titre_lecon']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/index.css">
    <link rel="stylesheet" href="../../assets/css/contact.css">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-xl mx-auto bg-white rounded-xl shadow-lg p-6 border-2 bordure-conteneur-animee">
        <div class="flex items-center justify-between mb-8">
            <a href="voir_contenu.php?cours_id=<?= htmlspecialchars($lecon['cours_id']) ?>" class="text-white bg-primary-local px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                <i class="fas fa-arrow-left mr-2"></i> Retour
            </a>
            <h1 class="text-2xl font-bold text-primary-local text-center flex-1 ml-4 mr-16">
                MODIFIER LA LEÇON : <br><?= htmlspecialchars($lecon['titre_lecon']) ?>
            </h1>
        </div>

        <!-- MESSAGE SUCCES -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <!-- MESSAGE ERREUR -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="edit_lecon.php?lecon_id=<?= htmlspecialchars($lecon_id) ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <!-- TITRE  -->
            <div>
                <label for="titre_lecon" class="block text-primary-local font-semibold mb-1">Titre de la leçon</label>
                <input type="text" id="titre_lecon" name="titre_lecon" value="<?= htmlspecialchars($lecon['titre_lecon']) ?>"
                       class="w-full border rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- TYPE CONTENU -->
            <div>
                <label for="type_contenu" class="block text-primary-local font-semibold mb-1">Type de contenu</label>
                <select id="type_contenu" name="type_contenu" onchange="alternerChampsContenuEdit(this)"
                        class="w-full border rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="texte" <?= ($lecon['type_contenu'] === 'texte') ? 'selected' : '' ?>>Texte</option>
                    <option value="pdf" <?= ($lecon['type_contenu'] === 'pdf') ? 'selected' : '' ?>>PDF</option>
                    <option value="video" <?= ($lecon['type_contenu'] === 'video') ? 'selected' : '' ?>>Vidéo</option>
                </select>
            </div>

            <!-- DESCRI -->
            <div id="lecon-texte-field">
                <label for="contenu_texte" class="text-primary-local font-semibold mb-1">Description / Contenu texte</label>
                <textarea id="contenu_texte" name="contenu_texte" rows="6"
                          class="w-full border rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($lecon['contenu_texte']) ?></textarea>
            </div>

            <!-- FICHIER PDF/VIDEO -->
            <div id="lecon-fichier-field" class="
                <?= ($lecon['type_contenu'] === 'pdf' || $lecon['type_contenu'] === 'video') ? '' : 'hidden' ?>
            ">
                <label for="fichier" class="block text-primary-local font-semibold mb-1">Fichier (PDF ou Vidéo)</label>
                <?php if ($lecon['chemin_fichier']): ?>
                    <p class="text-gray-600 text-sm mb-2">Fichier actuel :
                        <a href="../../<?= htmlspecialchars($lecon['chemin_fichier']) ?>" target="_blank" class="text-blue-500 hover:underline">
                            <?= htmlspecialchars(basename($lecon['chemin_fichier'])) ?>
                        </a>
                    </p>
                <?php endif; ?>
                <input type="file" id="fichier" name="fichier"
                       class="w-full border rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                       <?= ($lecon['type_contenu'] === 'texte') ? 'disabled' : '' ?>>
                <p class="text-sm text-gray-500 mt-1">Laissez vide pour conserver le fichier existant. Uploader un nouveau fichier écrasera l'ancien.</p>
            </div>

            <div>
                <label for="ordre_lecon" class="block text-primary-local font-semibold mb-1">Ordre de la leçon</label>
                <select id="ordre_lecon" name="ordre_lecon"
                        class="w-full border rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    <?php for ($i = 1; $i <= $total_lecons; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $lecon['ordre']) ? 'selected' : '' ?>>
                            <?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="pt-4">
                <button type="submit" class="bg-primary-local text-white px-6 py-2 rounded hover:bg-blue-700 transition duration-300">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

    <script>
        function alternerChampsContenuEdit(elementSelect) {
            const champTexte = document.getElementById('lecon-texte-field');
            const champFichier = document.getElementById('lecon-fichier-field');
            const inputFichier = document.getElementById('fichier');

            if (elementSelect.value === 'texte') {
                champTexte.classList.remove('hidden');
                champFichier.classList.add('hidden');
                inputFichier.setAttribute('disabled', 'disabled');
                inputFichier.value = ''; // Effacer fichier sélectionné = texte
            } else { // pdf ou video
                champTexte.classList.add('hidden');
                champFichier.classList.remove('hidden');
                inputFichier.removeAttribute('disabled');
            }
        }

        // Appeler fonction au chargement
        document.addEventListener('DOMContentLoaded', () => {
            alternerChampsContenuEdit(document.getElementById('type_contenu'));
        });
    </script>
</body>
</html>