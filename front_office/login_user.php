<?php
// Import classes librairie PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fichiers PHPMailer
require '../lib/PHPMailer/Exception.php';
require '../lib/PHPMailer/PHPMailer.php';
require '../lib/PHPMailer/SMTP.php';

// Démarre session 
session_start();

// Messages d'erreur
$error_message = '';

// Email & mpd existe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['password'])) {

    // Connexion à BD
    require '../include/config.php'; 

    // Récupèrer données formulaire
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Requête SQL 
        $stmt = $pdo->prepare("SELECT id, password, first_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // User & Mdp = true
        if ($user && password_verify($password, $user['password'])) {
            
            // random
            $otp_code = rand(100000, 999999);

            // Stock toutes informations nécessaires temporaire
            $_SESSION['otp_code'] = $otp_code;
            $_SESSION['otp_email_verification'] = $email;
            $_SESSION['otp_expire'] = time() + 300; // Code expiré dans 5 minutes
            $_SESSION['otp_user_id'] = $user['id']; // AJOUTE ID
            $_SESSION['otp_user_name'] = $user['first_name']; // AJOUT PRÉNOM

            // Envoi d'email avec PHPMailer 
            $mail = new PHPMailer(true);
            try {
                // Paramètres du serveur SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'projetinfo423@gmail.com'; 
                $mail->Password   = 'ytuqvsytxrjvzvhz'; // MDP application
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                // Destinataires
                $mail->setFrom('projetinfo423@gmail.com', 'Plateforme de Formation'); 
                $mail->addAddress($email);

                // Contenu de l'email
                $mail->isHTML(true);
                $mail->Subject = 'Votre code de connexion dans la plateforme de formation INFOMADA';
                $mail->Body    = "Bonjour, <br><br>Votre code de sécurité est : <b>$otp_code</b>";
                
                $mail->send();
                
                header('Location: verify_otp.php');
                exit();

            } catch (Exception $e) {
                $error_message = "Le code n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}";
            }

        } else {
            $error_message = 'Adresse email ou mot de passe incorrect.';
        }
    } catch (PDOException $e) {
        $error_message = "Erreur de service. Veuillez réessayer plus tard.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" >
    <link rel="stylesheet" href="../assets/css/login_user.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <!-- Date de naissance -->
    <link rel="stylesheet" href="../assets/css/flatpickr.min.css">
    <title>Connexion / Inscription</title>
    <style>
        .error-message { color: red; font-size: 14px; text-align: center; height: auto; min-height:20px; margin-bottom: 5px; }
    </style>
</head>
<body>

    <div class="container" id="container">
        <!-- SIGN UP -->
        <div class="form-container sign-up">
            <form action="../action/register.php" method="POST">
                <h1>Inscription</h1>
                <div class="row">
                    <div class="name-row">
                        <div class="input-group">
                            <label for="signup-name">Nom</label>
                            <input type="text" id="signup-name" name="last_name" placeholder="RAVO" required>
                        </div>
                        <div class="input-group">
                            <label for="signup-firstname">Prénom</label>
                            <input type="text" id="signup-firstname" name="first_name" placeholder="Manoa" required>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="signup-email">Email</label>
                        <input type="email" id="signup-email" name="email" placeholder="ravomanoa3@gmail.com" required>
                    </div>
                    <div class="input-group">
                        <label for="calendrier-naissance">Date de naissance</label>
                        <input type="text" id="calendrier-naissance" name="datenais" placeholder="Sélectionnez une date..." required>
                    </div>
                    <div class="input-group">
                        <label for="signup-password">Mot de passe</label>
                        <div class="password-container">
                            <input id="signup-password" name="password" type="password" placeholder="••••••••" required>
                            <i class="fa-solid fa-eye-slash toggle-password"></i>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="signup-password-confirm">Confirmer votre mot de passe</label>
                        <div class="password-container">
                            <input id="signup-password-confirm" name="password_confirmation" type="password" placeholder="••••••••" required>
                            <i class="fa-solid fa-eye-slash toggle-password"></i>
                        </div>
                    </div>
                </div>
                <button type="submit">S'inscrire</button>
                <!-- <div class="divider"><hr><span>ou</span><hr></div>
                <a href="#" class="social-login-btn facebook">
                    <i class="fab fa-facebook-f"></i><span>Continuer avec Facebook</span>
                </a>    -->
            </form>
        </div>

        <!-- LOGIN -->
        <div class="form-container sign-in">
            <form action="login_user.php" method="POST">
                <h1 class="connect">Se connecter</h1>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <div class="input-group">
                    <label for="signin-email">Adresse Email</label>
                    <input type="email" id="signin-email" name="email" placeholder="ravomanoa3@gmail.com" required>
                </div>
                <div class="input-group">
                    <label for="signin-password">Mot de passe</label>
                    <div class="password-container"><input type="password" id="signin-password" name="password" placeholder="••••••••" required><i class="fa-solid fa-eye-slash toggle-password"></i>
                </div>
            </div>
                <a href="#">Vous avez oublié votre mot de passe ?</a>
                <button type="submit">Se connecter</button>
                <!-- <div class="divider"><hr><span>ou</span><hr></div>
                <a href="#" class="social-login-btn facebook">
                    <i class="fab fa-facebook-f"></i><span>Continuer avec Facebook</span>
                </a> -->
            </form>
        </div>

        <!-- TOGGLE CONTAINER -->
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Te revoilà !</h1>
                    <p>Veuillez entrer les informations de votre compte pour vous connecter.</p>
                    <button class="hidden" id="login">Se connecter</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Bonjour !</h1>
                    <p>Créez un compte avec vos informations personnelles pour profiter de toutes nos fonctionnalités.</p>
                    <button class="hidden" id="register">S'inscrire</button>
                </div>
            </div>
        </div>
    </div>

    
    <script src="../assets/js/login_user.js"></script>
    <script src="../assets/js/flatpickr.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#calendrier-naissance", { dateFormat: "Y-m-d" });
      });
    </script>
</body>
</html>