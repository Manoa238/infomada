<?php
session_start();
require '../include/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login_user.php');
    exit();
}

$user = null;
$profile_image_path = '../assets/img/incognito.png'; 

try {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, datenais, profile_image FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) { die("Erreur critique : Utilisateur introuvable."); }
    if (!empty($user['profile_image'])) { $profile_image_path = '../uploads/' . htmlspecialchars($user['profile_image']); }

} catch (PDOException $e) { die("Erreur de connexion à la base de données : " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - INFOMADA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- QR Code du profil_user -->
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
</head>
<body class="bg-slate-100 py-10 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-4xl">
        <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
            
            <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Mon Profil</h1>
            <div id="display-view">
                <div class="flex flex-col md:flex-row items-start gap-8 border-b pb-8">
                    
                    <div class="w-full md:w-2/3 flex items-center gap-6">
                        <div class="flex-shrink-0">
                            <img src="<?php echo $profile_image_path; ?>" alt="Photo de profil" class="w-32 h-32 rounded-full object-cover border-4 border-slate-200">
                        </div>
                        <div class="text-left w-full">
                            <p class="text-lg mb-2">
                                <strong class="font-semibold text-gray-600 w-32 inline-block">Nom :</strong> 
                                <?php echo htmlspecialchars($user['last_name']); ?>
                            
                            </p>
                            <p class="text-lg mb-2">
                                <strong class="font-semibold text-gray-600 w-32 inline-block">Prénom :</strong>
                                <?php echo htmlspecialchars($user['first_name']); ?>
                            </p>
                            <p class="text-lg mb-2">
                                <strong class="font-semibold text-gray-600 w-32 inline-block">Email :</strong> 
                                <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                            <p class="text-lg">
                                <strong class="font-semibold text-gray-600 w-32 inline-block">Né(e) le :</strong> 
                                <?php echo htmlspecialchars($user['datenais']); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-1/3 flex flex-col items-center pt-4 md:pt-0">
                        <h3 class="font-semibold mb-2 text-gray-600">Partagez votre profil</h3>
                        <div id="qrcode-container" class="p-2 border rounded-lg inline-block"></div>
                    </div>
                </div>

                <div class="text-center mt-8">
                    <button id="edit-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg text-lg">
                        Modifier le profil
                    </button>
                </div>
            </div>

            <!-- VUE -->
            <div id="edit-view" class="hidden">
                    <h1 class="text-2xl font-bold mb-6 text-center">Modifier le Profil</h1>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-1 text-center">
                            <img src="<?php echo $profile_image_path; ?>" alt="Photo de profil" class="w-32 h-32 rounded-full mx-auto object-cover border-2 border-slate-200">
                            <div class="mt-4 space-y-2">
                                <button onclick="document.getElementById('file-input').click();" class="w-full bg-sky-500 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded text-sm">Changer la photo</button>
                                <?php if (!empty($user['profile_image'])): ?>
                                    <a href="../action/delete_photo.php" onclick="return confirm('Êtes-vous sûr de vouloir supprimer votre photo ?');" class="w-full inline-block bg-sky-700 hover:bg-sky-800 text-white font-bold py-2 px-4 rounded text-sm">Supprimer</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <form action="../action/update_profil.php" method="post">
                            <div class="mb-3">
                                <label class="block text-gray-700 text-sm font-bold mb-1" for="first_name">Prénom</label>
                                <input class="shadow border rounded w-full py-2 px-3" id="first_name" name="first_name" type="text" 
                                value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="block text-gray-700 text-sm font-bold mb-1" for="last_name">Nom</label>
                                <input class="shadow border rounded w-full py-2 px-3" id="last_name" name="last_name" type="text" 
                                value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="block text-gray-700 text-sm font-bold mb-1" for="email">Email</label>
                                <input class="shadow border rounded w-full py-2 px-3" id="email" name="email" type="email" 
                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="mb-5">
                                <label class="block text-gray-700 text-sm font-bold mb-1" for="datenais">Date de naissance</label>
                                <input class="shadow border rounded w-full py-2 px-3" id="datenais" name="datenais" type="date" 
                                value="<?php echo htmlspecialchars($user['datenais']); ?>" required>
                            </div>
                            <div class="flex items-center justify-between">
                                <button class="bg-sky-400 hover:bg-sky-400 text-sky-800 font-bold py-2 px-4 rounded" type="submit">Enregistrer</button>
                                <button type="button" id="cancel-btn" class="font-bold text-sm text-gray-500 hover:text-gray-800">Annuler</button>
                            </div>
                        </form>
                        <form action="../action/upload.php" method="post" enctype="multipart/form-data" id="form-upload" class="hidden">
                            <input type="file" name="profile_image" id="file-input" accept="image/jpeg, image/png" onchange="document.getElementById('form-upload').submit();">
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <script>
        // basculer les vues
        const displayView = document.getElementById('display-view');
        const editView = document.getElementById('edit-view');
        const editBtn = document.getElementById('edit-btn');
        const cancelBtn = document.getElementById('cancel-btn');
        editBtn.addEventListener('click', () => { displayView.classList.add('hidden'); editView.classList.remove('hidden'); });
        cancelBtn.addEventListener('click', () => { editView.classList.add('hidden'); displayView.classList.remove('hidden'); });

        // Générer QR Code
        const userId = <?php echo json_encode($user['id']); ?>;
        // URL du site
        const profileUrl = `http://localhost/INFOMADA/front_office/public_profile.php?id=${userId}`;
        new QRCode(document.getElementById("qrcode-container"), { text: profileUrl, width: 120, height: 120 });
        
    </script>
</body>
</html>