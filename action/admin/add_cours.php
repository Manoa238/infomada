<?php
session_start();
require_once '../../include/config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $categorie_id = filter_input(INPUT_POST, 'categorie_id', FILTER_VALIDATE_INT);
    $niveau_id = filter_input(INPUT_POST, 'niveau_id', FILTER_VALIDATE_INT);
    $duree = trim($_POST['duree']) ?: null;
    $prix = filter_input(INPUT_POST, 'prix', FILTER_VALIDATE_INT);

    $formateur_nom = trim($_POST['formateur_nom']) ?: null;
    $formateur_email = trim($_POST['formateur_email']) ?: null;

    // Vérifier champs obligatoires
    if (empty($titre) || !$categorie_id || !$niveau_id || $prix === false || empty($formateur_email)) {
        $_SESSION['error_message'] = "Veuillez remplir tous les champs obligatoires du cours et du formateur (titre, catégorie, niveau, prix, email du formateur).";
        header('Location: ../../back_office/admin_page_formation.php'); 
        exit();
    }

    $chemin_image_cours = null;
    $nom_original_image_cours = null;
    $chemin_image_formateur = null;

    // Upload image couverture du cours
    if (isset($_FILES['fichier_image']) && $_FILES['fichier_image']['error'] == UPLOAD_ERR_OK) {
        $dossier_upload_image = '../../uploads/images/'; // Chemin image
        if (!is_dir($dossier_upload_image)) {
            mkdir($dossier_upload_image, 0777, true); // Crée dossier si inexistant
        }
        $nom_original_image_cours = basename($_FILES['fichier_image']['name']);
        $extension = strtolower(pathinfo($nom_original_image_cours, PATHINFO_EXTENSION));
        // Liste blanche extensions 
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $nom_unique = 'cours_' . uniqid() . '.' . $extension;
            $destination_chemin = $dossier_upload_image . $nom_unique;
            if (move_uploaded_file($_FILES['fichier_image']['tmp_name'], $destination_chemin)) {
                $chemin_image_cours = 'uploads/images/' . $nom_unique; // Chemin stocker en BD
            } else {
                $_SESSION['error_message'] = "Erreur lors du déplacement de l'image du cours.";
                header('Location: ../../back_office/admin_page_formation.php'); 
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Format d'image de cours non autorisé. Seuls JPG, JPEG, PNG, GIF sont acceptés.";
            header('Location: ../../back_office/admin_page_formation.php'); 
            exit();
        }
    }

    // Upload image formateur
    if (isset($_FILES['formateur_image']) && $_FILES['formateur_image']['error'] == UPLOAD_ERR_OK) {
        $dossier_upload_formateur = '../../uploads/formateurs/'; // Chemin pour l'image
        if (!is_dir($dossier_upload_formateur)) {
            mkdir($dossier_upload_formateur, 0777, true);
        }
        $nom_original_formateur = basename($_FILES['formateur_image']['name']);
        $extension = strtolower(pathinfo($nom_original_formateur, PATHINFO_EXTENSION));
        // Liste blanche extensions
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $nom_unique = 'formateur_' . uniqid() . '.' . $extension;
            $destination_chemin = $dossier_upload_formateur . $nom_unique;
            if (move_uploaded_file($_FILES['formateur_image']['tmp_name'], $destination_chemin)) {
                $chemin_image_formateur = 'uploads/formateurs/' . $nom_unique; // Chemin stocker en BD
            } else {
                $_SESSION['error_message'] = "Erreur lors du déplacement de l'image du formateur.";
                header('Location: ../../back_office/admin_page_formation.php'); 
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Format d'image de formateur non autorisé. Seuls JPG, JPEG, PNG, GIF sont acceptés.";
            header('Location: ../../back_office/admin_page_formation.php'); 
            exit();
        }
    }

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $conn = new PDO($dsn, $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $formateur_id = null;
        // Formateur_email existe
        $stmt = $conn->prepare("SELECT id_formateur, nom_formateur, image_formateur FROM formateurs WHERE email_formateur = :email");
        $stmt->execute([':email' => $formateur_email]);
        $formateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($formateur) {
            $formateur_id = $formateur['id_formateur'];
            // Formateur existe, MàJ nom et image si nouvelle image fournie
            if ($formateur_nom !== $formateur['nom_formateur'] || ($chemin_image_formateur && $chemin_image_formateur !== $formateur['image_formateur'])) {
                $update_sql = "UPDATE formateurs SET nom_formateur = :nom, image_formateur = :image WHERE id_formateur = :id";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->execute([
                    ':nom' => $formateur_nom,
                    ':image' => $chemin_image_formateur ?: $formateur['image_formateur'], // Conserve ancienne image si aucune nouvelle n'est uploadée
                    ':id' => $formateur_id
                ]);
            }
        } else {
            // formateur = 0
            $stmt = $conn->prepare("INSERT INTO formateurs (nom_formateur, email_formateur, image_formateur) 
                                    VALUES (:nom, :email, :image) RETURNING id_formateur");
            $stmt->execute([
                ':nom' => $formateur_nom,
                ':email' => $formateur_email,
                ':image' => $chemin_image_formateur // Peut être null si aucune image n'a été uploadée
            ]);
            $formateur_id = $stmt->fetchColumn(); // Récupère l'ID du nouveau formateur
        }

        // IREQUETE INSERT cours
        $sql = "INSERT INTO cours (
                    titre, description, categorie_id, niveau_id,
                    chemin_image, nom_original_image,
                    duree, prix, id_formateur, date_creation
                ) VALUES (
                    :titre, :description, :categorie_id, :niveau_id,
                    :chemin_image, :nom_original_image,
                    :duree, :prix, :id_formateur, NOW()
                )"; // NOW() pour date_creation
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':titre' => $titre,
            ':description' => $description,
            ':categorie_id' => $categorie_id,
            ':niveau_id' => $niveau_id,
            ':chemin_image' => $chemin_image_cours,
            ':nom_original_image' => $nom_original_image_cours,
            ':duree' => $duree,
            ':prix' => $prix,
            ':id_formateur' => $formateur_id 
        ]);

        $_SESSION['success_message'] = "Cours ajouté avec succès ! Le formateur a été géré.";
        header('Location: ../../back_office/admin_page_formation.php'); 
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur de base de données : " . $e->getMessage();
        header('Location: ../../back_office/admin_page_formation.php'); 
        exit();
    } finally {
        $conn = null; 
    }
} else {
    header('Location: ../../back_office/admin_page_formation.php'); 
    exit();
}
?>