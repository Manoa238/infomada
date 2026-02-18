<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Notre Engagement - INFOMADA</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&family=Inter:wght@400;500;600&family=Lora:wght@500;600&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="../assets/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/about.css">
  <script src="../assets/js/Tailwind.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    header.scrolled {
      background-color: rgba(0, 64, 128, 0.85);
      backdrop-filter: blur(10px);
      padding-top: 1rem;
      padding-bottom: 1rem;
      box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
    .profil-menu { position: relative; display: inline-block; }
    .profil-menu .username {
      cursor: pointer;
      background-color: rgba(255, 255, 255, 0.1);
      padding: 0.5rem 1rem;
      border-radius: 9999px;
      font-weight: 600;
      transition: background-color 0.2s;
    }
    .profil-menu:hover .username { background-color: rgba(255, 255, 255, 0.2); }
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
    .profil-menu:hover .dropdown-content { display: block; }
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
    .dropdown-content a:hover { background-color: #f1f5f9; color: #0c4a6e; }
  </style>
  
</head>
<body class="bg-white font-sans text-slate-700 antialiased">
    
    <header id="site-header" class="bg-primary-local sticky top-0 z-50 text-white px-4 sm:px-6 lg:px-8 py-10 transition-all duration-300">
        <div class="container mx-auto flex justify-between items-center">
          <a href="../index.php" class="font-bold text-2xl tracking-tight">INFOMADA</a>
          <nav class="hidden md:flex items-center gap-6">
            <a href="../index.php" class="hover:text-sky-200 hover:-translate-y-1 transition-all duration-200">Accueil</a>
            <a href="../index.php#cours" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200">Cours</a>
            <a href="contact.php" class="hover:text-sky-300 hover:-translate-y-1 transition-all duration-200">Contact</a>
            <span class="text-white font-semibold border-b-2 border-sky-400 pb-1">A propos</span>
            
            <div class="relative">
              <input type="search" placeholder="Rechercher..." class="bg-primary-darker-local rounded-full pl-10 pr-4 py-2 text-sm text-white placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-400 transition-all duration-300 w-48 focus:w-56" />
              <svg class="w-5 h-5 text-slate-300 absolute -mt-2 left-3 -translate-y-1/2" viewBox="0 0 24 24" fill="currentColor">
                <path d="M15.5 14h-.79l-.28-.27a6.471 6.471 0 001.48-5.34C15.17 6.01 12.16 3 8.58 3S2 6.01 2 9.58 5.01 16.17 8.58 16.17c1.61 0 3.09-.59 4.22-1.57l.27.28v.79l4.25 4.25c.39.39 1.02.39 1.41 0s.39-1.02 0-1.41L15.5 14zM8.58 14c-2.44 0-4.42-1.99-4.42-4.42S6.14 5.17 8.58 5.17 13 7.16 13 9.58 11.02 14 8.58 14z"></path>
              </svg>
            </div>
            
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
              <a href="login_user.php" class="bg-white text-primary-local font-semibold px-5 py-2 rounded-md hover:bg-slate-200 hover:-translate-y-0.5 transition-all duration-200">
                Connexion</a>
            <?php endif; ?>

          </nav>
          <!-- Menu burger pour mobile -->
          <button class="md:hidden text-white">
            <i class="fas fa-bars text-2xl"></i>
          </button>
        </div>
      </header>

  <main>
    <!-- Invitation -->
    <section class="relative min-h-[87vh] flex items-center justify-center text-center text-white">
        <div class="absolute inset-0 bg-slate-900/60 z-10"></div>
        <img src="../assets/img/bg_infomada.gif" alt="Personne concentrée apprenant sur un ordinateur portable" class="absolute inset-0 w-full h-full object-cover blur-2md">
        <div class="relative z-20 px-4 -ml-15">
            <h1 class="font-serif text-4xl font-medium tracking-tight sm:text-5xl sm:pl-18 md:pl-32 lg:pl-48">L'art de la compétence numérique.</h1>
            <p class="mt-6 max-w-2xl mx-auto text-lg leading-8 text-slate-200">
                Nous croyons que l'éducation n'est pas une destination, mais un savoir-faire qui se cultive. Bienvenue chez INFOMADA.
            </p>
        </div>
    </section>

    <!-- Engagement -->
    <section class="bg-slate-50 overflow-hidden">
        <div class="container mx-auto px-6 py-24 sm:py-32">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div class="order-2 md:order-1">
                    <h2 class="font-serif text-3xl font-medium text-slate-900 sm:text-4xl">Notre Engagement</h2>
                    <p class="mt-6 text-slate-600 leading-relaxed">
                        Chez INFOMADA, nous nous engageons à offrir plus qu'une simple certification. Nous nous engageons à bâtir une base solide pour votre carrière, à nourrir votre curiosité et à vous accompagner à chaque étape de votre transformation professionnelle. Nous sommes les artisans de votre futur numérique.
                    </p>
                    <p class="mt-8 font-cursive text-4xl text-primary">L'équipe INFOMADA</p>
                </div>
                <div class="order-1 md:order-2">
                    <img src="../assets/img/formation_Infomada.jpg" alt="Discussion stratégique entre professionnels" class="rounded-xl shadow-lg w-full h-auto">
                </div>
            </div>
        </div>
    </section>

    
     <section class="container mx-auto px-6 py-24 sm:py-32">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="font-serif text-3xl font-medium text-slate-900 sm:text-4xl">Notre Crédo</h2>
            <p class="mt-4 text-lg text-slate-600">Trois principes qui forment le cœur de notre plateforme.</p>
        </div>

        <div class="mt-20 grid lg:grid-cols-3 gap-x-12 gap-y-16">
           
            <div class="flex flex-col items-center text-center">
                <i class="fa-regular fa-gem text-4xl text-primary mb-6"></i>
                <h3 class="font-semibold text-xl text-slate-900">La Qualité avant Tout</h3>
                <p class="mt-2 text-slate-600">Chaque cours est méticuleusement conçu pour être pertinent, pratique et dispensé avec une clarté absolue, garantissant une expérience d'apprentissage supérieure.</p>
            </div>
            
            <div class="flex flex-col items-center text-center">
                <i class="fa-regular fa-handshake text-4xl text-primary mb-6"></i>
                <h3 class="font-semibold text-xl text-slate-900">La Confiance par la Preuve</h3>
                <p class="mt-2 text-slate-600">Votre travail mérite une reconnaissance digne de ce nom. Nos certificats automatiques sont la preuve tangible et fiable de votre expertise nouvellement acquise.</p>
            </div>
           
            <div class="flex flex-col items-center text-center">
                <i class="fa-regular fa-paper-plane text-4xl text-primary mb-6"></i>
                <h3 class="font-semibold text-xl text-slate-900">L'Ambition comme Moteur</h3>
                <p class="mt-2 text-slate-600">Nous soutenons votre ambition en vous donnant les moyens de la réaliser. Votre parcours est le nôtre ; votre succès est notre plus grande fierté.</p>
            </div>
        </div>
    </section>


    <section class="container mx-auto text-center px-6 py-24 sm:py-32">
        <h2 class="font-serif text-3xl font-medium text-slate-900 sm:text-4xl">Commencez votre chapitre.</h2>
        <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-slate-600">Le chemin vers l'expertise est une succession de pas. Faites le premier aujourd'hui.</p>
        <div class="mt-10 flex items-center justify-center gap-x-6">
            <a href="../index.php#cours" class="rounded-md bg-primary px-8 py-3 text-base font-semibold text-white shadow-sm hover:bg-primary-darker focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all duration-300">
                Parcourir les cours
            </a>
        </div>
    </section>
  </main>

  <!-- FOOTER -->
  <footer class="bg-primary-local text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-8">
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div class="md:col-span-2 lg:col-span-1">
          <h2 class="text-white font-bold text-xl mb-4">INFOMADA</h2>
          <p class="text-sm leading-relaxed">Une plateforme de formation moderne et certifiée, pensée pour révéler votre plein potentiel.</p>
          <blockquote class="mt-4 border-l-4 border-slate-700 pl-4 text-sm text-slate-400 italic">
            “La solution à vos défis informatiques.”
          </blockquote>
        </div>
        <div class="lg:pl-12">
          <h4 class="text-white font-semibold mb-4 tracking-wide uppercase text-sm">Navigation</h4>
        
          <ul class="space-y-2 text-sm">
            <li><a href="../index.php" class="hover:text-white hover:pl-2 transition-all duration-200">🏠 Accueil</a></li>
            <li><a href="../index.php#cours" class="hover:text-white hover:pl-2 transition-all duration-200">📘 Formations</a></li>
            <li><a href="../certificats.php" class="hover:text-white hover:pl-2 transition-all duration-200">🎓 Certificats</a></li>
            <li><a href="contact.php" class="hover:text-white hover:pl-2 transition-all duration-200">📨 Contact</a></li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-4 tracking-wide uppercase text-sm">Contact</h4>
          <ul class="space-y-2 text-sm">
            <li class="flex items-start">
              <i class="fas fa-phone mt-1 mr-3 text-slate-400"></i><span>+261 34 45 039 43</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-map-marker-alt mt-1 mr-3 text-slate-400"></i><span>Anosizato, Antananarivo, Madagascar</span>
            </li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-4 tracking-wide uppercase text-sm">Suivez-nous</h4>
          <div class="flex space-x-4">
            <a href="https://web.facebook.com/infomadamdg" target="_blank" rel="noopener noreferrer" class="text-xl hover:text-sky-400 transform hover:scale-110 transition-all duration-200"><i class="fab fa-facebook-f"></i></a>
            <a href="https://wa.me/261344503943" target="_blank" class="text-xl hover:text-green-400 transform hover:scale-110 transition-all duration-200"><i class="fab fa-whatsapp"></i></a>
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