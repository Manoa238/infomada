<?php
// Connexion à PostgreSQL
$host = 'localhost';
$dbname = 'infomada';
$user = 'postgres';
$password = 'ravomanoa';
$dsn = "pgsql:host=$host;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $user, $password);

    // Récupération données du formulaire
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $email = $_POST['email'];
    $datenais = $_POST['datenais'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Sécurité
    $password_confirmation = $_POST['password_confirmation'];

    // Vérification mot de passe confirmé
    if (!password_verify($_POST['password_confirmation'], $password)) {
        die("Les mots de passe ne correspondent pas.");
    }

    // REQUETE SQL Insert
    $sql = "INSERT INTO users (last_name, first_name, email, datenais, password) 
            VALUES (:last_name, :first_name, :email, :datenais, :password)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':last_name' => $last_name,
        ':first_name' => $first_name,
        ':email' => $email,
        ':datenais' => $datenais,
        ':password' => $password
    ]);

    header("Location: ../front_office/registration_success.html");
    exit();

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
