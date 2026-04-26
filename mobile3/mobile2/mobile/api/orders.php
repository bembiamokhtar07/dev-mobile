<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Method not allowed.', 405);
}

$data = api_read_json();
$name = trim((string) ($data['customer_name'] ?? ''));
$phone = trim((string) ($data['customer_phone'] ?? ''));
$address = trim((string) ($data['customer_address'] ?? ''));
$itemsInput = $data['items'] ?? [];

if ($name === '' || $phone === '' || $address === '') {
    api_error('Veuillez remplir tous les champs de livraison.');
}
if (!is_array($itemsInput) || $itemsInput === []) {
    api_error('Panier vide.');
}

$cart = [];
foreach ($itemsInput as $entry) {
    if (!is_array($entry)) {
        continue;
    }
    $pid = (int) ($entry['product_id'] ?? 0);
    $qty = (int) ($entry['quantity'] ?? 0);
    if ($pid > 0 && $qty > 0) {
        $cart[$pid] = ($cart[$pid] ?? 0) + $qty;
    }
}
if ($cart === []) {
    api_error('Panier invalide.');
}

$ids = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

$known = [];
$items = [];
$total = 0.0;
foreach ($products as $product) {
    $pid = (int) $product['id'];
    $known[$pid] = true;
    $qty = (int) ($cart[$pid] ?? 0);
    if ($qty < 1) {
        continue;
    }
    if ((int) $product['stock'] < $qty) {
        api_error('Stock insuffisant pour ' . (string) $product['title'] . '.');
    }
    $unit = product_final_price($product);
    $line = $unit * $qty;
    $total += $line;
    $items[] = [
        'product' => $product,
        'qty' => $qty,
        'unit' => $unit,
        'line' => $line,
    ];
}

foreach ($ids as $id) {
    if (!isset($known[$id])) {
        api_error('Produit introuvable dans le panier.');
    }
}
if ($items === []) {
    api_error('Panier vide.');
}

$pdo->beginTransaction();
try {
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
            ':title' => (string) $item['product']['title'],
            ':unit_price' => (float) $item['unit'],
            ':quantity' => (int) $item['qty'],
        ]);
        $stockStmt->execute([
            ':qty' => (int) $item['qty'],
            ':id' => (int) $item['product']['id'],
        ]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    api_error('Commande echouee: ' . $e->getMessage(), 500);
}

api_ok([
    'message' => 'Merci, votre commande est enregistree.',
    'order_id' => $orderId,
    'total' => round($total, 2),
]);
