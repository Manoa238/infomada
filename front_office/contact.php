<?php session_start();?>
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
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="../assets/js/Tailwind.js"></script>
  <link rel="stylesheet" href="../assets/css/contact.css"> 
  <link rel="stylesheet" href="../assets/css/all.min.css"> 

  <!-- MENU DÉROULANT PROFIL -->
  <style>
    header.scrolled {
      background-color: rgba(0, 64, 128, 0.85);
      backdrop-filter: blur(10px);
      padding-top: 1rem;
      padding-bottom: 1rem;
      box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
    
    .profil-menu {
      position: relative;
      display: inline-block;
    }

    .profil-menu .username {
      cursor: pointer;
      background-color: rgba(255, 255, 255, 0.1);
      padding: 0.5rem 1rem;
      border-radius: 9999px;
      font-weight: 600;
      transition: background-color 0.2s;
    }

    .profil-menu:hover .username {
        background-color: rgba(255, 255, 255, 0.2);
    }

    .profil-menu .dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      background-color: white;
      min-width: 220px;
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
      z-index: 1;
      border-radius: 0.5rem;
      padding: 1rem;
      margin-top: 0.5rem;
      color: #333;
      text-align: center;
    }
    
    .profil-menu:hover .dropdown-content {
      display: block;
    }

    .dropdown-content img {
      border-radius: 50%;
      margin: 0 auto 1rem auto;
      border: 3px solid #004080;
    }

    .dropdown-content a {
      color: #004080;
      padding: 10px 12px;
      text-decoration: none;
      display: block;
      font-weight: 500;
      border-radius: 0.25rem;
      transition: background-color 0.2s, color 0.2s;
    }

    .dropdown-content a:hover {
      background-color: #f1f5f9;
      color: #0c4a6e;
    }
  </style>

