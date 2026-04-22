<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="fr" class="landing-html">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?> — Patrimoine mauritanien</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="page-landing">
    <header class="landing-nav">
        <div class="landing-nav-inner">
            <span class="landing-logo"><?= e(APP_NAME) ?></span>
            <nav class="landing-nav-links">
                <a class="landing-link" href="#heritage">Heritage</a>
                <a class="landing-link" href="#vivant">Traditions</a>
                <a class="pill" href="cart.php">Panier (<?= cart_count() ?>)</a>
            </nav>
        </div>
    </header>

    <section class="landing-hero" id="accueil">
        <div class="landing-hero-bg" aria-hidden="true"></div>
        <div class="landing-hero-content">
            <p class="landing-eyebrow"><?= e('Terre de poetes, de tisserands et d artisans') ?></p>
            <h1 class="landing-title"><?= e(APP_NAME) ?></h1>
            <p class="landing-subtitle">
                <?= e('Celebrons le patrimoine mauritanien : une memoire gravee dans le verbe hassani, les motifs des etoffes et la patience des mains qui faconnent la terre et le metal.') ?>
            </p>
            <div class="landing-hero-actions">
                <a class="btn btn-landing-primary" href="boutique.php"><?= e('Entrer dans la boutique') ?></a>
                <a class="btn btn-landing-ghost" href="#heritage"><?= e('Decouvrir l heritage') ?></a>
            </div>
        </div>
        <div class="landing-scroll-hint" aria-hidden="true">
            <span></span>
        </div>
    </section>

    <section class="landing-section" id="heritage">
        <div class="container">
            <h2 class="landing-section-title">Une identite racontee</h2>
            <p class="landing-section-lead">
                <?= e('Le patrimoine mauritanien est un symbole d\'authenticite et d\'identite. Il regroupe les traditions comme la poesie hassaniya, les habits traditionnels et l\'artisanat. C\'est un heritage precieux que nous devons preserver pour les generations futures.') ?>
            </p>
            <div class="landing-grid-3">
                <article class="landing-card">
                    <span class="landing-card-icon" aria-hidden="true">&#10077;</span>
                    <h3>La voix hassaniya</h3>
                    <p class="muted">
                        <?= e('Poemes et recits qui traversent le Sahara : la langue porte l histoire des tribus, des oasis et du desert.') ?>
                    </p>
                </article>
                <article class="landing-card">
                    <span class="landing-card-icon" aria-hidden="true">&#9826;</span>
                    <h3>Melhafa et teintures</h3>
                    <p class="muted">
                        <?= e('Etoffes aux couleurs du soleil et de la terre, brodees ou teintes selon des gestes transmis de mere en fille.') ?>
                    </p>
                </article>
                <article class="landing-card">
                    <span class="landing-card-icon" aria-hidden="true">&#9670;</span>
                    <h3>Artisanat du feu et du bois</h3>
                    <p class="muted">
                        <?= e('Poteries, vannerie, martelage du cuivre : des ateliers ou le savoir-faire repond encore au rythme de la main.') ?>
                    </p>
                </article>
            </div>
        </div>
    </section>

    <section class="landing-section landing-section-alt" id="vivant">
        <div class="container landing-split">
            <div class="landing-quote-block">
                <blockquote class="landing-quote">
                    <?= e('Heritage n est pas seulement ce que l on garde dans une vitrine : c est ce que l on fait vivre chaque jour, en parlant, en cousant, en creant.') ?>
                </blockquote>
                <p class="landing-quote-author">— <?= e(APP_NAME) ?></p>
            </div>
            <div class="landing-invite">
                <h2>Votre pas vers la boutique</h2>
                <p class="muted">
                    <?= e('Des pieces choisies pour honorer cet univers : objets d art, decor et trouvailles inspirees du patrimoine.') ?>
                </p>
                <p class="landing-fine-print muted"><?= e('Paiement a la commande — livraison selon vos coordonnees.') ?></p>
            </div>
        </div>
    </section>

    <footer class="landing-footer">
        <div class="container landing-footer-inner">
            <span class="landing-logo landing-logo-footer"><?= e(APP_NAME) ?></span>
            <div class="landing-footer-links">
                <a href="admin/login.php">Administration</a>
            </div>
        </div>
    </footer>
</body>
</html>
