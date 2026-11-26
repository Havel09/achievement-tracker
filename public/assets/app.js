// Barre de recherche des succès (page d'un jeu).

const search = document.getElementById('search');

// Sur les autres pages, la barre de recherche n'existe pas : on ne fait rien
if (search) {
    const achievements = document.querySelectorAll('.achievement'); // tous les succès de la liste
    const noResult = document.getElementById('no-result'); // message « Aucun succès trouvé »

    // Met un texte en minuscules et sans accents : "evasion" trouvera aussi "Évasion".
    // normalize('NFD') sépare les lettres de leurs accents, puis le replace enlève les accents.
    function clean(text) {
        return text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    // Affiche seulement les succès qui contiennent le texte recherché
    function filterAchievements() {
        const query = clean(search.value.trim());
        let visibleCount = 0;

        for (const achievement of achievements) {
            // textContent = tout le texte du succès (son nom et sa description)
            const match = clean(achievement.textContent).includes(query);

            // display = '' remet l'affichage normal, 'none' cache le succès
            achievement.style.display = match ? '' : 'none';

            if (match) {
                visibleCount++;
            }
        }

        // Le message n'apparaît que si aucun succès ne correspond
        noResult.hidden = visibleCount > 0;
    }

    // À chaque fois que le texte de la barre change, on relance le filtre
    search.addEventListener('input', filterAchievements);
}