</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased">

  <!-- HEADER -->
  <header id="site-header" class="bg-primary-local sticky top-0 z-50 text-white px-4 sm:px-6 lg:px-8 py-10 transition-all duration-300">
    <div class="container mx-auto flex justify-between items-center">
      <a href="../index.php" class="font-bold text-2xl tracking-tight">INFOMADA</a>
      <nav class="hidden md:flex items-center gap-6">
        <a href="../index.php" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200">Accueil</a>
        <a href="../index.php#cours" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200">Cours</a>
        <span class="text-white font-semibold border-b-2 border-sky-400 pb-1">Contact</span>
        <a href="about.php" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200">A propos</a>
        
        <div class="relative">
          <input type="search" placeholder="Rechercher..." class="bg-primary-darker-local rounded-full pl-10 pr-4 py-2 text-sm text-white placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-400 transition-all duration-300 w-48 focus:w-56" />
          <svg class="w-5 h-5 text-slate-300 absolute -mt-2 left-3 -translate-y-1/2" viewBox="0 0 24 24" fill="currentColor">
            <path d="M15.5 14h-.79l-.28-.27a6.471 6.471 0 001.48-5.34C15.17 6.01 12.16 3 8.58 3S2 6.01 2 9.58 5.01 16.17 8.58 16.17c1.61 0 3.09-.59 4.22-1.57l.27.28v.79l4.25 4.25c.39.39 1.02.39 1.41 0s.39-1.02 0-1.41L15.5 14zM8.58 14c-2.44 0-4.42-1.99-4.42-4.42S6.14 5.17 8.58 5.17 13 7.16 13 9.58 11.02 14 8.58 14z"></path>
          </svg>
        </div>
        
        <!-- CONNEXION -->
        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="profil-menu">
            <span class="username"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <div class="dropdown-content">
              <?php
                $profile_pic = '../uploads/' . htmlspecialchars($_SESSION['user_id']) . '.jpg';
                
                if (!file_exists($profile_pic)) {
                    $profile_pic = '../assets/img/incognito.png'; 
                }
              ?>
              <img src="<?php echo $profile_pic; ?>?v=<?php echo time(); ?>" alt="Photo de profil" width="80" height="80" style="object-fit: cover;">
              <a href="profil.php">Modifier le profil</a>
              <!-- <a href="../certificats.php">Voir mes certificats</a> -->
              <a href="../action/logout.php">Déconnexion</a>
            </div>
          </div>
        <?php else: ?>
          <a href="login_user.php" class="bg-white text-primary-local font-semibold px-5 py-2 rounded-md hover:bg-slate-200 hover:-translate-y-0.5 transition-all duration-200">Connexion</a>
        <?php endif; ?>
        
      </nav>
      <button class="md:hidden text-white">
        <i class="fas fa-bars text-2xl"></i>
      </button>
    </div>
  </header>

  <main>
    <div class="max-w-6xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h1 class="text-4xl font-extrabold tracking-tight text-primary">Entrer en contact</h1>
        <p class="mt-3 text-lg text-slate-600">Nous sommes là pour répondre à toutes vos questions.</p>
      </div>
      <div class="flex flex-col lg:flex-row gap-12 lg:gap-16">
        <div class="lg:w-2/5">
          <h2 class="text-2xl font-bold text-slate-900 mb-6">Nos Coordonnées</h2>
          <p class="text-slate-600 mb-8">N'hésitez pas à nous contacter directement via les informations ci-dessous. Nous vous répondrons dans les plus brefs délais.</p>
          <div class="space-y-6">
            <div class="flex items-start gap-4">
              <i class="fas fa-map-marker-alt text-red-500 text-xl w-6 text-center mt-1"></i>
              <div>
                <strong class="text-slate-900">Adresse</strong><br>
                <a href="https://www.google.com/maps/search/?api=1&query=Anosizato+Andrefana,+Antananarivo,+Madagascar" 
                target="_blank" 
                rel="noopener noreferrer" 
                class="text-slate-600 hover:text-primary transition-colors">
               Anosizato Andrefana, Antananarivo, Madagascar
             </a>
              </div>
            </div>
            <div class="flex items-start gap-4">
              <i class="fas fa-phone text-green-500 text-xl w-6 text-center mt-1"></i>
              <div>
                <strong class="text-slate-900">Téléphone</strong><br>
                <a href="tel:+261344503943" class="text-slate-600 hover:text-primary transition-colors">+261 34 45 039 43</a>
              </div>
            </div>
          </div>
          <div class="mt-12">
            <h3 class="font-bold text-slate-900 mb-3">Suivez-nous</h3>
            <div class="flex space-x-7">
              <a href="https://web.facebook.com/infomadamdg" target="_blank" class="text-slate-500 text-2xl hover:text-sky-600 transform hover:scale-110 transition-all">
                <i class="fab fa-facebook-f"></i>
              </a>
              <a href="https://wa.me/261344503943" target="_blank" class="text-slate-500 text-2xl hover:text-green-500 transform hover:scale-110 transition-all">
                <i class="fab fa-whatsapp"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="lg:w-3/5 bg-white p-8 sm:p-10 rounded-2xl border-2 bordure-conteneur-animee">
          <h2 class="text-2xl font-bold text-slate-900 mb-8">Contactez-nous</h2>
          <form action="../action/send_message.php" method="POST" class="space-y-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
              <div class="relative">
                <input type="text" name="Nom" id="Nom" placeholder=" " required class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-sky-500 appearance-none focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary peer" />
                <label for="nom" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1">
                  Nom</label>
              </div>
              <div class="relative">
                <input type="text" name="Prénom" id="Prénom" placeholder=" " class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-sky-500 appearance-none focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary peer" />
                <label for="Prénom" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1">
                  Prénom</label>
              </div>
            </div>
            <div class="relative">
              <input 
                type="email" 
                name="email" 
                id="email" 
                placeholder=" " 
                required 
                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-sky-500 appearance-none focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary peer transition-colors duration-200"
              />
              <label for="email" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1">
                Votre Email</label>
            </div>
            <div class="relative">
              <input type="text" name="Sujet" id="Sujet" placeholder=" " required class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-sky-500 appearance-none focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary peer" />
              <label for="Sujet" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1">
                Sujet</label>
            </div>
            <div class="relative">
              <textarea name="message" id="message" rows="4" placeholder=" " required class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-sky-500 appearance-none focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary peer"></textarea>
              <label for="message" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-4 scale-75 top-4 z-10 origin-[0] bg-white px-2 peer-focus:px-2 peer-focus:text-primary peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-6 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1">
                Votre message</label>
            </div>
            <div class="text-right pt-2">
              <button type="submit" class="inline-flex items-center bg-primary-darker-local text-white font-bold px-8 py-3 rounded-lg shadow-md hover:bg-primary-darker transform hover:-translate-y-0.5 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="fas fa-paper-plane mr-2"></i>
                Envoyer le message
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
  
  <!-- FOOTER -->
  <footer class="bg-primary-local text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-8">
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div>
          <h2 class="text-white font-bold text-xl mb-4">INFOMADA</h2>
          <p class="text-sm leading-relaxed">Une plateforme de formation moderne et certifiée, pensée pour révéler votre plein potentiel.</p>
        </div>
        <div class="lg:pl-12">
          <h4 class="text-white font-semibold mb-4 tracking-wide uppercase text-sm">Navigation</h4>
          <ul class="space-y-2 text-sm">
            <li><a href="../index.php" class="hover:text-white hover:pl-2 transition-all">🏠 Accueil</a></li>
            <li><a href="../index.php#cours" class="hover:text-white hover:pl-2 transition-all">📘 Formations</a></li>
            <li><a href="../certificats.php" class="hover:text-white hover:pl-2 transition-all">🎓 Certificats</a></li>
            <li><a href="contact.php" class="hover:text-white hover:pl-2 transition-all">📨 Contact</a></li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-4 tracking-wide uppercase text-sm">Contact</h4>
          <ul class="space-y-2 text-sm">
            <li class="flex items-start">
              <i class="fas fa-phone mt-1 mr-3 text-slate-400"></i>
              <span>+261 34 45 039 43</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-map-marker-alt mt-1 mr-3 text-slate-400"></i>
              <span>Anosizato, Antananarivo, Madagascar</span>
            </li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-4 tracking-wide uppercase text-sm">Suivez-nous</h4>
          <div class="flex space-x-4">
            <a href="https://web.facebook.com/infomadamdg" target="_blank" class="text-xl hover:text-sky-400 transform hover:scale-110 transition-all">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://wa.me/261344503943" target="_blank" class="text-xl hover:text-green-400 transform hover:scale-110 transition-all">
              <i class="fab fa-whatsapp"></i>
            </a>
          </div>
        </div>
      </div>
      <div class="mt-0.5 pt-1 border-t border-slate-800 text-center text-slate-500 text-sm">
        <p>© 2025 INFOMADA — Tous droits réservés.</p>
      </div>
    </div>
  </footer>
  
  <script src="../assets/js/script.js"></script>

</body>
</html>