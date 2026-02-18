<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion - Admin INFOMADA</title>
  <link rel="stylesheet" href="../assets/css/index.css">
  <link rel="stylesheet" href="../assets/css/all.min.css">
  
  <script src="../assets/js/login.js"></script>
  <script src="../assets/js/Tailwind.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#004080',
            'primary-darker': '#003366',
          }
        }
      }
    }
  </script>
</head>
<body class="h-full flex items-center justify-center font-sans antialiased bg-slate-200">

  <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-xl shadow-lg">
    
    <div class="text-center">
      <h1 class="text-3xl font-bold text-primary-local ">Admin INFOMADA</h1>
      <p class="mt-2 text-slate-600">Veuillez vous connecter pour continuer</p>
    </div>

    <form id="login-form" class="mt-8 space-y-6" method="POST">
      <div class="relative">
        <input id="username" name="username" type="text" required
               class="peer h-10 w-full border-b-2 border-slate-300 text-slate-900 placeholder-transparent focus:outline-none focus:border-primary">
        <label for="username"
               class="absolute left-0 -top-3.5 text-slate-900 text-primary-local
                text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-slate-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-primary peer-focus:text-sm">
               Nom de l'administrateur
        </label>
      </div>

      <div class="relative mt-4">
          <input id="password" name="password" type="password" required
                class="peer h-10 w-full border-b-2 border-slate-300 text-slate-900 placeholder-transparent focus:outline-none focus:border-primary pr-10">
          <label for="password"
                class="absolute left-0 -top-3.5 text-slate-900 text-primary-local text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-slate-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-primary peer-focus:text-sm">
            Mot de passe
          </label>
          <!-- ICÔNE-->
          <span id="togglePassword" class="absolute right-0 top-2.5 text-slate-500 cursor-pointer">
              <i id="eye-icon" class="fas fa-eye-slash"></i>
          </span>
      </div>
      
      <!-- MESSAGE D'ERREUR -->
      <div id="error-message" class="text-red-500 text-sm text-center font-semibold hidden"></div>

      <div class="text-center">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-gradient-to-r bg-primary-local
                               text-white font-semibold text-sm px-4 py-2 rounded-lg shadow-lg hover:shadow-xl 
                               hover:-translate-y-0.5 transition-all duration-300">
          <i class="fas fa-sign-in-alt mr-2"></i>
          Se connecter
        </button>
      </div>
    </form>
  </div>
  
</body>
</html>