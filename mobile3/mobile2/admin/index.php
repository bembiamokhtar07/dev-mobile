<?php
declare(strict_types=1);
require __DIR__ . '/../config.php';

if (empty($_SESSION['admin_logged'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim((string) ($_POST['action'] ?? '')) === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM order_items WHERE product_id = :id')->execute([':id' => $id]);
            $del = $pdo->prepare('DELETE FROM products WHERE id = :id');
            $del->execute([':id' => $id]);
            $pdo->commit();
            if ($del->rowCount() > 0) {
                $_SESSION['flash'] = 'Produit supprime.';
                $remaining = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
                if ($remaining === 0) {
                    file_put_contents(__DIR__ . '/../data/.skip_demo_autofill', date('c'));
                }
            } else {
                $_SESSION['flash_error'] = 'Produit introuvable (deja supprime?).';
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['flash_error'] = 'Suppression impossible. Reessayez.';
        }
    }
    header('Location: index.php');
    exit;
}

$products = $pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();
$orders = $pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 10')->fetchAll();
$flash = $_SESSION['flash'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a href="../index.php" class="brand">Admin - <?= e(APP_NAME) ?></a>
            <div class="row">
                <a class="pill" href="logout.php">Se deconnecter</a>
            </div>
        </div>
    </header>
    <main class="container">
        <?php if ($flash): ?><div class="notice"><?= e($flash) ?></div><?php endif; ?>
        <?php if ($flashError): ?><div class="notice error"><?= e($flashError) ?></div><?php endif; ?>

        <section class="panel">
            <h2>Ajouter un produit</h2>
            <form method="post" action="save_product.php">
                <input type="hidden" name="id" value="0">
                <div class="form-grid">
                    <div>
                        <label>Titre</label>
                        <input type="text" name="title" required>
                    </div>
                    <div>
                        <label>Image URL</label>
                        <input type="url" name="image_url" required>
                    </div>
                    <div>
                        <label>Prix (<?= e(CURRENCY_CODE) ?>)</label>
                        <input type="number" step="0.01" min="0" name="price" required>
                    </div>
                    <div>
                        <label>Reduction (%)</label>
                        <input type="number" min="0" max="100" name="discount_percent" value="0" required>
                    </div>
                    <div>
                        <label>Stock restant</label>
                        <input type="number" min="0" name="stock" value="1" required>
                    </div>
                    <div style="grid-column:1/-1">
                        <label>Description</label>
                        <textarea name="description" rows="3" required></textarea>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit" style="margin-top:12px">Enregistrer</button>
            </form>
            <p class="muted" style="margin-top:16px">
                <?= e('Ajouter d\'un coup les 10 articles de demonstration (sans effacer le catalogue).') ?>
            </p>
            <form method="post" action="seed_demo_products.php" onsubmit="return confirm('Ajouter les 10 produits demo ?');">
                <input type="hidden" name="confirm" value="1">
                <button class="btn btn-dark" type="submit">Inserer les 10 produits demo</button>
            </form>
        </section>

        <section class="panel">
            <h2>Produits</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Prix</th>
                            <th>Reduction</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= (int) $product['id'] ?></td>
                            <td><?= e($product['title']) ?></td>
                            <td><?= number_format((float) $product['price'], 2) ?> <?= e(CURRENCY_CODE) ?></td>
                            <td><?= (int) $product['discount_percent'] ?>%</td>
                            <td><?= (int) $product['stock'] ?></td>
                            <td>
                                <a class="btn btn-dark" href="save_product.php?id=<?= (int) $product['id'] ?>">Modifier</a>
                                <form method="post" action="index.php" class="inline-delete-form">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                    <button class="btn btn-danger" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <h2>Dernieres commandes</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Telephone</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td><?= e($order['customer_phone']) ?></td>
                            <td><?= number_format((float) $order['total'], 2) ?> <?= e(CURRENCY_CODE) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>

