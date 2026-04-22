<?php
declare(strict_types=1);

$dataDir = __DIR__ . '/data';
$sessionDir = $dataDir . '/sessions';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}
if (!is_dir($sessionDir)) {
    mkdir($sessionDir, 0777, true);
}
if (is_writable($sessionDir)) {
    session_save_path($sessionDir);
}
session_start();

const APP_NAME = 'tourathne';
const CURRENCY_CODE = 'MRO';
const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD = 'admin123';

$dbPath = $dataDir . '/app.sqlite';

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec('PRAGMA foreign_keys = ON');

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        image_url TEXT NOT NULL,
        price REAL NOT NULL,
        discount_percent INTEGER NOT NULL DEFAULT 0,
        stock INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL
    )'
);

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_name TEXT NOT NULL,
        customer_phone TEXT NOT NULL,
        customer_address TEXT NOT NULL,
        total REAL NOT NULL,
        created_at TEXT NOT NULL
    )'
);

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        unit_price REAL NOT NULL,
        quantity INTEGER NOT NULL,
        FOREIGN KEY(order_id) REFERENCES orders(id)
    )'
);

/**
 * 10 produits de demonstration (patrimoine, artisanat). Chaque ligne:
 * titre, description, URL image, prix (MRO), reduction %, stock
 *
 * @return list<array{0:string,1:string,2:string,3:float,4:int,5:int}>
 */
function demo_product_samples(): array
{
    return [
        [
            'Vase en terre cuite grave',
            'Vase ou poterie traditionnelle, motifs geometriques inspires de l architecture du desert.',
            'https://images.unsplash.com/photo-1565193566174-5f6dc9fc216e?auto=format&fit=crop&w=900&q=80',
            4850.00,
            10,
            6,
        ],
        [
            'Montre de poche vintage',
            'Boitier metal, mecanisme d epoque, bon etat de marche.',
            'https://images.unsplash.com/photo-1509048191080-d2e8e7f3d4e1?auto=format&fit=crop&w=900&q=80',
            3200.00,
            0,
            4,
        ],
        [
            'Sculpture en pierre tendre',
            'Piece artisanale sculptee a la main, patine naturelle.',
            'https://images.unsplash.com/photo-1577083552431-6e5fd75a5f62?auto=format&fit=crop&w=900&q=80',
            6200.00,
            15,
            3,
        ],
        [
            'Tapis noue main',
            'Laine naturelle, motifs berberes, grand format salon.',
            'https://images.unsplash.com/photo-1586075010923-2dd4570fb338?auto=format&fit=crop&w=900&q=80',
            12500.00,
            5,
            2,
        ],
        [
            'Bracelet argent cisel',
            'Bijou argent avec motifs traditionnels, taille ajustable.',
            'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=900&q=80',
            2100.00,
            0,
            12,
        ],
        [
            'Coffret en bois sculpte',
            'Coffret de rangement, bois dur, decorations faconnees au ciseau.',
            'https://images.unsplash.com/photo-1611486212557-88d67ddb6404?auto=format&fit=crop&w=900&q=80',
            3800.00,
            8,
            5,
        ],
        [
            'Dallah en cuivre martele',
            'Cafe traditionnel, cuivre rouge, anse et bec ouvrages.',
            'https://images.unsplash.com/photo-1514228742587-6bafd8b8502d?auto=format&fit=crop&w=900&q=80',
            1650.00,
            0,
            8,
        ],
        [
            'Corbeille en vannerie',
            'Tressage sparte ou roseau, utile decoration.',
            'https://images.unsplash.com/photo-1606800052052-a08af7148866?auto=format&fit=crop&w=900&q=80',
            950.00,
            0,
            15,
        ],
        [
            'Pendentif pierre et argent',
            'Pierre semi-precieuse sertie, chainette argent fournie.',
            'https://images.unsplash.com/photo-1617038220317-876f19d6c3f4?auto=format&fit=crop&w=900&q=80',
            2750.00,
            12,
            7,
        ],
        [
            'Encadrement calligraphie artisanale',
            'Motifs geometriques peints sur support papier, cadre bois.',
            'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a2?auto=format&fit=crop&w=900&q=80',
            4400.00,
            0,
            4,
        ],
    ];
}

function insert_demo_products(PDO $pdo): void
{
    $seed = $pdo->prepare(
        'INSERT INTO products (title, description, image_url, price, discount_percent, stock, created_at)
         VALUES (:title, :description, :image_url, :price, :discount, :stock, :created_at)'
    );
    $now = date('c');
    foreach (demo_product_samples() as $item) {
        $seed->execute([
            ':title' => $item[0],
            ':description' => $item[1],
            ':image_url' => $item[2],
            ':price' => $item[3],
            ':discount' => $item[4],
            ':stock' => $item[5],
            ':created_at' => $now,
        ]);
    }
}

$demoSeedMarker = $dataDir . '/.demo_seeded';
$skipDemoAutofill = $dataDir . '/.skip_demo_autofill';
$count = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
// Marque la base comme deja initialisée si elle contient déjà des lignes
if ($count > 0 && !is_file($demoSeedMarker)) {
    file_put_contents($demoSeedMarker, date('c'));
}
// Catalogue vide : réinjecter les produits demo sauf si l’admin a vidé le catalogue volontairement (.skip_demo_autofill)
if ($count === 0 && !is_file($skipDemoAutofill)) {
    insert_demo_products($pdo);
    if (!is_file($demoSeedMarker)) {
        file_put_contents($demoSeedMarker, date('c'));
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function product_final_price(array $product): float
{
    $discount = max(0, min(100, (int) $product['discount_percent']));
    $price = (float) $product['price'];
    return round($price * (100 - $discount) / 100, 2);
}

function cart_normalize_session(): void
{
    $cart = $_SESSION['cart'] ?? [];
    if (!is_array($cart)) {
        $_SESSION['cart'] = [];
        return;
    }
    $out = [];
    foreach ($cart as $key => $qty) {
        $pid = (int) $key;
        if ($pid < 1) {
            continue;
        }
        $q = (int) $qty;
        if ($q > 0) {
            $out[$pid] = $q;
        }
    }
    $_SESSION['cart'] = $out;
}

/** Retire les produits inexistants (supprimes en base) pour que le compteur panier reste juste. */
function cart_prune_missing_products(PDO $pdo): void
{
    cart_normalize_session();
    $ids = array_keys($_SESSION['cart'] ?? []);
    if ($ids === []) {
        return;
    }
    $ids = array_map('intval', $ids);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $known = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $row) {
        $known[(int) $row] = true;
    }
    foreach ($ids as $kid) {
        if (!isset($known[$kid])) {
            unset($_SESSION['cart'][$kid]);
        }
    }
}

function cart_count(): int
{
    cart_normalize_session();
    return array_sum($_SESSION['cart']);
}

