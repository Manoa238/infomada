document.addEventListener('DOMContentLoaded', function () {
    // Sélection éléments du page 
    const formulairePaiement = document.getElementById('payment-form');
    const fenetreSucces = document.getElementById('success-modal');
    const boutonOK = document.getElementById('ok-button');
    const conteneurPrincipal = document.getElementById('content-wrapper');
    const colonneRecu = document.getElementById('receipt-column');
    const boutonTelecharger = document.getElementById('download-receipt-btn'); 

    let paiementSoumis = false;

    // Écouteur pour soumission de formulaire_paiement
    formulairePaiement.addEventListener('submit', function (evenement) {
        evenement.preventDefault(); // Empêcher la rechargement du page
        
        if (paiementSoumis) {
            return; // Empêche l'envoi multiple du formulaire
        }

        const donneesFormulaire = new FormData(formulairePaiement);

        // Envoie données en arrière-plan
        fetch(formulairePaiement.action, {
            method: 'POST',
            body: donneesFormulaire
        })
        .then(reponse => reponse.json()) // S'attend à recevoir réponse au format JSON
        .then(donnees => {
            if (donnees.success) {
                paiementSoumis = true;
                fenetreSucces.classList.add('open'); // Afficher fenêtre de succès
                activerBoutonTelechargement(); 
            } else {
                alert('Erreur : ' + donnees.message);
            }
        })
        .catch(erreur => {
            // Erreur connexion 
            console.error('Erreur de communication:', erreur);
            alert('Une erreur technique est survenue. Veuillez réessayer.');
        });
    });

    // Écouteur du bouton OK (succès)
    boutonOK.addEventListener('click', function () {
        fenetreSucces.classList.remove('open'); // Close fenêtre
        // Modification grille pour ajout reçu
        conteneurPrincipal.classList.remove('md:grid-cols-2');
        conteneurPrincipal.classList.add('lg:grid-cols-3');
        
        // Colonne reçu
        colonneRecu.classList.remove('hidden');
        setTimeout(() => {
            colonneRecu.classList.add('visible');
        }, 10);
    });

    // Bouton de téléchargement
    function activerBoutonTelechargement() {
        // Récupèrer valeurs entrées par l'utilisateur
        const referenceTransaction = document.getElementById('transaction_id').value;
        const numeroTelephone = document.getElementById('phone_number').value;

        // MàJ informations du reçu visible sur la page
        document.getElementById('receipt-date').textContent = new Date().toLocaleString('fr-FR');
        document.getElementById('receipt-phone').textContent = numeroTelephone;
        document.getElementById('receipt-ref').textContent = referenceTransaction;
        
        // Lien de téléchargement 
        const urlTelechargement = `../action/telecharger_recu.php?ref=${encodeURIComponent(referenceTransaction)}`;
        
        // Attache lien au bouton
        boutonTelecharger.href = urlTelechargement;
        boutonTelecharger.setAttribute('download', `recu_paiement_${referenceTransaction}.pdf`);
        boutonTelecharger.classList.remove('bg-slate-400', 'cursor-not-allowed', 'pointer-events-none', 'shadow-inner');
        boutonTelecharger.classList.add('bg-primary-local', 'hover:bg-primary-dark-local', 'hover:-translate-y-1', 'shadow-lg');
    }
});