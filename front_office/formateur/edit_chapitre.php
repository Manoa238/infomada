<?php
session_start();
require_once '../../include/config.php';

$formateur_id = $_SESSION['formateur_id'] ?? 0;
if ($formateur_id == 0) {
    header('Location: ../../index.php');
    exit();
}

if (!isset($_GET['chapitre_id']) || !filter_var($_GET['chapitre_id'], FILTER_VALIDATE_INT)) {
    header('Location: formateur_page.php');
    exit();
}
$chapitre_id = (int) $_GET['chapitre_id'];

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer informations chapitre et cours formateur
    $stmt = $conn->prepare("
        SELECT ch.id, ch.titre_chapitre, ch.ordre, ch.cours_id, c.titre AS titre_cours
        FROM chapitres ch
        JOIN cours c ON ch.cours_id = c.id
        WHERE ch.id = :chapitre_id AND c.id_formateur = :formateur_id
    ");
    $stmt->execute([':chapitre_id' => $chapitre_id, ':formateur_id' => $formateur_id]);
    $chapitre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chapitre) {
        die("Chapitre non trouvé ou accès refusé.");
    }

    $cours_id = $chapitre['cours_id'];

    // Récupérer nombre de chapitres pour ordre
    $stmt_max_ordre = $conn->prepare("SELECT COUNT(*) FROM chapitres WHERE cours_id = :cours_id");
    $stmt_max_ordre->execute([':cours_id' => $cours_id]);
    $total_chapitres = $stmt_max_ordre->fetchColumn();


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nouveau_titre = trim($_POST['titre_chapitre'] ?? '');
        $nouvel_ordre = (int) ($_POST['ordre_chapitre'] ?? $chapitre['ordre']);

        if (empty($nouveau_titre)) {
            $_SESSION['error_message'] = "Le titre du chapitre ne peut pas être vide.";
            header("Location: edit_chapitre.php?chapitre_id=$chapitre_id");
            exit();
        }

        // Si l'ordre a changé
        if ($nouvel_ordre != $chapitre['ordre']) {
            // MàJ ordre des autres chapitres
            if ($nouvel_ordre > $chapitre['ordre']) {
                $stmt_update_order = $conn->prepare("
                    UPDATE chapitres
                    SET ordre = ordre - 1
                    WHERE cours_id = :cours_id AND ordre > :ancien_ordre AND ordre <= :nouvel_ordre
                ");
                $stmt_update_order->execute([
                    ':cours_id' => $cours_id,
                    ':ancien_ordre' => $chapitre['ordre'],
                    ':nouvel_ordre' => $nouvel_ordre
                ]);
            } elseif ($nouvel_ordre < $chapitre['ordre']) {
                $stmt_update_order = $conn->prepare("
                    UPDATE chapitres
                    SET ordre = ordre + 1
                    WHERE cours_id = :cours_id AND ordre < :ancien_ordre AND ordre >= :nouvel_ordre
                ");
                $stmt_update_order->execute([
                    ':cours_id' => $cours_id,
                    ':ancien_ordre' => $chapitre['ordre'],
                    ':nouvel_ordre' => $nouvel_ordre
                ]);
            }
        }
        
        // Mis à jour chapitre
        $stmt_update = $conn->prepare("UPDATE chapitres SET titre_chapitre = :titre, ordre = :ordre WHERE id = :id");
        $stmt_update->execute([
            ':titre' => $nouveau_titre,
            ':ordre' => $nouvel_ordre,
            ':id' => $chapitre_id
        ]);

        $_SESSION['success_message'] = "Chapitre mis à jour avec succès.";
        header("Location: voir_contenu.php?cours_id=" . $chapitre['cours_id']);
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    header("Location: voir_contenu.php?cours_id=" . $chapitre['cours_id']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Chapitre - <?= htmlspecialchars($chapitre['titre_chapitre']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/index.css">
    <link rel="stylesheet" href="../../assets/css/contact.css">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-xl mx-auto bg-white rounded-xl shadow-lg p-6 border-2 bordure-conteneur-animee">
        <div class="flex items-center justify-between mb-8">
            <a href="voir_contenu.php?cours_id=<?= htmlspecialchars($chapitre['cours_id']) ?>" class="text-white bg-primary-local px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                <i class="fas fa-arrow-left mr-2"></i> Retour
            </a>
            <h1 class="text-2xl font-bold text-primary-local text-center flex-1 ml-4 mr-16">
                MODIFIER LE CHAPITRE : <br><?= htmlspecialchars($chapitre['titre_chapitre']) ?>
            </h1>
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

        <form action="edit_chapitre.php?chapitre_id=<?= htmlspecialchars($chapitre_id) ?>" method="POST" class="space-y-4">
            <div>
                <label for="titre_chapitre" class="block text-primary-local font-semibold mb-1">Titre du Chapitre</label>
                <input type="text" id="titre_chapitre" name="titre_chapitre" value="<?= htmlspecialchars($chapitre['titre_chapitre']) ?>" required
                       class="w-full border rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="ordre_chapitre" class="block text-primary-local font-semibold mb-1">Ordre du Chapitre</label>
                <select id="ordre_chapitre" name="ordre_chapitre"
                        class="w-full border rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    <?php for ($i = 1; $i <= $total_chapitres; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $chapitre['ordre']) ? 'selected' : '' ?>>
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
</body>
</html>