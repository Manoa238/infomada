<?php
session_start();

if (!isset($_SESSION['otp_email_verification'], $_SESSION['otp_user_id'], $_SESSION['otp_user_name'])) {
    header('Location: login_user.php');
    exit();
}

$error_message = '';

// TRAITEMENT FORMULAIRE OTP 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submitted_otp = $_POST['otp_code'] ?? '';

    // Code soumis = 1 ET pas expiré
    if (isset($_SESSION['otp_code']) && $submitted_otp == $_SESSION['otp_code']) {
        if (time() < $_SESSION['otp_expire']) {
            // User authentifié
            
            // User connecté
            $_SESSION['user_loggedin'] = true;
            $_SESSION['user_id'] = $_SESSION['otp_user_id'];
            $_SESSION['user_email'] = $_SESSION['otp_email_verification'];
            $_SESSION['user_name'] = $_SESSION['otp_user_name'];

            // Effacer variables temporaires session
            unset($_SESSION['otp_code'], $_SESSION['otp_expire'], $_SESSION['otp_email_verification'], $_SESSION['otp_user_id'], $_SESSION['otp_user_name']);

            header('Location: ../index.php'); 
            exit();

        } else {
            // Code expiré
            $_SESSION['error_message'] = 'Le code a expiré. Veuillez vous reconnecter.';
            header('Location: login_user.php');
            exit();
        }
    } else {
        $error_message = 'Le code de sécurité est incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du code</title>
    <script src="../assets/js/Tailwind.js"></script>
    <link rel="stylesheet" href="../assets/css/contact.css">
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">

    <div class="bg-white py-10 px-[30px] rounded-lg shadow-xl w-[420px] border-2 text-center bordure-conteneur-animee">
        <h1 class="mb-4 text-2xl font-bold text-primary-local">Validation de l'accès requise</h1>
        <p class="mb-6 text-sm leading-normal text-gray-600">
            Un code de sécurité a été envoyé à l'adresse e-mail :<br>
            <strong><?php echo htmlspecialchars($_SESSION['otp_email_verification']); ?></strong>
        </p>
        <form action="verify_otp.php" method="POST" class="w-full">
            <div>
                <input 
                    type="text" 
                    name="otp_code" 
                    placeholder="------" 
                    required 
                    maxlength="6" 
                    autofocus
                    class="w-4/5 p-2 mx-auto text-2xl text-center tracking-[10px] border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                >
            </div>
            <div class="mt-4 text-sm text-red-600 h-[20px]">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        
            <button type="submit" class="mt-5 w-full bg-primary-local text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300">
                Vérifier et se connecter
            </button>
        </form>
    </div>
</body>
</html>