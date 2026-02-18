document.addEventListener('DOMContentLoaded', () => {
    const currentPagePath = window.location.pathname;

    // PAGE ADMIN
    if (currentPagePath.includes('admin_page.php')) {
        const navLinks = document.querySelectorAll('aside .nav-link');
        const pages = document.querySelectorAll('main .page');

        function showPage(targetId) {
            pages.forEach(page => page.classList.add('hidden'));
            const activePage = document.getElementById(targetId);
            if (activePage) {
                activePage.classList.remove('hidden');
            }
            navLinks.forEach(link => {
                link.classList.remove('bg-accent-light', 'text-primary', 'font-semibold');
                link.classList.add('text-secondary', 'hover:bg-accent-light');

                if (link.getAttribute('data-target') === targetId) {
                    link.classList.add('bg-accent-light', 'text-primary', 'font-semibold');
                    link.classList.remove('text-secondary');
                }
            });
        }

        navLinks.forEach(link => {
            if (link.getAttribute('href') === '#') {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const target = link.getAttribute('data-target');
                    if (target) {
                        showPage(target);
                        window.history.pushState(null, '', '#' + target);
                    }
                });
            }
        });

        const initialTarget = window.location.hash.substring(1) || 'dashboard';
        showPage(initialTarget);
    }

    // admin_page_formation.php
    if (currentPagePath.includes('admin_page_formation.php')) {
        
        //  MODALES & FORMULAIRES
        const btnAjouterCours = document.getElementById('btn-ajouter-cours');
        const formulaireAjoutCours = document.getElementById('formulaire-ajout-cours');
        const btnParametres = document.getElementById('btn-parametres');
        const btnFermerModale = document.getElementById('btn-fermer-modale');
        const modaleParametres = document.getElementById('modale-parametres');

        if (btnAjouterCours) { btnAjouterCours.addEventListener('click', () => { formulaireAjoutCours.classList.toggle('open'); }); }
        if (btnParametres) { btnParametres.addEventListener('click', () => { modaleParametres.classList.add('open'); }); }
        if (btnFermerModale) { btnFermerModale.addEventListener('click', () => { modaleParametres.classList.remove('open'); }); }
        if (modaleParametres) { modaleParametres.addEventListener('click', (e) => { if (e.target === modaleParametres) { modaleParametres.classList.remove('open'); } }); }

        //  CONFIRMATION DE SUPPRESSION
        const modaleConfirmation = document.getElementById('modale-confirmation');
        const titreModaleConfirmation = document.getElementById('titre-modale-confirmation');
        const btnConfirmerSuppression = document.getElementById('btn-confirmer-suppression');
        const btnAnnulerSuppression = document.getElementById('btn-annuler-suppression');
        const allDeleteLinks = document.querySelectorAll('.lien-supprimer-cours, .confirm-delete-link');
        let deleteUrlToConfirm = null;

        const openConfirmModal = (event) => {
            event.preventDefault();
            const link = event.currentTarget;
            deleteUrlToConfirm = link.href;
            const type = link.dataset.type || 'cours';
            let message = '';
            switch (type) {
                case 'categorie': message = 'Voulez-vous vraiment<br>supprimer cette catégorie ?'; break;
                case 'niveau': message = 'Voulez-vous vraiment<br>supprimer ce niveau ?'; break;
                default: message = 'Voulez-vous vraiment<br>supprimer ce cours ?'; break;
            }
            if(titreModaleConfirmation) titreModaleConfirmation.innerHTML = message;
            if (modaleConfirmation) modaleConfirmation.classList.add('open');
        };

        const closeConfirmModal = () => {
            if (modaleConfirmation) modaleConfirmation.classList.remove('open');
            deleteUrlToConfirm = null;
        };

        allDeleteLinks.forEach(link => link.addEventListener('click', openConfirmModal));
        
        if (btnConfirmerSuppression) {
            btnConfirmerSuppression.addEventListener('click', () => {
                if (deleteUrlToConfirm) window.location.href = deleteUrlToConfirm;
            });
        }
        if (btnAnnulerSuppression) btnAnnulerSuppression.addEventListener('click', closeConfirmModal);
        
        if (modaleConfirmation) {
            modaleConfirmation.addEventListener('click', (event) => {
                if (event.target === modaleConfirmation) closeConfirmModal();
            });
        }
        
        //ROUVRIR MODALE APRÈS AJOUT RÉUSSI
        
        // Lire paramètres URL
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'success_cat' || status === 'success_niv') {
            if (modaleParametres) {
                modaleParametres.classList.add('open');
            }
        }
    }
});