<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

cart_prune_missing_products($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
    $id = (int) ($_POST['product_id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT id, stock FROM products WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();
        if ($product) {
            $_SESSION['cart'] = $_SESSION['cart'] ?? [];
            $current = (int) ($_SESSION['cart'][$id] ?? 0);
            if ($current < (int) $product['stock']) {
                $_SESSION['cart'][$id] = $current + 1;
                $_SESSION['flash'] = 'Produit ajoute au panier.';
            } else {
                $_SESSION['flash_error'] = 'Stock insuffisant.';
            }
        }
    }
    header('Location: boutique.php');
    exit;
}

$products = $pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();
$flash = $_SESSION['flash'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boutique — <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a href="index.php" class="brand"><?= e(APP_NAME) ?></a>
            <div class="row">
                <a class="pill" href="cart.php">Panier (<?= cart_count() ?>)</a>
                <a class="pill" href="admin/login.php">Admin</a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="hero">
            <p class="hero-tagline">Collection et artisanat</p>
            <h1><?= e('Boutique') ?></h1>
            <p class="heritage-blurb">
                <?= e('Pieces choisies au fil du desert et des ateliers : chaque objet porte une part du patrimoine mauritanien.') ?>
            </p>
        </section>

        <?php if ($flash): ?><div class="notice"><?= e($flash) ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="notice error"><?= e($flashError) ?></div><?php endif; ?>

        <section class="grid">
            <?php if (count($products) === 0): ?>
                <p class="muted" style="grid-column: 1 / -1;">
                    <?= e('Aucun produit pour le moment. Ouvrez l\'espace admin pour inserer les 10 produits demo ou ajouter un article.') ?>
                    <a class="pill" href="admin/login.php" style="display:inline-block;margin-top:10px;">Espace admin</a>
                </p>
            <?php endif; ?>
            <?php foreach ($products as $product): ?>
                <?php
                $final = product_final_price($product);
                $stock = (int) $product['stock'];
                ?>
                <article class="card">
                    <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['title']) ?>">
                    <div class="card-content">
                        <h3><?= e($product['title']) ?></h3>
                        <p class="muted"><?= e($product['description']) ?></p>
                        <div class="price-wrap">
                            <?php if ((int) $product['discount_percent'] > 0): ?>
                                <span class="old-price"><?= number_format((float) $product['price'], 2) ?> <?= e(CURRENCY_CODE) ?></span>
                                <span class="badge">-<?= (int) $product['discount_percent'] ?>%</span>
                            <?php endif; ?>
                            <span class="price"><?= number_format($final, 2) ?> <?= e(CURRENCY_CODE) ?></span>
                        </div>
                        <p>
                            <span class="badge <?= $stock > 0 ? '' : 'badge-danger' ?>">
                                <?= $stock > 0 ? 'En stock: ' . $stock : 'Rupture de stock' ?>
                            </span>
                        </p>
                        <form method="post">
                            <input type="hidden" name="action" value="add_to_cart">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <button class="btn btn-primary" type="submit" <?= $stock < 1 ? 'disabled' : '' ?>>
                                Ajouter au panier
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>
