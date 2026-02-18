document.addEventListener('DOMContentLoaded', function() {
    //  CONFIRMATION DE SUPPRESSION_PAIEMENT

    // Sélectionne éléments modale
    const modaleConfirmation = document.getElementById('modale-confirmation');
    const titreModale = document.getElementById('titre-modale-confirmation');
    const btnConfirmer = document.getElementById('btn-confirmer-suppression');
    const btnAnnuler = document.getElementById('btn-annuler-suppression');
    
    const liensSuppression = document.querySelectorAll('.lien-supprimer-paiement');

    // Stocker URL_lien
    let lienASupprimer = null;

    // Ecouteur événement/lien_suppression
    liensSuppression.forEach(lien => {
        lien.addEventListener('click', function(event) {
            // Empêcher redirection
            event.preventDefault();

            // Stocker URL suppression
            lienASupprimer = this.href;

            // Récupèrer email_user
            const emailApprenant = this.getAttribute('data-email');
            titreModale.textContent = `Supprimer le paiement de ${emailApprenant} ?`;
            modaleConfirmation.classList.add('open');
        });
    });

    // Ecouteur bouton OUI
    btnConfirmer.addEventListener('click', function() {
        if (lienASupprimer) {
            window.location.href = lienASupprimer;
        }
    });

    // Ecouteur bouton NON 
    btnAnnuler.addEventListener('click', function() {
        modaleConfirmation.classList.remove('open');
        lienASupprimer = null; // Reinit variable
    });

    // Close modale si user clic en dehors boîte dialogue
    modaleConfirmation.addEventListener('click', function(event) {
        if (event.target === modaleConfirmation) {
            modaleConfirmation.classList.remove('open');
            lienASupprimer = null;
        }
    });
});