function alternerChampsContenu(elementSelect) {
    const parentElement = elementSelect.closest('.lecon-item');
    const champFichier = parentElement.querySelector('.lecon-fichier-field');
    const champInputFichier = champFichier.querySelector('input[type="file"]');

    if (elementSelect.value === 'texte') {
        champFichier.style.display = 'none'; // Cacher champ
        champInputFichier.setAttribute('disabled', 'disabled'); // Désactiver input
        champInputFichier.value = ''; // Effacer fichier sélectionné
    } else { // pdf/video
        champFichier.style.display = 'block'; // Afficher champ_fichier
        champInputFichier.removeAttribute('disabled'); // Activer input fichier
    }
}

function ajouterNouvelleLecon() {
    const conteneur = document.getElementById('lecons-container');
    const premierElement = document.querySelector('.lecon-item');
    const nouvelElement = premierElement.cloneNode(true); // Cloner avec tous événements & attributs

    // Effacer valeurs & réinitialiser new leçon
    nouvelElement.querySelectorAll('input, textarea').forEach(el => el.value = '');
    nouvelElement.querySelector('select[name="lecon_type[]"]').value = 'texte'; // Default = 'texte'

    const inputFichierClone = nouvelElement.querySelector('.lecon-fichier-field input[type="file"]');

    inputFichierClone.setAttribute('disabled', 'disabled'); // Default Input fichier désactivé
    nouvelElement.querySelector('.lecon-fichier-field').style.display = 'none'; // Default Champ_fichier caché

    conteneur.appendChild(nouvelElement);
}

function supprimerLecon(elementBouton) {
    const elementASupprimer = elementBouton.closest('.lecon-item');
    if (document.querySelectorAll('.lecon-item').length > 1) { // Ne pas supprimer dernière leçon
        elementASupprimer.remove();
    } else {
        alert("Vous devez avoir au moins une leçon.");
    }
}

// Initialiser champs au chargement page pour éléments existants
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.lecon-item select[name="lecon_type[]"]').forEach(select => {
        select.closest('.lecon-item').querySelector('.lecon-texte-field label').textContent = 'Description / Contenu texte';
        alternerChampsContenu(select); 
    });
});
