<?php
session_start();
$error = '';
if($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST['code'])){
    if($_POST['code'] === $_SESSION['otp']){
        $_SESSION['auth_formateur'] = true;
        header("Location: formateur_page.php");
        exit;
    }else{
        $error = "Code incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vérification du Formateur</title>
<script src="../../assets/js/Tailwind.js"></script>
<link rel="stylesheet" href="../../assets/css/index.css">
</head>
<body class="h-screen flex items-center justify-center bg-gray-100">

<div class="bg-white py-8 px-6 rounded-lg shadow-xl w-[460px] border bordure-conteneur-animee">
<h1 class="mb-4 text-2xl font-bold text-primary-local text-center">Validation Formateur</h1>
<p class="mb-6 text-sm text-gray-600 text-center">
    Vérifiez votre email et entrez le code reçu pour accéder à votre espace.
</p>

<form method="post" class="space-y-4">
    <input type="text" name="code" placeholder="--------" required maxlength="8"
           class="w-full p-3 border text-center text-lg tracking-widest border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
    <button type="submit"
            class="w-full bg-primary-local text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
        Vérifier le code
    </button>
</form>

<?php if(!empty($error)) : ?>
<p class="mt-4 text-red-600 text-sm text-center"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

</div>
</body>
</html>
