<?php
session_start();
require_once '../../include/config.php';
require '../../lib/PHPMailer/PHPMailer.php';
require '../../lib/PHPMailer/SMTP.php';
require '../../lib/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(!isset($_POST['email']) || empty($_SESSION['formateur_nom'])){
    echo "Erreur : Email ou session invalide.";
    exit;
}

//  Générer OTP alphanumérique 8 k
$otp = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
$_SESSION['otp'] = $otp;
$_SESSION['otp_expire'] = time() + 300; // valide 5 min

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'projetinfo423@gmail.com';
    $mail->Password   = 'ytuqvsytxrjvzvhz'; // MDP Application Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('projetinfo423@gmail.com', 'Formateur Infomada');
    $mail->addAddress($_POST['email'], $_SESSION['formateur_nom']);

    $mail->isHTML(true);
    $mail->Subject = "Code de vérification";
    $mail->Body    = "Bonjour {$_SESSION['formateur_nom']},<br><br>
                      Voici votre code de vérification : <b style='font-size:18px;'>$otp</b><br><br>
                      ⚠ Ce code est valide pendant <b>5 minutes</b>.";

    $mail->send();
    echo "ok";

} catch (Exception $e) {
    echo "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}";
}
