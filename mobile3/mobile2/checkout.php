<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$_SESSION['cart'] = $_SESSION['cart'] ?? [];

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
        $line = product_final_price($product) * $qty;
        $total += $line;
        $items[] = ['product' => $product, 'qty' => $qty, 'line_price' => $line];
    }
    foreach ($ids as $kid) {
        $k = (int) $kid;
        if (!isset($found[$k])) {
            unset($_SESSION['cart'][$k]);
        }
    }
}

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$items) {
        $error = 'Panier vide.';
    } else {
        $name = trim($_POST['customer_name'] ?? '');
        $phone = trim($_POST['customer_phone'] ?? '');
        $address = trim($_POST['customer_address'] ?? '');

        if ($name === '' || $phone === '' || $address === '') {
            $error = 'Veuillez remplir tous les champs de livraison.';
        } else {
            $pdo->beginTransaction();
            try {
                foreach ($items as $item) {
                    $pid = (int) $item['product']['id'];
                    $qty = (int) $item['qty'];
                    $check = $pdo->prepare('SELECT stock FROM products WHERE id = :id');
                    $check->execute([':id' => $pid]);
                    $stockNow = (int) $check->fetchColumn();
                    if ($stockNow < $qty) {
                        throw new RuntimeException('Stock insuffisant pour ' . $item['product']['title']);
                    }
                }

                $orderStmt = $pdo->prepare(
                    'INSERT INTO orders (customer_name, customer_phone, customer_address, total, created_at)
                     VALUES (:name, :phone, :address, :total, :created_at)'
                );
                $orderStmt->execute([
                    ':name' => $name,
                    ':phone' => $phone,
                    ':address' => $address,
                    ':total' => $total,
                    ':created_at' => date('c'),
                ]);

                $orderId = (int) $pdo->lastInsertId();
                $itemStmt = $pdo->prepare(
                    'INSERT INTO order_items (order_id, product_id, title, unit_price, quantity)
                     VALUES (:order_id, :product_id, :title, :unit_price, :quantity)'
                );
                $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - :qty WHERE id = :id');

                foreach ($items as $item) {
                    $itemStmt->execute([
                        ':order_id' => $orderId,
                        ':product_id' => (int) $item['product']['id'],
                        ':title' => $item['product']['title'],
                        ':unit_price' => product_final_price($item['product']),
                        ':quantity' => (int) $item['qty'],
                    ]);
                    $stockStmt->execute([
                        ':qty' => (int) $item['qty'],
                        ':id' => (int) $item['product']['id'],
                    ]);
                }

                $pdo->commit();
                $_SESSION['cart'] = [];
                $success = 'Merci. Votre commande a ete enregistree avec succes.';
            } catch (Throwable $e) {
                $pdo->rollBack();
                $error = 'Commande echouee: ' . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validation - <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a href="index.php" class="brand"><?= e(APP_NAME) ?></a>
            <div class="row">
                <a class="pill" href="boutique.php">Boutique</a>
                <a class="pill" href="cart.php">Panier</a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="panel">
            <h2>Finaliser votre commande</h2>
            <p class="muted">Aucune information personnelle n est demandee avant cette etape.</p>
            <?php if ($success): ?><div class="notice"><?= e($success) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>

            <?php if (!$success): ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Quantite</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= e($item['product']['title']) ?></td>
                                <td><?= (int) $item['qty'] ?></td>
                                <td><?= number_format((float) $item['line_price'], 2) ?> <?= e(CURRENCY_CODE) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <h3>Total a payer: <?= number_format($total, 2) ?> <?= e(CURRENCY_CODE) ?></h3>

                <form method="post" class="panel">
                    <div class="form-grid">
                        <div>
                            <label>Nom complet</label>
                            <input type="text" name="customer_name" required>
                        </div>
                        <div>
                            <label>Telephone</label>
                            <input type="text" name="customer_phone" required>
                        </div>
                    </div>
                    <div style="margin-top:12px">
                        <label>Adresse de livraison</label>
                        <textarea name="customer_address" rows="3" required></textarea>
                    </div>
                    <div style="margin-top:12px">
                        <button class="btn btn-primary" type="submit">Confirmer la commande</button>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>

