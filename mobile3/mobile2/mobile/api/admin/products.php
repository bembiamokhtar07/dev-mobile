<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

$admin = api_admin_from_bearer($pdo);
if (!$admin) {
    api_error('Non autorise.', 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Method not allowed.', 405);
}

$data = api_read_json();
$title = trim((string) ($data['title'] ?? ''));
$description = trim((string) ($data['description'] ?? ''));
$imageUrl = trim((string) ($data['image_url'] ?? ''));
$price = (float) ($data['price'] ?? 0);
$discount = max(0, min(100, (int) ($data['discount_percent'] ?? 0)));
$stock = max(0, (int) ($data['stock'] ?? 0));
$category = trim((string) ($data['category'] ?? ''));
$icon = trim((string) ($data['icon'] ?? ''));

if ($title === '' || $description === '' || $imageUrl === '' || $price <= 0) {
    api_error('Donnees invalides.');
}
if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
    api_error('URL image invalide.');
}

$stmt = $pdo->prepare(
    'INSERT INTO products (title, description, image_url, price, discount_percent, stock, category, icon, created_at)
     VALUES (:title, :description, :image_url, :price, :discount, :stock, :category, :icon, :created_at)'
);
$stmt->execute([
    ':title' => $title,
    ':description' => $description,
    ':image_url' => $imageUrl,
    ':price' => $price,
    ':discount' => $discount,
    ':stock' => $stock,
    ':category' => $category === '' ? 'Artisanat' : $category,
    ':icon' => $icon === '' ? '🧵' : $icon,
    ':created_at' => date('c'),
]);

api_ok(['message' => 'Produit ajoute avec succes.', 'id' => (int) $pdo->lastInsertId()]);
