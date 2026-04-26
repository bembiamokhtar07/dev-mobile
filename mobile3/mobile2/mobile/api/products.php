<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_error('Method not allowed.', 405);
}

$products = $pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();
$out = [];
foreach ($products as $product) {
    $out[] = [
        'id' => (int) $product['id'],
        'title' => (string) $product['title'],
        'description' => (string) $product['description'],
        'image_url' => (string) $product['image_url'],
        'price' => (float) $product['price'],
        'discount_percent' => (int) $product['discount_percent'],
        'stock' => (int) $product['stock'],
        'category' => (string) ($product['category'] ?? 'Artisanat'),
        'icon' => (string) ($product['icon'] ?? '🧵'),
    ];
}

api_ok(['products' => $out, 'currency' => CURRENCY_CODE]);
