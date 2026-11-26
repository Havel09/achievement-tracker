<?php
/*
 * Page d'accueil : formulaire du pseudo + liste des jeux du joueur.
 * Variables reçues du LibraryController : $pseudo, $steamId, $games, $error
 */
?>

<h1>Ma bibliothèque Steam</h1>

<?php /* Le formulaire renvoie vers la même page, avec le pseudo dans l'adresse (/?pseudo=...) */ ?>
<form method="get" action="/">
    <label for="pseudo">Entrez votre SteamID ou le lien de votre profil</label>
    <input type="text" id="pseudo" name="pseudo" value="<?= echapper($pseudo) ?>" required>
    <button type="submit">Afficher ma bibliothèque</button>
</form>

<?php if ($error !== null): ?>

    <?php /* Un problème est survenu (pseudo inconnu, profil privé...) : on affiche le message */ ?>
    <p class="error"><?= echapper($error) ?></p>

<?php elseif ($pseudo !== ''): ?>

    <?php /* Un joueur a été trouvé : on affiche ses jeux */ ?>
    <p><?= count($games) ?> jeux trouvés</p>

    <ul class="games">
        <?php foreach ($games as $game): ?>
            <li>
                <?php /* Le lien mène à la page du jeu : /game/SteamID/appId */ ?>
                <a href="/game/<?= echapper($steamId) ?>/<?= $game->appId ?>">
                    <?php if ($game->iconUrl !== ''): ?>
                        <img src="<?= echapper($game->iconUrl) ?>" alt="" width="32" height="32">
                    <?php endif; ?>
                    <?= echapper($game->name) ?>
                </a>

                <?php /* Les minutes sont converties en heures, arrondies à une décimale */ ?>
                <small><?= round($game->playtimeMinutes / 60, 1) ?> h de jeu</small>
            </li>
        <?php endforeach; ?>
    </ul>

<?php endif; ?>
