document.addEventListener('DOMContentLoaded', function() {

    // SÉLECTION ÉLÉMENTS
    const champRecherche = document.getElementById('search-input');
    const filtreCategorie = document.getElementById('filter-category');
    const filtreNiveau = document.getElementById('filter-level');
    const cartesDeCours = document.querySelectorAll('.course-card');
    const messageAucunResultat = document.getElementById('no-results-message');
  
    // FILTRE COURS
    function filtrerCours() {
      // Valeurs filtres
      const termeRecherche = champRecherche.value.toLowerCase(); // recherche insensible à la casse
      const categorieSelectionnee = filtreCategorie.value;
      const niveauSelectionne = filtreNiveau.value;
      
      let coursVisibles = 0; // compteur de cours
  
      // Parcourir carte du cours
      cartesDeCours.forEach(carte => {
        // Récupère informations stockées 
        const titre = carte.dataset.title;
        const categorie = carte.dataset.category;
        const niveau = carte.dataset.level;
  
        // VÉRIFICATION CORRESPONDANCES
        const correspondanceTitre = titre.includes(termeRecherche);
        const correspondanceCategorie = (categorieSelectionnee === 'all' || categorie === categorieSelectionnee);
        const correspondanceNiveau = (niveauSelectionne === 'all' || niveau === niveauSelectionne);
  
        // Carte correspond au critères
        if (correspondanceTitre && correspondanceCategorie && correspondanceNiveau) {
          carte.style.display = 'flex'; 
          coursVisibles++; // incrémentation compteur.
        } else {
          // masquer la carte
          carte.style.display = 'none';
        }
      });
  
      // AUCUN RÉSULTAT
      if (coursVisibles === 0 && cartesDeCours.length > 0) {
        // Message d'avertissement 
        messageAucunResultat.style.display = 'block';
      } else {
        // cache
        messageAucunResultat.style.display = 'none';
      }
    }
  
    //  ÉCOUTEURS D'ÉVÉNEMENTS
    // champ de recherche
    champRecherche.addEventListener('input', filtrerCours); 
    
    // catégorie
    filtreCategorie.addEventListener('change', filtrerCours); 
    
    // niveau
    filtreNiveau.addEventListener('change', filtrerCours); 
  });