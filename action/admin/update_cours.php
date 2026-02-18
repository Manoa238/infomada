<?php
session_start();
require_once '../../include/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer données du formulaire
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $categorie_id = filter_input(INPUT_POST, 'categorie_id', FILTER_VALIDATE_INT);
    $niveau_id = filter_input(INPUT_POST, 'niveau_id', FILTER_VALIDATE_INT);
    $duree = trim($_POST['duree']);
    $prix = filter_input(INPUT_POST, 'prix', FILTER_VALIDATE_INT);
    $formateur_nom = trim($_POST['formateur_nom']);
    $formateur_email = trim($_POST['formateur_email']); // AJOUTÉ

    // Validation mise à jour
    if (!$course_id || empty($titre) || !$categorie_id || !$niveau_id || $prix === false || empty($formateur_email)) { // MODIFIÉ
        $_SESSION['error_message'] = "Données invalides. Veuillez vérifier le formulaire et l'email du formateur.";
        header('Location: ../../back_office/edit_cours.php?id=' . $course_id); // Correction du nom de fichier
        exit();
    }

    try {
        $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer toutes les données actuelles du cours pour connaître les anciens fichiers et l'ID du formateur
        $stmt = $conn->prepare("
            SELECT
                c.*,
                f.id_formateur,
                f.nom_formateur,
                f.email_formateur,
                f.image_formateur AS chemin_image_formateur_db
            FROM public.cours c
            LEFT JOIN public.formateurs f ON c.id_formateur = f.id_formateur
            WHERE c.id = :id
        ");
        $stmt->execute([':id' => $course_id]);
        $current_course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_course) {
            $_SESSION['error_message'] = "Cours introuvable.";
            header('Location: ../../back_office/admin_page_formation.php');
            exit();
        }

        // Initialisation des variables avec les valeurs actuelles
        $chemin_image_final = $current_course['chemin_image'];
        $nom_image_final = $current_course['nom_original_image'];
        $chemin_image_formateur_final = $current_course['chemin_image_formateur_db']; // Utiliser la valeur de la BD
        $formateur_id = $current_course['id_formateur']; // L'ID actuel du formateur

        // Gérer l'upload de la NOUVELLE IMAGE DE COUVERTURE
        if (isset($_FILES['fichier_image']) && $_FILES['fichier_image']['error'] == UPLOAD_ERR_OK) {
            // Supprimer l'ancienne image si elle existe
            if ($chemin_image_final && file_exists('../../' . $chemin_image_final)) {
                unlink('../../' . $chemin_image_final);
            }

            $dossier_upload_image = '../../uploads/images/';
            if (!is_dir($dossier_upload_image)) mkdir($dossier_upload_image, 0755, true);

            $nom_image_final = basename($_FILES['fichier_image']['name']);
            $extension = strtolower(pathinfo($nom_image_final, PATHINFO_EXTENSION));
            $nom_unique = 'cours_image_' . uniqid() . '.' . $extension;
            if (move_uploaded_file($_FILES['fichier_image']['tmp_name'], $dossier_upload_image . $nom_unique)) {
                $chemin_image_final = 'uploads/images/' . $nom_unique;
            }
        }

        // --- GESTION DU FORMATEUR (AJOUTÉE/MODIFIÉE) ---
        // Vérifier si le formateur existe déjà par email
        $stmt = $conn->prepare("SELECT id_formateur, image_formateur FROM formateurs WHERE email_formateur = :email");
        $stmt->execute([':email' => $formateur_email]);
        $existing_formateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_formateur) {
            $formateur_id = $existing_formateur['id_formateur'];
            // Si une nouvelle image de formateur est uploadée, mettre à jour l'image du formateur existant
            if (isset($_FILES['formateur_image']) && $_FILES['formateur_image']['error'] == UPLOAD_ERR_OK) {
                // Supprimer l'ancienne image si elle existe et est différente de la nouvelle
                if ($existing_formateur['image_formateur'] && file_exists('../../' . $existing_formateur['image_formateur'])) {
                    unlink('../../' . $existing_formateur['image_formateur']);
                }
                $dossier_upload_formateur = '../../uploads/formateurs/';
                if (!is_dir($dossier_upload_formateur)) mkdir($dossier_upload_formateur, 0755, true);
                $nom_original_formateur = basename($_FILES['formateur_image']['name']);
                $extension = strtolower(pathinfo($nom_original_formateur, PATHINFO_EXTENSION));
                $nom_unique_formateur = 'formateur_' . uniqid() . '.' . $extension;
                if (move_uploaded_file($_FILES['formateur_image']['tmp_name'], $dossier_upload_formateur . $nom_unique_formateur)) {
                    $chemin_image_formateur_final = 'uploads/formateurs/' . $nom_unique_formateur;
                    $stmt = $conn->prepare("UPDATE formateurs SET nom_formateur = :nom, image_formateur = :image WHERE id_formateur = :id");
                    $stmt->execute([
                        ':nom' => $formateur_nom,
                        ':image' => $chemin_image_formateur_final,
                        ':id' => $formateur_id
                    ]);
                } else {
                    // Si l'upload échoue, garder l'ancienne image mais mettre à jour le nom
                    $stmt = $conn->prepare("UPDATE formateurs SET nom_formateur = :nom WHERE id_formateur = :id");
                    $stmt->execute([
                        ':nom' => $formateur_nom,
                        ':id' => $formateur_id
                    ]);
                }
            } else {
                // Pas de nouvelle image, juste mettre à jour le nom si nécessaire
                $stmt = $conn->prepare("UPDATE formateurs SET nom_formateur = :nom WHERE id_formateur = :id");
                $stmt->execute([
                    ':nom' => $formateur_nom,
                    ':id' => $formateur_id
                ]);
                // Conserver l'image existante si aucune nouvelle n'est téléchargée
                $chemin_image_formateur_final = $existing_formateur['image_formateur'];
            }

        } else {
            // Créer un nouveau formateur
            $dossier_upload_formateur = '../../uploads/formateurs/';
            if (!is_dir($dossier_upload_formateur)) mkdir($dossier_upload_formateur, 0755, true);
            $chemin_image_formateur_new = null;

            if (isset($_FILES['formateur_image']) && $_FILES['formateur_image']['error'] == UPLOAD_ERR_OK) {
                $nom_original_formateur = basename($_FILES['formateur_image']['name']);
                $extension = strtolower(pathinfo($nom_original_formateur, PATHINFO_EXTENSION));
                $nom_unique_formateur = 'formateur_' . uniqid() . '.' . $extension;
                if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    move_uploaded_file($_FILES['formateur_image']['tmp_name'], $dossier_upload_formateur . $nom_unique_formateur);
                    $chemin_image_formateur_new = 'uploads/formateurs/' . $nom_unique_formateur;
                }
            }

            $stmt = $conn->prepare("INSERT INTO formateurs (nom_formateur, email_formateur, image_formateur)
                                    VALUES (:nom, :email, :image) RETURNING id_formateur");
            $stmt->execute([
                ':nom' => $formateur_nom,
                ':email' => $formateur_email,
                ':image' => $chemin_image_formateur_new
            ]);
            $formateur_id = $stmt->fetchColumn();
            $chemin_image_formateur_final = $chemin_image_formateur_new; // Mettre à jour pour l'insertion du cours
        }

        // Mise à jour de la table des cours
        $sql = "UPDATE public.cours SET
                    titre = :titre,
                    description = :description,
                    categorie_id = :categorie_id,
                    niveau_id = :niveau_id,
                    duree = :duree,
                    prix = :prix,
                    id_formateur = :id_formateur, -- AJOUTÉ OU MODIFIÉ
                    chemin_image = :chemin_image,
                    nom_original_image = :nom_original_image
                WHERE id = :id";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ':titre' => $titre,
            ':description' => $description,
            ':categorie_id' => $categorie_id,
            ':niveau_id' => $niveau_id,
            ':duree' => $duree,
            ':prix' => $prix,
            ':id_formateur' => $formateur_id, // UTILISE L'ID OBTENU CI-DESSUS
            ':chemin_image' => $chemin_image_final,
            ':nom_original_image' => $nom_image_final,
            ':id' => $course_id
        ]);

        $_SESSION['success_message'] = "Le cours et les informations du formateur ont été mis à jour avec succès.";
        header('Location: ../../back_office/admin_page_formation.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur lors de la mise à jour : " . $e->getMessage();
        header('Location: ../../back_office/edit_cours.php?id=' . $course_id); // Correction du nom de fichier
        exit();
    }
} else {
    header('Location: ../../back_office/admin_page_formation.php');
    exit();
}
?>