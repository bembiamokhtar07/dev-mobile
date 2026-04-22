<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$_SESSION['cart'] = $_SESSION['cart'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));
    $id = (int) ($_POST['product_id'] ?? 0);
    if ($action === 'remove' && $id > 0) {
        unset($_SESSION['cart'][$id], $_SESSION['cart'][(string) $id]);
    }
    if ($action === 'update' && $id > 0) {
        $qty = max(0, (int) ($_POST['quantity'] ?? 0));
        if ($qty === 0) {
            unset($_SESSION['cart'][$id], $_SESSION['cart'][(string) $id]);
        } else {
            $_SESSION['cart'][$id] = $qty;
        }
    }
    cart_normalize_session();
    header('Location: cart.php');
    exit;
}

$ids = array_keys($_SESSION['cart']);
$items = [];
$total = 0.0;

if ($ids) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    $found = [];
    foreach ($products as $product) {
        $pid = (int) $product['id'];
        $found[$pid] = true;
        $qty = min((int) ($_SESSION['cart'][$pid] ?? 0), (int) $product['stock']);
        if ($qty < 1) {
            unset($_SESSION['cart'][$pid]);
            continue;
        }
        $_SESSION['cart'][$pid] = $qty;
        $linePrice = product_final_price($product) * $qty;
        $total += $linePrice;
        $items[] = ['product' => $product, 'qty' => $qty, 'line_price' => $linePrice];
    }
    foreach ($ids as $kid) {
        $k = (int) $kid;
        if (!isset($found[$k])) {
            unset($_SESSION['cart'][$k]);
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panier - <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a href="index.php" class="brand"><?= e(APP_NAME) ?></a>
            <div class="row">
                <a class="pill" href="boutique.php">Boutique</a>
                <a class="pill" href="admin/login.php">Admin</a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="panel">
            <h2>Votre panier</h2>
            <?php if (!$items): ?>
                <p class="muted">Votre panier est vide.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantite</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= e($item['product']['title']) ?></td>
                                <td><?= number_format(product_final_price($item['product']), 2) ?> <?= e(CURRENCY_CODE) ?></td>
                                <td>
                                    <form method="post" class="row">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= (int) $item['product']['id'] ?>">
                                        <input type="number" min="0" max="<?= (int) $item['product']['stock'] ?>" name="quantity" value="<?= (int) $item['qty'] ?>" style="max-width:90px">
                                        <button class="btn btn-dark" type="submit">Mettre a jour</button>
                                    </form>
                                </td>
                                <td><?= number_format((float) $item['line_price'], 2) ?> <?= e(CURRENCY_CODE) ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?= (int) $item['product']['id'] ?>">
                                        <button class="btn btn-danger" type="submit">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <h3>Total: <?= number_format($total, 2) ?> <?= e(CURRENCY_CODE) ?></h3>
                <a class="btn btn-primary" href="checkout.php">Passer la commande</a>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>

