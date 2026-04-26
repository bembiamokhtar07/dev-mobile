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

$productColumns = $pdo->query('PRAGMA table_info(products)')->fetchAll();
$productColumnNames = [];
foreach ($productColumns as $column) {
    $productColumnNames[(string) ($column['name'] ?? '')] = true;
}
if (!isset($productColumnNames['category'])) {
    $pdo->exec("ALTER TABLE products ADD COLUMN category TEXT NOT NULL DEFAULT 'Artisanat'");
}
if (!isset($productColumnNames['icon'])) {
    $pdo->exec("ALTER TABLE products ADD COLUMN icon TEXT NOT NULL DEFAULT '🧵'");
}

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

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        created_at TEXT NOT NULL
    )'
);

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS admin_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        admin_id INTEGER NOT NULL,
        token_hash TEXT NOT NULL UNIQUE,
        created_at TEXT NOT NULL,
        expires_at TEXT NOT NULL,
        FOREIGN KEY(admin_id) REFERENCES admins(id) ON DELETE CASCADE
    )'
);

$adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($adminCount === 0) {
    $seedAdmin = $pdo->prepare(
        'INSERT INTO admins (username, password_hash, created_at)
         VALUES (:username, :password_hash, :created_at)'
    );
    $seedAdmin->execute([
        ':username' => ADMIN_USERNAME,
        ':password_hash' => password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT),
        ':created_at' => date('c'),
    ]);
}

/**
 * 10 produits de demonstration. Chaque ligne:
 * titre, description, URL image, prix (MRU), reduction %, stock, categorie, icone
 *
 * @return list<array{0:string,1:string,2:string,3:float,4:int,5:int,6:string,7:string}>
 */
function demo_product_samples(): array
{
    return [
        [
            'Melhafa traditionnelle mauritanienne',
            'Tissu elegant porte lors des ceremonies, confection artisanale locale.',
            'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=1000&q=80',
            8900.00,
            5,
            10,
            'Textile',
            '🧣',
        ],
        [
            'Daraha (boubou mauritanien)',
            'Boubou ample pour hommes, broderie traditionnelle du Sahara.',
            'https://images.unsplash.com/photo-1594938328870-9623159c8c99?auto=format&fit=crop&w=1000&q=80',
            9800.00,
            0,
            8,
            'Vetement',
            '👕',
        ],
        [
            'The vert Gunpowder premium',
            'The utilise dans le rituel de l ataya, parfum intense.',
            'https://images.unsplash.com/photo-1597481499750-3e6b22637e12?auto=format&fit=crop&w=1000&q=80',
            2200.00,
            10,
            20,
            'The & Cafe',
            '🍵',
        ],
        [
            'Theiere ataya en metal',
            'Theiere solide pour service du the mauritanien.',
            'https://images.unsplash.com/photo-1583623025817-d180a2221d0a?auto=format&fit=crop&w=1000&q=80',
            3900.00,
            5,
            12,
            'Ustensiles',
            '🫖',
        ],
        [
            'Tapis maure tisse main',
            'Tapis decoratif inspire des motifs nomades de Mauritanie.',
            'https://images.unsplash.com/photo-1616627547584-bf28cee262db?auto=format&fit=crop&w=1000&q=80',
            15500.00,
            0,
            4,
            'Maison',
            '🧶',
        ],
        [
            'Bijou traditionnel en argent',
            'Pendentif artisanal porte lors des fetes et mariages.',
            'https://images.unsplash.com/photo-1617038220317-876f19d6c3f4?auto=format&fit=crop&w=1000&q=80',
            5200.00,
            8,
            9,
            'Bijoux',
            '📿',
        ],
        [
            'Encens bakhour saharien',
            'Parfum d ambiance tres utilise dans les maisons mauritaniennes.',
            'https://images.unsplash.com/photo-1603006905393-c2fc09f1383c?auto=format&fit=crop&w=1000&q=80',
            1500.00,
            0,
            25,
            'Parfum',
            '🪔',
        ],
        [
            'Coussin cuir artisanal',
            'Coussin fabrique a Nouakchott, style saharien moderne.',
            'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1000&q=80',
            3600.00,
            0,
            6,
            'Decoration',
            '🛋️',
        ],
        [
            'Miel naturel de l Adrar',
            'Miel local collecte dans les oasis de l Adrar.',
            'https://images.unsplash.com/photo-1587049352851-8d4e89133924?auto=format&fit=crop&w=1000&q=80',
            3100.00,
            7,
            14,
            'Epicerie',
            '🍯',
        ],
        [
            'Dattes premium des oasis',
            'Selection de dattes moelleuses, consommation quotidienne et fetes.',
            'https://images.unsplash.com/photo-1603048588665-791ca8aea617?auto=format&fit=crop&w=1000&q=80',
            2400.00,
            0,
            22,
            'Epicerie',
            '🌴',
        ],
    ];
}

function insert_demo_products(PDO $pdo): void
{
    $seed = $pdo->prepare(
        'INSERT INTO products (title, description, image_url, price, discount_percent, stock, category, icon, created_at)
         VALUES (:title, :description, :image_url, :price, :discount, :stock, :category, :icon, :created_at)'
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
            ':category' => $item[6],
            ':icon' => $item[7],
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

function admin_find_by_username(PDO $pdo, string $username): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function admin_create_token(PDO $pdo, int $adminId): string
{
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare(
        'INSERT INTO admin_tokens (admin_id, token_hash, created_at, expires_at)
         VALUES (:admin_id, :token_hash, :created_at, :expires_at)'
    );
    $stmt->execute([
        ':admin_id' => $adminId,
        ':token_hash' => $tokenHash,
        ':created_at' => date('c'),
        ':expires_at' => date('c', time() + 60 * 60 * 24 * 7),
    ]);
    return $token;
}

function api_admin_from_bearer(PDO $pdo): ?array
{
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.+)/i', $auth, $matches)) {
        return null;
    }
    $token = trim($matches[1]);
    if ($token === '') {
        return null;
    }
    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare(
        'SELECT a.*
         FROM admin_tokens t
         JOIN admins a ON a.id = t.admin_id
         WHERE t.token_hash = :token_hash
           AND t.expires_at > :now
         LIMIT 1'
    );
    $stmt->execute([
        ':token_hash' => $tokenHash,
        ':now' => date('c'),
    ]);
    $admin = $stmt->fetch();
    return $admin ?: null;
}

