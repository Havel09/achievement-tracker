<?php
// Petite fonction pour afficher du texte sans risque (protège contre le HTML injecté)
$e = fn(string $texte): string => htmlspecialchars($texte, ENT_QUOTES, 'UTF-8');
?>

<h1>Ma bibliothèque Steam</h1>

<form method="get" action="/">
    <label for="pseudo">Pseudo Steam ou SteamID</label>
    <input type="text" id="pseudo" name="pseudo" value="<?= $e($pseudo) ?>" required>
    <button type="submit">Afficher ma bibliothèque</button>
</form>

<?php if ($error !== null): ?>

    <p class="error"><?= $e($error) ?></p>

<?php elseif ($pseudo !== ''): ?>

    <p><?= count($games) ?> jeux trouvés</p>

    <ul class="games">
        <?php foreach ($games as $game): ?>
            <li>
                <a href="/game/<?= $e($steamId) ?>/<?= $game->appId ?>">
                    <?php if ($game->iconUrl !== ''): ?>
                        <img src="<?= $e($game->iconUrl) ?>" alt="" width="32" height="32">
                    <?php endif; ?>
                    <?= $e($game->name) ?>
                </a>
                <small><?= round($game->playtimeMinutes / 60, 1) ?> h de jeu</small>
            </li>
        <?php endforeach; ?>
    </ul>

<?php endif; ?>
