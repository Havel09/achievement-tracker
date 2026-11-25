<?php
/*
 * Cadre commun à toutes les pages : l'en-tête, le titre et les fichiers CSS et JS.
 * $content contient la page à afficher (library.php, game.php ou 404.php).
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>achievement-tracker</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header>
        <a href="/">achievement-tracker</a>

        <?php /* Ce lien apparaît dès qu'un joueur a été consulté (SteamID gardé en session) */ ?>
        <?php if (!empty($_SESSION['steamId'])): ?>
            <a href="/?pseudo=<?= echapper($_SESSION['steamId']) ?>">Ma bibliothèque</a>
        <?php endif; ?>
    </header>

    <main>
        <?= $content ?>
    </main>

    <script src="/assets/app.js"></script>
</body>
</html>
