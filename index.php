<?php
session_start();
require_once 'include/config.php';

$liste_cours = [];
$db_error = '';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->query("SELECT id, titre, description, chemin_image FROM public.cours ORDER BY date_creation DESC LIMIT 10");
    $liste_cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $db_error = "Erreur de connexion à la base de données. Impossible d'afficher les cours.";
} finally {
    $conn = null;
}
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth"> 
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>INFOMADA</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/index.css">
  <link rel="stylesheet" href="assets/css/all.min.css">
  <script src="assets/js/Tailwind.js"></script> 

  <style>
    header.scrolled { background-color: rgba(0, 64, 128, 0.85); backdrop-filter: blur(10px); padding-top: 1rem; padding-bottom: 1rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); }
    .profil-menu { position: relative; display: inline-block; }
    .profil-menu .username { cursor: pointer; background-color: rgba(255, 255, 255, 0.1); padding: 0.5rem 1rem; border-radius: 9999px; font-weight: 600; transition: background-color 0.2s; }
    .profil-menu:hover .username { background-color: rgba(255, 255, 255, 0.2); }
    .profil-menu .dropdown-content { display: none; position: absolute; right: 0; background-color: white; min-width: 220px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); z-index: 1; border-radius: 0.5rem; padding: 1rem; margin-top: 0.5rem; color: #333; text-align: center; }
    .profil-menu:hover .dropdown-content { display: block; }
    .dropdown-content img { border-radius: 50%; margin: 0 auto 1rem auto; border: 3px solid #004080; }
    .dropdown-content #qrcode { margin: 1rem auto; }
    .dropdown-content a { color: #004080; padding: 10px 12px; text-decoration: none; display: block; font-weight: 500; border-radius: 0.25rem; transition: background-color 0.2s, color 0.2s; }
    .dropdown-content a:hover { background-color: #f1f5f9; color: #0c4a6e; }
  </style>
</head>
<body class="bg-slate-50 font-sans text-slate-700 antialiased">

  <!-- HEADER -->
  <header id="site-header" class="bg-primary-local sticky top-0 z-50 text-white px-4 sm:px-6 lg:px-8 py-10 transition-all duration-300">
    <div class="container mx-auto flex justify-between items-center">
      <h2 class="font-bold text-2xl tracking-tight">INFOMADA</h2>
      <nav class="hidden md:flex items-center gap-6">
        <a href='#'><span class="text-white font-semibold border-b-2 border-sky-400 pb-1">Accueil</span></a>
        <a href="#cours" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200">Cours</a>
        <a href="front_office/contact.php" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200">Contact</a>
        <a href="front_office/about.php" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200">A propos</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="front_office/mes_cours.php" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200 font-semibold">Mes Cours</a>
        <?php endif; ?>

        <div class="relative">
            <!-- BARRE DE RECHERCHE -->
            <input type="search" placeholder="Rechercher..." class="bg-primary-darker-local rounded-full pl-10 pr-4 py-2 text-sm text-white placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-400 transition-all duration-300 w-48 focus:w-56" />
            <svg class="w-5 h-5 text-slate-300 absolute -mt-4 left-3 -translate-y-1/2" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27a6.471 6.471 0 001.48-5.34C15.17 6.01 12.16 3 8.58 3S2 6.01 2 9.58 5.01 16.17 8.58 16.17c1.61 0 3.09-.59 4.22-1.57l.27.28v.79l4.25 4.25c.39.39 1.02.39 1.41 0s.39-1.02 0-1.41L15.5 14zM8.58 14c-2.44 0-4.42-1.99-4.42-4.42S6.14 5.17 8.58 5.17 13 7.16 13 9.58 11.02 14 8.58 14z"></path></svg>
        </div>

        <!-- USER SESSION -->
        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="profil-menu">
            <span class="username"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <div class="dropdown-content">
              <?php
                $profile_pic = 'uploads/' . htmlspecialchars($_SESSION['user_id']) . '.jpg';
                if (!file_exists($profile_pic)) { $profile_pic = 'assets/img/incognito.png'; }
              ?>
              <img src="<?php echo $profile_pic; ?>?v=<?php echo time(); ?>" alt="Photo de profil" width="80" height="80" style="object-fit: cover;">
              <a href="front_office/profil.php">Modifier le profil</a>
              <!-- <a href="front_office/voir_certificat.php">Voir les certificats</a>  -->
              <a href="action/logout.php">Déconnexion</a>
            </div>
          </div>
        <?php else: ?>
          <a href="front_office/login_user.php" class="bg-white text-primary-local font-semibold px-5 py-2 rounded-md hover:bg-slate-200 hover:-translate-y-0.5 transition-all duration-200">Connexion</a>
        <?php endif; ?>
        
      </nav>
      <button class="md:hidden text-white"><i class="fas fa-bars text-2xl"></i></button>
    </div>
  </header>

  <main>
  <section class="bg-primary-local text-white">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-10 grid md:grid-cols-2 items-center gap-12">
        <div class="hero-content text-center md:text-left">
          <h1 class="text-4xl lg:text-5xl font-extrabold leading-tight tracking-tight">La solution à vos défis informatiques</h1>
          <p class="mt-4 text-lg text-slate-200 max-w-lg mx-auto md:mx-0">Débloquez votre potentiel grâce à des formations certifiantes, accessibles à tout moment.</p>
          <a href="#cours" class="mt-8 inline-block bg-white text-primary-local font-bold px-8 py-3 rounded-lg shadow-lg hover:bg-slate-200 transform hover:-translate-y-1 transition-all duration-300">
            Explorer les cours
          </a>
        </div>
        <div class="hero-image">
          <img src="assets/img/learning.png" alt="Formation en ligne informatique" class="w-full max-w-md mx-auto">
        </div>
      </div>
    </section>
    <?php require 'front_office/cours.php'; ?>
  </main>
  <footer class="bg-primary-local text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-8">
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div class="md:col-span-2 lg:col-span-1">
          <h2 class="text-white font-bold text-xl mb-4">INFOMADA</h2>
          <p class="text-sm leading-relaxed">Une plateforme de formation moderne et certifiée, pensée pour révéler votre plein potentiel.</p>
          <blockquote class="mt-4 border-l-4 border-slate-700 pl-4 text-sm text-slate-400 italic">“La solution à vos défis informatiques.”</blockquote>
        </div>
        <div class="lg:pl-12">
          <h4 class="text-white font-semibold mb-4 tracking-wide uppercase text-sm">Navigation</h4>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="hover:text-white hover:pl-2 transition-all duration-200">🏠 Accueil</a></li>
            <li><a href="#cours" class="hover:text-white hover:pl-2 transition-all duration-200">📘 Formations</a></li>
            <li><a href="#" class="hover:text-white hover:pl-2 transition-all duration-200">🎓 Certificats</a></li>
            <li><a href="front_office/contact.php" class="hover:text-white hover:pl-2 transition-all duration-200">📨 Contact</a></li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-4 tracking-wide uppercase text-sm">Contact</h4>
          <ul class="space-y-2 text-sm">
            <li class="flex items-start"> <i class="fas fa-phone mt-1 mr-3 text-slate-400"></i> <span>+261 34 45 039 43</span> </li>
            <li class="flex items-start"> <i class="fas fa-map-marker-alt mt-1 mr-3 text-slate-400"></i> <span>Anosizato, Antananarivo, Madagascar</span> </li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-4 tracking-wide uppercase text-sm">Suivez-nous</h4>
          <div class="flex space-x-4">
            <a href="https://web.facebook.com/infomadamdg" target="_blank" rel="noopener noreferrer" class="text-xl hover:text-sky-400 transform hover:scale-110 transition-all duration-200"> <i class="fab fa-facebook-f"></i> </a>
            <a href="https://wa.me/261344503943" target="_blank" class="text-xl hover:text-green-400 transform hover:scale-110 transition-all duration-200"> <i class="fab fa-whatsapp"></i> </a>
          </div>
        </div>
      </div>
      <div class="mt-0.5 pt-1 border-t border-slate-800 text-center text-slate-500 text-sm">
        <p>© 2025 INFOMADA — Tous droits réservés.</p>
      </div>
    </div>
  </footer>

  <script src="assets/js/script.js"></script>
</body>
</html>