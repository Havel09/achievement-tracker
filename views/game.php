<?php
/*
 * Page d'un jeu : en-tête, progression, recherche et checklist des succès.
 * Variables reçues du GameController : $steamId, $game, $error, $total, $unlocked
 */
?>

<?php /* Retour à la bibliothèque du même joueur, sans avoir à retaper son SteamID */ ?>
<p><a href="/?pseudo=<?= echapper($steamId) ?>">Retour à la bibliothèque</a></p>

<?php if ($error !== null): ?>

    <?php /* Un problème est survenu (jeu introuvable, profil privé...) : on affiche le message */ ?>
    <p class="error"><?= echapper($error) ?></p>

<?php else: ?>

    <?php /* Progression en pourcentage, calculée par le modèle Game */ ?>
    <?php $progress = $game->getProgress(); ?>

    <?php /* --- En-tête du jeu --- */ ?>
    <section class="game-header">
        <?php if ($game->iconUrl !== ''): ?>
            <img src="<?= echapper($game->iconUrl) ?>" alt="" width="64" height="64">
        <?php endif; ?>

        <h1><?= echapper($game->name) ?></h1>
        <p><?= round($game->playtimeMinutes / 60, 1) ?> h de jeu</p>
        <p><a href="<?= echapper($game->getWikiUrl()) ?>" target="_blank" rel="noopener">Voir le wiki du jeu</a></p>
    </section>

    <?php if ($total === 0): ?>

        <p>Ce jeu n'a pas de succès.</p>

    <?php else: ?>

        <?php /* --- Progression : texte + barre --- */ ?>
        <section class="progression">
            <p><?= $unlocked ?> / <?= $total ?> succès (<?= $progress ?> %)</p>
            <progress max="100" value="<?= $progress ?>"></progress>
        </section>

        <?php /* --- Barre de recherche (le filtre est fait par public/assets/app.js) --- */ ?>
        <input type="search" id="search" placeholder="Rechercher un succès..." aria-label="Rechercher un succès">
        <p id="no-result" hidden>Aucun succès trouvé.</p>

        <?php /* --- Checklist : une ligne par succès --- */ ?>
        <ul class="achievements">
            <?php foreach ($game->achievements as $achievement): ?>
                <?php /* Les classes unlocked / locked permettent de styler selon l'état */ ?>
                <li class="achievement <?= $achievement->unlocked ? 'unlocked' : 'locked' ?>">
                    <?php /* Case cochée si débloqué (elle ne se clique pas : c'est Steam qui décide) */ ?>
                    <input type="checkbox" disabled <?= $achievement->unlocked ? 'checked' : '' ?>>

                    <?php if ($achievement->iconUrl !== ''): ?>
                        <img src="<?= echapper($achievement->iconUrl) ?>" alt="" width="48" height="48">
                    <?php endif; ?>

                    <div>
                        <strong><?= echapper($achievement->name) ?></strong>
                        <p>
                            <?php if ($achievement->description !== ''): ?>
                                <?= echapper($achievement->description) ?>
                            <?php elseif ($achievement->hidden): ?>
                                Succès caché
                            <?php endif; ?>
                        </p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

    <?php endif; ?>

<?php endif; ?>
