// Header pour changement au scroll
window.addEventListener('scroll', function() {
  const header = document.getElementById('site-header');
  if (header) {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }
});

// Disparaître preloader quand page chargée
window.addEventListener('load', function() {
  const preloader = document.getElementById('preloader');
  if (preloader) {
    // Déclencher transition disparition
    preloader.classList.add('hidden');
  }
});