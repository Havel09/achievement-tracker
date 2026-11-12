<?php
    $e = fn(string $texte): string => htmlspecialchars($texte, ENT_QUOTES, 'UTF-8');
?>

<p><a href="/?pseudo=<?= $e($steamId) ?>">Retour à la bibliothèque</a></p>

<?php if ($error !== null): ?>

    <p class="error"><?= $e($error) ?></p>

<?php else: ?>

    <?php
    $total = count($game->achievements);
    $unlocked = count(array_filter($game->achievements, fn($a) => $a->unlocked));
    ?>

    <section class="game-header">
        <?php if ($game->iconUrl !== ''): ?>
            <img src="<?= $e($game->iconUrl) ?>" alt="" width="64" height="64">
        <?php endif; ?>

        <h1><?= $e($game->name) ?></h1>
        <p><?= round($game->playtimeMinutes / 60, 1) ?> h de jeu</p>
        <p><a href="<?= $e($game->getWikiUrl()) ?>" target="_blank" rel="noopener">Voir le wiki du jeu</a></p>
    </section>

    <?php if ($total === 0): ?>

        <p>Ce jeu n'a pas de succès.</p>

    <?php else: ?>

        <section class="progression">
            <p><?= $unlocked ?> / <?= $total ?> succès (<?= $game->getProgress() ?> %)</p>
            <progress max="100" value="<?= $game->getProgress() ?>"></progress>
        </section>

        <ul class="achievements">
            <?php foreach ($game->achievements as $achievement): ?>
                <li class="achievement <?= $achievement->unlocked ? 'unlocked' : 'locked' ?>">
                    <input type="checkbox" disabled <?= $achievement->unlocked ? 'checked' : '' ?>>

                    <?php if ($achievement->iconUrl !== ''): ?>
                        <img src="<?= $e($achievement->iconUrl) ?>" alt="" width="48" height="48">
                    <?php endif; ?>

                    <div>
                        <strong><?= $e($achievement->name) ?></strong>
                        <p>
                            <?php if ($achievement->description !== ''): ?>
                                <?= $e($achievement->description) ?>
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
