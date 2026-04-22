<?php
declare(strict_types=1);
require __DIR__ . '/../config.php';

if (empty($_SESSION['admin_logged'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $discount = max(0, min(100, (int) ($_POST['discount_percent'] ?? 0)));
    $stock = max(0, (int) ($_POST['stock'] ?? 0));

    if ($title === '' || $description === '' || $imageUrl === '' || $price < 0) {
        $_SESSION['flash'] = 'Donnees invalides.';
        header('Location: index.php');
        exit;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE products
             SET title = :title, description = :description, image_url = :image_url,
                 price = :price, discount_percent = :discount, stock = :stock
             WHERE id = :id'
        );
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':image_url' => $imageUrl,
            ':price' => $price,
            ':discount' => $discount,
            ':stock' => $stock,
            ':id' => $id,
        ]);
        $_SESSION['flash'] = 'Produit mis a jour.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO products (title, description, image_url, price, discount_percent, stock, created_at)
             VALUES (:title, :description, :image_url, :price, :discount, :stock, :created_at)'
        );
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':image_url' => $imageUrl,
            ':price' => $price,
            ':discount' => $discount,
            ':stock' => $stock,
            ':created_at' => date('c'),
        ]);
        $_SESSION['flash'] = 'Produit ajoute.';
    }

    header('Location: index.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();
if (!$product) {
    $_SESSION['flash'] = 'Produit introuvable.';
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier produit</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <main class="container">
        <section class="panel">
            <h2>Modifier le produit #<?= (int) $product['id'] ?></h2>
            <form method="post">
                <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                <div class="form-grid">
                    <div>
                        <label>Titre</label>
                        <input type="text" name="title" value="<?= e($product['title']) ?>" required>
                    </div>
                    <div>
                        <label>Image URL</label>
                        <input type="url" name="image_url" value="<?= e($product['image_url']) ?>" required>
                    </div>
                    <div>
                        <label>Prix (<?= e(CURRENCY_CODE) ?>)</label>
                        <input type="number" step="0.01" min="0" name="price" value="<?= e((string) $product['price']) ?>" required>
                    </div>
                    <div>
                        <label>Reduction (%)</label>
                        <input type="number" min="0" max="100" name="discount_percent" value="<?= (int) $product['discount_percent'] ?>" required>
                    </div>
                    <div>
                        <label>Stock restant</label>
                        <input type="number" min="0" name="stock" value="<?= (int) $product['stock'] ?>" required>
                    </div>
                    <div style="grid-column:1/-1">
                        <label>Description</label>
                        <textarea name="description" rows="3" required><?= e($product['description']) ?></textarea>
                    </div>
                </div>
                <div style="margin-top:12px" class="row">
                    <button class="btn btn-primary" type="submit">Sauvegarder</button>
                    <a class="btn btn-dark" href="index.php">Annuler</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>

