<?php
session_start();
require_once '../include/config.php';

// Valider ID cours via l'URL
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "Cours non spécifié ou invalide.";
    header('Location: ../back_office/admin_page_formation.php');
    exit();
}
$course_id = $_GET['id'];

$course = null;
$niveaux_list = [];
$categories_list = [];
$error_message = '';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer informations cours & formateur
    $stmt = $conn->prepare("
        SELECT
            c.*,
            f.nom_formateur,
            f.email_formateur,
            f.image_formateur AS chemin_image_formateur
        FROM public.cours c
        LEFT JOIN public.formateurs f ON c.id_formateur = f.id_formateur
        WHERE c.id = :id
    ");
    $stmt->execute([':id' => $course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        throw new Exception("Aucun cours trouvé avec cet ID.");
    }

    // Récupérer listes catégories & niveaux (menus déroulants)
    $niveaux_list = $conn->query("SELECT id, nom FROM public.niveaux ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
    $categories_list = $conn->query("SELECT id, nom FROM public.categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    header('Location: admin_page_formation.php');
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
    <title>Modifier le Cours - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <script src="../assets/js/Tailwind.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { DEFAULT: '#3b82f6', hover: '#2563eb' },
                        'primary-local': '#0d6efd',
                        secondary: '#475569',
                        accent: { light: '#eff6ff', DEFAULT: '#60a5fa' },
                        danger: '#ef4444',
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-slate-100 text-slate-800 antialiased">
    <main class="flex-grow p-6 sm:p-10">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center mb-3">
                <a href="admin_page_formation.php" class="text-secondary hover:text-primary transition-colors mr-4" title="Retour à la liste">
                    <i class="fa-solid fa-arrow-left fa-lg"></i>
                </a>
                <h1 class="text-3xl font-bold text-slate-800">Modifier le cours</h1>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-2xl border border-slate-200">
                <h2 class="text-2xl font-bold mb-3 text-slate-700 border-b pb-4">Détails du cours : <?php echo htmlspecialchars($course['titre']); ?></h2>

                <form action="../action/admin/update_cours.php" method="POST" enctype="multipart/form-data" class="space-y-3">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">

                    <!-- TITRE & IMAGE_COUVERTURE  -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="titre" class="block text-sm font-semibold text-secondary-local mb-2">Titre du cours</label>
                            <input id="titre" type="text" name="titre" value="<?php echo htmlspecialchars($course['titre']); ?>" required class="w-full border-2 border-slate-200 rounded-lg p-3">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-secondary-local mb-2">Image de couverture actuelle</label>
                            <div class="flex items-center gap-4">
                                <img src="<?php echo !empty($course['chemin_image']) ? '../' . htmlspecialchars($course['chemin_image']) : 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs='; ?>" alt="Aperçu de l'image" class="w-20 h-20 object-cover rounded-lg border border-slate-300 <?php if(empty($course['chemin_image'])) echo 'hidden'; ?>">
                                <input id="fichier_image" type="file" name="fichier_image" accept="image/png, image/jpeg, image/gif" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Laissez ce champ vide pour ne pas changer l'image.</p>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-secondary-local mb-2">Description</label>
                        <textarea id="description" name="description" rows="4" class="w-full border-2 border-slate-200 rounded-lg p-3"><?php echo htmlspecialchars($course['description']); ?></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <div>
                            <label for="categorie_id" class="block text-sm font-semibold text-secondary-local mb-2">Catégorie</label>
                            <select id="categorie_id" name="categorie_id" required class="w-full border-2 border-slate-200 rounded-lg p-3 bg-white">
                                <?php foreach($categories_list as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php if($cat['id'] == $course['categorie_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($cat['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                         </div>
                         <div>
                            <label for="niveau_id" class="block text-sm font-semibold text-secondary-local mb-2">Niveau requis</label>
                            <select id="niveau_id" name="niveau_id" required class="w-full border-2 border-slate-200 rounded-lg p-3 bg-white">
                                <?php foreach($niveaux_list as $niv): ?>
                                    <option value="<?php echo $niv['id']; ?>" <?php if($niv['id'] == $course['niveau_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($niv['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                         </div>
                    </div>

                    <!-- DUREE & PRIX -->
                    <div class="border-t border-slate-200 pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="duree" class="block text-sm font-semibold text-secondary-local mb-2">Durée</label>
                            <input id="duree" type="text" name="duree" value="<?php echo htmlspecialchars($course['duree'] ?? ''); ?>" placeholder="Ex: 15 heures" class="w-full border-2 border-slate-200 rounded-lg p-3">
                        </div>
                        <div>
                            <label for="prix" class="block text-sm font-semibold text-secondary-local mb-2">Prix (MGA)</label>
                            <input id="prix" type="number" name="prix" value="<?php echo htmlspecialchars($course['prix'] ?? 0); ?>" step="1" min="0" class="w-full border-2 border-slate-200 rounded-lg p-3">
                        </div>
                    </div>

                    <!-- FORMATEUR-->
                    <div class="border-t border-slate-200 pt-6">
                        <h3 class="text-xl font-bold mb-4 text-slate-700">Informations du formateur</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Image_formateur -->
                            <div>
                                <label class="block text-sm font-semibold text-secondary-local mb-2">Photo actuelle du formateur</label>
                                <div class="flex items-center gap-4 mb-3">
                                    <img src="<?php echo !empty($course['chemin_image_formateur']) ? '../' . htmlspecialchars($course['chemin_image_formateur']) : '../assets/img/incognito.png'; ?>" alt="Photo formateur" class="w-15 h-13 rounded-full object-cover border border-slate-300">
                                </div>
                                <label for="formateur_image" class="block text-sm font-semibold text-secondary-local mb-2">Changer la photo</label>
                                <input id="formateur_image" type="file" name="formateur_image" accept="image/png, image/jpeg" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="text-xs text-slate-500 mt-2">Laissez vide pour ne pas changer.</p>
                            </div>

                            <!-- Nom_formateur -->
                            <div>
                                <label for="formateur_nom" class="block text-sm font-semibold text-secondary-local mb-2">Nom du formateur</label>
                                <input id="formateur_nom" type="text" name="formateur_nom" value="<?php echo htmlspecialchars($course['nom_formateur'] ?? ''); ?>" placeholder="Ex: Jean Dupont" class="w-full border-2 border-slate-200 rounded-lg p-3">
                            </div>

                            <!-- Email_formateur -->
                            <div>
                                <label for="formateur_email" class="block text-sm font-semibold text-secondary-local mb-2">Email du formateur</label>
                                <input id="formateur_email" type="email" name="formateur_email" value="<?php echo htmlspecialchars($course['email_formateur'] ?? ''); ?>" placeholder="Ex: jean.dupont@exemple.com" required class="w-full border-2 border-slate-200 rounded-lg p-3">
                            </div>
                        </div>
                    </div>


                    <div class="pt-6 border-t border-slate-200">
                        <button type="submit" class="w-full text-lg bg-primary-local text-white font-bold py-4 rounded-lg hover:bg-primary-hover"><i class="fa-solid fa-save mr-2"></i> Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>