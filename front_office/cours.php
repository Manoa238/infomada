<?php
if (!isset($liste_cours)) {
    echo '<p class="text-center text-red-500 mt-8">Erreur : La liste des cours n\'est pas disponible.</p>';
    return;
}
?>

<section id="cours" class="py-20 md:py-28">
  <div class="container mx-auto px-4 sm:px-6 lg:px-8">

    <div class="relative mb-12">
      <div class="text-center">
        <h2 class="text-3xl font-bold text-slate-800">Nos Formations Populaires</h2>
        <p class="mt-2 text-slate-500">Lancez-vous dans une nouvelle compétence dès aujourd'hui.</p>
        <p class="mt-2 text-slate-500">Veuillez trouver tous les cours sur le catalogue.</p>
      </div>
      <div class="mt-6 text-center md:absolute md:right-0 md:top-1/2 md:-translate-y-1/2 md:mt-0">
        <a href="front_office/catalogue_cours.php" 
           class="group inline-flex items-center justify-center gap-3 bg-gradient-to-r from-sky-500 to-indigo-600 
           text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
          <span>Catalogue de cours</span>
          <i class="fa-solid fa-arrow-right transition-transform duration-300 group-hover:translate-x-1"></i>
        </a>
      </div>
    </div>

    <?php if (isset($db_error) && !empty($db_error)): ?>
        <p class="text-center text-red-500 mt-8"><?php echo htmlspecialchars($db_error); ?></p>
    <?php elseif (empty($liste_cours)): ?>
        <p class="text-center text-slate-500 mt-16">Formation indisponible. Revenez bientôt !</p>
    <?php else: ?>
        <!-- 4 colonnes de cours -->
        <div class="mt-16 grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
          <?php foreach ($liste_cours as $cours): ?>
              
              <!-- hauteur carte  -->
              <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 overflow-hidden group flex flex-col">                
                <!--Image -->
                <div class="h-40 bg-white flex items-center justify-center overflow-hidden border-b border-slate-200">
                  <!-- default img -->
                    <?php
                        $image_path = !empty($cours['chemin_image']) 
                            ? htmlspecialchars($cours['chemin_image']) 
                            : 'assets/img/cours.jpg'; 
                    ?>
                    <!-- Affichage image, $image_path source -->
                    <img src="<?php echo $image_path; ?>" 
                         alt="Image du cours <?php echo htmlspecialchars($cours['titre']); ?>" 
                         class="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105">
                </div>
                
                <div class="p-6 flex flex-col flex-grow">
                  <h3 class="font-bold text-base text-primary-local group-hover:text-sky-600 transition-colors">
                      <?php echo htmlspecialchars($cours['titre']); ?>
                  </h3>
                  <p class="mt-2 text-sm text-slate-600 line-clamp-2 flex-grow">
                      <?php echo htmlspecialchars($cours['description']); ?>
                  </p>
                  <!-- <a href="#" class="mt-auto pt-4 inline-block font-semibold text-sm text-primary-local group-hover:underline">
                      En savoir plus →
                  </a> -->
                </div>
              </div>
          <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </div>
</section>