document.addEventListener('DOMContentLoaded', () => {

    // GÉRER AFFICHAGE MDP
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', () => {
            const eyeIcon = document.getElementById('eye-icon');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.classList.toggle('fa-eye-slash');
            eyeIcon.classList.toggle('fa-eye');
        });
    }

    // GESTION CONNEXION
    if (sessionStorage.getItem('isLoggedIn') === 'true') {
        window.location.href = 'admin_page.php';
    }
  
    const loginForm = document.getElementById('login-form');
    const errorMessage = document.getElementById('error-message');
  
    loginForm.addEventListener('submit', (event) => {
        event.preventDefault(); // Empêche le rechargement du page

        const usernameInput = document.getElementById('username').value;
        const passwordInput = document.getElementById('password').value;
    
        const CORRECT_USERNAME = 'admin';
        const CORRECT_PASSWORD = 'password123';
  
    //    Sensible à la casse
        if (usernameInput.toLowerCase() === CORRECT_USERNAME.toLowerCase() && passwordInput === CORRECT_PASSWORD) {
            sessionStorage.setItem('isLoggedIn', 'true');
            errorMessage.classList.add('hidden'); // Cacher message d'erreur
            window.location.href = 'admin_page.php'; 
        } else {S
            errorMessage.textContent = 'Nom de l\'administrateur ou mot de passe incorrect.';
            errorMessage.classList.remove('hidden');
        }
    });
});