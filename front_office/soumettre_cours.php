<?php
session_start();
require_once '../include/config.php';

// User non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login_user.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$cours_id = null;
$score = 0;
$total_points_examen = 0;
$resultat_message = '';
$grade = '';
$db_error = ''; // Gestion erreurs BD

// Requête=POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer ID_cours
    if (isset($_POST['cours_id']) && filter_var($_POST['cours_id'], FILTER_VALIDATE_INT)) {
        $cours_id = (int) $_POST['cours_id'];
    } else {
        $_SESSION['error_message'] = "ID de cours manquant ou invalide.";
        header('Location: mes_cours.php');
        exit();
    }

    // Chemin du fichier JSON d'examen
    $examen_cours_json_file = '../uploads/examens_json/examen_cours_' . $cours_id . '.json';

    if (file_exists($examen_cours_json_file)) {
        $json_content = file_get_contents($examen_cours_json_file);
        $examen_questions = json_decode($json_content, true);

        if (json_last_error() === JSON_ERROR_NONE && !empty($examen_questions)) {
            $user_reponses = $_POST['reponses'] ?? [];

            foreach ($examen_questions as $index => $question_data) {
                $total_points_examen += ($question_data['points'] ?? 0); // total points 

                // Question a été répondue
                if (isset($user_reponses[$index])) {
                    $reponse_correcte = $question_data['answer'];
                    $reponse_utilisateur = $user_reponses[$index];

                    if (isset($question_data['multiple']) && $question_data['multiple']) {
                        // Questions à choix multiple mis dans tableaux
                        if (!is_array($reponse_utilisateur)) {
                            $reponse_utilisateur = [$reponse_utilisateur];
                        }
                        if (count(array_diff($reponse_correcte, $reponse_utilisateur)) === 0 &&
                            count(array_diff($reponse_utilisateur, $reponse_correcte)) === 0) {
                            $score += ($question_data['points'] ?? 0);
                        }
                    } else {
                        // Questions choix unique
                        if ($reponse_utilisateur === $reponse_correcte) {
                            $score += ($question_data['points'] ?? 0);
                        }
                    }
                }
            }

            // Calcul note/20
            $note_sur_20 = ($total_points_examen > 0) ? round(($score / $total_points_examen) * 20, 2) : 0;

            // Mention & statut du certificat
            if ($note_sur_20 < 10) {
                $grade = "Échec";
                $resultat_message = "Désolé, vous n'avez pas obtenu le certificat. Vous avez obtenu une note de " . $note_sur_20 . "/20. N'hésitez pas à réviser le cours et à retenter l'examen !";
            } elseif ($note_sur_20 >= 10 && $note_sur_20 < 12) {
                $grade = "Passable";
                $resultat_message = "Félicitations ! Vous avez obtenu le certificat avec une note de " . $note_sur_20 . "/20 (Passable).";
            } elseif ($note_sur_20 >= 12 && $note_sur_20 < 14) {
                $grade = "Assez bien";
                $resultat_message = "Félicitations ! Vous avez obtenu le certificat avec une note de " . $note_sur_20 . "/20 (Assez bien).";
            } elseif ($note_sur_20 >= 14 && $note_sur_20 < 16) {
                $grade = "Bien";
                $resultat_message = "Félicitations ! Vous avez obtenu le certificat avec une note de " . $note_sur_20 . "/20 (Bien).";
            } else { // >= 16
                $grade = "Très bien";
                $resultat_message = "Félicitations ! Vous avez obtenu le certificat avec une note de " . $note_sur_20 . "/20 (Très bien).";
            }

            // Afficher résultat
            $_SESSION['examen_resultat'] = [
                'score' => $score,
                'total_points_examen' => $total_points_examen,
                'note_sur_20' => $note_sur_20,
                'grade' => $grade,
                'message' => $resultat_message,
                'cours_id' => $cours_id // Lien_retour
            ];
            header('Location: resultat_examen.php');
            exit();

        } else {
            $_SESSION['error_message'] = "Erreur : Le fichier d'examen est vide ou invalide.";
        }
    } else {
        $_SESSION['error_message'] = "Erreur : L'examen pour ce cours est introuvable.";
    }
} else {
    $_SESSION['error_message'] = "Accès non autorisé à la page de soumission de l'examen.";
}

// Erreur
if ($cours_id) {
    header('Location: apprendre_cours.php?course_id=' . $cours_id);
} else {
    header('Location: mes_cours.php');
}
exit();
?>