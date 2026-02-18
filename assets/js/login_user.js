// --- LOGIQUE EXISTANTE POUR LE PANNEAU (NE PAS SUPPRIMER)
const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

registerBtn.addEventListener('click', () => {
    container.classList.add('active');
});

loginBtn.addEventListener('click', () => {
    container.classList.remove('active');
});

// TOGGLE MOT DE PASSE 
const togglePasswordIcons = document.querySelectorAll('.toggle-password');

togglePasswordIcons.forEach(icon => {
    icon.addEventListener('click', () => {
        const passwordInput = icon.previousElementSibling;

        // Changer type d'input
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        // Changer icône d'œil
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
});