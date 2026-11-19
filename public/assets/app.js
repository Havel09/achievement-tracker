// Barre de recherche des succès (page d'un jeu)
const search = document.getElementById('search');

// Sur les autres pages, la barre n'existe pas : on ne fait rien
if (search) {
    const achievements = document.querySelectorAll('.achievement');
    const noResult = document.getElementById('no-result');

    // Minuscules et sans accents : taper "evasion" trouve aussi "Évasion"
    const clean = (text) => text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    search.addEventListener('input', () => {
        const query = clean(search.value.trim());
        let visible = 0;

        achievements.forEach((achievement) => {
            // textContent = le nom et la description du succès
            const match = clean(achievement.textContent).includes(query);

            achievement.style.display = match ? '' : 'none';

            if (match) {
                visible++;
            }
        });

        // Message affiché seulement quand rien ne correspond
        noResult.hidden = visible > 0;
    });
}