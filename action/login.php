<?php
session_start();

$host = 'localhost';
$dbname = 'infomada'; 
$user = 'postgres';
$password = 'ravomanoa'; 

$dsn = "pgsql:host=$host;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $user, $password);

    // Récupération formulaire
    $email = $_POST['email'];
    $passwordInput = $_POST['password'];

    // Email existe
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($passwordInput, $user['password'])) {
        // Authentification réussie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_birth'] = $user['datenais']; // QR code

        header("Location: ../index.php");
        exit();
    } else {
        // Erreur de connexion
        echo "<script>
            alert('Email ou mot de passe incorrect.');
            window.location.href = '../front_office/login_user.html';
        </script>";
    }

} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
}
?>
